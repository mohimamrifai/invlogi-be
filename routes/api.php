<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\PublicTrackingController;
use App\Http\Controllers\Api\Customer\RegistrationController;
use App\Http\Controllers\Api\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Api\Customer\ShipmentController as CustomerShipmentController;
use App\Http\Controllers\Api\Customer\InvoiceController as CustomerInvoiceController;
use App\Http\Controllers\Api\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Api\Customer\CompanyController as CustomerCompanyController;
use App\Http\Controllers\Api\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\MasterDataController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\ShipmentController as AdminShipmentController;
use App\Http\Controllers\Api\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Api\Admin\VendorController;
use App\Http\Controllers\Api\Admin\BranchController;
use App\Http\Controllers\Api\Admin\CustomerDiscountController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes – Invlogi Backend
|--------------------------------------------------------------------------
*/

// ══════════════════════════════════════════════
//  PUBLIC (tanpa autentikasi)
// ══════════════════════════════════════════════
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegistrationController::class, 'register']);
Route::get('/tracking', [PublicTrackingController::class, 'track']);
Route::get('/tracking/waybill-pdf', [PublicTrackingController::class, 'waybillPdf']);
// Midtrans notification (no auth - called by Midtrans)
Route::post('/payments/midtrans/notification', [MidtransWebhookController::class, 'notification']);

// ══════════════════════════════════════════════
//  AUTHENTICATED (memerlukan Sanctum token)
// ══════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth & Profil ──
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // ══════════════════════════════════════════
    //  ADMIN INTERNAL
    // ══════════════════════════════════════════
    Route::prefix('admin')->middleware('admin')->group(function () {

        // Customer Management
        Route::apiResource('companies', CompanyController::class);
        Route::post('companies/{company}/approve', [CompanyController::class, 'approve']);
        Route::post('companies/{company}/reject', [CompanyController::class, 'reject']);
        // Branch Management (nested under company)
        Route::get('companies/{company}/branches', [BranchController::class, 'index']);
        Route::post('companies/{company}/branches', [BranchController::class, 'store']);
        Route::get('companies/{company}/branches/{branch}', [BranchController::class, 'show']);
        Route::put('companies/{company}/branches/{branch}', [BranchController::class, 'update']);
        Route::delete('companies/{company}/branches/{branch}', [BranchController::class, 'destroy']);
        // Customer Discount Management (nested under company)
        Route::get('companies/{company}/customer-discounts', [CustomerDiscountController::class, 'index']);
        Route::post('companies/{company}/customer-discounts', [CustomerDiscountController::class, 'store']);
        Route::get('companies/{company}/customer-discounts/{customerDiscount}', [CustomerDiscountController::class, 'show']);
        Route::put('companies/{company}/customer-discounts/{customerDiscount}', [CustomerDiscountController::class, 'update']);
        Route::delete('companies/{company}/customer-discounts/{customerDiscount}', [CustomerDiscountController::class, 'destroy']);

        // User Management
        Route::apiResource('users', UserController::class);

        // Master Data – Locations
        Route::get('locations', [MasterDataController::class, 'locations']);
        Route::post('locations', [MasterDataController::class, 'storeLocation']);
        Route::put('locations/{location}', [MasterDataController::class, 'updateLocation']);
        Route::delete('locations/{location}', [MasterDataController::class, 'destroyLocation']);

        // Master Data – Transport Modes
        Route::get('transport-modes', [MasterDataController::class, 'transportModes']);
        Route::post('transport-modes', [MasterDataController::class, 'storeTransportMode']);
        Route::put('transport-modes/{transportMode}', [MasterDataController::class, 'updateTransportMode']);
        Route::delete('transport-modes/{transportMode}', [MasterDataController::class, 'destroyTransportMode']);

        // Master Data – Service Types
        Route::get('service-types', [MasterDataController::class, 'serviceTypes']);
        Route::post('service-types', [MasterDataController::class, 'storeServiceType']);
        Route::put('service-types/{serviceType}', [MasterDataController::class, 'updateServiceType']);
        Route::delete('service-types/{serviceType}', [MasterDataController::class, 'destroyServiceType']);

        // Master Data – Container Types
        Route::get('container-types', [MasterDataController::class, 'containerTypes']);
        Route::post('container-types', [MasterDataController::class, 'storeContainerType']);
        Route::put('container-types/{containerType}', [MasterDataController::class, 'updateContainerType']);
        Route::delete('container-types/{containerType}', [MasterDataController::class, 'destroyContainerType']);

        // Master Data – Additional Services
        Route::get('additional-services', [MasterDataController::class, 'additionalServices']);
        Route::post('additional-services', [MasterDataController::class, 'storeAdditionalService']);
        Route::put('additional-services/{additionalService}', [MasterDataController::class, 'updateAdditionalService']);
        Route::delete('additional-services/{additionalService}', [MasterDataController::class, 'destroyAdditionalService']);

        // Booking Management
        Route::get('bookings', [AdminBookingController::class, 'index']);
        Route::get('bookings/{booking}', [AdminBookingController::class, 'show']);
        Route::post('bookings/{booking}/approve', [AdminBookingController::class, 'approve']);
        Route::post('bookings/{booking}/reject', [AdminBookingController::class, 'reject']);
        Route::post('bookings/{booking}/convert-to-shipment', [AdminBookingController::class, 'convertToShipment']);

        // Shipment Management
        Route::get('shipments', [AdminShipmentController::class, 'index']);
        Route::get('shipments/{shipment}', [AdminShipmentController::class, 'show']);
        Route::put('shipments/{shipment}', [AdminShipmentController::class, 'update']);
        Route::post('shipments/{shipment}/tracking', [AdminShipmentController::class, 'updateTracking']);
        Route::post('shipments/{shipment}/containers', [AdminShipmentController::class, 'addContainer']);
        Route::post('containers/{container}/racks', [AdminShipmentController::class, 'addRack']);
        Route::post('shipments/{shipment}/items', [AdminShipmentController::class, 'addItem']);
        Route::put('shipment-items/{item}', [AdminShipmentController::class, 'updateItem']);
        Route::delete('shipment-items/{item}', [AdminShipmentController::class, 'destroyItem']);
        Route::get('shipments/{shipment}/waybill-pdf', [AdminShipmentController::class, 'downloadWaybillPdf']);

        // Invoice Management
        Route::get('invoices', [AdminInvoiceController::class, 'index']);
        Route::get('invoices/{invoice}', [AdminInvoiceController::class, 'show']);
        Route::get('invoices/{invoice}/pdf', [AdminInvoiceController::class, 'downloadPdf']);
        Route::post('invoices', [AdminInvoiceController::class, 'store']);
        Route::put('invoices/{invoice}', [AdminInvoiceController::class, 'update']);

        // Payment / AR Management
        Route::get('payments', [AdminPaymentController::class, 'index']);
        Route::get('payments/overdue-invoices', [AdminPaymentController::class, 'overdueInvoices']);
        Route::get('payments/{payment}', [AdminPaymentController::class, 'show']);

        // Vendor & Pricing Management
        Route::apiResource('vendors', VendorController::class);
        Route::post('vendors/{vendor}/services', [VendorController::class, 'storeService']);
        Route::post('vendor-services/{vendorService}/pricings', [VendorController::class, 'storePricing']);
        Route::put('pricings/{pricing}', [VendorController::class, 'updatePricing']);
    });

    // ══════════════════════════════════════════
    //  CUSTOMER PORTAL
    // ══════════════════════════════════════════
    Route::prefix('customer')->middleware('customer')->group(function () {

        // Dashboard (ringkasan untuk halaman Dashboard Customer Portal)
        Route::get('dashboard', [CustomerDashboardController::class, 'index']);

        // Booking
        Route::post('bookings/estimate-price', [CustomerBookingController::class, 'estimatePrice']);
        Route::get('bookings', [CustomerBookingController::class, 'index']);
        Route::post('bookings', [CustomerBookingController::class, 'store']);
        Route::get('bookings/{booking}', [CustomerBookingController::class, 'show']);

        // Shipment
        Route::get('shipments', [CustomerShipmentController::class, 'index']);
        Route::get('shipments/{shipment}', [CustomerShipmentController::class, 'show']);

        // Invoice
        Route::get('invoices', [CustomerInvoiceController::class, 'index']);
        Route::get('invoices/{invoice}', [CustomerInvoiceController::class, 'show']);
        Route::get('invoices/{invoice}/pdf', [CustomerInvoiceController::class, 'downloadPdf']);
        // Payment
        Route::get('payments', [CustomerPaymentController::class, 'index']);
        Route::post('invoices/{invoice}/pay', [CustomerPaymentController::class, 'pay']);

        // Company Settings
        Route::get('company', [CustomerCompanyController::class, 'show']);
        Route::put('company', [CustomerCompanyController::class, 'update']);
    });
});
