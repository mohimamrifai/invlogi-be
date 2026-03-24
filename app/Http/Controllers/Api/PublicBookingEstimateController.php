<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingPriceEstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Estimasi harga booking untuk pengunjung (tanpa login).
 * Diskon perusahaan tidak diterapkan (company_id null).
 */
class PublicBookingEstimateController extends Controller
{
    public function __construct(
        private BookingPriceEstimateService $priceEstimateService
    ) {}

    public function estimate(Request $request): JsonResponse
    {
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
            'company_id' => null,
        ];

        $result = $this->priceEstimateService->estimate($params);

        return response()->json(['data' => $result]);
    }
}
