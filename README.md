
# DSCMS - Dairy Supply Chain Management System

A comprehensive Laravel-based system for managing dairy supply chain operations from farm to retail.

## Overview

DSCMS (Dairy Supply Chain Management System) is designed to track and manage the movement of dairy products through the entire supply chain — from raw milk collection by dairy farmers, processing in factories, distribution to wholesalers, and finally to retailers.

## Features

- **Product Tracking**: Complete traceability from farm to consumer
- **Inventory Management**: Smart FEFO (First Expired, First Out) system
- **Role-based Access**: Different dashboards for farmers, wholesalers, retailers, suppliers, and administrators
- **Order Management**: Complete order workflow from creation to delivery
- **Quality Monitoring**: Automated quality control and testing workflows
- **Analytics Dashboard**: ML-driven insights and demand prediction
- **Real-time Reporting**: Live updates on supply chain metrics
- **Payment Processing**: Multi-method payment verification and tracking
- **Document Verification**: User verification system with document upload

## User Roles

- **Admin**: System oversight and analytics access
- **Farmers**: Milk production and quality data input
- **Suppliers**: Equipment and supply management for farms and factories
- **Plant Managers**: Production facility management and processing
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
4. Copy environment file: `cp .env.example .env`
5. Configure database settings in `.env`
6. Run migrations: `php artisan migrate`
7. Install frontend dependencies: `npm install` or `yarn install`
8. Build assets: `npm run build` or `yarn build`
9. Seed the database: `php artisan db:seed`
10. Start the server: `php artisan serve`

## Database Setup

After running migrations, seed the database with sample users:

```bash
# Seed all users
php artisan db:seed

# Or seed specific user types
php artisan db:seed --class=AdminUserSeeder
```

## Using Tinker
```bash
php artisan tinker $user = new App\Models\User();
$user->name = 'Your Name'; $user->email = 'your@email.com';
$user->password = Hash::make('your_password');
$user->role = 'admin'; $user->save();
```


> after that you can log in with the default admin credentials shown below

Default admin credentials:
- Email: admin@dscms.com
- Password: admin123 
=======
### Admin Users
- **Email**: admin@dscms.com
- **Password**: admin123

### Supplier Users
- **AgriSupply Co.**
  - Email: supplier@agrisupply.com
  - Password: password123

- **FarmTech Solutions**
  - Email: contact@farmtech.ph
  - Password: password123

- **GreenHarvest Supplies**
  - Email: info@greenharvest.com
  - Password: password123

- **Dairy Equipment Plus**
  - Email: sales@dairyequipment.ph
  - Password: password123

- **ProFeed Nutrition**
  - Email: orders@profeed.com
  - Password: password123

### Test Users (Various Roles)
- **Farmer**: farmer@test.com / password123
- **Retailer**: retailer@test.com / password123
- **Wholesaler**: wholesaler@test.com / password123
- **Plant Manager**: manager@test.com / password123

## Role-Specific Features

### Suppliers
- **Dashboard**: Order statistics, inventory overview, revenue tracking
- **Order Management**: View, approve, reject, and ship orders
- **Inventory Management**: Add products, manage stock levels, set thresholds
- **Bulk Operations**: CSV import/export for inventory management

### Farmers
- **Production Tracking**: Milk production records and quality metrics
- **Inventory**: Manage raw materials and finished products
- **Order Fulfillment**: Process orders from wholesalers and retailers

### Retailers
- **Point of Sale**: Manage retail transactions
- **Inventory**: Track stock levels with auto-reorder capabilities
- **Supplier Management**: Browse and order from multiple suppliers
- **Payment Processing**: Handle various payment methods

### Wholesalers
- **Bulk Distribution**: Manage large-scale orders and distribution
- **Multi-tier Ordering**: Source from farmers/factories, sell to retailers
- **Logistics**: Coordinate deliveries and shipments

## API Endpoints

### Authentication
- `POST /login` - User authentication
- `POST /register` - New user registration
- `POST /logout` - User logout

### Orders
- `GET /supplier/orders` - Supplier order dashboard
- `POST /supplier/orders/{order}/approve` - Approve order
- `POST /supplier/orders/{order}/reject` - Reject order
- `POST /supplier/orders/{order}/ship` - Mark order as shipped

### Inventory
- `GET /supplier/inventory` - Inventory management
- `POST /supplier/inventory` - Add new product
- `PUT /supplier/inventory/{inventory}` - Update inventory item
- `DELETE /supplier/inventory/{inventory}` - Remove inventory item
>>>>>>> parent of ee001a0 (Update README.md to clarify supplier role description and adjust test user passwords)

## Technology Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Blade templates, Bootstrap 5, AlpineJS
- **Database**: MySQL/SQLite
- **Build Tools**: Vite, NPM/Yarn
- **Styling**: TailwindCSS, SCSS
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel Storage (local/cloud)

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
# Fix code style
./vendor/bin/pint

# Check code style
./vendor/bin/pint --test
```

### Database Management
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Troubleshooting

### Common Issues

1. **Route not found errors**: Clear route cache with `php artisan route:clear`
2. **View not found errors**: Clear view cache with `php artisan view:clear`
3. **Permission denied**: Check file permissions for storage and bootstrap/cache directories
4. **Database connection**: Verify `.env` database configuration

### Support

For technical support or feature requests, please contact the development team.

## License

This project is proprietary software developed for dairy supply chain management.

© 2025 DSCMS. All rights reserved.
