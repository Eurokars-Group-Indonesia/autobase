<?php

namespace App\Imports;

use App\Models\TransactionHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionHeaderImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    protected $brandId;
    protected $errors = [];
    protected $successCount = 0;
    public $currentRow = 1; // Start from 1 (header row) - public agar bisa diakses dari controller

    public function __construct($brandId)
    {
        $this->brandId = $brandId;
    }

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
            // Log raw row data for debugging
            Log::info("Processing row {$this->currentRow}", ['data' => $row]);

            // Validate required fields
            if (empty($row['wipno'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNO',
                    'value' => $row['wipno'] ?? 'empty',
                    'error' => 'WIPNO is required and cannot be empty'
                ];
                return null;
            }

            // Parse dates
            $invoiceDate = $this->parseDate($row['invdate'] ?? null);
            $registrationDate = $this->parseDate($row['regdate'] ?? null);

            if (empty($invoiceDate)) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvDate',
                    'value' => $row['invdate'] ?? 'empty',
                    'error' => 'Invoice Date is required and must be a valid date'
                ];
                return null;
            }

            // Parse numeric fields
            $vehicleId = $this->parseNumeric($row['magich'] ?? null);
            $mileage = $this->parseNumeric($row['mileage'] ?? null);
            $invoiceNo = $this->parseNumeric($row['invno'] ?? null);
            $exchangeRate = $this->parseDecimal($row['exchangerate'] ?? null);
            $grossValue = $this->parseDecimal($row['grossvalue'] ?? null);
            $netValue = $this->parseDecimal($row['netvalue'] ?? null);

            // Validate required numeric fields
            if ($vehicleId === null || $vehicleId === '') {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'MAGICH',
                    'value' => $row['magich'] ?? 'empty',
                    'error' => 'Vehicle ID (MAGICH) is required and must be a valid number'
                ];
                return null;
            }

            if ($invoiceNo === null || $invoiceNo === '') {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvNo',
                    'value' => $row['invno'] ?? 'empty',
                    'error' => 'Invoice Number is required and must be a valid number'
                ];
                return null;
            }

            // Mileage boleh 0, tapi tidak boleh null atau empty string
            if ($mileage === null || $mileage === '') {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Mileage',
                    'value' => $row['mileage'] ?? 'empty',
                    'error' => 'Mileage is required and must be a valid number (0 is allowed)'
                ];
                return null;
            }

            // Validate document type
            $docType = strtoupper($row['doctype'] ?? '');
            if (!in_array($docType, ['I', 'C'])) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'DocType',
                    'value' => $row['doctype'] ?? 'empty',
                    'error' => 'Document Type must be either I (Invoice) or C (Credit Note)'
                ];
                return null;
            }

            // Validate currency code
            $currCode = strtoupper($row['currcode'] ?? '');
            if (empty($currCode) || strlen($currCode) > 3) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'CurrCode',
                    'value' => $row['currcode'] ?? 'empty',
                    'error' => 'Currency Code is required and must be 3 characters or less'
                ];
                return null;
            }

            // Check if record exists: wipno + invno + brand
            $existing = TransactionHeader::where('wip_no', $row['wipno'])
                ->where('invoice_no', $invoiceNo)
                ->where('brand_id', $this->brandId)
                ->first();

            // Prepare data
            $data = [
                'wip_no' => $row['wipno'],
                'invoice_no' => $invoiceNo,
                'brand_id' => $this->brandId,
                'account_code' => $row['account'] ?? null,
                'customer_name' => $row['custname'] ?? null,
                'address_1' => $row['add1'] ?? null,
                'address_2' => $row['add2'] ?? null,
                'address_3' => $row['add3'] ?? null,
                'address_4' => $row['add4'] ?? null,
                'address_5' => $row['add5'] ?? null,
                'department' => $row['dept'] ?? null,
                'invoice_date' => $invoiceDate,
                'vehicle_id' => $vehicleId,
                'document_type' => $docType,
                'exchange_rate' => $exchangeRate,
                'registration_no' => $row['regno'] ?? null,
                'chassis' => $row['chassis'] ?? null,
                'mileage' => $mileage,
                'currency_code' => $currCode,
                'gross_value' => $grossValue ?? 0,
                'net_value' => $netValue ?? 0,
                'customer_discount' => $row['custdisc'] ?? '0',
                'service_code' => $row['svccode'] ?? null,
                'registration_date' => $registrationDate,
                'description' => $row['description'] ?? null,
                'engine_no' => $row['engineno'] ?? null,
                'account_company' => $row['acctcompany'] ?? null,
                'is_active' => '1',
            ];

            if ($existing) {
                // UPDATE: Record exists
                $data['updated_by'] = (string) Auth::id();
                $existing->update($data);
                $header = $existing;
                Log::info("Row {$this->currentRow} UPDATED", [
                    'header_id' => $header->header_id,
                    'wipno' => $row['wipno'],
                    'invno' => $invoiceNo
                ]);
            } else {
                // INSERT: Record not exists
                $data['created_by'] = (string) Auth::id();
                // header_id dibiarkan null (auto increment)
                $header = TransactionHeader::create($data);
                Log::info("Row {$this->currentRow} INSERTED", [
                    'header_id' => $header->header_id,
                    'wipno' => $row['wipno'],
                    'invno' => $invoiceNo
                ]);
            }

            $this->successCount++;
            return $header;

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
            'wipno' => 'required',
            'magich' => 'nullable|numeric',
            'mileage' => 'nullable|numeric',
            'exchangerate' => 'nullable|numeric',
            'grossvalue' => 'nullable|numeric',
            'netvalue' => 'nullable|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'wipno.required' => 'WIPNO is required.',
            'magich.numeric' => 'Vehicle ID (MAGICH) must be a number.',
            'mileage.numeric' => 'Mileage must be a number.',
            'exchangerate.numeric' => 'Exchange Rate must be a number.',
            'grossvalue.numeric' => 'Gross Value must be a number.',
            'netvalue.numeric' => 'Net Value must be a number.',
        ];
    }

    private function parseNumeric($value)
    {
        // Check if value is null or empty string (but allow 0)
        if ($value === null || $value === '') {
            return null;
        }
        
        // If value is already 0, return 0
        if ($value === 0 || $value === '0') {
            return 0;
        }
        
        // Remove any non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^0-9\-]/', '', $value);
        
        return is_numeric($cleaned) ? (int)$cleaned : null;
    }

    private function parseDecimal($value)
    {
        // Check if value is null or empty string (but allow 0)
        if ($value === null || $value === '') {
            return null;
        }
        
        // If value is already 0, return 0
        if ($value === 0 || $value === '0' || $value === 0.0 || $value === '0.0') {
            return 0;
        }
        
        // Remove any non-numeric characters except decimal point and minus
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
            
            // Handle string dates
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}

