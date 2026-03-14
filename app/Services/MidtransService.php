<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    public function createSnapTransaction(Invoice $invoice, array $customerDetails): array
    {
        $orderId = 'INV-' . $invoice->id . '-' . Str::random(6);
        $grossAmount = (int) round($invoice->total_amount);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customerDetails['first_name'] ?? $customerDetails['name'] ?? 'Customer',
                'last_name' => $customerDetails['last_name'] ?? '',
                'email' => $customerDetails['email'] ?? '',
                'phone' => $customerDetails['phone'] ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'invoice-' . $invoice->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Invoice ' . $invoice->invoice_number,
                ],
            ],
        ];

        $response = Http::withBasicAuth(config('midtrans.server_key'), '')
            ->post(config('midtrans.snap_url'), $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Midtrans API error: ' . ($response->json('error_messages.0') ?? $response->body())
            );
        }

        $body = $response->json();
        $token = $body['token'] ?? null;
        $redirectUrl = $body['redirect_url'] ?? null;

        if (! $token) {
            throw new \RuntimeException('Midtrans did not return token.');
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'midtrans_order_id' => $orderId,
            'amount' => $invoice->total_amount,
            'status' => 'pending',
            'midtrans_response' => $body,
        ]);

        return [
            'order_id' => $orderId,
            'token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Handle notification from Midtrans (callback). Update payment and invoice status.
     */
    public function handleNotification(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (! $orderId) {
            return;
        }

        $payment = Payment::where('midtrans_order_id', $orderId)->first();
        if (! $payment) {
            return;
        }

        $payment->update([
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $payment->midtrans_transaction_id,
            'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
            'status' => $this->mapTransactionStatus($transactionStatus, $fraudStatus),
            'midtrans_response' => array_merge($payment->midtrans_response ?? [], $payload),
            'paid_at' => in_array($transactionStatus, ['capture', 'settlement'], true) ? now() : null,
        ]);

        if ($payment->isSuccess()) {
            $payment->invoice->markAsPaid();
        }
    }

    private function mapTransactionStatus(?string $status, ?string $fraudStatus): string
    {
        if (in_array($status, ['capture', 'settlement'], true)) {
            return 'success';
        }
        if (in_array($status, ['pending'], true)) {
            return 'pending';
        }
        if (in_array($status, ['deny', 'cancel', 'expire'], true)) {
            return $status === 'expire' ? 'expired' : 'failed';
        }
        if ($status === 'authorize' && $fraudStatus === 'accept') {
            return 'success';
        }
        return 'pending';
    }
}
