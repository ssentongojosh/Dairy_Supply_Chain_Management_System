
# DSCMS - Dairy Supply Chain Management System

A comprehensive Laravel-based system for managing dairy supply chain operations from farm to retail.

## Overview

DSCMS (Dairy Supply Chain Management System) is designed to track and manage the movement of dairy products through the entire supply chain — from raw milk collection by dairy farmers, processing in factories, distribution to wholesalers, and finally to retailers.

## Features

- **Product Tracking**: Complete traceability from farm to consumer
- **Inventory Management**: Smart FEFO (First Expired, First Out) system
- **Role-based Access**: Different dashboards for farmers, wholesalers, retailers, and administrators
- **Quality Monitoring**: Automated quality control and testing workflows
- **Analytics Dashboard**: ML-driven insights and demand prediction
- **Real-time Reporting**: Live updates on supply chain metrics

## User Roles

- **Admin**: System oversight and analytics access
- **Farmers**: Milk production and quality data input
- **Wholesalers**: Bulk distribution management
- **Retailers**: End-point sales and inventory
- **Warehouse Managers**: Storage and logistics coordination
- **Quality Assurance**: Testing and compliance monitoring
- **Drivers**: Delivery tracking and route optimization
- **Executives**: High-level reporting and decision making
- **Inspectors**: Regulatory compliance and auditing

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Generate application key: `php artisan key:generate`
4. Install frontend dependencies: `yarn install`
5. Build assets: `yarn build`
6. Start the server: `php artisan serve`


# Admin User Setup

After setting up the project, you can create admin users using one of these methods:

## Using Database Seeders (Recommended)
```bash
php artisan db:seed --class=AdminUserSeeder
```

## Using Tinker
```bash
php artisan tinker $user = new App\Models\User();
$user->name = 'Your Name'; $user->email = 'your@email.com';
$user->password = Hash::make('your_password');
$user->role = 'admin'; $user->save();
```

> after that you can log in with the default admin credentils shown below

Default admin credentials:
- Email: admin@dscms.com
- Password: admin123 

## Technology Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Blade templates, Bootstrap 5, AlpineJS
- **Database**: MySQL
- **Build Tools**: Vite, Yarn
- **Styling**: TailwindCSS, SCSS


## License

This project is proprietary software developed for dairy supply chain management.

© 2025 DSCMS. All rights reserved.
