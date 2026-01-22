<?php

namespace App\Imports;

use App\Models\TransactionBody;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionBodyImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    SkipsEmptyRows, 
    SkipsOnFailure,
    WithChunkReading
{
    use SkipsFailures;

    protected $errors = [];
    protected $successCount = 0;
    public $currentRow = 1;

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function model(array $row)
    {
        $this->currentRow++;
        
        try {
            Log::info("Processing row {$this->currentRow}", ['data' => $row]);

            // Validate required fields
            if (empty($row['part'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Part',
                    'value' => $row['part'] ?? 'empty',
                    'error' => 'Part is required and cannot be empty'
                ];
                return null;
            }

            if (empty($row['invno'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvNo',
                    'value' => $row['invno'] ?? 'empty',
                    'error' => 'Invoice Number is required and cannot be empty'
                ];
                return null;
            }

            if (empty($row['wipno'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNo',
                    'value' => $row['wipno'] ?? 'empty',
                    'error' => 'WIP Number is required and cannot be empty'
                ];
                return null;
            }

            if (empty($row['line'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Line',
                    'value' => $row['line'] ?? 'empty',
                    'error' => 'Line is required and cannot be empty'
                ];
                return null;
            }

            // Parse numeric fields
            $invoiceNo = $this->parseNumeric($row['invno'] ?? null);
            $wipNo = $this->parseNumeric($row['wipno'] ?? null);
            $line = $this->parseNumeric($row['line'] ?? null);
            $qty = $this->parseDecimal($row['qty'] ?? 0);
            $sellingPrice = $this->parseDecimal($row['sellprice'] ?? 0);
            $discount = $this->parseDecimal($row['disc'] ?? 0);
            $extendedPrice = $this->parseDecimal($row['extprice'] ?? 0);
            $menuPrice = $this->parseDecimal($row['mp'] ?? 0);
            $costPrice = $this->parseDecimal($row['costpr'] ?? 0);
            $contribution = $this->parseDecimal($row['contrib'] ?? 0);
            $currencyPrice = $this->parseDecimal($row['curprice'] ?? null);
            $minsPerUnit = $this->parseNumeric($row['mpu'] ?? null);
            $magic1 = $this->parseNumeric($row['hmagic1'] ?? 0);
            $magic2 = $this->parseNumeric($row['hmagic2'] ?? 0);
            $poNo = $this->parseNumeric($row['po'] ?? null);
            $grnNo = $this->parseNumeric($row['grn'] ?? null);
            $menuCode = $this->parseNumeric($row['menu'] ?? null);
            $menuLink = $this->parseNumeric($row['menulink'] ?? 0);

            // Parse date
            $dateDecard = $this->parseDate($row['datedecard'] ?? null);

            // Validate required numeric fields
            // InvNo boleh 0, tapi tidak boleh null atau empty string
            if ($invoiceNo === null || $invoiceNo === '') {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvNo',
                    'value' => $row['invno'] ?? 'empty',
                    'error' => 'Invoice Number must be a valid number (0 is allowed)'
                ];
                return null;
            }

            if ($wipNo === null || $wipNo === '') {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNo',
                    'value' => $row['wipno'] ?? 'empty',
                    'error' => 'WIP Number must be a valid integer number (e.g., 1, 123). Text values like "WIP000001" are not allowed.'
                ];
                return null;
            }

            if ($line === null || $line === '') {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Line',
                    'value' => $row['line'] ?? 'empty',
                    'error' => 'Line must be a valid number'
                ];
                return null;
            }

            // Validate analysis_code (required, 1 char)
            $analysisCode = strtoupper($row['analcode'] ?? '');
            if (empty($analysisCode) || strlen($analysisCode) > 1) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'AnalCode',
                    'value' => $row['analcode'] ?? 'empty',
                    'error' => 'Analysis Code is required and must be 1 character'
                ];
                return null;
            }

            // Validate invoice_status (required, 1 char, X or C)
            $invoiceStatus = strtoupper($row['invstat'] ?? '');
            if (!in_array($invoiceStatus, ['X', 'C'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvStat',
                    'value' => $row['invstat'] ?? 'empty',
                    'error' => 'Invoice Status must be either X (Closed) or C (Completed)'
                ];
                return null;
            }

            // Validate sales_type (required, 1 char)
            $salesType = strtoupper($row['saletype'] ?? '');
            if (empty($salesType) || strlen($salesType) > 1) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'SaleType',
                    'value' => $row['saletype'] ?? 'empty',
                    'error' => 'Sales Type is required and must be 1 character'
                ];
                return null;
            }

            // Validate warranty_code (optional, max 3 chars)
            $warrantyCode = !empty($row['wcode']) ? strtoupper($row['wcode']) : null;
            if ($warrantyCode !== null && strlen($warrantyCode) > 3) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Wcode',
                    'value' => $row['wcode'],
                    'error' => 'Warranty Code must be 3 characters or less'
                ];
                return null;
            }

            // Validate part_or_labour (required, P or L)
            $partOrLabour = strtoupper($row['partslabour'] ?? '');
            if (!in_array($partOrLabour, ['P', 'L'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Parts/Labour',
                    'value' => $row['partslabour'] ?? 'empty',
                    'error' => 'Parts/Labour must be either P (Part) or L (Labour)'
                ];
                return null;
            }

            // Validate part_no (max 100 chars)
            if (strlen($row['part']) > 100) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Part',
                    'value' => substr($row['part'], 0, 50) . '...',
                    'error' => 'Part Number must be 100 characters or less'
                ];
                return null;
            }

            // Validate description (max 250 chars)
            if (!empty($row['desc']) && strlen($row['desc']) > 250) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Desc',
                    'value' => substr($row['desc'], 0, 50) . '...',
                    'error' => 'Description must be 250 characters or less'
                ];
                return null;
            }

            // Validate unit (max 10 chars)
            if (!empty($row['uoi']) && strlen($row['uoi']) > 10) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'UOI',
                    'value' => $row['uoi'],
                    'error' => 'Unit must be 10 characters or less'
                ];
                return null;
            }

            // Validate account_code (max 20 chars)
            if (!empty($row['acct']) && strlen($row['acct']) > 20) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Acct',
                    'value' => $row['acct'],
                    'error' => 'Account Code must be 20 characters or less'
                ];
                return null;
            }

            // Validate department (max 50 chars)
            if (!empty($row['dept']) && strlen($row['dept']) > 50) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Dept',
                    'value' => $row['dept'],
                    'error' => 'Department must be 50 characters or less'
                ];
                return null;
            }

            // Validate franchise_code (max 3 chars)
            if (!empty($row['fc']) && strlen($row['fc']) > 3) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'FC',
                    'value' => $row['fc'],
                    'error' => 'Franchise Code must be 3 characters or less'
                ];
                return null;
            }

            // Validate supplier_code (max 20 chars)
            if (!empty($row['supp']) && strlen($row['supp']) > 20) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Supp',
                    'value' => $row['supp'],
                    'error' => 'Supplier Code must be 20 characters or less'
                ];
                return null;
            }

            // Check if record exists: part_no + invoice_no + wip_no + line
            $existing = TransactionBody::where('part_no', $row['part'])
                ->where('invoice_no', $invoiceNo)
                ->where('wip_no', $wipNo)
                ->where('line', $line)
                ->first();

            // Prepare data
            $data = [
                'part_no' => $row['part'],
                'invoice_no' => $invoiceNo,
                'wip_no' => $wipNo,
                'line' => $line,
                'description' => $row['desc'] ?? null,
                'qty' => $qty,
                'selling_price' => $sellingPrice,
                'discount' => $discount,
                'extended_price' => $extendedPrice,
                'menu_price' => $menuPrice,
                'vat' => strtoupper($row['vat'] ?? ''),
                'menu_vat' => strtoupper($row['mv'] ?? ''),
                'cost_price' => $costPrice,
                'analysis_code' => $analysisCode,
                'invoice_status' => $invoiceStatus,
                'unit' => $row['uoi'] ?? null,
                'mins_per_unit' => $minsPerUnit,
                'account_code' => $row['acct'] ?? null,
                'department' => $row['dept'] ?? null,
                'franchise_code' => $row['fc'] ?? null,
                'sales_type' => $salesType,
                'warranty_code' => $warrantyCode,
                'menu_flag' => strtoupper($row['menuflag'] ?? ''),
                'contribution' => $contribution,
                'date_decard' => $dateDecard,
                'magic_1' => $magic1,
                'magic_2' => $magic2,
                'po_no' => $poNo,
                'grn_no' => $grnNo,
                'menu_code' => $menuCode,
                'labour_rates' => strtoupper($row['lr'] ?? ''),
                'supplier_code' => $row['supp'] ?? null,
                'menu_link' => $menuLink,
                'currency_price' => $currencyPrice,
                'part_or_labour' => $partOrLabour,
                'is_active' => '1',
            ];

            if ($existing) {
                // UPDATE: Record exists
                $data['updated_by'] = (string) Auth::id();
                $existing->update($data);
                $body = $existing;
                Log::info("Row {$this->currentRow} UPDATED", [
                    'body_id' => $body->body_id,
                    'part_no' => $row['part'],
                    'invno' => $invoiceNo,
                    'wipno' => $wipNo,
                    'line' => $line
                ]);
            } else {
                // INSERT: Record not exists
                $data['created_by'] = (string) Auth::id();
                $data['unique_id'] = (string) \Illuminate\Support\Str::uuid();
                $body = TransactionBody::create($data);
                Log::info("Row {$this->currentRow} INSERTED", [
                    'body_id' => $body->body_id,
                    'part_no' => $row['part'],
                    'invno' => $invoiceNo,
                    'wipno' => $wipNo,
                    'line' => $line
                ]);
            }

            $this->successCount++;
            return $body;

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle SQL errors specifically
            $errorMessage = $e->getMessage();
            
            // Check for integer value error
            if (strpos($errorMessage, 'Incorrect integer value') !== false) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNo',
                    'value' => $row['wipno'] ?? 'N/A',
                    'error' => 'WIP Number must be a valid integer. Text values like "WIP000001" are not allowed. Please use only numbers (e.g., 1, 123).'
                ];
            } else {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Database',
                    'value' => 'N/A',
                    'error' => 'Database error: ' . $errorMessage
                ];
            }
            
            Log::error("Database error importing row {$this->currentRow}", [
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        } catch (\Exception $e) {
            $this->errors[] = [
                'row' => $this->currentRow,
                'field' => 'General',
                'value' => 'N/A',
                'error' => $e->getMessage()
            ];
            
            Log::error("Error importing row {$this->currentRow}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'part' => 'required',
            'invno' => 'required|numeric',
            'wipno' => 'required|numeric',
            'line' => 'required|numeric',
            'qty' => 'nullable|numeric',
            'sellprice' => 'nullable|numeric',
            'disc' => 'nullable|numeric',
            'extprice' => 'nullable|numeric',
            'mp' => 'nullable|numeric',
            'costpr' => 'nullable|numeric',
            'contrib' => 'nullable|numeric',
            'curprice' => 'nullable|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'part.required' => 'Part is required.',
            'invno.required' => 'Invoice Number is required.',
            'invno.numeric' => 'Invoice Number must be a number.',
            'wipno.required' => 'WIP Number is required.',
            'wipno.numeric' => 'WIP Number must be a number.',
            'line.required' => 'Line is required.',
            'line.numeric' => 'Line must be a number.',
            'qty.numeric' => 'Qty must be a number.',
            'sellprice.numeric' => 'Selling Price must be a number.',
            'disc.numeric' => 'Discount must be a number.',
            'extprice.numeric' => 'Extended Price must be a number.',
            'mp.numeric' => 'Menu Price must be a number.',
            'costpr.numeric' => 'Cost Price must be a number.',
            'contrib.numeric' => 'Contribution must be a number.',
            'curprice.numeric' => 'Currency Price must be a number.',
        ];
    }

    private function parseNumeric($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if ($value === 0 || $value === '0') {
            return 0;
        }
        
        $cleaned = preg_replace('/[^0-9\-]/', '', $value);
        
        return is_numeric($cleaned) ? (int)$cleaned : null;
    }

    private function parseDecimal($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if ($value === 0 || $value === '0' || $value === 0.0 || $value === '0.0') {
            return 0;
        }
        
        $cleaned = preg_replace('/[^0-9\.\-]/', '', $value);
        
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Handle Excel date serial number
            if (is_numeric($date)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
            }
            
            // Handle string dates with various formats
            // Try common date formats: d/m/Y, d-m-Y, Y-m-d, etc.
            $formats = [
                'd/m/Y',    // 31/08/2009
                'd-m-Y',    // 31-08-2009
                'Y-m-d',    // 2009-08-31
                'd/m/y',    // 31/08/09
                'd-m-y',    // 31-08-09
                'm/d/Y',    // 08/31/2009
                'm-d-Y',    // 08-31-2009
            ];
            
            foreach ($formats as $format) {
                try {
                    $parsed = Carbon::createFromFormat($format, $date);
                    if ($parsed !== false) {
                        return $parsed;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // If all formats fail, try Carbon::parse as fallback
            return Carbon::parse($date);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$date}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Chunk size for reading Excel file
     * Process 1000 rows at a time for better memory management
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
