<?php

/**
 * Midtrans Payment Gateway
 *
 * Set Notification URL di dashboard Midtrans (Settings > Configuration):
 * Production: https://your-domain.com/api/payments/midtrans/notification
 * Sandbox:    https://your-domain.com/api/payments/midtrans/notification
 */

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',
];
