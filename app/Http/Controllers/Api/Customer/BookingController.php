<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Services\BookingPriceEstimateService;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingPriceEstimateService $priceEstimateService,
        private MidtransService $midtransService,
    ) {}

    /**
     * Get estimated price for a booking (before submit). No booking is created.
     */
    public function estimatePrice(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'transport_mode_id' => 'required|exists:transport_modes,id',
            'service_type_id' => 'required|exists:service_types,id',
            'container_type_id' => 'nullable|exists:container_types,id',
            'container_count' => 'nullable|integer|min:1',
            'estimated_weight' => 'nullable|numeric|min:0',
            'estimated_cbm' => 'nullable|numeric|min:0',
            'additional_services' => 'nullable|array',
            'additional_services.*' => 'exists:additional_services,id',
        ]);

        $params = [
            ...$data,
            'additional_services' => $data['additional_services'] ?? [],
            'company_id' => $user->company_id,
        ];

        $result = $this->priceEstimateService->estimate($params);

        return response()->json(['data' => $result]);
    }
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Booking::with([
            'originLocation:id,name,code', 'destinationLocation:id,name,code',
            'serviceType:id,name,code', 'transportMode:id,name',
        ])->where('company_id', $user->company_id);

        if ($request->filled('status')) $query->where('status', $request->status);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Cek apakah company punya invoice overdue
        if ($user->company && $user->company->hasOverdueInvoices()) {
            return response()->json([
                'message' => 'Perusahaan Anda memiliki invoice jatuh tempo. Silakan lunasi terlebih dahulu.',
            ], 403);
        }

        $data = $request->validate([
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'transport_mode_id' => 'required|exists:transport_modes,id',
            'service_type_id' => 'required|exists:service_types,id',
            'container_type_id' => 'nullable|exists:container_types,id',
            'container_count' => 'nullable|integer|min:1',
            'estimated_weight' => 'nullable|numeric|min:0',
            'estimated_cbm' => 'nullable|numeric|min:0',
            'cargo_category_id' => 'required|exists:cargo_categories,id',
            'cargo_description' => [
                'nullable', 'string',
                // Rule 8.3: Mixed Cargo requires description
                function ($attribute, $value, $fail) use ($request) {
                    $cat = \App\Models\CargoCategory::find($request->cargo_category_id);
                    if ($cat && $cat->code === 'MIX' && empty($value)) {
                        $fail('Deskripsi barang wajib diisi untuk kategori Mixed Cargo.');
                    }
                }
            ],
            'departure_date' => 'nullable|date|after_or_equal:today',
            'shipper_name' => 'required|string|max:255',
            'shipper_address' => 'required|string',
            'shipper_phone' => 'required|string|max:50',
            'consignee_name' => 'required|string|max:255',
            'consignee_address' => 'required|string',
            'consignee_phone' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'additional_services' => 'nullable', // Can be JSON string from FormData
            
            // New fields with strict validation
            'is_dangerous_goods' => 'nullable|in:0,1,true,false',
            'dg_class_id' => 'nullable|required_if:is_dangerous_goods,1|exists:dg_classes,id',
            'un_number' => 'nullable|required_if:is_dangerous_goods,1|string|max:50',
            'msds_file' => 'nullable|required_if:is_dangerous_goods,1|file|mimes:pdf|max:5120',
            'equipment_condition' => 'nullable|in:CLEAN,RESIDUAL',
            'temperature' => [
                'nullable', 'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $cat = \App\Models\CargoCategory::find($request->cargo_category_id);
                    if ($cat && $cat->requires_temperature && $value === null) {
                        $fail('Suhu (temperature) wajib diisi untuk kategori kargo ini.');
                    }
                }
            ],
        ]);

        // Handle additional_services if it came from FormData as a JSON string
        if (is_string($request->additional_services)) {
            $data['additional_services'] = json_decode($request->additional_services, true);
        }

        $estimateParams = [
            'origin_location_id' => $data['origin_location_id'],
            'destination_location_id' => $data['destination_location_id'],
            'transport_mode_id' => $data['transport_mode_id'],
            'service_type_id' => $data['service_type_id'],
            'container_type_id' => $data['container_type_id'] ?? null,
            'container_count' => $data['container_count'] ?? 1,
            'estimated_weight' => $data['estimated_weight'] ?? 0,
            'estimated_cbm' => $data['estimated_cbm'] ?? 0,
            'additional_services' => array_column($data['additional_services'] ?? [], 'id'),
            'company_id' => $user->company_id,
        ];
        $estimate = $this->priceEstimateService->estimate($estimateParams);

        // Handle MSDS file upload
        $msdsPath = null;
        if ($request->hasFile('msds_file')) {
            $msdsPath = $request->file('msds_file')->store('msds_files', 'public');
        }

        $booking = Booking::create([
            ...$data,
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'status' => 'submitted',
            'estimated_price' => $estimate['estimated_price'],
            'msds_file' => $msdsPath,
            'additional_services' => null, // Remove from data array to avoid conflict with relationship
        ]);

        // Tambah layanan tambahan
        if (! empty($data['additional_services'])) {
            foreach ($data['additional_services'] as $svc) {
                $booking->additionalServices()->attach($svc['id'], [
                    'notes' => $svc['notes'] ?? null,
                ]);
            }
        }
        $booking->load(['originLocation', 'destinationLocation', 'serviceType', 'additionalServices', 'company']);

        $prepaidPayload = null;

        // Jika perusahaan menggunakan skema pre-paid, langsung buat shipment, invoice, dan transaksi Midtrans.
        if ($booking->company && $booking->company->payment_type === 'prepaid') {
            // Set booking menjadi approved oleh sistem
            $booking->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Buat shipment mirip convertToShipment admin
            $shipment = Shipment::create([
                'booking_id' => $booking->id,
                'company_id' => $booking->company_id,
                'origin_location_id' => $booking->origin_location_id,
                'destination_location_id' => $booking->destination_location_id,
                'transport_mode_id' => $booking->transport_mode_id,
                'service_type_id' => $booking->service_type_id,
                'status' => 'created',
                'created_by' => $user->id,
                'cargo_category_id' => $booking->cargo_category_id,
                'is_dangerous_goods' => $booking->is_dangerous_goods,
                'dg_class_id' => $booking->dg_class_id,
                'un_number' => $booking->un_number,
                'msds_file' => $booking->msds_file,
                'equipment_condition' => $booking->equipment_condition,
                'temperature' => $booking->temperature,
            ]);

            $shipment->trackings()->create([
                'status' => 'created',
                'notes' => 'Shipment dibuat otomatis (pre-paid) dari booking '.$booking->booking_number,
                'tracked_at' => now(),
                'updated_by' => $user->id,
            ]);

            // Buat invoice dari estimated price
            $issuedDate = now()->toDateString();
            $dueDate = $issuedDate; // pre-paid: jatuh tempo sama dengan tanggal terbit
            $subtotal = (float) ($estimate['estimated_price'] ?? 0);
            $taxAmount = $subtotal * 0.11;
            $totalAmount = $subtotal + $taxAmount;

            $invoice = Invoice::create([
                'shipment_id' => $shipment->id,
                'company_id' => $booking->company_id,
                'issued_date' => $issuedDate,
                'due_date' => $dueDate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
                'notes' => null,
                'created_by' => $user->id,
            ]);

            $invoice->items()->create([
                'description' => 'Freight & services untuk booking '.$booking->booking_number,
                'quantity' => 1,
                'unit_price' => $totalAmount,
                'total_price' => $totalAmount,
            ]);

            // Buat transaksi Midtrans (Snap)
            $customerDetails = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ];

            $snap = $this->midtransService->createSnapTransaction($invoice, $customerDetails);
            $prepaidPayload = [
                'invoice' => $invoice->load('items'),
                'midtrans' => $snap,
            ];
        }

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'data' => $booking,
            'estimated_price' => $estimate['estimated_price'],
            'breakdown' => $estimate['breakdown'],
            'prepaid' => $prepaidPayload,
        ], 201);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->company_id !== $user->company_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $booking->load([
            'originLocation', 'destinationLocation', 'transportMode',
            'serviceType', 'containerType', 'additionalServices', 'shipment',
        ]);

        return response()->json(['data' => $booking]);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->company_id !== $user->company_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Hanya bisa cancel jika status masih 'submitted'
        if ($booking->status !== 'submitted') {
            return response()->json(['message' => 'Hanya booking dengan status "Submitted" yang dapat dibatalkan.'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Booking berhasil dibatalkan.', 'data' => $booking]);
    }
}
