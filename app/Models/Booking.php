<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number', 'company_id', 'user_id',
        'origin_location_id', 'destination_location_id',
        'transport_mode_id', 'service_type_id',
        'container_type_id', 'container_count',
        'estimated_weight', 'estimated_cbm',
        'cargo_description', 'pickup_date',
        'estimated_price', 'status',
        'rejection_reason', 'notes',
        'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'approved_at' => 'datetime',
            'estimated_weight' => 'decimal:2',
            'estimated_cbm' => 'decimal:2',
            'estimated_price' => 'decimal:2',
            'container_count' => 'integer',
        ];
    }

    // ── Auto-generate booking number ──
    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BK-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
    }

    // ── Relationships ──
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function transportMode(): BelongsTo
    {
        return $this->belongsTo(TransportMode::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function containerType(): BelongsTo
    {
        return $this->belongsTo(ContainerType::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function additionalServices(): BelongsToMany
    {
        return $this->belongsToMany(AdditionalService::class, 'booking_additional_service')
            ->withPivot(['notes', 'price'])
            ->withTimestamps();
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
