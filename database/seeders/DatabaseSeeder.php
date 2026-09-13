<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial System Settings
        SystemSetting::firstOrCreate([], [
            'business_name' => 'InstaDrop Courier Services',
            'admin_whatsapp_number' => '+448001234455',
            'admin_notification_email' => 'dispatch@instadrop.co.uk',
            'currency_code' => 'GBP',
            'vat_rate' => 20.00,
        ]);

        // 2. 5 Realistic Customer Quote Requests (All 11 Fields)
        $quotes = [
            [
                'quote_number'        => 'Q-88492',
                'first_name'          => 'Sarah',
                'last_name'           => 'Mitchell',
                'email'               => 'sarah.m@company.co.uk',
                'phone'               => '+44 07123 456789',
                'contact_preference'  => 'email',
                'collection_postcode' => 'M1 1AE',
                'delivery_postcode'   => 'SW1A 1AA',
                'vehicle_type'        => 'luton_tail_lift',
                'timescale'           => 'asap_60min',
                'enquiry_type'        => 'business',
                'additional_info'     => '2 Heavy Euro Pallets (800kg total), fragile machinery components. Needs tail-lift unloading at ground level.',
                'status'              => 'pending',
            ],
            [
                'quote_number'        => 'Q-77201',
                'first_name'          => 'David',
                'last_name'           => 'Miller',
                'email'               => 'david.m@construct.co.uk',
                'phone'               => '+44 07822 998877',
                'contact_preference'  => 'whatsapp',
                'collection_postcode' => 'B1 1BB',
                'delivery_postcode'   => 'LS1 4AP',
                'vehicle_type'        => 'small_van',
                'timescale'           => 'asap_60min',
                'enquiry_type'        => 'business',
                'additional_info'     => '1 Box of architectural drawings and architectural site equipment (15kg).',
                'status'              => 'pending',
            ],
            [
                'quote_number'        => 'Q-66104',
                'first_name'          => 'Alexander',
                'last_name'           => 'Wright',
                'email'               => 'alex.w@gmail.com',
                'phone'               => '+44 07933 112233',
                'contact_preference'  => 'phone_call',
                'collection_postcode' => 'G1 1XQ',
                'delivery_postcode'   => 'EH1 1YZ',
                'vehicle_type'        => 'medium_van',
                'timescale'           => 'today_afternoon',
                'enquiry_type'        => 'personal',
                'additional_info'     => '3 Crates of household antiques and artwork. Extra care required.',
                'status'              => 'pending',
            ],
            [
                'quote_number'        => 'Q-55923',
                'first_name'          => 'Claire',
                'last_name'           => 'Bennet',
                'email'               => 'claire@legalchambers.co.uk',
                'phone'               => '+44 07744 556677',
                'contact_preference'  => 'email',
                'collection_postcode' => 'BS1 3AG',
                'delivery_postcode'   => 'CF10 1BH',
                'vehicle_type'        => 'courier_car',
                'timescale'           => 'asap_60min',
                'enquiry_type'        => 'business',
                'additional_info'     => 'Sealed confidential court brief bundle. Hand-to-hand named recipient delivery required.',
                'status'              => 'quoted',
            ],
            [
                'quote_number'        => 'Q-44812',
                'first_name'          => 'Robert',
                'last_name'           => 'Vance',
                'email'               => 'r.vance@vancecorp.co.uk',
                'phone'               => '+44 07555 889900',
                'contact_preference'  => 'whatsapp',
                'collection_postcode' => 'L1 8JQ',
                'delivery_postcode'   => 'NE1 1D3',
                'vehicle_type'        => 'large_van',
                'timescale'           => 'scheduled_date',
                'enquiry_type'        => 'business',
                'additional_info'     => '4 Oversized trade show exhibition display banners and stand gear.',
                'status'              => 'converted',
            ],
        ];

        foreach ($quotes as $quoteData) {
            QuoteRequest::firstOrCreate(
                ['quote_number' => $quoteData['quote_number']],
                $quoteData
            );
        }
    }
}
