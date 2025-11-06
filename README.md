# NexusERP

**NexusERP** is a modern, modular Enterprise Resource Planning (ERP) system built with Laravel 12 and FilamentPHP 4. Designed for scalability and flexibility, NexusERP provides comprehensive business management solutions with a focus on clean architecture, reusable components, and enterprise-grade user experience.

## 🎯 Project Purpose

NexusERP aims to provide a complete business management platform with specialized modules for:

- **Purchase Management**: End-to-end procurement processes including requisitions, RFQs, purchase orders, receiving, and invoice processing
- **Business Partner Management**: Centralized customer and supplier information with relationship tracking
- **Multi-Currency Support**: Global business operations with automatic exchange rate management
- **Company & Status Management**: Multi-entity support with customizable workflow states
- **Future Modules**: Inventory, Asset Management, HR, Facility Management, and more

## ✨ Key Features

- **Modular Architecture**: Clean separation of concerns with independent, reusable modules
- **FilamentPHP 4 Admin Panel**: Modern, responsive admin interface with multiple panel support
- **Multi-Currency Operations**: Built-in currency management with exchange rate tracking
- **Workflow Management**: Status-based workflows using Spatie Model Status
- **Action-Based Business Logic**: Granular, testable business logic using Laravel Actions
- **Serial Numbering**: Controlled document numbering for all transactional entities
- **Audit Trail**: Complete tracking of created_by/updated_by/approved_by for all records
- **Soft Deletes**: Data retention with soft delete support across all models
- **Role-Based Access Control**: Fine-grained permissions using Spatie Laravel Permission

## 🛠️ Tech Stack

### Backend
- **Laravel 12**: Modern PHP framework with cutting-edge features
- **PHP 8.2+**: Latest PHP version with strict typing and performance improvements
- **FilamentPHP 4**: Admin panel builder with rich UI components

### Frontend
- **Tailwind CSS 4**: Utility-first CSS framework via Vite plugin
- **Vite 7**: Fast build tool and development server
- **Alpine.js**: Lightweight JavaScript framework (via Filament)

### Key Packages
- **lorisleiva/laravel-actions**: Action-based business logic pattern
- **spatie/laravel-permission**: Role and permission management
- **spatie/laravel-model-status**: Workflow state management
- **spatie/eloquent-sortable**: Sortable model support
- **ariaieboy/filament-currency**: Currency input components
- **akaunting/laravel-money**: Money value handling
- **filament/spatie-laravel-media-library-plugin**: Media management
- **lara-zeus/activity-timeline**: Activity tracking and visualization
- **parallax/filament-comments**: Commenting system

### Custom Packages (In Development)
- `azaharizaman/laravel-backoffice`: Core backoffice functionality
- `azaharizaman/laravel-serial-numbering`: Controlled document numbering
- `azaharizaman/laravel-uom-management`: Unit of Measure management
- `azaharizaman/laravel-inventory-management`: Inventory operations (planned)

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- NPM or Yarn
- SQLite (default) or MySQL/PostgreSQL

## 🚀 Installation

### Quick Setup

```bash
# Clone the repository
git clone https://github.com/azaharizaman/NexusErp.git
cd NexusErp

# Run automated setup (installs dependencies, generates key, runs migrations, builds assets)
composer setup
```

### Manual Setup

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Install frontend dependencies
npm install

# Build frontend assets
npm run build
```

## 🔧 Development

### Starting the Development Environment

```bash
# Start all development servers concurrently (app server, queue worker, logs, vite)
composer dev
```

This command starts:
- Laravel development server on http://localhost:8000
- Queue worker for background jobs
- Laravel Pail for real-time log viewing
- Vite dev server for hot module replacement

### Individual Services

```bash
# Start only the application server
php artisan serve

# Start only the queue worker
php artisan queue:listen

# Start only the frontend dev server
npm run dev

# Watch logs in real-time
php artisan pail
```

### Running Tests

```bash
# Run the test suite
composer test

# Or directly with PHPUnit
php artisan test
```

### Code Quality

```bash
# Run Laravel Pint for code formatting
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test
```

## 📁 Project Structure

```
NexusErp/
├── app/
│   ├── Actions/           # Business logic actions (Laravel Actions pattern)
│   ├── Filament/          # Filament admin panels
│   │   ├── Nexus/         # Main Nexus panel resources
│   │   └── PurchaseModule/ # Purchase module panel resources
│   ├── Models/            # Eloquent models
│   └── Providers/         # Service providers
├── config/
│   └── nexus-settings.php # Application settings configuration
├── database/
│   ├── factories/         # Model factories for testing
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── docs/                  # Additional documentation
├── resources/
│   ├── views/             # Blade templates
│   └── css/               # Stylesheets
├── routes/                # Application routes
└── tests/                 # Test suite
```

## 📚 Documentation

- [System Architecture Specification](spec/architecture-nexus-erp.md) - Complete system architecture and design requirements
- [Architectural Decisions](ARCHITECTURAL_DECISIONS.md) - Key technical decisions and rationale
- [Progress Checklist](PROGRESS_CHECKLIST.md) - Development roadmap and completed milestones
- [Accounting Module Planning](ACCOUNTING_MODULE_PLANNING.md) - Detailed accounting module implementation plan
- [Purchase Management Planning](PURCHASE_MANAGEMENT_MODULES_PLANNING.md) - Purchase module development roadmap
- [Laravel Actions Guide](LARAVEL_ACTIONS_GUIDE.md) - Business logic implementation patterns
- [Settings Implementation](SETTINGS_IMPLEMENTATION.md) - Application settings management
- [Company Status Management](COMPANY_STATUS_MANAGEMENT.md) - Status workflow documentation
- [Business Partners](docs/business-partners.md) - Business partner module documentation
- [Company Status Rules](docs/company-status-rules.md) - Status transition rules

## 🎯 Current Development Status

NexusERP is under active development. The following phases are completed or in progress:

✅ **Phase 1: Core Foundations & Setup** - Complete
- Package integrations (UOM, Serial Numbering)
- Core database models (Currency, Exchange Rates, Price Lists, Tax Rules, Terms Templates)
- Filament panel configuration with navigation groups

✅ **Phase 2: Procurement Setup Modules** - Complete
- Supplier management (Business Partners)
- Currency and exchange rate management
- Price list management
- Tax rule configuration
- Terms and conditions templates

🚧 **Phase 3: Requisition Management** - In Progress
- ✅ Purchase Requests (PR) with approval workflow
- Request for Quotation (RFQ) - Planned
- Quotation comparison and evaluation - Planned
- Purchase recommendations - Planned

📅 **Future Phases**:
- Phase 4: Sourcing & Ordering (Purchase Orders, Contracts)
- Phase 5: Receiving & Invoice Processing
- Phase 6: Payments & Settlements
- Phase 7: Procurement Insights & Reports
- Phase 8: Administration & Policy
- And more...

See [PROGRESS_CHECKLIST.md](PROGRESS_CHECKLIST.md) for detailed development progress.

## 🤝 Contributing

We welcome contributions to NexusERP! Please follow these guidelines:

1. **Follow the Project's Coding Standards**: 
   - Use Laravel Pint for code formatting
   - Follow PSR-12 coding standards
   - Write unit and feature tests for new functionality

2. **Understand the Architecture**:
   - Review [ARCHITECTURAL_DECISIONS.md](ARCHITECTURAL_DECISIONS.md)
   - Follow the established patterns (Actions, Resources, Models)
   - Use FilamentPHP 4 conventions

3. **Business Logic Pattern**:
   - Implement business logic using Laravel Actions
   - Place actions in `App\Actions` namespace
   - Keep actions granular and reusable

4. **Model Conventions**:
   - Models with status should use Spatie Model Status
   - Sortable models should implement Spatie Eloquent Sortable
   - Currency/money properties should use ariaieboy/filament-currency

5. **Documentation**:
   - Update relevant documentation files
   - Add comments for complex logic
   - Update [ARCHITECTURAL_DECISIONS.md](ARCHITECTURAL_DECISIONS.md) for architectural changes
   - Update [PROGRESS_CHECKLIST.md](PROGRESS_CHECKLIST.md) for completed features

6. **Testing**:
   - Write tests for all new functionality
   - Ensure existing tests pass
   - Run `composer test` before submitting

7. **Pull Requests**:
   - Create descriptive PR titles
   - Reference related issues
   - Provide clear descriptions of changes

## 🔒 Security

If you discover a security vulnerability within NexusERP, please send an email to the project maintainer. All security vulnerabilities will be promptly addressed.

## 📄 License

NexusERP is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

Built with:
- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [FilamentPHP](https://filamentphp.com) - The elegant TALL stack admin panel
- [Spatie](https://spatie.be/open-source) - High-quality Laravel packages
- And many other amazing open-source packages

---

**Status**: Active Development | **Version**: 0.1.0-dev | **Laravel**: 12.x | **FilamentPHP**: 4.x
