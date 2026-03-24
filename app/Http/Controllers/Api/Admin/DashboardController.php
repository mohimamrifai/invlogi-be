<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Ringkasan agregat untuk dashboard internal (admin).
     */
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $bookingsToday = Booking::whereDate('created_at', $today)->count();

        $activeShipments = Shipment::whereNotIn('status', ['completed', 'cancelled'])->count();

        $overdueInvoices = Invoice::where('status', 'overdue')->count();

        $activeCompanies = Company::where('status', 'active')->count();

        $pendingCompanyApprovals = Company::where('status', 'pending')->count();

        $unpaidInvoices = Invoice::whereIn('status', ['unpaid', 'overdue'])->count();

        $paymentsToday = Payment::where('status', 'success')
            ->whereDate('paid_at', $today)
            ->count();

        $departuresToday = Shipment::where(function ($q) use ($today) {
            $q->whereDate('estimated_departure', $today)
                ->orWhereDate('actual_departure', $today);
        })->count();

        $arrivalsToday = Shipment::whereDate('actual_arrival', $today)->count();

        $pendingBookings = Booking::with([
            'company:id,name',
            'originLocation:id,name,code',
            'destinationLocation:id,name,code',
            'serviceType:id,name,code',
        ])
            ->whereIn('status', ['submitted', 'draft'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'booking_number' => $b->booking_number,
                'code' => $b->booking_number,
                'customer' => $b->company?->name,
                'route' => ($b->originLocation?->name ?? '').' → '.($b->destinationLocation?->name ?? ''),
                'serviceType' => $b->serviceType?->name ?? $b->serviceType?->code,
                'status' => $b->status,
            ]);

        $recentShipments = Shipment::with([
            'company:id,name',
            'originLocation:id,name,code',
            'destinationLocation:id,name,code',
        ])
            ->orderBy('updated_at', 'desc')
            ->limit(8)
            ->get()
            ->map(fn ($s) => [
                'id' => (string) $s->id,
                'customer' => $s->company?->name,
                'route' => ($s->originLocation?->name ?? '').' → '.($s->destinationLocation?->name ?? ''),
                'status' => $s->status,
            ]);

        $overdueInvoiceRows = Invoice::with('company:id,name')
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->limit(8)
            ->get()
            ->map(fn ($i) => [
                'number' => $i->invoice_number,
                'customer' => $i->company?->name,
                'status' => 'overdue',
                'dueDate' => $i->due_date?->toDateString(),
                'amount' => (float) $i->total_amount,
            ]);

        $recentPayments = Payment::with(['invoice.company:id,name'])
            ->whereHas('invoice')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                return [
                    'ref' => $p->midtrans_order_id ?? (string) $p->id,
                    'customer' => $p->invoice?->company?->name ?? '',
                    'method' => $p->payment_type ?? 'midtrans',
                    'amount' => (float) $p->amount,
                    'status' => $p->status,
                ];
            });

        $shipmentVolumeByWeek = $this->shipmentVolumeByWeek();

        return response()->json([
            'data' => [
                'summary' => [
                    'bookingsToday' => $bookingsToday,
                    'activeShipments' => $activeShipments,
                    'rackUtilization' => 0,
                    'overdueInvoices' => $overdueInvoices,
                    'activeCompanies' => $activeCompanies,
                    'pendingCompanyApprovals' => $pendingCompanyApprovals,
                    'unpaidInvoices' => $unpaidInvoices,
                    'paymentsToday' => $paymentsToday,
                    'departuresToday' => $departuresToday,
                    'arrivalsToday' => $arrivalsToday,
                    'activeCustomers' => $activeCompanies,
                    'newCustomersThisWeek' => Company::where('created_at', '>=', now()->subWeek())->count(),
                    'pendingProspects' => $pendingCompanyApprovals,
                ],
                'pendingBookings' => $pendingBookings,
                'activeShipments' => $recentShipments,
                'overdueInvoices' => $overdueInvoiceRows,
                'recentPayments' => $recentPayments,
                'shipmentVolumeByWeek' => $shipmentVolumeByWeek,
            ],
        ]);
    }

    /**
     * Shipment baru per minggu (4 minggu terakhir), dipisah FCL vs LCL dari kode/nama service type.
     */
    private function shipmentVolumeByWeek(): array
    {
        $series = [];

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();

            $shipments = Shipment::query()
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->with('serviceType:id,code,name')
                ->get();

            $fcl = 0;
            $lcl = 0;

            foreach ($shipments as $s) {
                $code = strtoupper((string) ($s->serviceType?->code ?? ''));
                $name = strtoupper((string) ($s->serviceType?->name ?? ''));

                if (str_contains($code, 'LCL') || str_contains($name, 'LCL')) {
                    $lcl++;
                } elseif (str_contains($code, 'FCL') || str_contains($name, 'FCL')) {
                    $fcl++;
                } else {
                    $fcl++;
                }
            }

            $series[] = [
                'week' => $weekStart->format('d M').' – '.$weekEnd->format('d M'),
                'fcl' => $fcl,
                'lcl' => $lcl,
            ];
        }

        return $series;
    }
}
