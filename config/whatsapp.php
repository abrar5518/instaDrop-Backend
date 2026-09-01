<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Cloud API Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => (bool) env('WHATSAPP_ENABLED', true),

    'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v25.0'),

    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),

    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', '1349924141534825'),

    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID', '1391593043055339'),

    'admin_number' => env('WHATSAPP_ADMIN_NUMBER', '447852502775'),

    'order_template' => env('WHATSAPP_ORDER_TEMPLATE', 'new_order_admin_alert'),

    'inquiry_template' => env('WHATSAPP_INQUIRY_TEMPLATE', 'new_inquiry_admin_alert'),

    'payment_template' => env('WHATSAPP_PAYMENT_TEMPLATE', 'payment_received_admin_alert'),

    'template_language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'en'),
];
