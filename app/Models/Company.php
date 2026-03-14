<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'npwp',
        'nib',
        'address',
        'city',
        'province',
        'postal_code',
        'contact_person',
        'email',
        'phone',
        'status',
        'billing_cycle',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function customerDiscounts(): HasMany
    {
        return $this->hasMany(CustomerDiscount::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasOverdueInvoices(): bool
    {
        return $this->invoices()->where('status', 'overdue')->exists();
    }
}
