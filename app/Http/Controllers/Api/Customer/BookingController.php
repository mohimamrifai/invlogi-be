<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingPriceEstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingPriceEstimateService $priceEstimateService
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
            'cargo_description' => 'nullable|string',
            'pickup_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'additional_services' => 'nullable|array',
            'additional_services.*.id' => 'exists:additional_services,id',
            'additional_services.*.notes' => 'nullable|string',
        ]);

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

        $booking = Booking::create([
            ...$data,
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'status' => 'submitted',
            'estimated_price' => $estimate['estimated_price'],
        ]);

        // Tambah layanan tambahan
        if (! empty($data['additional_services'])) {
            foreach ($data['additional_services'] as $svc) {
                $booking->additionalServices()->attach($svc['id'], [
                    'notes' => $svc['notes'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'data' => $booking->load(['originLocation', 'destinationLocation', 'serviceType', 'additionalServices']),
            'estimated_price' => $estimate['estimated_price'],
            'breakdown' => $estimate['breakdown'],
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
}
