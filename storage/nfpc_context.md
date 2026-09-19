# NFPC Laravel Project - Comprehensive Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture & Technology Stack](#architecture--technology-stack)
3. [Directory Structure](#directory-structure)
4. [Core Components](#core-components)
5. [API Structure](#api-structure)
6. [Database Architecture](#database-architecture)
7. [Authentication & Authorization](#authentication--authorization)
8. [Key Features & Modules](#key-features--modules)
9. [Integration Points](#integration-points)
10. [Development Patterns](#development-patterns)
11. [Configuration Details](#configuration-details)
12. [Prompt Generation Guide](#prompt-generation-guide)

---

## Project Overview

**NFPC** is a comprehensive Laravel-based Sales Force Automation (SFA) and Field Force Management system designed for managing sales operations, field teams, inventory, orders, deliveries, invoicing, and reporting. The application supports multi-tenant architecture with organization-based isolation.

### Key Statistics
- **Total Controllers**: 156 files
- **Total Models**: 318 files
- **Database Migrations**: 438 files
- **API Routes**: 1,552 lines
- **Exports**: 99 classes
- **Imports**: 40 classes
- **Observers**: 10 classes
- **Jobs**: 6 classes
- **Console Commands**: 4 classes

---

## Architecture & Technology Stack

### Framework & Core
- **Laravel Framework**: Version compatible with PHP 7.4+
- **PHP Version**: 7.4+
- **Database**: MySQL (Primary), PostgreSQL support, SQL Server support
- **Multi-Database**: Supports `mysql` (default) and `server_mysql` connections
- **Timezone**: Asia/Dubai (configurable per organization)

### Key Packages & Libraries
- **Laravel Passport**: API authentication
- **Spatie Permission**: Role-based access control
- **Maatwebsite Excel**: Excel import/export functionality
- **Intervention Image**: Image processing
- **Laravel Mpdf**: PDF generation
- **Spatie Activity Log**: Activity logging
- **Soft Deletes**: Soft delete functionality across models
- **UUID**: Unique identifier generation
- **JDE Julian Converter**: Date conversion utilities
- **Curl (Ixudra)**: HTTP client operations
- **Google Client**: Firebase messaging integration

### Frontend Technologies
- **Blade Templates**: Server-side rendering
- **Vue.js**: Component-based frontend (ExampleComponent.vue)
- **SASS**: Styling
- **JavaScript**: Bootstrap.js, app.js

---

## Directory Structure

### Root Level
```
nfpc/
├── app/                    # Application core
├── bootstrap/              # Bootstrap files
├── config/                 # Configuration files
├── database/              # Migrations, seeds, factories
├── resources/             # Views, assets, lang
├── routes/                # Route definitions
├── storage/               # Logs, cache, backups
└── .git/                 # Git repository
```

### App Directory (`app/`)
```
app/
├── Console/
│   ├── Commands/         # Artisan commands (4 files)
│   └── Kernel.php        # Console kernel
├── Exceptions/           # Exception handlers
├── Exports/              # Excel export classes (99 files)
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # API controllers (52 files)
│   │   └── Auth/         # Authentication controllers (6 files)
│   ├── Middleware/       # Custom middleware (8 files)
│   └── Kernel.php        # HTTP kernel
├── Imports/              # Excel import classes (40 files)
├── Jobs/                 # Queue jobs (6 files)
├── Library/              # Helper libraries (2 files)
├── Model/                # Eloquent models (318 files)
├── Observers/            # Model observers (10 files)
├── Providers/            # Service providers (5 files)
├── Traits/               # Reusable traits (3 files)
└── User.php              # User model
```

### Database Directory (`database/`)
```
database/
├── factories/            # Model factories
├── migrations/           # Database migrations (438 files)
└── seeds/               # Database seeders (57 files)
```

### Resources Directory (`resources/`)
```
resources/
├── js/                   # JavaScript files
│   ├── app.js
│   ├── bootstrap.js
│   └── components/      # Vue components
├── lang/                 # Language files (en/)
├── sass/                 # SASS stylesheets
└── views/                # Blade templates
    ├── auth/             # Authentication views
    ├── emails/           # Email templates (10 files)
    ├── export/           # Export templates
    ├── html/             # PDF/HTML templates (20+ files)
    └── layouts/          # Layout templates
```

### Config Directory (`config/`)
```
config/
├── app.php               # Application configuration
├── auth.php              # Authentication config
├── database.php          # Database connections
├── dompdf.php           # PDF generation config
├── excel.php             # Excel config
├── ldap.php              # LDAP config
├── mail.php              # Mail configuration
├── permission.php         # Permission config
├── pdf.php               # PDF config
├── queue.php             # Queue configuration
├── sentry.php            # Error tracking
└── view.php              # View configuration
```

---

## Core Components

### 1. Models (`app/Model/`)

The application uses **318 Eloquent models** covering all business entities. Key models include:

#### User & Organization Management
- `User.php` - Core user model with Passport tokens, roles, soft deletes
- `Organisation.php` - Multi-tenant organization model
- `OrganisationRole.php` - Role definitions
- `OrganisationSetting.php` - Organization settings
- `Plan.php`, `PlanFeature.php` - Subscription plans
- `Software.php` - Software module definitions

#### Sales & Customer Management
- `CustomerInfo.php` - Customer master data
- `CustomerType.php`, `CustomerCategory.php`, `CustomerGroup.php`
- `CustomerMerchandiser.php` - Customer-salesman mapping
- `CustomerVisit.php` - Customer visit tracking
- `CustomerActivity.php` - Customer activity logs
- `CustomerComment.php` - Customer comments

#### Salesman & Field Force
- `SalesmanInfo.php` - Salesman details
- `SalesmanType.php` - Salesman type definitions
- `SalesmanRoleMenu.php` - Salesman menu permissions
- `SalesmanLoginLog.php` - Login tracking
- `SalesmanActivityProfileDetail.php` - Activity profiles
- `SalesmanUnloadDetail.php` - Unload tracking
- `SalesmanTripInfos.php` - Trip information

#### Inventory & Warehouse
- `Warehouse.php` - Warehouse master
- `Warehousedetail.php` - Warehouse item details
- `Storagelocation.php` - Storage locations
- `StoragelocationDetail.php` - Storage location details
- `Item.php` - Product/item master
- `ItemGroup.php`, `ItemUomMaster.php` - Item grouping
- `ItemBranchPlant.php` - Branch plant mapping
- `StockAdjustment.php` - Stock adjustments

#### Order Management
- `Order.php` - Order master
- `OrderDetail.php` - Order line items
- `OrderType.php` - Order type definitions
- `OrderTemplate.php` - Order templates
- `OrderLog.php` - Order activity logs

#### Delivery & Logistics
- `Delivery.php` - Delivery master
- `Trip.php` - Trip management
- `Van.php`, `VanCategory.php`, `VanType.php` - Vehicle management
- `OdoMeter.php` - Odometer tracking
- `LoadRequest.php` - Load request management
- `SalesmanLoad.php` - Salesman load tracking

#### Invoicing & Financial
- `Invoice.php` - Invoice master
- `InvoiceDetail.php` - Invoice line items
- `Collection.php` - Payment collections
- `CreditNote.php` - Credit notes
- `DebitNote.php` - Debit notes
- `Estimation.php` - Estimates/quotes
- `Expense.php` - Expense tracking
- `ExpenseCategory.php` - Expense categories

#### Pricing & Promotions
- `PriceDiscoPromo.php` - Pricing, discount, promotion plans
- `PDPItem.php`, `PDPItemGroup.php`, `PDPLob.php` - PDP components
- `PDPRegion.php`, `PDPRoute.php` - PDP geographic mapping
- `Promotional.php` - Promotional campaigns
- `PromotionalPostItem.php` - Promotional postings

#### Geographic & Routing
- `CountryMaster.php` - Country master
- `Region.php` - Region master
- `Area.php` - Area master
- `Depot.php` - Depot master
- `Route.php` - Route master
- `JourneyPlan.php` - Journey planning
- `JourneyPlanDay.php` - Daily journey plans
- `CustomerRoute.php` - Customer-route mapping
- `RouteItemGrouping.php` - Route-item grouping

#### Reporting & Analytics
- `SalesVsGrv.php` - Sales vs GRV reports
- `DIFOTReport.php` - DIFOT reporting
- `Grvreport.php` - GRV reports
- `TimeSheetReport.php` - Timesheet reports
- `SpotReport.php` - Spot reports

#### Workflow & Approvals
- `WorkFlowObject.php` - Workflow objects
- `WorkFlowRuleModule.php` - Workflow modules
- `WorkFlowRuleApprovalRole.php` - Approval roles
- `WorkFlowRuleApprovalUser.php` - Approval users
- `OrgAutoAppWorksflowActionLog.php` - Auto-approval logs

#### Distribution & Merchandising
- `Distribution.php` - Distribution tracking
- `PlanogramPost.php` - Planogram postings
- `PlanogramPostImage.php` - Planogram images
- `AssignInventory.php` - Inventory assignments
- `PortfolioManagementCustomer.php` - Portfolio management
- `PortfolioManagementItem.php` - Portfolio items

#### Surveys & Feedback
- `Survey.php` - Survey master
- `SurveyQuestion.php` - Survey questions
- `SurveyQuestionAnswer.php` - Survey answers
- `ComplaintFeedback.php` - Complaint management

#### Asset & Competitor Tracking
- `AssetTrackingPost.php` - Asset tracking
- `AssetTrackingPostImage.php` - Asset images
- `CompetitorInfo.php` - Competitor information
- `CompetitorInfoImage.php` - Competitor images

#### Custom Fields & Configuration
- `CustomField.php` - Custom field definitions
- `CustomFieldValueSave.php` - Custom field values
- `Module.php` - Module definitions
- `CodeSettingPrd.php` - Code settings

#### Notifications & Communication
- `Notifications.php` - Notification system
- `InvoiceReminder.php` - Invoice reminders

#### Additional Models
- `Currency.php`, `CurrencyMaster.php` - Currency management
- `TaxRates.php` - Tax rate definitions
- `PaymentTerm.php` - Payment terms
- `Brand.php`, `Channel.php` - Brand and channel management
- `Lob.php` - Line of business
- `Zone.php` - Zone management
- `Palette.php` - Pallet management
- `CashierReciept.php` - Cashier receipts
- `Goodreceiptnote.php` - GRN management
- `PurchaseOrder.php` - Purchase orders
- `Vendor.php` - Vendor management
- `ProductCatalog.php` - Product catalog
- `DecimalRate.php` - Decimal rate configuration

### 2. Controllers (`app/Http/Controllers/`)

#### API Controllers (`app/Http/Controllers/Api/`)

**52 API controllers** organized by functionality:

**Authentication & User Management**
- `AuthController.php` - Login, signup, verification, logout
- `ForgotPasswordController.php` - Password reset
- `ResetPasswordController.php` - Password reset handling
- `InviteUserController.php` - User invitations
- `UserPermissionController.php` - User permissions

**Master Data Management**
- `CountryController.php` - Country CRUD
- `RegionController.php` - Region management
- `AreaController.php` - Area management
- `DepotController.php` - Depot management
- `RouteController.php` - Route management
- `ChannelController.php` - Channel management
- `BrandController.php` - Brand management
- `CustomerCategoryController.php` - Customer category
- `CustomerGroupController.php` - Customer group
- `ItemGroupController.php` - Item group
- `ItemMajorCategoryController.php` - Major category
- `ItemUomController.php` - UOM management
- `VanController.php`, `VanCategoryController.php`, `VanTypeController.php` - Vehicle management
- `WarehouseController.php` - Warehouse management
- `StoragelocationController.php` - Storage location
- `ZoneController.php` - Zone management
- `LobController.php` - Line of business

**Customer Management**
- `CustomerController.php` - Customer CRUD, import, export, balance statements
- `CustomerVisitController.php` - Customer visit tracking
- `CustomerActivityController.php` - Customer activities
- `CustomerWarehouseMappingController.php` - Warehouse mapping
- `CustomerRegionMappingController.php` - Region mapping
- `CustomerKamMappingController.php` - KAM/KSM mapping

**Salesman Management**
- `SalesmanController.php` - Salesman CRUD, import, mobile data
- `SalesmanMenuController.php` - Menu management
- `SalesmanActivityProfileController.php` - Activity profiles
- `SalesmanLoadController.php` - Load management
- `SalesmanUnloadController.php` - Unload management
- `SalesmanRouteChangeController.php` - Route changes

**Item Management**
- `ItemController.php` - Item CRUD, import, export, pricing
- `ItemBasePriceController.php` - Base pricing
- `ItemBranchPlantController.php` - Branch plant mapping
- `OutletProductController.php` - Outlet products
- `ProductCatalogController.php` - Product catalog

**Order Management**
- `OrderController.php` - Order CRUD, import, pricing, cancellation
- `OrderPostingController.php` - Order posting
- `OrderPostingPrdController.php` - Order posting (PRD)
- `OrderTypeController.php` - Order types

**Delivery Management**
- `DeliveryController.php` - Delivery CRUD, import, update, cancellation
- `TripController.php` - Trip management (begin/end day)

**Invoicing**
- `InvoiceController.php` - Invoice CRUD, import, ERP posting, cancellation
- `InvoiceReminderController.php` - Invoice reminders

**Financial Management**
- `CollectionsController.php` - Payment collections
- `CreditNoteController.php` - Credit notes, ERP posting
- `DebitNoteController.php` - Debit notes
- `ExpenseController.php` - Expense management
- `ExpenseCategoryController.php` - Expense categories
- `EstimationController.php` - Estimates
- `CashierRecieptController.php` - Cashier receipts

**Inventory Management**
- `StockAdjustmentController.php` - Stock adjustments
- `GoodreceiptnoteController.php` - GRN management
- `PurchaseOrderController.php` - Purchase orders
- `VendorController.php` - Vendor management
- `AssignInventoryController.php` - Inventory assignments
- `VantovanTransferController.php` - Van-to-van transfers

**Pricing & Promotions**
- `PriceDiscoPromoController.php` - Pricing, discount, promotion plans
- `PromotionalController.php` - Promotional campaigns
- `PricingCheckController.php` - Pricing checks

**Journey Planning**
- `JourneyPlanController.php` - Journey plan CRUD, import, customer visits

**Workflow & Approvals**
- `WorkFlowRuleController.php` - Workflow rules
- `WFMApprovalRequestController.php` - Approval requests
- `GeoApprovalController.php` - Geo-approval requests
- `OverdueLimitController.php` - Overdue limit approvals

**Distribution & Merchandising**
- `DistributionController.php` - Distribution tracking, import
- `DistributionModelStockController.php` - Model stock
- `PlanogramController.php` - Planogram management
- `PlanogramPostController.php` - Planogram postings
- `PortfolioManagementController.php` - Portfolio management
- `AssignInventoryController.php` - Inventory assignments

**Surveys & Feedback**
- `SurveyController.php` - Survey management
- `SurveyTypeController.php` - Survey types
- `SurveyQuestionController.php` - Survey questions
- `SurveyByCustomerController.php` - Customer surveys
- `SurveyMerchandiserByCustomer.php` - Merchandiser surveys
- `ComplaintFeedbackController.php` - Complaint management
- `CampaignPictureController.php` - Campaign pictures

**Asset & Competitor Tracking**
- `AssetTrackingController.php` - Asset tracking
- `CompetitorInfoController.php` - Competitor information

**Reports**
- `ReportController.php` - Various sales and operational reports
- `ReportItemController.php` - Item-based reports
- `MerchandiserReportController.php` - Merchandiser reports
- `DashboardController.php`, `Dashboard2Controller.php`, `Dashboard3Controller.php`, `Dashboard4Controller.php` - Dashboards

**Configuration & Settings**
- `OrganisationController.php` - Organization management
- `OrganisationSettingController.php` - Organization settings
- `RoleController.php` - Role management
- `DefaultRolePermissionController.php` - Default permissions
- `ModuleController.php` - Module management
- `CustomFieldController.php` - Custom fields
- `CustomFieldValueController.php` - Custom field values
- `CodeSettingController.php` - Code settings
- `TemplateController.php` - Template management
- `PlanController.php` - Plan management
- `PlanFeatureController.php` - Plan features
- `SoftwareController.php` - Software management
- `OfferController.php` - Offer management
- `ThemeController.php` - Theme management
- `TaxExemptionController.php` - Tax exemption
- `TaxRateController.php` - Tax rates
- `TaxPreferenceController.php` - Tax preferences
- `TaxSettingController.php` - Tax settings
- `ReasonTypeController.php` - Reason types
- `ResonsController.php` - Reasons
- `CurrencyController.php` - Currency management
- `DecimalrateController.php` - Decimal rate
- `PaymentTermsController.php` - Payment terms
- `BankInformationController.php` - Bank information

**Utilities & Helpers**
- `GlobalController.php` - Global utilities, master data, bulk actions
- `DefaultController.php` - Default operations
- `KeyCombinationController.php` - Key combination filters
- `CommonController.php` - Common operations
- `DownloadController.php` - File downloads
- `ExportController.php` - Export operations
- `ImportController.php` - Import operations
- `NotificationController.php` - Notifications
- `ActionhistoryController.php` - Action history
- `ReportmoduleController.php` - Report modules
- `MerchandiserReplacementsController.php` - Merchandiser replacements
- `DriverAndVanSwappingController.php` - Driver/van swapping
- `PaletteController.php` - Pallet management
- `OdoMeterController.php` - Odometer tracking
- `LoadrequestController.php` - Load requests
- `SalesTargetController.php` - Sales targets
- `ShareOfShelfController.php` - Share of shelf
- `ShareOfAssortmentController.php` - Share of assortment
- `ShareOfDisplayController.php` - Share of display
- `SOSController.php` - Share of shelf
- `MarketPromotionController.php` - Market promotions
- `ALBItemController.php` - ALB items
- `PaymentController.php` - Payment/subscription
- `LogDetailController.php` - Login logs
- `TestDistributionController.php` - Test distribution

**Mobile-Specific Controllers**
- Mobile endpoints prefixed with `v1/` in routes

### 3. Middleware (`app/Http/Middleware/`)

**8 custom middleware classes:**
- `Authenticate.php` - Authentication middleware
- `CheckForMaintenanceMode.php` - Maintenance mode check
- `EncryptCookies.php` - Cookie encryption
- `GzipMiddleware.php` - Gzip compression for API responses
- `RedirectIfAuthenticated.php` - Redirect authenticated users
- `TrimStrings.php` - String trimming
- `TrustProxies.php` - Proxy trust
- `VerifyCsrfToken.php` - CSRF token verification

### 4. Jobs (`app/Jobs/`)

**6 queue job classes:**
- `ForgotPasswordJob.php` - Password reset emails
- `InviteUserJob.php` - User invitation emails
- `NewUserRegisterJob.php` - New user registration emails
- `NotificationPostJob.php` - Notification posting
- `ReminderInvoiceJob.php` - Invoice reminder emails
- `DeliveryUpdateImportJob.php` - Delivery update imports

### 5. Observers (`app/Observers/`)

**10 model observers:**
- `CollectionObserver.php` - Collection model events
- `CreditNoteObserver.php` - Credit note events
- `CustomerInfoObserver.php` - Customer info events
- `DeliveryObserver.php` - Delivery events
- `GoodreceiptnoteObserver.php` - GRN events
- `InvoiceObserver.php` - Invoice events
- `ItemObserver.php` - Item events
- `JourneyPlanObserver.php` - Journey plan events
- `OrderObserver.php` - Order events
- `SalesmanInfoObserver.php` - Salesman info events
- `WarehouseObserver.php` - Warehouse events

**Note**: Observers are registered but currently commented out in `AppServiceProvider.php`

### 6. Exports (`app/Exports/`)

**99 Excel export classes** for various reports and data exports:
- `InvoicesReportExport.php`
- `DebitnotesReportExport.php`
- `JourneyPlanExport.php`
- `LoadRequestExport.php`
- `MerchandiserCompetitorInfoExport.php`
- `MerchandiserOrderItemExport.php`
- `MerchandiserStockAvailabilityExport.php`
- `MerchandiserShareOfShelfExport.php`
- `CampaignPictureExport.php`
- `GlobalReportExport.php`
- `TotalDeliveryReportExport.php`
- `LoadingChartByWarehouseReportExport.php`
- `CompetitorinfoExport.php`
- `OrderSCReportExport.php`
- `DepotExport.php`
- `MerchandiserMultipleSheets.php`
- `DailyCSRFExport.php`
- `PlanogramExport.php`
- And 81 more export classes...

### 7. Imports (`app/Imports/`)

**40 Excel import classes** for bulk data imports:
- `CustomersImport.php`
- `UsersImport.php`
- `ItemImport.php`
- `ItemMinimumImport.php`
- `SalesmanImport.php`
- `JourneyPlanImport.php`
- `DeliveryImport.php`
- `DeliveryUpdateImport.php`
- `InvoiceImport.php`
- `CreditNoteUpdateImport.php`
- `ExpensesImport.php`
- `EstimationImport.php`
- `PlanogramImport.php`
- `DistributionImport.php`
- `CompetitorinfoImport.php`
- `ComplaintFeedbackImport.php`
- `CampaignPictureImport.php`
- `AssettrackingImport.php`
- `AssignInventoryImport.php`
- `PortfolioManagementImport.php`
- `RegionImport.php`
- `CustomerRegionImport.php`
- `ShelfDisplayImport.php`
- `CustomerMerchandiserImport.php`
- `DepotImport.php`
- `AdidUpdateImport.php`
- And 15 more import classes...

### 8. Console Commands (`app/Console/Commands/`)

**4 scheduled commands:**
- `RfGenViewCron.php` - Runs every 2 minutes
- `ReturnTruckAllocationCron.php` - Runs daily at 05:30
- `VehicleUtilisationReportCron.php` - Runs daily at 01:30
- `SalesVsGRVCron.php` - Runs daily at 02:00

### 9. Traits (`app/Traits/`)

**3 reusable traits:**
- `Organisationid.php` - Organization ID scoping
- `Roleid.php` - Role ID scoping
- `Sortable.php` - Sorting functionality

### 10. Library (`app/Library/`)

**2 helper libraries:**
- `helper.php` - Global helper functions
- `Stripe.php` - Stripe payment integration

---

## API Structure

### Route Organization

The application uses **1,552 lines** of API routes organized in `routes/api.php`:

#### Authentication Routes (Public)
```
POST /api/auth/login
POST /api/auth/salesman-login
POST /api/auth/supervisor-login
POST /api/auth/ad-login
POST /api/auth/ad-salesman-login
POST /api/auth/social-login
POST /api/auth/signup
POST /api/auth/user-verification
POST /api/auth/forgot-password
POST /api/auth/reset-password
```

#### Protected Routes (auth:api middleware)
All routes below require `auth:api` middleware and `gzip` compression:

**User Management**
- `GET /api/user` - Current user details
- `GET /api/logout` - Logout
- `GET /api/supervisor-logout` - Supervisor logout
- `GET /api/user-login-log/{id}` - User login logs

**Master Data Routes**
- `/api/country/*` - Country management
- `/api/region/*` - Region management
- `/api/area/*` - Area management
- `/api/depot/*` - Depot management
- `/api/route/*` - Route management
- `/api/channel/*` - Channel management
- `/api/brand/*` - Brand management
- `/api/warehouse/*` - Warehouse management
- `/api/storage-location/*` - Storage location
- `/api/van/*` - Van management
- `/api/van-category/*` - Van category
- `/api/van-type/*` - Van type
- `/api/zone/*` - Zone management
- `/api/lob/*` - Line of business

**Customer Routes**
- `/api/customer/*` - Customer CRUD, import, export, balance statements
- `/api/customer-comment/*` - Customer comments
- `/api/customer-visit/*` - Customer visits
- `/api/customer-activity/*` - Customer activities
- `/api/customer-warehouse-mapping/*` - Warehouse mapping
- `/api/customer-region-mapping/*` - Region mapping
- `/api/customer-ksm-kam-mapping/*` - KAM/KSM mapping
- `/api/customer-type/*` - Customer types
- `/api/customer-category/*` - Customer categories
- `/api/customer-group/*` - Customer groups

**Salesman Routes**
- `/api/salesman/*` - Salesman CRUD, import, mobile data
- `/api/salesman-menu/*` - Menu management
- `/api/salesman-activity-profile/*` - Activity profiles
- `/api/salesman-load/*` - Load management
- `/api/salesman-unload/*` - Unload management
- `/api/salesman-route-approval/*` - Route changes
- `/api/salesman-login-log/*` - Login logs
- `/api/salesman-role/*` - Salesman roles
- `/api/salesman-type/*` - Salesman types

**Item Routes**
- `/api/item/*` - Item CRUD, import, export, pricing
- `/api/item-group/*` - Item groups
- `/api/item-uom/*` - UOM management
- `/api/item-branch-plant/*` - Branch plant mapping
- `/api/item-base-price/*` - Base pricing
- `/api/major-category/*` - Major categories
- `/api/outlet-product/*` - Outlet products
- `/api/product-catalog/*` - Product catalog

**Order Routes**
- `/api/order/*` - Order CRUD, import, pricing, cancellation
- `/api/orderposting/*` - Order posting
- `/api/orderpostingprd/*` - Order posting (PRD)
- `/api/order-type/*` - Order types
- `/api/ocr-order/*` - OCR order creation

**Delivery Routes**
- `/api/delivery/*` - Delivery CRUD, import, update, cancellation
- `/api/beginday/*` - Begin day trip
- `/api/endday/*` - End day trip
- `/api/getdeliveries` - Get deliveries
- `/api/getDeliveriesDetails` - Delivery details

**Invoice Routes**
- `/api/invoice/*` - Invoice CRUD, import, ERP posting, cancellation
- `/api/invoice-reminder/*` - Invoice reminders
- `/api/invoice/sendinvoice` - Send invoice email
- `/api/pending-invoice/list/{route_id}` - Pending invoices

**Financial Routes**
- `/api/collection/*` - Payment collections
- `/api/creditnotes/*` - Credit notes, ERP posting
- `/api/debit-notes/*` - Debit notes
- `/api/expenses/*` - Expense management
- `/api/expense-category/*` - Expense categories
- `/api/estimation/*` - Estimates
- `/api/cashierreciept/*` - Cashier receipts
- `/api/apply-credit-save` - Apply credit
- `/api/invoice-by-credit-note-number/{creditnote_number}` - Invoice by credit note

**Inventory Routes**
- `/api/stock-adjustment/*` - Stock adjustments
- `/api/goodreceiptnote/*` - GRN management
- `/api/purchaseorder/*` - Purchase orders
- `/api/vendor/*` - Vendor management
- `/api/assign-inventory/*` - Inventory assignments
- `/api/van-to-van-transfer/*` - Van-to-van transfers
- `/api/depot-damage-expiry/*` - Depot damage/expiry

**Pricing & Promotion Routes**
- `/api/pricing-paln/*` - Pricing plans
- `/api/bundle-promotion/*` - Bundle promotions
- `/api/discount/*` - Discounts
- `/api/promotional/*` - Promotional campaigns
- `/api/pdp-mobile` - Mobile pricing
- `/api/pricing-check/*` - Pricing checks

**Journey Planning Routes**
- `/api/journey-plan/*` - Journey plan CRUD, import, customer visits

**Workflow Routes**
- `/api/work-flow/*` - Workflow rules
- `/api/work-flow-module/*` - Workflow modules
- `/api/request-for-approval/*` - Approval requests
- `/api/bulk-request-for-approval/action` - Bulk approvals
- `/api/salesman-geo-approval-request` - Geo-approval (salesman)
- `/api/supervisor-geo-approval-request` - Geo-approval (supervisor)
- `/api/salesman-overdue-limits-approval-request` - Overdue limits (salesman)
- `/api/supervisor-overdue-limits-approval-request` - Overdue limits (supervisor)

**Distribution & Merchandising Routes**
- `/api/distribution/*` - Distribution tracking, import
- `/api/distribution-model-stock/*` - Model stock
- `/api/distribution-post-image/*` - Distribution images
- `/api/distribution-expire-item/*` - Expiry items
- `/api/distribution-damage-item/*` - Damage items
- `/api/distribution-stock-item/*` - Stock items
- `/api/distribution-all-in-one-item/*` - All-in-one items
- `/api/distribution-msl/add/{date}` - MSL creation
- `/api/planogram/*` - Planogram management
- `/api/planogram-post/*` - Planogram postings
- `/api/portfolio-management/*` - Portfolio management
- `/api/assign-inventory/*` - Inventory assignments

**Survey Routes**
- `/api/survey/*` - Survey management
- `/api/survey-type/*` - Survey types
- `/api/survey-question/*` - Survey questions
- `/api/survey-question-answer/*` - Survey answers
- `/api/consumer/survey/{customer_id}` - Consumer survey by customer
- `/api/asset-tracking/survey/{customer_id}` - Asset tracking survey
- `/api/distribution-survey/merchandiser/{merchandiser_id}` - Distribution survey
- `/api/asset-tracking-survey/merchandiser/{merchandiser_id}` - Asset tracking survey
- `/api/consumer-survey/merchandiser/{merchandiser_id}` - Consumer survey
- `/api/sensory-survey` - Sensory survey

**Asset & Competitor Routes**
- `/api/asset-tracking/*` - Asset tracking
- `/api/asset-tracking-post/*` - Asset tracking postings
- `/api/competitor-info/*` - Competitor information

**Report Routes**
- `/api/report/sales_by_customer` - Sales by customer
- `/api/report/sales_by_item` - Sales by item
- `/api/report/sales_by_salesman` - Sales by salesman
- `/api/report/invoice_details` - Invoice details
- `/api/report/payment_received` - Payment received
- `/api/report/creditnote_detail` - Credit note details
- `/api/report/debitnote_detail` - Debit note details
- `/api/report/estimate_detail` - Estimate details
- `/api/report/aging_summary` - Aging summary
- `/api/report/consolidatedLoadReport` - Consolidated load report
- `/api/report/loadingChartByWarehouse` - Loading chart by warehouse
- `/api/report/consolidate-load-return` - Consolidate load return
- `/api/report/orderDetailsReport` - Order details report
- `/api/report/truck-utilisation` - Truck utilization
- `/api/report/driver_utilisation` - Driver utilization
- `/api/report/csrf` - CSRF report
- `/api/report/sales_quantity` - Sales quantity
- `/api/report/sales_grv_report` - Sales GRV report
- `/api/report/difot` - DIFOT report
- `/api/report/delivery_driver_journey_plan` - Delivery driver journey plan
- `/api/report/return_grv_Report` - Return GRV report
- `/api/report/itemreport` - Item report
- `/api/report/grvreport` - GRV report
- `/api/report/CfrRegionReport` - CFR region report
- `/api/report/spot_report` - Spot report
- `/api/report/vehicle-utilisation` - Vehicle utilization
- `/api/report/cancel_return` - Cancel return
- `/api/report/sales_vs_grv_report` - Sales vs GRV report
- `/api/report/vehicle-utilisation-yearly` - Vehicle utilization yearly
- `/api/report/geo-approval` - Geo-approval report
- `/api/report/orderSCReport` - Order SC report
- `/api/report/merchandiser` - Merchandiser reports

**Dashboard Routes**
- `/api/dashboard` - Dashboard 1
- `/api/dashboard2` - Dashboard 2
- `/api/dashboard3` - Dashboard 3
- `/api/dashboard4` - Dashboard 4

**Configuration Routes**
- `/api/organisation/*` - Organization management
- `/api/organisation-setting/*` - Organization settings
- `/api/org-roles/*` - Role management
- `/api/default-roles/*` - Default roles
- `/api/permissions/*` - Permissions
- `/api/user-with-permission/*` - User permissions
- `/api/invite-user/*` - User invitations
- `/api/module/*` - Module management
- `/api/customfield/*` - Custom fields
- `/api/customfieldvalue/*` - Custom field values
- `/api/code-setting` - Code settings
- `/api/template/*` - Template management
- `/api/plan/*` - Plan management
- `/api/plan-feature/*` - Plan features
- `/api/software/*` - Software management
- `/api/offer/*` - Offer management
- `/api/themes` - Themes
- `/api/change/theme` - Change theme
- `/api/tax-exemption/*` - Tax exemption
- `/api/tax-rate/*` - Tax rates
- `/api/tax-preference/*` - Tax preferences
- `/api/tax-setting/*` - Tax settings
- `/api/reason-type/*` - Reason types
- `/api/reason/*` - Reasons
- `/api/currency/*` - Currency management
- `/api/decimalrate/*` - Decimal rate
- `/api/payment-term/*` - Payment terms
- `/api/bank-information/*` - Bank information

**Utility Routes**
- `/api/data-collection` - Master data collection
- `/api/data-collection-mobile` - Mobile master data
- `/api/get/combination*` - Key combination filters
- `/api/advanced-search` - Advanced search
- `/api/Export/module` - Module export
- `/api/action-history/*` - Action history
- `/api/notification/*` - Notifications
- `/api/get-menu-by-software` - Menu by software
- `/api/get-setting-menu-by-software` - Setting menu by software
- `/api/permission` - Permission check
- `/api/global-setting` - Global settings
- `/api/combination-key` - Combination key
- `/api/send-mail` - Send email
- `/api/log-upload` - Log upload
- `/api/login-detail/{user_id}` - Login details
- `/api/user/adid` - AD ID update

**Download Routes**
- `/api/invoice/download` - Invoice download
- `/api/delivery/download` - Delivery download
- `/api/credit-note/download` - Credit note download
- `/api/debit-note/download` - Debit note download
- `/api/order/download` - Order download
- `/api/estimate/download` - Estimate download
- `/api/delivery/group_pdf_download` - Group PDF download
- `/api/customer/download` - Customer download
- `/api/expense/download` - Expense download
- `/api/collection/download` - Collection download

**Import Routes**
- `/api/customer/import` - Customer import
- `/api/customer/finalimport` - Customer final import
- `/api/item/import` - Item import
- `/api/item/finalimport` - Item final import
- `/api/salesman/import` - Salesman import
- `/api/salesman/finalimport` - Salesman final import
- `/api/journey-plan/import` - Journey plan import
- `/api/journey-plan/finalimport` - Journey plan final import
- `/api/delivery/import` - Delivery import
- `/api/delivery/update-import` - Delivery update import
- `/api/delivery/update-import-new` - Delivery update import (new)
- `/api/delivery/template-update` - Delivery template update
- `/api/invoice/import` - Invoice import
- `/api/invoice-details/import` - Invoice details import
- `/api/invoice-details/import3` - Invoice details import 3
- `/api/invoice-haris-customer/import-haris-customer` - Haris customer import
- `/api/creditnotes/import` - Credit notes import
- `/api/creditnotes/finalimport` - Credit notes final import
- `/api/creditnotes/update-import` - Credit notes update import
- `/api/creditnotes/update-truck` - Credit notes update truck
- `/api/debitnote/import` - Debit note import
- `/api/expenses/import` - Expenses import
- `/api/estimation/import` - Estimation import
- `/api/planogram/import` - Planogram import
- `/api/planogram/finalimport` - Planogram final import
- `/api/distribution/import` - Distribution import
- `/api/distribution/finalimport` - Distribution final import
- `/api/distribution/shelf-display-import` - Shelf display import
- `/api/distribution/shelf-data-import` - Shelf data import
- `/api/distribution/json-shelf-data-import` - JSON shelf data import
- `/api/distribution/shelf-stock-import` - Shelf stock import
- `/api/distribution/shelf-customer-import` - Shelf customer import
- `/api/assigninventory/import` - Assign inventory import
- `/api/assign-inventory/import` - Assign inventory import
- `/api/assign-inventory/finalimport` - Assign inventory final import
- `/api/competitor-info/import` - Competitor info import
- `/api/competitor-info/finalimport` - Competitor info final import
- `/api/complaintfeedback/import` - Complaint feedback import
- `/api/complaintfeedback/finalimport` - Complaint feedback final import
- `/api/campaignpictures/import` - Campaign pictures import
- `/api/assettracking/import` - Asset tracking import
- `/api/van/import` - Van import
- `/api/depot/import` - Depot import
- `/api/region/import` - Region import
- `/api/item-uom/import` - Item UOM import
- `/api/pricing/import` - Pricing import
- `/api/pricing/finalimport` - Pricing final import
- `/api/collection/import` - Collection import
- `/api/vendor/import` - Vendor import
- `/api/bank-information/import` - Bank information import
- `/api/purchaseorder/import` - Purchase order import
- `/api/customer-based-price-mapping/import` - Customer-based price import
- `/api/customerGeoImport` - Customer geo import
- `/api/exsice-import` - Excise import
- `/api/import/customers/lat-long` - Customer lat/long import

**Mobile-Specific Routes (v1 prefix)**
- `/api/v1/reason-type/list` - Reason types (mobile)
- `/api/v1/item/list` - Items (mobile)
- `/api/v1/helper/list` - Helpers (mobile)
- `/api/v1/item-branch-plant/list` - Item branch plant (mobile)
- `/api/v1/item-base-price/list` - Item base price (mobile)
- `/api/v1/pdp-mobile` - Mobile pricing
- `/api/v1/salesman-tomorrow-delivery/{salesman_id}` - Tomorrow delivery
- `/api/v1/salesman-shipment` - Shipment delivery status
- `/api/v1/invoice-submitted/{salesman_id}` - Invoice submitted
- `/api/v1/invoice-submitted-post` - Invoice submitted posting
- `/api/v1/credit-note/update/{uuid}` - Credit note update
- `/api/v1/get-pallets` - Get pallets
- `/api/v1/pallet-status-update` - Pallet status update
- `/api/v1/pallet-return-add` - Pallet return add
- `/api/v1/item-return-show/{salesman_id}` - Item return show

**Other Routes**
- `/api/loadrequest/*` - Load request management
- `/api/odo-meter-by-van` - Odometer by van
- `/api/odometer/*` - Odometer management
- `/api/salestarget/*` - Sales targets
- `/api/salesperson/*` - Sales person
- `/api/account/list` - Account list
- `/api/route-item-grouping/*` - Route item grouping
- `/api/share-of-shelf/*` - Share of shelf
- `/api/share-assortment/*` - Share of assortment
- `/api/share-display/*` - Share of display
- `/api/sos/*` - Share of shelf
- `/api/market-promotion/*` - Market promotions
- `/api/merchandiser-replacement/*` - Merchandiser replacements
- `/api/merchandiser-swap/add` - Merchandiser swap
- `/api/driver-van-swaping/*` - Driver/van swapping
- `/api/palettes/*` - Pallet management
- `/api/palette/*` - Pallet operations
- `/api/pallet-return/{salesman_id}` - Pallet return
- `/api/update-pallet-return` - Update pallet return
- `/api/merchandiser/list` - Merchandiser list
- `/api/choose/plan/*` - Plan selection
- `/api/subscription` - Subscription
- `/api/unsubscription` - Unsubscription
- `/api/update-subscription` - Update subscription
- `/api/login-track` - Login domain track
- `/api/jde-customer-download` - JDE customer download
- `/api/jde-lob-customer-download` - JDE LOB customer download
- `/api/jde-item-download` - JDE item download
- `/api/jde-warehouse-download` - JDE warehouse download
- `/api/get-salesman-sales` - Get salesman sales
- `/api/geoMail` - Geo mail
- `/api/test-notification` - Test notification
- `/api/load_item` - Load item
- `/api/password` - Password
- `/api/order-sendmail` - Order send mail

**Public Routes (No Authentication)**
- `/api/country/all` - All countries
- `/api/merchandiserUpdateSysnc` - Merchandiser update sync
- `/api/invite-user/password-change` - Password change (invite)
- `/api/notification-test` - Notification test
- `/api/orderposting/add` - Order posting add
- `/api/orderpostingprd/add` - Order posting PRD add
- `/api/phpinfo` - PHP info (debug)
- `/api/orderSpot_report` - Order spot report
- `/api/orderSpot_report_clone` - Order spot report clone
- `/api/customer-merchandiser/add` - Customer merchandiser add
- `/api/plan-by-pass/list` - Plan by pass list
- `/api/distributionImport` - Distribution import
- `/api/channelToRadius` - Channel to radius
- `/api/distributionItemsChannel` - Distribution items channel
- `/api/ItemsChannel` - Items channel
- `/api/itemCategory` - Item category
- `/api/customer-price-import` - Customer price import
- `/api/rfgen_order_picking` - RF gen order picking
- `/api/order_views` - Order views
- `/api/returnAssingSalesman` - Return assign salesman
- `/api/truck_utilisation_report` - Truck utilization report
- `/api/spotReturn_report` - Spot return report
- `/api/odUpdate` - OD update
- `/api/salesVsGrv` - Sales vs GRV
- `/api/saleunlo` - Sale unload
- `/api/merchandiserSupervisorASMUpdate` - Merchandiser supervisor ASM update
- `/api/text-mail` - Test mail
- `/api/prd-order` - PRD order

### Route Patterns

**Standard CRUD Pattern:**
- `GET /api/{resource}/list` - List resources
- `POST /api/{resource}/add` - Create resource
- `GET /api/{resource}/edit/{uuid}` - Get resource for edit
- `POST /api/{resource}/edit/{uuid}` - Update resource
- `ANY /api/{resource}/delete/{uuid}` - Delete resource
- `POST /api/{resource}/bulk-action` - Bulk actions
- `POST /api/{resource}/import` - Import data

**Mobile API Pattern:**
- Mobile-specific endpoints use `/api/v1/` prefix
- Optimized for mobile performance
- Reduced payload sizes

---

## Database Architecture

### Database Connections

**Primary Connection (`mysql`):**
- Default database connection
- Used for all application data
- Supports soft deletes, timestamps

**Secondary Connection (`server_mysql`):**
- Separate database connection
- Used for server-specific operations
- Configured via `SERVER_DB_*` environment variables

### Migration Structure

**438 migration files** organized by feature/module:
- User and organization migrations
- Customer management migrations
- Salesman management migrations
- Item and inventory migrations
- Order and delivery migrations
- Invoice and financial migrations
- Pricing and promotion migrations
- Journey planning migrations
- Workflow and approval migrations
- Distribution and merchandising migrations
- Survey and feedback migrations
- Asset and competitor tracking migrations
- Report and analytics migrations
- Configuration and settings migrations

### Key Database Features

1. **UUID Support**: All models use UUID as primary identifier
2. **Soft Deletes**: Most models support soft deletion
3. **Activity Logging**: Spatie Activity Log integration
4. **Multi-Tenancy**: Organization-based data isolation
5. **Timestamps**: Created/updated timestamps on all models
6. **Foreign Keys**: Proper relationships with foreign key constraints

### Database Seeders

**57 seeder files** for initial data:
- Country masters
- Plan features
- Setting menus
- Default roles and permissions
- And more...

---

## Authentication & Authorization

### Authentication Methods

1. **Laravel Passport (OAuth2)**
   - Primary authentication for API
   - Token-based authentication
   - `auth:api` middleware

2. **Multiple Login Types**
   - Standard login (`/api/auth/login`)
   - Salesman login (`/api/auth/salesman-login`)
   - Supervisor login (`/api/auth/supervisor-login`)
   - AD login (`/api/auth/ad-login`)
   - AD Salesman login (`/api/auth/ad-salesman-login`)
   - Social login (`/api/auth/social-login`)

3. **Password Management**
   - Forgot password flow
   - Reset password
   - Password change (invited users)

### Authorization

1. **Spatie Permission Package**
   - Role-based access control (RBAC)
   - Permission-based authorization
   - Role middleware: `role`
   - Permission middleware: `permission`

2. **Organization-Based Access**
   - Multi-tenant architecture
   - Organization ID scoping via `Organisationid` trait
   - Data isolation per organization

3. **Role Hierarchy**
   - Default roles (system-wide)
   - Organization roles (per organization)
   - User-specific custom permissions
   - Salesman role menus

### User Types

- **Admin**: System administrators
- **Organization Admin**: Organization administrators
- **Salesman/Merchandiser**: Field sales personnel
- **Supervisor**: Field supervisors
- **Manager**: Sales managers
- **Customer**: Customer users

---

## Key Features & Modules

### 1. Sales Force Automation (SFA)

**Order Management**
- Order creation, editing, cancellation
- Order templates
- Order posting to ERP
- Order import/export
- OCR order creation
- Order pricing (normal, promotional, customer-based)
- Order status tracking
- Order reports

**Journey Planning**
- Weekly journey plans
- Customer visit scheduling
- Route optimization
- Journey plan import/export
- Customer visit tracking
- Journey plan reports

**Customer Management**
- Customer master data
- Customer types, categories, groups
- Customer-merchandiser mapping
- Customer visit tracking
- Customer activity logs
- Customer comments
- Customer balance statements
- Customer credit limits
- Customer warehouse mapping
- Customer region mapping
- Customer KAM/KSM mapping
- Customer LOB mapping

**Salesman Management**
- Salesman master data
- Salesman types and roles
- Salesman menu permissions
- Salesman activity profiles
- Salesman load/unload tracking
- Salesman trip management
- Salesman login tracking
- Salesman route changes
- Salesman replacements/swapping

### 2. Inventory Management

**Warehouse Management**
- Warehouse master
- Warehouse details (items)
- Storage locations
- Storage location details
- Stock adjustments
- Stock availability checks
- Multi-warehouse support

**Item Management**
- Item master data
- Item groups and categories
- Item UOM management
- Item branch plant mapping
- Item base pricing
- Item customer-based pricing
- Item import/export
- Item stock tracking

**Stock Operations**
- Stock adjustments
- Good receipt notes (GRN)
- Purchase orders
- Van-to-van transfers
- Depot damage/expiry tracking
- Stock availability reports

### 3. Delivery & Logistics

**Delivery Management**
- Delivery creation and tracking
- Delivery import/update
- Delivery cancellation
- Delivery code changes
- Delivery trip changes
- Delivery templates
- Delivery notes
- Delivery reports

**Vehicle Management**
- Van master data
- Van categories and types
- Odometer tracking
- Vehicle utilization reports
- Driver-van swapping
- Load requests
- Salesman load/unload

**Trip Management**
- Begin day trip
- End day trip
- Trip sequence tracking
- Trip reports

### 4. Invoicing & Financial Management

**Invoice Management**
- Invoice creation and editing
- Invoice import/export
- Invoice cancellation
- Invoice ERP posting
- Invoice reminders
- Invoice email sending
- Invoice reports
- Pending invoices

**Payment Collection**
- Collection management
- Collection import
- Collection reports
- Cashier receipts
- Payment tracking

**Credit & Debit Notes**
- Credit note management
- Credit note import/update
- Credit note ERP posting
- Credit note supervisor approval
- Debit note management
- Debit note import
- Return management

**Financial Reports**
- Sales by customer/item/salesman
- Payment received reports
- Credit note details
- Debit note details
- Estimate details
- Aging summary
- Balance statements

### 5. Pricing & Promotions

**Pricing Plans (PDP)**
- Pricing plan creation
- Item-based pricing
- Item group-based pricing
- LOB-based pricing
- Region-based pricing
- Route-based pricing
- Customer-based pricing
- Customer group-based pricing
- Mobile pricing APIs

**Discounts & Promotions**
- Discount plans
- Bundle promotions
- Promotional campaigns
- Promotional postings
- Pricing checks

### 6. Distribution & Merchandising

**Distribution Tracking**
- Distribution creation
- Distribution postings
- Distribution images
- Expiry items tracking
- Damage items tracking
- Stock items tracking
- Shelf display tracking
- Model stock levels (MSL)
- Distribution import/export

**Planogram Management**
- Planogram creation
- Planogram postings
- Planogram images
- Planogram import/export
- Customer planogram mapping

**Portfolio Management**
- Portfolio customer mapping
- Portfolio item mapping
- MSL compliance tracking
- Merchandiser MSL management

**Inventory Assignments**
- Assign inventory to customers
- Inventory posting
- Damage/expiry tracking
- Inventory reports

### 7. Surveys & Feedback

**Survey Management**
- Survey creation
- Survey types
- Survey questions
- Survey answers
- Survey by customer
- Survey by merchandiser
- Consumer surveys
- Asset tracking surveys
- Distribution surveys
- Sensory surveys

**Complaint Management**
- Complaint feedback
- Complaint import/export
- Complaint tracking

**Campaign Pictures**
- Campaign picture upload
- Campaign picture import/export

### 8. Asset & Competitor Tracking

**Asset Tracking**
- Asset tracking postings
- Asset images
- Asset survey integration
- Asset import/export

**Competitor Information**
- Competitor data entry
- Competitor images
- Competitor import/export
- Competitor brand tracking

### 9. Reporting & Analytics

**Sales Reports**
- Sales by customer
- Sales by item
- Sales by salesman
- Sales quantity reports
- Sales vs GRV reports

**Operational Reports**
- Invoice details
- Payment received
- Credit note details
- Debit note details
- Estimate details
- Aging summary
- Order details
- Delivery reports
- DIFOT reports
- Spot reports

**Logistics Reports**
- Truck utilization
- Driver utilization
- Vehicle utilization (daily/yearly)
- Loading chart by warehouse
- Consolidated load reports
- Return GRV reports

**Merchandiser Reports**
- Merchandiser order items
- Merchandiser stock availability
- Merchandiser competitor info
- Merchandiser share of shelf
- Merchandiser multiple sheets

**Other Reports**
- Item reports
- GRV reports
- CFR region reports
- Geo-approval reports
- Order SC reports
- CSRF reports
- Daily CSRF reports

### 10. Workflow & Approvals

**Workflow Rules**
- Workflow module definition
- Workflow rule creation
- Approval role assignment
- Approval user assignment
- Auto-approval workflows

**Approval Requests**
- Request for approval listing
- Approval actions
- Bulk approval actions
- Geo-approval requests
- Overdue limits approval

### 11. Configuration & Settings

**Organization Settings**
- Organization management
- Organization settings
- Organization themes
- Organization roles
- Organization plans

**Module Management**
- Module definitions
- Module status checking
- Custom fields per module
- Custom field values

**Code Settings**
- Code generation settings
- Next code generation

**Template Management**
- Template creation
- Template assignment
- Template updates

**Tax Configuration**
- Tax rates
- Tax exemptions
- Tax preferences
- Tax settings

**Other Settings**
- Currency management
- Decimal rate configuration
- Payment terms
- Bank information
- Reason types
- Expense categories

---

## Integration Points

### 1. ERP Integration

**JDE (JD Edwards) Integration**
- Invoice posting to JDE
- Credit note posting to JDE
- Customer download from JDE
- Item download from JDE
- Warehouse download from JDE
- LOB customer download

**SAP Integration**
- Credit note posting to SAP
- Customer posting to SAP
- Item posting to SAP

### 2. External Services

**Firebase Cloud Messaging (FCM)**
- Push notifications
- Notification service
- Test notification endpoint

**Google Services**
- Google Client integration
- Firebase messaging

**Stripe Payment Gateway**
- Payment processing
- Subscription management

### 3. Email Services

**Email Templates**
- Forgot password emails
- User invitation emails
- New user registration emails
- Invoice emails
- Delivery emails
- Reminder invoice emails
- Notification emails

**Email Jobs**
- Queue-based email sending
- Async email processing

### 4. File Processing

**Excel Import/Export**
- Maatwebsite Excel package
- 99 export classes
- 40 import classes
- Bulk data operations

**PDF Generation**
- Laravel Mpdf
- Invoice PDFs
- Delivery PDFs
- Credit note PDFs
- Debit note PDFs
- Order PDFs
- Estimate PDFs
- Balance statement PDFs
- Report PDFs

**Image Processing**
- Intervention Image
- Image uploads
- Image resizing
- Planogram images
- Asset tracking images
- Competitor images
- Campaign pictures

### 5. Third-Party APIs

**Routific Integration**
- Route optimization
- Test routific endpoint

**LDAP Integration**
- LDAP configuration
- AD login support

---

## Development Patterns

### 1. Model Patterns

**Standard Model Structure:**
```php
- UUID generation in boot method
- Soft deletes support
- Activity logging (Spatie)
- Organisationid trait for multi-tenancy
- Fillable attributes
- Relationships (belongsTo, hasMany, etc.)
- Accessors/Mutators
```

**Common Traits:**
- `Organisationid` - Organization scoping
- `Roleid` - Role scoping
- `Sortable` - Sorting functionality

### 2. Controller Patterns

**Standard Controller Methods:**
- `index()` - List resources with filtering
- `store()` - Create resource
- `edit()` - Get resource for editing
- `update()` - Update resource
- `destroy()` - Delete resource
- `bulkAction()` - Bulk operations
- `import()` - Import data
- `export()` - Export data

**Authorization Check:**
```php
if (!$this->isAuthorized) {
    return prepareResult(false, [], [], "User not authenticate", $this->unauthorized);
}
```

**Response Format:**
```php
return prepareResult(true, $data, [], "Success message", $this->success);
```

### 3. Import/Export Patterns

**Import Pattern:**
1. Upload file
2. Validate mapping fields
3. Import to temp table
4. Validate data
5. Final import to main table

**Export Pattern:**
1. Query data with filters
2. Format data
3. Generate Excel/PDF
4. Return download response

### 4. API Response Patterns

**Success Response:**
```json
{
    "status": true,
    "data": [...],
    "errors": [],
    "message": "Success message",
    "status_code": 200
}
```

**Error Response:**
```json
{
    "status": false,
    "data": [],
    "errors": [...],
    "message": "Error message",
    "status_code": 401/404/500
}
```

### 5. Route Patterns

**Standard CRUD Routes:**
- `GET /api/{resource}/list` - List
- `POST /api/{resource}/add` - Create
- `GET /api/{resource}/edit/{uuid}` - Edit
- `POST /api/{resource}/edit/{uuid}` - Update
- `ANY /api/{resource}/delete/{uuid}` - Delete
- `POST /api/{resource}/bulk-action` - Bulk actions
- `POST /api/{resource}/import` - Import
- `POST /api/{resource}/download` - Download

**Mobile Routes:**
- Prefixed with `/api/v1/`
- Optimized responses
- Reduced payload

### 6. Database Patterns

**Multi-Tenancy:**
- Organization ID in all tables
- `Organisationid` trait for scoping
- Organization-based data isolation

**Soft Deletes:**
- `deleted_at` column
- `SoftDeletes` trait
- Restore functionality

**UUID:**
- UUID as primary identifier
- Generated in model boot method
- Used in API routes

**Activity Logging:**
- Spatie Activity Log
- Logs all model changes
- Configurable log attributes

---

## Configuration Details

### Application Configuration (`config/app.php`)

**Key Settings:**
- Application name: `env('APP_NAME', 'Laravel')`
- Environment: `env('APP_ENV', 'production')`
- Debug mode: `env('APP_DEBUG', false)`
- Timezone: `Asia/Dubai` (configurable per organization)
- Locale: `en`
- Fallback locale: `en`

**Service Providers:**
- Laravel core providers
- Maatwebsite Excel
- Spatie Permission
- JDE Julian Converter
- Curl (Ixudra)
- Intervention Image

**Aliases:**
- Standard Laravel facades
- `Excel` - Maatwebsite Excel
- `Image` - Intervention Image
- `JJC` - JDE Julian Converter
- `Curl` - Ixudra Curl

### Database Configuration (`config/database.php`)

**Connections:**
- `mysql` - Primary connection
- `server_mysql` - Secondary connection
- `pgsql` - PostgreSQL support
- `sqlsrv` - SQL Server support

**Redis:**
- Default connection
- Cache connection
- Cluster support

### Authentication Configuration (`config/auth.php`)

**Guards:**
- `web` - Web authentication
- `api` - API authentication (Passport)

**Providers:**
- User provider with Eloquent

### Mail Configuration (`config/mail.php`)

**Mail Settings:**
- SMTP configuration
- From address: `accounts.receivable@nfpc.net`
- Email templates in `resources/views/emails/`

### Permission Configuration (`config/permission.php`)

**Spatie Permission:**
- Role and permission management
- Cache configuration
- Table names

### Queue Configuration (`config/queue.php`)

**Queue Drivers:**
- Database
- Redis
- Sync (for development)

### Other Configurations

**PDF (`config/pdf.php`):**
- Laravel Mpdf settings

**Excel (`config/excel.php`):**
- Maatwebsite Excel settings

**LDAP (`config/ldap.php`):**
- LDAP authentication settings

**Sentry (`config/sentry.php`):**
- Error tracking configuration

---

## Prompt Generation Guide

This documentation is designed to help you generate effective prompts for working with this Laravel codebase. Use the following guidelines:

### 1. Understanding the Codebase

**When asking about the codebase:**
- Reference specific modules (e.g., "Customer Management", "Order Processing")
- Mention model names (e.g., "CustomerInfo", "Order")
- Reference API endpoints (e.g., "/api/customer/list")
- Specify controller names (e.g., "CustomerController")

**Example Prompts:**
```
"How does the customer import functionality work in CustomerController?"
"What models are involved in the order-to-delivery workflow?"
"Explain the pricing plan (PDP) structure and how it applies to orders."
```

### 2. Adding New Features

**When adding features:**
- Follow existing patterns (CRUD, import/export, reports)
- Use standard route naming conventions
- Implement proper authorization checks
- Add activity logging
- Support multi-tenancy (organization scoping)
- Include import/export if applicable

**Example Prompts:**
```
"Add a new feature for tracking customer complaints with CRUD operations, 
import/export, and integration with the existing customer management system."
"Create a new report for sales performance by region with export functionality."
```

### 3. Modifying Existing Features

**When modifying features:**
- Reference existing code structure
- Maintain backward compatibility
- Update related models/controllers
- Update API documentation
- Consider impact on mobile APIs

**Example Prompts:**
```
"Modify the order creation process to include automatic pricing calculation 
based on customer-based pricing rules."
"Add a new status field to the delivery model and update all related 
controllers and APIs."
```

### 4. Debugging Issues

**When debugging:**
- Reference specific endpoints
- Mention error messages
- Include relevant model/controller names
- Specify API routes

**Example Prompts:**
```
"The customer import is failing with validation errors. Review the 
CustomersImport class and CustomerController import methods."
"The invoice ERP posting endpoint is not working. Check the 
InvoiceController@postInvoiceInJDE method."
```

### 5. Database Changes

**When working with database:**
- Reference migration files
- Mention model relationships
- Consider multi-tenancy
- Include soft deletes if applicable

**Example Prompts:**
```
"Create a migration to add a new field 'discount_percentage' to the 
orders table and update the Order model."
"Add a relationship between CustomerInfo and a new Promotions model 
with proper foreign keys."
```

### 6. API Development

**When working with APIs:**
- Follow RESTful conventions
- Use standard response format
- Implement proper error handling
- Add authorization checks
- Consider mobile API versions

**Example Prompts:**
```
"Create a new API endpoint for fetching customer order history with 
pagination and filtering options."
"Add a mobile-optimized version (v1) of the item list endpoint with 
reduced payload size."
```

### 7. Integration Development

**When integrating external services:**
- Reference existing integrations (JDE, SAP, Firebase)
- Follow existing patterns
- Handle errors gracefully
- Add logging

**Example Prompts:**
```
"Integrate a new payment gateway similar to the existing Stripe 
integration for processing subscription payments."
"Add a new ERP integration endpoint following the pattern used in 
InvoiceController@postInvoiceInJDE."
```

### 8. Testing & Quality

**When testing:**
- Reference specific features
- Mention test scenarios
- Include edge cases
- Consider multi-tenancy

**Example Prompts:**
```
"Create test cases for the customer import functionality covering 
success scenarios, validation errors, and bulk operations."
"Test the order pricing calculation with different pricing plans 
(PDP) and customer-based pricing rules."
```

### Key Points for Prompt Generation

1. **Be Specific**: Reference exact file names, class names, method names
2. **Follow Patterns**: Mention existing patterns to follow
3. **Consider Context**: Include multi-tenancy, authorization, logging
4. **Reference Documentation**: Point to relevant sections in this doc
5. **Include Examples**: Provide examples from existing code when possible
6. **Think Holistically**: Consider impact on related modules

### Common Prompt Templates

**Feature Addition:**
```
"Add [FEATURE_NAME] feature to [MODULE_NAME] module following the 
existing patterns in [REFERENCE_MODULE]. Include:
- Model: [MODEL_NAME] with relationships to [RELATED_MODELS]
- Controller: [CONTROLLER_NAME] with CRUD operations
- Routes: Standard CRUD routes plus [ADDITIONAL_ROUTES]
- Import/Export: [YES/NO] with mapping fields
- Reports: [YES/NO] with export functionality
- Authorization: [AUTHORIZATION_REQUIREMENTS]
- Multi-tenancy: Organization scoping required
- Activity Logging: Log all changes"
```

**Bug Fix:**
```
"Fix [ISSUE_DESCRIPTION] in [FILE_NAME/METHOD_NAME]. 
Current behavior: [CURRENT_BEHAVIOR]
Expected behavior: [EXPECTED_BEHAVIOR]
Related files: [RELATED_FILES]
Considerations: [MULTI_TENANCY/AUTHORIZATION/LOGGING]"
```

**Refactoring:**
```
"Refactor [COMPONENT_NAME] to [IMPROVEMENT_GOAL]. 
Current implementation: [CURRENT_APPROACH]
Target approach: [TARGET_APPROACH]
Maintain backward compatibility: [YES/NO]
Update related components: [LIST_COMPONENTS]"
```

---

## Additional Notes

### Code Quality Considerations

1. **Authorization**: Always check `$this->isAuthorized`
2. **Multi-Tenancy**: Use `Organisationid` trait for scoping
3. **Activity Logging**: Models use Spatie Activity Log
4. **Soft Deletes**: Most models support soft deletion
5. **UUID**: All models use UUID as primary key
6. **Validation**: Use Laravel validators
7. **Error Handling**: Use `prepareResult()` helper
8. **Response Format**: Consistent JSON responses

### Performance Considerations

1. **Eager Loading**: Use `with()` for relationships
2. **Pagination**: Implement pagination for large datasets
3. **Caching**: Use Redis for caching
4. **Queue Jobs**: Use queues for heavy operations
5. **Gzip Compression**: API responses are gzipped
6. **Database Indexing**: Ensure proper indexes on foreign keys

### Security Considerations

1. **Authentication**: Laravel Passport for API
2. **Authorization**: Spatie Permission for RBAC
3. **CSRF Protection**: Enabled for web routes
4. **Input Validation**: Validate all inputs
5. **SQL Injection**: Use Eloquent/Query Builder
6. **XSS Protection**: Blade templating escapes output
7. **File Uploads**: Validate file types and sizes

### Maintenance Considerations

1. **Migrations**: 438 migration files to manage
2. **Seeders**: 57 seeder files for initial data
3. **Observers**: Currently commented out (can be enabled)
4. **Cron Jobs**: 4 scheduled commands
5. **Queue Workers**: Required for email/notification jobs
6. **Logs**: Activity logs and application logs

---

## Conclusion

This Laravel application is a comprehensive Sales Force Automation system with extensive features for managing sales operations, field teams, inventory, orders, deliveries, invoicing, and reporting. The codebase follows Laravel best practices with proper separation of concerns, multi-tenancy support, and extensive API coverage.

Use this documentation as a reference when generating prompts for:
- Understanding the codebase
- Adding new features
- Modifying existing features
- Debugging issues
- Database changes
- API development
- Integration development
- Testing and quality assurance

For best results, always reference specific components, follow existing patterns, and consider the multi-tenant architecture and authorization requirements.

---

**Document Version**: 1.0  
**Last Updated**: Based on current codebase analysis  
**Total Files Analyzed**: 1,000+ files  
**Lines of Code**: 100,000+ lines
