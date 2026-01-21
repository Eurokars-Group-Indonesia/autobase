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
use Carbon\Carbon;

class TransactionHeaderImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    protected $brandId;

    public function __construct($brandId)
    {
        $this->brandId = $brandId;
    }

    public function model(array $row)
    {
        // Parse dates
        $invoiceDate = $this->parseDate($row['invdate'] ?? null);
        $registrationDate = $this->parseDate($row['regdate'] ?? null);

        // Parse numeric fields - set to null if not numeric
        $vehicleId = $this->parseNumeric($row['magich'] ?? null);
        $mileage = $this->parseNumeric($row['mileage'] ?? null);
        $exchangeRate = $this->parseDecimal($row['exchangerate'] ?? null);
        $grossValue = $this->parseDecimal($row['grossvalue'] ?? null);
        $netValue = $this->parseDecimal($row['netvalue'] ?? null);

        return TransactionHeader::updateOrCreate(
            [
                'wip_no' => $row['wipno'],
                'brand_id' => $this->brandId,
            ],
            [
                'account_code' => $row['account'] ?? null,
                'customer_name' => $row['custname'] ?? null,
                'address_1' => $row['add1'] ?? null,
                'address_2' => $row['add2'] ?? null,
                'address_3' => $row['add3'] ?? null,
                'address_4' => $row['add4'] ?? null,
                'address_5' => $row['add5'] ?? null,
                'department' => $row['dept'] ?? null,
                'invoice_no' => $row['invno'] ?? null,
                'invoice_date' => $invoiceDate,
                'vehicle_id' => $vehicleId,
                'document_type' => $row['doctype'] ?? null,
                'exchange_rate' => $exchangeRate,
                'registration_no' => $row['regno'] ?? null,
                'chassis' => $row['chassis'] ?? null,
                'mileage' => $mileage,
                'currency_code' => $row['currcode'] ?? null,
                'gross_value' => $grossValue,
                'net_value' => $netValue,
                'customer_discount' => $row['custdisc'] ?? null,
                'service_code' => $row['svccode'] ?? null,
                'registration_date' => $registrationDate,
                'description' => $row['description'] ?? null,
                'engine_no' => $row['engineno'] ?? null,
                'account_company' => $row['acctcompany'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'is_active' => '1',
            ]
        );
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
        if (empty($value)) {
            return null;
        }
        
        // Remove any non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^0-9\-]/', '', $value);
        
        return is_numeric($cleaned) ? (int)$cleaned : null;
    }

    private function parseDecimal($value)
    {
        if (empty($value)) {
            return null;
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

