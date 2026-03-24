<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Shipment::with([
            'originLocation:id,name,code', 'destinationLocation:id,name,code',
            'serviceType:id,name,code',
        ])->where('company_id', $user->company_id);

        if ($request->filled('status')) $query->where('status', $request->status);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('waybill_number', 'like', "%{$s}%")
                    ->orWhere('shipment_number', 'like', "%{$s}%");
            });
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function show(Request $request, Shipment $shipment): JsonResponse
    {
        if ($shipment->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $shipment->load([
            'originLocation', 'destinationLocation', 'transportMode', 'serviceType',
            'items', 'trackings.photos', 'invoice',
        ]);

        return response()->json(['data' => $shipment]);
    }
}
