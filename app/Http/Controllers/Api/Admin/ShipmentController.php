<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\Container;
use App\Models\Rack;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Shipment::with([
            'company:id,name', 'originLocation:id,name,code',
            'destinationLocation:id,name,code', 'serviceType:id,name,code',
        ]);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('shipment_number', 'like', "%{$s}%")
                ->orWhere('waybill_number', 'like', "%{$s}%"));
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function show(Shipment $shipment): JsonResponse
    {
        $shipment->load([
            'booking', 'company', 'originLocation', 'destinationLocation',
            'transportMode', 'serviceType', 'createdByUser:id,name',
            'containers.containerType', 'containers.racks.items',
            'items', 'trackings.photos', 'trackings.updatedByUser:id,name',
            'invoice',
        ]);

        return response()->json(['data' => $shipment]);
    }

    public function update(Request $request, Shipment $shipment): JsonResponse
    {
        $data = $request->validate([
            'estimated_departure' => 'nullable|date',
            'estimated_arrival' => 'nullable|date',
            'actual_departure' => 'nullable|date',
            'actual_arrival' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $shipment->update($data);

        return response()->json(['message' => 'Shipment diperbarui.', 'data' => $shipment]);
    }

    // ── STATUS TRACKING ──
    public function updateTracking(Request $request, Shipment $shipment): JsonResponse
    {
        $statuses = config('shipment.tracking_statuses', []);
        $statusRule = empty($statuses) ? 'required|string' : 'required|string|in:' . implode(',', $statuses);
        $data = $request->validate([
            'status' => $statusRule,
            'notes' => 'nullable|string',
            'location' => 'nullable|string',
            'tracked_at' => 'nullable|date',
            'photos' => 'nullable|array',
            'photos.*' => 'file|image|max:5120',
        ]);

        $tracking = $shipment->trackings()->create([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'location' => $data['location'] ?? null,
            'tracked_at' => $data['tracked_at'] ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        // Upload foto
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store("tracking/{$shipment->id}", 'public');
                $tracking->photos()->create(['path' => $path]);
            }
        }

        // Update status shipment
        $shipment->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Tracking berhasil diperbarui.',
            'data' => $tracking->load('photos'),
        ], 201);
    }

    // ── CONTAINER MANAGEMENT ──
    public function addContainer(Request $request, Shipment $shipment): JsonResponse
    {
        $data = $request->validate([
            'container_type_id' => 'required|exists:container_types,id',
            'container_number' => 'nullable|string|max:255',
            'seal_number' => 'nullable|string|max:255',
        ]);

        $container = $shipment->containers()->create($data);

        return response()->json([
            'message' => 'Container ditambahkan.',
            'data' => $container->load('containerType'),
        ], 201);
    }

    // ── RACK MANAGEMENT ──
    public function addRack(Request $request, Container $container): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        $rack = $container->racks()->create($data);

        return response()->json(['message' => 'Rack ditambahkan.', 'data' => $rack], 201);
    }

    // ── SHIPMENT ITEMS ──
    public function addItem(Request $request, Shipment $shipment): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'gross_weight' => 'required|numeric|min:0',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'cbm' => 'nullable|numeric',
            'is_fragile' => 'boolean',
            'is_stackable' => 'boolean',
            'placement_type' => 'required|in:rack,floor',
            'container_id' => 'nullable|exists:containers,id',
            'rack_id' => 'nullable|exists:racks,id',
        ]);

        $item = $shipment->items()->create($data);

        return response()->json(['message' => 'Item ditambahkan.', 'data' => $item], 201);
    }

    public function updateItem(Request $request, ShipmentItem $item): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'sometimes|integer|min:1',
            'gross_weight' => 'sometimes|numeric|min:0',
            'length' => 'nullable|numeric', 'width' => 'nullable|numeric', 'height' => 'nullable|numeric',
            'cbm' => 'nullable|numeric',
            'is_fragile' => 'boolean', 'is_stackable' => 'boolean',
            'placement_type' => 'sometimes|in:rack,floor',
            'container_id' => 'nullable|exists:containers,id',
            'rack_id' => 'nullable|exists:racks,id',
        ]);

        $item->update($data);

        return response()->json(['message' => 'Item diperbarui.', 'data' => $item]);
    }

    public function destroyItem(ShipmentItem $item): JsonResponse
    {
        $item->delete();
        return response()->json(['message' => 'Item dihapus.']);
    }

    public function downloadWaybillPdf(Shipment $shipment)
    {
        $shipment->load([
            'originLocation', 'destinationLocation',
            'trackings' => fn ($q) => $q->orderBy('tracked_at', 'asc'),
        ]);

        $pdf = Pdf::loadView('pdf.waybill', ['shipment' => $shipment]);

        return $pdf->download('waybill-' . $shipment->waybill_number . '.pdf');
    }
}
