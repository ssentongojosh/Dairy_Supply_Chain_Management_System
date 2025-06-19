<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\Role;
use App\Models\Inventory;
use App\Models\Product;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'verified',
        'business_document_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'verified' => 'boolean',
        ];
    }

    /**
     * Check if user has the specified role
     *
     * @param Role $role
     * @return bool
     */
    public function hasRole(Role $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Get the role label for display
     *
     * @return string
     */
    public function getRoleLabel(): string
    {
        return $this->role->label();
    }

    // Check if user is a vendor (farmer)
    public function isVendor(): bool
    {
        return $this->role === Role::FARMER;
    }

    // Check if user is a customer (wholesaler or retailer)
    public function isCustomer(): bool
    {
        return in_array($this->role, [Role::WHOLESALER, Role::RETAILER]);
    }

    // Inventory relationship
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    // Orders where this user is the buyer
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    // Orders where this user is the seller
    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    // Products sold by this user (if they are a vendor)
    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }
}


