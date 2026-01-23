<?php

namespace App\Http\Controllers;

use App\Models\TransactionHeader;
use App\Models\Brand;
use App\Imports\TransactionHeaderImport;
use App\Exports\TransactionHeaderExport;
use App\Exports\TransactionHeaderOnlyExport;
use App\Jobs\LogSearchHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionHeaderController extends Controller
{
    public function index(Request $request)
    {
        // Start timing
        $startTime = microtime(true);
        
        // Check if there's any search/filter parameter
        $hasSearch = $request->has('search') && $request->search != '';
        $hasDateFrom = $request->has('date_from') && $request->date_from != '';
        $hasDateTo = $request->has('date_to') && $request->date_to != '';
        $hasFilter = $hasSearch || $hasDateFrom || $hasDateTo;
        
        // Base query with brand
        $query = TransactionHeader::with('brand')->where('tx_header.is_active', '1')->orderBy('tx_header.invoice_date', 'desc');
        
        // Only use cache when there's search/filter
        if ($hasFilter) {
            // Generate cache key based on user and search parameters
            $userId = auth()->id();
            $search = $request->get('search', '');
            $dateFrom = $request->get('date_from', '');
            $dateTo = $request->get('date_to', '');
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            $cacheKey = "header:{$userId}:{$search}:{$dateFrom}:{$dateTo}:{$perPage}:{$page}";
            
            // Try to get from cache (1 hour)
            $transactions = cache()->remember($cacheKey, now()->addHour(), function () use ($request, $query) {
                // Search by text - search in header and body
                if ($request->has('search') && $request->search != '') {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        // Search in header fields
                        $q->where('tx_header.customer_name', 'like', $search . '%')
                          ->orWhere('tx_header.chassis', 'like', $search . '%')
                          ->orWhere('tx_header.invoice_no', 'like', $search . '%')
                          ->orWhere('tx_header.wip_no', 'like', $search . '%')
                          ->orWhere('tx_header.registration_no', 'like', $search . '%')
                          ->orWhereDate('tx_header.invoice_date', '=', $search)
                          // Search in body fields using whereExists
                          ->orWhereExists(function($existsQuery) use ($search) {
                              $existsQuery->select(\DB::raw(1))
                                          ->from('tx_body')
                                          ->whereColumn('tx_body.wip_no', 'tx_header.wip_no')
                                          ->whereColumn('tx_body.invoice_no', 'tx_header.invoice_no')
                                          ->where('tx_body.is_active', '1')
                                          ->where(function($bodyWhere) use ($search) {
                                              $bodyWhere->where('tx_body.part_no', 'like', $search . '%')
                                                        ->orWhere('tx_body.wip_no', 'like', $search . '%')
                                                        ->orWhere('tx_body.invoice_no', 'like', $search . '%')
                                                        ->orWhereDate('tx_body.date_decard', '=', $search);
                                          });
                          });
                    });
                }
                
                // Filter by date range
                if ($request->has('date_from') && $request->date_from != '') {
                    $query->whereDate('tx_header.invoice_date', '>=', $request->date_from);
                }
                
                if ($request->has('date_to') && $request->date_to != '') {
                    $query->whereDate('tx_header.invoice_date', '<=', $request->date_to);
                }
                
                // Pagination
                $perPage = $request->get('per_page', 10);
                $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
                
                return $query->paginate($perPageValue)->withQueryString();
            });
            
            // Manually load bodies for each transaction using batch query to avoid N+1
            if ($transactions->count() > 0) {
                // Get all transaction keys
                $transactionKeys = $transactions->map(function($t) {
                    return $t->wip_no . '|' . $t->invoice_no;
                })->toArray();
                
                // Fetch all bodies at once
                $allBodies = \DB::table('tx_body')
                    ->where('is_active', '1')
                    ->whereIn(\DB::raw("CONCAT(wip_no, '|', invoice_no)"), $transactionKeys)
                    ->orderBy('wip_no')
                    ->orderBy('invoice_no')
                    ->orderBy('line')
                    ->get()
                    ->groupBy(function($body) {
                        return $body->wip_no . '|' . $body->invoice_no;
                    });
                
                // Assign bodies to each transaction
                foreach ($transactions as $transaction) {
                    $key = $transaction->wip_no . '|' . $transaction->invoice_no;
                    $bodies = $allBodies->get($key, collect());
                    
                    $transaction->bodies = $bodies->map(function($body) {
                        // Convert to TransactionBody model instance
                        $bodyModel = new \App\Models\TransactionBody();
                        foreach ($body as $key => $value) {
                            $bodyModel->$key = $value;
                        }
                        return $bodyModel;
                    });
                }
            }
        } else {
            // No search/filter - execute query directly without cache
            $perPage = $request->get('per_page', 10);
            $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
            $transactions = $query->paginate($perPageValue)->withQueryString();
        }
        
        // Calculate execution time
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Log search history asynchronously only if there's a search query or date filter
        if ($hasFilter) {
            LogSearchHistory::dispatch(
                auth()->id(),
                $request->get('search'),
                $request->get('date_from'),
                $request->get('date_to'),
                $executionTime,
                'H'
            );
        }
        
        return view('transactions.index', compact('transactions', 'hasFilter'));
    }

    public function showImport()
    {
        $brands = Brand::where('is_active', '1')->orderBy('brand_name')->get();
        return view('transactions.import', compact('brands'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
            ],
            'brand_id' => 'required|exists:ms_brand,brand_id',
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.max' => 'File size must not exceed 10MB.',
            'brand_id.required' => 'Please select a brand.',
            'brand_id.exists' => 'Selected brand is invalid.',
        ]);

        // Manual validation for file extension
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['csv', 'xls', 'xlsx'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->route('transactions.header.import')
                ->withErrors(['file' => 'File must be in CSV, XLS, or XLSX format.'])
                ->withInput();
        }

        try {
            $import = new TransactionHeaderImport($request->brand_id);
            Excel::import($import, $file);

            // Get custom errors from import class
            $customErrors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            
            // Get validation failures
            $failures = $import->failures();
            
            // Combine all errors
            $allErrors = [];
            
            // Add custom errors
            foreach ($customErrors as $error) {
                $allErrors[] = [
                    'row' => $error['row'],
                    'field' => $error['field'],
                    'value' => $error['value'],
                    'error' => $error['error']
                ];
            }
            
            // Add validation failures
            foreach ($failures as $failure) {
                $allErrors[] = [
                    'row' => $failure->row(),
                    'field' => $failure->attribute(),
                    'value' => $failure->values()[$failure->attribute()] ?? 'N/A',
                    'error' => implode(', ', $failure->errors())
                ];
            }
            
            if (count($allErrors) > 0) {
                // Clear cache after import (even with errors, some data might be imported)
                $this->clearTransactionCache();
                
                return redirect()->route('transactions.header.import')
                    ->with('import_errors', $allErrors)
                    ->with('success_count', $successCount)
                    ->with('error', "Import completed with {$successCount} success and " . count($allErrors) . " error(s). Please check the details below.");
            }

            // Clear cache after successful import
            $this->clearTransactionCache();

            return redirect()->route('transactions.index')
                ->with('success', "Transaction headers imported successfully! {$successCount} records imported.");
        } catch (\Illuminate\Database\QueryException $e) {
            // Clear cache on error
            $this->clearTransactionCache();
            
            // Handle SQL errors with detailed messages
            $errorDetails = $this->parseSqlErrorDetailed($e, $import);
            
            \Log::error('Import SQL Error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);
            
            return redirect()->route('transactions.header.import')
                ->with('sql_error', $errorDetails);
        } catch (\Exception $e) {
            // Clear cache on error
            $this->clearTransactionCache();
            
            \Log::error('Import Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('transactions.header.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Clear all cache after import
     * Simple and effective - ensures data consistency
     */
    private function clearTransactionCache()
    {
        cache()->flush();
        \Log::info('All cache cleared after import by user: ' . auth()->id());
    }

    private function parseSqlError($message)
    {
        // Parse common SQL errors into user-friendly messages
        if (strpos($message, 'Incorrect integer value') !== false) {
            preg_match("/Incorrect integer value: '([^']+)' for column '([^']+)'/", $message, $matches);
            if (count($matches) >= 3) {
                $value = $matches[1];
                $column = $matches[2];
                $friendlyColumn = $this->getFriendlyColumnName($column);
                return "Invalid data type: '{$value}' is not a valid number for {$friendlyColumn}. Please ensure this field contains only numeric values.";
            }
            return "Invalid data type: One or more numeric fields contain non-numeric values. Please check your data.";
        }
        
        if (strpos($message, 'Data too long for column') !== false) {
            preg_match("/Data too long for column '([^']+)'/", $message, $matches);
            if (count($matches) >= 2) {
                $column = $matches[1];
                $friendlyColumn = $this->getFriendlyColumnName($column);
                return "Data too long: The value for {$friendlyColumn} exceeds the maximum allowed length.";
            }
            return "Data too long: One or more fields exceed the maximum allowed length.";
        }
        
        if (strpos($message, 'Duplicate entry') !== false) {
            return "Duplicate entry: A record with the same WIPNO and Brand already exists.";
        }
        
        // Return original message if no pattern matches
        return "Database error occurred. Please check your data format and try again.";
    }

    private function parseSqlErrorDetailed($exception, $import = null)
    {
        $message = $exception->getMessage();
        $errors = [];
        
        // Get current row from import if available
        $currentRow = $import ? $import->currentRow : 'Unknown';
        
        // Parse Incorrect integer value
        if (strpos($message, 'Incorrect integer value') !== false) {
            preg_match_all("/Incorrect integer value: '([^']+)' for column '([^']+)' at row (\d+)/", $message, $matches, PREG_SET_ORDER);
            
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $value = $match[1];
                    $column = $match[2];
                    $row = $match[3] + 1; // +1 karena row 1 adalah header
                    $friendlyColumn = $this->getFriendlyColumnName($column);
                    
                    $errors[] = [
                        'row' => $row,
                        'field' => $friendlyColumn,
                        'value' => $value,
                        'error' => "'{$value}' is not a valid number. Please ensure this field contains only numeric values."
                    ];
                }
                return $errors;
            }
        }
        
        // Parse Data too long
        if (strpos($message, 'Data too long for column') !== false) {
            preg_match("/Data too long for column '([^']+)' at row (\d+)/", $message, $matches);
            if (count($matches) >= 3) {
                $column = $matches[1];
                $row = $matches[2] + 1; // +1 karena row 1 adalah header
                $friendlyColumn = $this->getFriendlyColumnName($column);
                
                $errors[] = [
                    'row' => $row,
                    'field' => $friendlyColumn,
                    'value' => 'Too long',
                    'error' => "The value exceeds the maximum allowed length for this field."
                ];
                return $errors;
            }
        }
        
        // Parse Duplicate entry
        // if (strpos($message, 'Duplicate entry') !== false) {
        //     preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $message, $matches);
        //     if (count($matches) >= 2) {
        //         dd($matches);
        //         $value = $matches[1];
        //         $key = $matches[2] ?? '';
                
        //         // Determine field based on key name
        //         $field = 'Unknown';
        //         $errorMsg = "A record with this value already exists. This should not happen with import. Please contact support.";
                
        //         if (strpos($key, 'unique_id') !== false) {
        //             $field = 'Unique ID';
        //             $errorMsg = "Duplicate Unique ID detected. This is a system error. Please contact support.";
        //         }

        //         // if (strpos($key, 'unique_id') !== false) {
        //         //     $field = 'Unique ID';
        //         //     $errorMsg = "Duplicate Unique ID detected. This is a system error. Please contact support.";
        //         // } elseif (strpos($key, 'invoice_no') !== false || strpos($key, 'wip_no') !== false) {
        //         //     $field = 'WIPNO + InvNo + Brand';
        //         //     $errorMsg = "A record with this WIPNO, Invoice Number, and Brand combination already exists. The record should be updated instead of creating a new one.";
        //         // }
                
        //         $errors[] = [
        //             'row' => $currentRow, // Use current row from import
        //             'field' => $field,
        //             'value' => $value,
        //             'error' => $errorMsg
        //         ];
        //         return $errors;
        //     }
        // }
        
        // Parse Incorrect date value
        if (strpos($message, 'Incorrect date value') !== false || strpos($message, 'Incorrect datetime value') !== false) {
            preg_match("/Incorrect (?:date|datetime) value: '([^']+)' for column '([^']+)' at row (\d+)/", $message, $matches);
            if (count($matches) >= 4) {
                $value = $matches[1];
                $column = $matches[2];
                $row = $matches[3] + 1; // +1 karena row 1 adalah header
                $friendlyColumn = $this->getFriendlyColumnName($column);
                
                $errors[] = [
                    'row' => $row,
                    'field' => $friendlyColumn,
                    'value' => $value,
                    'error' => "Invalid date format. Please use YYYY-MM-DD format (e.g., 2026-01-22)."
                ];
                return $errors;
            }
        }
        
        // Parse Column cannot be null
        if (strpos($message, 'cannot be null') !== false) {
            preg_match("/Column '([^']+)' cannot be null/", $message, $matches);
            if (count($matches) >= 2) {
                $column = $matches[1];
                $friendlyColumn = $this->getFriendlyColumnName($column);
                
                $errors[] = [
                    'row' => $currentRow, // Use current row from import
                    'field' => $friendlyColumn,
                    'value' => 'NULL',
                    'error' => "This field is required and cannot be empty."
                ];
                return $errors;
            }
        }
        
        // Generic error - remove technical jargon
        $cleanMessage = str_replace('Database error: ', '', $message);
        $cleanMessage = str_replace('SQLSTATE[', '', $cleanMessage);
        $cleanMessage = preg_replace('/\[.*?\]/', '', $cleanMessage);
        $cleanMessage = substr($cleanMessage, 0, 200);
        
        $errors[] = [
            'row' => $currentRow, // Use current row from import
            'field' => 'System',
            'value' => 'N/A',
            'error' => "An error occurred: " . trim($cleanMessage)
        ];
        
        return $errors;
    }

    private function getFriendlyColumnName($column)
    {
        $columnMap = [
            'header_id' => 'Header ID',
            'brand_id' => 'Brand',
            'invoice_no' => 'Invoice Number (InvNo)',
            'wip_no' => 'WIP Number (WIPNO)',
            'account_code' => 'Account Code',
            'customer_name' => 'Customer Name (CustName)',
            'address_1' => 'Address 1 (Add1)',
            'address_2' => 'Address 2 (Add2)',
            'address_3' => 'Address 3 (Add3)',
            'address_4' => 'Address 4 (Add4)',
            'address_5' => 'Address 5 (Add5)',
            'department' => 'Department (Dept)',
            'invoice_date' => 'Invoice Date (InvDate)',
            'vehicle_id' => 'Vehicle ID (MAGICH)',
            'document_type' => 'Document Type (DocType)',
            'exchange_rate' => 'Exchange Rate',
            'registration_no' => 'Registration Number (RegNo)',
            'chassis' => 'Chassis Number',
            'mileage' => 'Mileage',
            'currency_code' => 'Currency Code (CurrCode)',
            'gross_value' => 'Gross Value',
            'net_value' => 'Net Value',
            'customer_discount' => 'Customer Discount (CustDisc)',
            'service_code' => 'Service Code (SvcCode)',
            'registration_date' => 'Registration Date (RegDate)',
            'description' => 'Description',
            'engine_no' => 'Engine Number (EngineNo)',
            'account_company' => 'Account Company (AcctCompany)',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'unique_id' => 'Unique ID',
            'is_active' => 'Active Status',
        ];
        
        return $columnMap[$column] ?? ucwords(str_replace('_', ' ', $column));
    }

    public function downloadTemplate()
    {
        $headers = [
            'WIPNO',
            'Account',
            'CustName',
            'Add1',
            'Add2',
            'Add3',
            'Add4',
            'Add5',
            'Dept',
            'InvNo',
            'InvDate',
            'MAGICH',
            'DocType',
            'ExchangeRate',
            'RegNo',
            'Chassis',
            'Mileage',
            'CurrCode',
            'GrossValue',
            'NetValue',
            'CustDisc',
            'SvcCode',
            'RegDate',
            'Description',
            'EngineNo',
            'AcctCompany',
        ];

        $filename = 'transaction_header_template.csv';
        
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function getBodyDetails(Request $request)
    {
        $request->validate([
            'wip_no' => 'required',
            'invoice_no' => 'required',
            'brand_id' => 'required',
        ]);

        $bodies = \App\Models\TransactionBody::where('wip_no', $request->wip_no)
            ->where('invoice_no', $request->invoice_no)
            ->where('is_active', '1')
            ->orderBy('line')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bodies
        ]);
    }

    public function export(Request $request)
    {
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Check if there's any filter
        $hasFilter = !empty($search) || !empty($dateFrom) || !empty($dateTo);

        // Only allow export when there's filter
        if (!$hasFilter) {
            return redirect()->route('transactions.index')
                ->with('error', 'Please apply search or date filter before exporting.');
        }

        $filename = 'transaction_headers_' . date('Y-m-d_His') . '.xlsx';

        // Export with body details
        return Excel::download(
            new TransactionHeaderExport($search, $dateFrom, $dateTo),
            $filename
        );
    }
}
