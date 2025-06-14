<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case USER = 'user';  // Adding the standard user role
    case RETAILER = 'retailer';
    case WHOLESALER = 'wholesaler';
    case FARMER = 'farmer';
    case DRIVER = 'driver';
    case WAREHOUSE_MANAGER = 'warehouse_manager';
    case EXECUTIVE = 'executive';
    case INSPECTOR = 'inspector';
    case QUALITY_ASSURANCE = 'quality_assurance';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'Standard User',  // Adding corresponding label
            self::RETAILER => 'Retailer',
            self::WHOLESALER => 'Wholesaler',
            self::FARMER => 'Dairy Farmer',
            self::DRIVER => 'Delivery Driver',
            self::WAREHOUSE_MANAGER => 'Warehouse Manager',
            self::EXECUTIVE => 'Executive',
            self::INSPECTOR => 'Field Inspector',
            self::QUALITY_ASSURANCE => 'Quality Assurance Manager',
        };
    }
}
