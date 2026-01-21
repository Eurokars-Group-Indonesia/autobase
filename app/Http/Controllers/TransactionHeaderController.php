<?php

namespace App\Http\Controllers;

use App\Models\TransactionHeader;
use App\Models\Brand;
use App\Imports\TransactionHeaderImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionHeaderController extends Controller
{
    public function index(Request $request)
    {
        // Generate cache key based on user and search parameters
        $userId = auth()->id();
        $search = $request->get('search', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        $cacheKey = "header:{$userId}:{$search}:{$dateFrom}:{$dateTo}:{$perPage}:{$page}";
        
        // Try to get from cache (1 hour), if not found execute query and cache it
        $transactions = cache()->remember($cacheKey, now()->addHour(), function () use ($request, $perPage) {
            $query = TransactionHeader::with('brand')->where('is_active', '1')->orderBy('invoice_date', 'desc');
            
            // Search by text (customer_name, chassis, invoice_date, invoice_no, wip_no, registration_no)
            // Using 'search%' pattern to utilize B-tree index efficiently
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('customer_name', 'like', $search . '%')
                      ->orWhere('chassis', 'like', $search . '%')
                      ->orWhere('invoice_no', 'like', $search . '%')
                      ->orWhere('wip_no', 'like', $search . '%')
                      ->orWhere('registration_no', 'like', $search . '%')
                      ->orWhereDate('invoice_date', '=', $search);
                });
            }
            
            // Filter by date range
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('invoice_date', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('invoice_date', '<=', $request->date_to);
            }
            
            // Pagination with per_page option
            $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
            
            return $query->paginate($perPageValue)->withQueryString();
        });
        
        return view('transactions.index', compact('transactions'));
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

            $failures = $import->failures();
            
            if ($failures->count() > 0) {
                $errors = [];
                foreach ($failures as $failure) {
                    $errors[] = [
                        'row' => $failure->row(),
                        'attribute' => $failure->attribute(),
                        'errors' => $failure->errors(),
                        'values' => $failure->values()
                    ];
                }
                
                // Clear cache after import (even with errors, some data might be imported)
                $this->clearTransactionCache();
                
                return redirect()->route('transactions.header.import')
                    ->with('import_errors', $errors)
                    ->with('warning', 'Import completed with ' . count($errors) . ' error(s). Please check the details below.');
            }

            // Clear cache after successful import
            $this->clearTransactionCache();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction headers imported successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle SQL errors with user-friendly messages
            $errorMessage = $this->parseSqlError($e->getMessage());
            
            return redirect()->route('transactions.header.import')
                ->with('error', 'Import failed: ' . $errorMessage);
        } catch (\Exception $e) {
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

    private function getFriendlyColumnName($column)
    {
        $columnMap = [
            'vehicle_id' => 'Vehicle ID (MAGICH)',
            'mileage' => 'Mileage',
            'exchange_rate' => 'Exchange Rate',
            'gross_value' => 'Gross Value',
            'net_value' => 'Net Value',
            'invoice_no' => 'Invoice Number',
            'wip_no' => 'WIP Number',
            'customer_name' => 'Customer Name',
            'chassis' => 'Chassis',
            'registration_no' => 'Registration Number',
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
}
