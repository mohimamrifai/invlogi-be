<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    /**
     * Registrasi perusahaan baru (status = pending).
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            // Data Perusahaan
            'company_name' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:30',
            'nib' => 'nullable|string|max:30',
            'company_address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'company_phone' => 'nullable|string|max:20',
            // Data User (Company Admin)
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $company = Company::create([
            'name' => $data['company_name'],
            'npwp' => $data['npwp'] ?? null,
            'nib' => $data['nib'] ?? null,
            'address' => $data['company_address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'phone' => $data['company_phone'] ?? null,
            'contact_person' => $data['name'],
            'email' => $data['email'],
            'status' => 'pending',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'user_type' => 'customer',
            'company_id' => $company->id,
            'status' => 'pending',
        ]);

        $user->assignRole('company_admin');

        return response()->json([
            'message' => 'Registrasi berhasil. Akun Anda akan direview oleh tim kami.',
            'data' => [
                'company' => $company,
                'user' => $user->load('roles'),
            ],
        ], 201);
    }
}
