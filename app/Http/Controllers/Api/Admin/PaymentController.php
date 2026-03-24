<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with([
            'invoice:id,invoice_number,company_id,shipment_id,total_amount,status',
            'invoice.company:id,name',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }
        if ($request->filled('company_id')) {
            $query->whereHas('invoice', fn ($q) => $q->where('company_id', $request->company_id));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('midtrans_order_id', 'like', "%{$s}%")
                    ->orWhere('midtrans_transaction_id', 'like', "%{$s}%")
                    ->orWhereHas('invoice', fn ($iq) => $iq->where('invoice_number', 'like', "%{$s}%"));
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($payments);
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['invoice.company', 'invoice.shipment']);
        return response()->json(['data' => $payment]);
    }

    /**
     * List invoices that are overdue (for monitoring).
     */
    public function overdueInvoices(Request $request): JsonResponse
    {
        $query = Invoice::with(['company:id,name', 'shipment:id,waybill_number'])
            ->where('status', 'overdue');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $invoices = $query->orderBy('due_date')->paginate($request->per_page ?? 15);

        return response()->json($invoices);
    }
}
