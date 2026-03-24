<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    // ── LOCATIONS ──
    public function locations(Request $request): JsonResponse
    {
        $query = \App\Models\Location::query();
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        if ($request->boolean('active_only', false)) {
            $query->active();
        }

        return response()->json($query->orderBy('name')->paginate($request->per_page ?? 15));
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:locations,code',
            'type' => 'required|in:port,city,hub,warehouse',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        return response()->json(['data' => \App\Models\Location::create($data)], 201);
    }

    public function updateLocation(Request $request, \App\Models\Location $location): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => "nullable|string|max:20|unique:locations,code,{$location->id}",
            'type' => 'sometimes|in:port,city,hub,warehouse',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $location->update($data);
        return response()->json(['data' => $location]);
    }

    public function destroyLocation(\App\Models\Location $location): JsonResponse
    {
        $location->delete();
        return response()->json(['message' => 'Lokasi berhasil dihapus.']);
    }

    // ── TRANSPORT MODES ──
    public function transportModes(Request $request): JsonResponse
    {
        $query = \App\Models\TransportMode::with('serviceTypes')->orderBy('name');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function storeTransportMode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10|unique:transport_modes,code',
            'is_active' => 'boolean',
        ]);
        return response()->json(['data' => \App\Models\TransportMode::create($data)], 201);
    }

    public function updateTransportMode(Request $request, \App\Models\TransportMode $transportMode): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => "nullable|string|max:10|unique:transport_modes,code,{$transportMode->id}",
            'is_active' => 'boolean',
        ]);
        $transportMode->update($data);
        return response()->json(['data' => $transportMode]);
    }

    public function destroyTransportMode(\App\Models\TransportMode $transportMode): JsonResponse
    {
        $transportMode->delete();
        return response()->json(['message' => 'Transport mode berhasil dihapus.']);
    }

    // ── SERVICE TYPES ──
    public function serviceTypes(Request $request): JsonResponse
    {
        $query = \App\Models\ServiceType::with('transportMode');
        if ($request->filled('transport_mode_id')) {
            $query->where('transport_mode_id', $request->transport_mode_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return response()->json($query->orderBy('name')->paginate($request->per_page ?? 15));
    }

    public function storeServiceType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'transport_mode_id' => 'required|exists:transport_modes,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        return response()->json(['data' => \App\Models\ServiceType::create($data)], 201);
    }

    public function updateServiceType(Request $request, \App\Models\ServiceType $serviceType): JsonResponse
    {
        $data = $request->validate([
            'transport_mode_id' => 'sometimes|exists:transport_modes,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $serviceType->update($data);
        return response()->json(['data' => $serviceType]);
    }

    public function destroyServiceType(\App\Models\ServiceType $serviceType): JsonResponse
    {
        $serviceType->delete();
        return response()->json(['message' => 'Service type berhasil dihapus.']);
    }

    // ── CONTAINER TYPES ──
    public function containerTypes(Request $request): JsonResponse
    {
        $query = \App\Models\ContainerType::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('size', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return response()->json($query->orderBy('size')->paginate($request->per_page ?? 15));
    }

    public function storeContainerType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'size' => 'required|string|max:10',
            'capacity_weight' => 'nullable|numeric',
            'capacity_cbm' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);
        return response()->json(['data' => \App\Models\ContainerType::create($data)], 201);
    }

    public function updateContainerType(Request $request, \App\Models\ContainerType $containerType): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'size' => 'sometimes|string|max:10',
            'capacity_weight' => 'nullable|numeric',
            'capacity_cbm' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);
        $containerType->update($data);
        return response()->json(['data' => $containerType]);
    }

    public function destroyContainerType(\App\Models\ContainerType $containerType): JsonResponse
    {
        $containerType->delete();
        return response()->json(['message' => 'Container type berhasil dihapus.']);
    }

    // ── ADDITIONAL SERVICES ──
    public function additionalServices(Request $request): JsonResponse
    {
        $query = \App\Models\AdditionalService::query();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%{$s}%");
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return response()->json($query->orderBy('category')->orderBy('name')->paginate($request->per_page ?? 15));
    }

    public function storeAdditionalService(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:pickup,packing,handling,other',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        return response()->json(['data' => \App\Models\AdditionalService::create($data)], 201);
    }

    public function updateAdditionalService(Request $request, \App\Models\AdditionalService $additionalService): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|in:pickup,packing,handling,other',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $additionalService->update($data);
        return response()->json(['data' => $additionalService]);
    }

    public function destroyAdditionalService(\App\Models\AdditionalService $additionalService): JsonResponse
    {
        $additionalService->delete();
        return response()->json(['message' => 'Additional service berhasil dihapus.']);
    }
}
