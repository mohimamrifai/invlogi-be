<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with([
            'company:id,name',
            'user:id,name',
            'originLocation:id,name,code',
            'destinationLocation:id,name,code',
            'serviceType:id,name,code',
            'transportMode:id,name',
        ]);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhere('cargo_description', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load([
            'company', 'user', 'originLocation', 'destinationLocation',
            'transportMode', 'serviceType', 'containerType',
            'additionalServices', 'shipment', 'approvedByUser:id,name',
        ]);

        return response()->json(['data' => $booking]);
    }

    /**
     * Setujui booking.
     */
    public function approve(Request $request, Booking $booking): JsonResponse
    {
        if (! in_array($booking->status, ['submitted', 'confirmed'])) {
            return response()->json(['message' => 'Booking tidak dalam status yang bisa disetujui.'], 422);
        }

        $booking->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Booking berhasil disetujui.',
            'data' => $booking,
        ]);
    }

    /**
     * Tolak booking.
     */
    public function reject(Request $request, Booking $booking): JsonResponse
    {
        if (! in_array($booking->status, ['submitted', 'confirmed'])) {
            return response()->json(['message' => 'Booking tidak dalam status yang bisa ditolak.'], 422);
        }

        $request->validate(['reason' => 'required|string|max:1000']);

        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Booking berhasil ditolak.',
            'data' => $booking,
        ]);
    }

    /**
     * Konversi booking yang sudah disetujui → Shipment.
     */
    public function convertToShipment(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->status !== 'approved') {
            return response()->json(['message' => 'Hanya booking yang sudah disetujui yang bisa dikonversi.'], 422);
        }

        if ($booking->shipment()->exists()) {
            return response()->json(['message' => 'Booking ini sudah memiliki shipment.'], 422);
        }

        $shipment = Shipment::create([
            'booking_id' => $booking->id,
            'company_id' => $booking->company_id,
            'origin_location_id' => $booking->origin_location_id,
            'destination_location_id' => $booking->destination_location_id,
            'transport_mode_id' => $booking->transport_mode_id,
            'service_type_id' => $booking->service_type_id,
            'status' => 'created',
            'created_by' => $request->user()->id,
        ]);

        // Buat tracking awal
        $shipment->trackings()->create([
            'status' => 'created',
            'notes' => 'Shipment dibuat dari booking ' . $booking->booking_number,
            'tracked_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Shipment berhasil dibuat dari booking.',
            'data' => $shipment->load(['booking', 'trackings']),
        ], 201);
    }
}
