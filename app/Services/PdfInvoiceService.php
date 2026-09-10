<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class PdfInvoiceService
{
    /**
     * Generate HTML / PDF Invoice Data Payload
     */
    public function generateInvoiceData(Invoice $invoice): array
    {
        $order = $invoice->order;
        $setting = SystemSetting::first();

        $businessName = $setting ? $setting->business_name : 'InstaDrop Courier Services Ltd';
        $adminEmail = $setting ? $setting->admin_notification_email : 'dispatch@instadrop.uk';
        $vatRate = $setting ? $setting->vat_rate : 20.00;

        return [
            'invoice_number'  => $invoice->invoice_number,
            'date'            => $invoice->created_at->format('d M Y'),
            'due_date'        => $invoice->created_at->addDays(7)->format('d M Y'),
            'business_name'   => $businessName,
            'business_email'  => $adminEmail,
            'customer_name'   => $order->customer_name,
            'customer_email'  => $order->customer_email,
            'customer_phone'  => $order->customer_phone,
            'pickup_address'  => $order->pickup_address,
            'delivery_address' => $order->delivery_address,
            'vehicle_type'    => $order->vehicle_type,
            'subtotal'        => $invoice->subtotal,
            'vat_rate'        => $vatRate,
            'vat_amount'      => $invoice->vat_amount,
            'total_amount'    => $invoice->total_amount,
            'status'          => strtoupper($invoice->status),
        ];
    }
}
