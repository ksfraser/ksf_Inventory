# Architecture - ksf_Inventory

## Document Information
- **Module**: ksf_Inventory
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Technical Architecture

### 1.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                        ksf_Inventory Module                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │                      Ksfraser\Inventory                      │    │
│  ├─────────────────────────────────────────────────────────────┤    │
│  │  SerialNumber      BatchNumber       WarehouseLocation       │    │
│  │  - find()          - find()          - find()              │    │
│  │  - findByItem()     - findByItem()    - findByType()        │    │
│  │  - assignToLocation()               - getHierarchy()      │    │
│  │  - recordSale()                      - getTree()           │    │
│  │  - isUnderWarranty()                                      │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │                       Ksfraser\Vendor                       │    │
│  ├─────────────────────────────────────────────────────────────┤    │
│  │  VendorManagement              VendorPerformance            │    │
│  │  - find()                       - calculateMetrics()        │    │
│  │  - search()                     - getOnTimeRate()          │    │
│  │  - getTopVendors()                                       │    │
│  │  - getByCategory()                                       │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │                     Ksfraser\Reporting                       │    │
│  ├─────────────────────────────────────────────────────────────┤    │
│  │  ReportingDashboard                                          │    │
│  │  - renderKPIs()                                             │    │
│  │  - renderVendorScorecard()                                  │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     FrontAccounting Platform                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                   │
│  │  Database  │  │   Purchase  │  │    Item    │                   │
│  │             │  │   Orders   │  │   Master   │                   │
│  └─────────────┘  └─────────────┘  └─────────────┘                   │
└─────────────────────────────────────────────────────────────────────┘
```

### 1.2 Class Diagram

```
┌────────────────────────────────────────────────────────────────────┐
│                          SerialNumber                               │
├────────────────────────────────────────────────────────────────────┤
│ + id: int                                                           │
│ + serial_no: string                                                 │
│ + item_code: string                                                 │
│ + serialized_date: string                                           │
│ + purchase_date: ?string                                            │
│ + supplier_ref: ?string                                              │
│ + purchase_cost: ?string                                             │
│ + location_code: ?string                                             │
│ + status: string                                                    │
│ + warranty_end_days: ?int                                            │
│ + notes: string                                                      │
├────────────────────────────────────────────────────────────────────┤
│ + assignToLocation(string): bool                                   │
│ + recordSale(string): bool                                          │
│ + isUnderWarranty(): bool                                           │
│ + static find(string): ?SerialNumber                                │
│ + static findByItem(string): array                                 │
│ + static listByLocation(string): array                             │
└────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────┐
│                          BatchNumber                                │
├────────────────────────────────────────────────────────────────────┤
│ + id: int                                                           │
│ + batch_no: string                                                  │
│ + item_code: string                                                 │
│ + quantity: int                                                     │
│ + batch_date: string                                                │
│ + expiry_date: ?string                                              │
│ + status: string                                                    │
├────────────────────────────────────────────────────────────────────┤
│ + isExpired(): bool                                                 │
│ + isExhausted(): bool                                               │
│ + static find(string): ?BatchNumber                                 │
│ + static findByItem(string): array                                 │
└────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────┐
│                       WarehouseLocation                              │
├────────────────────────────────────────────────────────────────────┤
│ + id: int                                                           │
│ + location_code: string                                             │
│ + location_name: string                                             │
│ + parent_code: ?string                                              │
│ + location_type: string                                             │
│ + address, city, state, postal_code, country: string                │
│ + is_active: bool                                                   │
├────────────────────────────────────────────────────────────────────┤
│ + getHierarchy(): array                                             │
│ + getFullPath(): string                                              │
│ + getChildren(): array                                              │
│ + static find(string): ?WarehouseLocation                            │
│ + static findByType(string): array                                 │
│ + static getTree(): array                                           │
└────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────┐
│                       VendorManagement                               │
├────────────────────────────────────────────────────────────────────┤
│ + id: int                                                           │
│ + vendor_no: string                                                 │
│ + name, contact, email, phone: string                               │
│ + address fields: string                                           │
│ + payment_terms, credit_limit, currency: string                     │
│ + rating: float                                                    │
│ + category: string                                                  │
│ + approved: bool                                                    │
├────────────────────────────────────────────────────────────────────┤
│ + getPurchaseHistory(): array                                       │
│ + getTotalSpend(): float                                            │
│ + static find(string): ?VendorManagement                             │
│ + static search(string, int): array                                │
│ + static getTopVendors(int): array                                  │
│ + static getByCategory(string): array                               │
└────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────┐
│                        VendorPerformance                             │
├────────────────────────────────────────────────────────────────────┤
│ + vendor_no: string                                                 │
│ + order_count: int                                                  │
│ + total_spend: float                                                │
│ + avg_order_value: float                                            │
│ + on_time_deliveries: int                                           │
│ + late_deliveries: int                                             │
│ + damage_claims: int                                               │
│ + quality_score: float                                              │
├────────────────────────────────────────────────────────────────────┤
│ + calculateMetrics(): void                                          │
│ + getOnTimeRate(): float                                            │
└────────────────────────────────────────────────────────────────────┘
```

---

## 2. Data Flow Diagrams

### 2.1 Serial Assignment Flow

```
┌──────────┐    ┌──────────────┐    ┌────────────┐    ┌─────────────┐
│  User    │    │  Inventory   │    │   Serial   │    │  Database   │
│          │    │    UI        │    │   Number   │    │             │
└──────────┘    └──────────────┘    └────────────┘    └─────────────┘
     │                 │                 │                │
     │ Assign location │                 │                │
     │────────────────>│                 │                │
     │                 │                 │                │
     │                 │ assignToLocation()               │
     │                 │────────────────>│                │
     │                 │                 │                │
     │                 │                 │ UPDATE query  │
     │                 │                 │───────────────>│
     │                 │                 │                │
     │                 │                 │<───────────────│
     │                 │                 │ Result        │
     │                 │<────────────────│                │
     │ Success         │                 │                │
     │<────────────────│                 │                │
```

### 2.2 Warranty Check Flow

```
┌──────────┐    ┌──────────────┐    ┌────────────┐
│  User    │    │   Serial     │    │  Warranty  │
│          │    │   Number     │    │  Calc     │
└──────────┘    └──────────────┘    └────────────┘
     │                 │                │
     │ Check warranty  │                │
     │────────────────>│                │
     │                 │                │
     │                 │ get purchase_date           │
     │                 │ get warranty_end_days       │
     │                 │                │
     │                 │ Calculate end date           │
     │                 │ (purchase + days)            │
     │                 │                │
     │                 │ Compare to now  │
     │                 │                │
     │ Result: bool    │                │
     │<────────────────│                │
```

---

## 3. Database Schema

### 3.1 Serial Numbers Table

```sql
CREATE TABLE `{PREFIX}inventory_serial_numbers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_no` VARCHAR(64) UNIQUE NOT NULL,
    `item_code` VARCHAR(32) NOT NULL,
    `serialized_date` DATE NOT NULL,
    `purchase_date` DATE,
    `supplier_ref` VARCHAR(64),
    `purchase_cost` DECIMAL(10,2),
    `location_code` VARCHAR(64),
    `status` ENUM('Available','Sold','Reserved','In Repair','Scrapped') DEFAULT 'Available',
    `warranty_end_days` INT,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_item_code` (`item_code`),
    INDEX `idx_location_code` (`location_code`),
    INDEX `idx_status` (`status`)
);
```

### 3.2 Batch Numbers Table

```sql
CREATE TABLE `{PREFIX}inventory_batch_numbers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `batch_no` VARCHAR(64) UNIQUE NOT NULL,
    `item_code` VARCHAR(32) NOT NULL,
    `quantity` INT DEFAULT 0,
    `batch_date` DATE NOT NULL,
    `expiry_date` DATE,
    `supplier_ref` VARCHAR(64),
    `status` ENUM('Active','Closed','Partial') DEFAULT 'Active',
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_item_code` (`item_code`),
    INDEX `idx_status` (`status`)
);
```

### 3.3 Warehouse Locations Table

```sql
CREATE TABLE `{PREFIX}inventory_warehouse_locations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `location_code` VARCHAR(32) UNIQUE NOT NULL,
    `location_name` VARCHAR(128) NOT NULL,
    `parent_code` VARCHAR(32),
    `location_type` ENUM('Warehouse','Zone','Aisle','Rack','Shelf','Bin') DEFAULT 'Warehouse',
    `address` VARCHAR(255),
    `city` VARCHAR(64),
    `state` VARCHAR(64),
    `postal_code` VARCHAR(20),
    `country` VARCHAR(64),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_parent_code` (`parent_code`),
    INDEX `idx_location_type` (`location_type`)
);
```

### 3.4 Serial Location Log Table

```sql
CREATE TABLE `{PREFIX}inventory_serial_location_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_no` VARCHAR(64) NOT NULL,
    `from_location` VARCHAR(64),
    `to_location` VARCHAR(64) NOT NULL,
    `moved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `moved_by` VARCHAR(64),
    INDEX `idx_serial_no` (`serial_no`),
    INDEX `idx_moved_at` (`moved_at`)
);
```

### 3.5 Vendor Tables

```sql
CREATE TABLE `{PREFIX}vendor_performance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_no` VARCHAR(32) NOT NULL,
    `year` YEAR NOT NULL,
    `order_count` INT DEFAULT 0,
    `total_spend` DECIMAL(15,2) DEFAULT 0,
    `on_time_count` INT DEFAULT 0,
    `late_count` INT DEFAULT 0,
    `damage_count` INT DEFAULT 0,
    `quality_score` DECIMAL(5,2) DEFAULT 0,
    UNIQUE KEY `uk_vendor_year` (`vendor_no`, `year`)
);

CREATE TABLE `{PREFIX}vendor_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(64) NOT NULL,
    `description` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `{PREFIX}vendor_price_list` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_no` VARCHAR(32) NOT NULL,
    `item_code` VARCHAR(32) NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `effective_from` DATE NOT NULL,
    `effective_to` DATE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_vendor_item` (`vendor_no`, `item_code`)
);
```

---

## 4. API Design

### 4.1 SerialNumber API

```php
class SerialNumber
{
    // Constants
    public const STATUS_AVAILABLE = 'Available';
    public const STATUS_SOLD = 'Sold';
    public const STATUS_RESERVED = 'Reserved';
    public const STATUS_IN_REPAIR = 'In Repair';
    public const STATUS_SCRAPPED = 'Scrapped';

    // Instance Methods
    public function assignToLocation(string $location_code): bool;
    public function recordSale(string $customer_no): bool;
    public function isUnderWarranty(): bool;

    // Static Methods
    public static function find(string $serial_no): ?self;
    public static function findByItem(string $item_code): array;
    public static function listByLocation(string $location_code): array;
}
```

### 4.2 BatchNumber API

```php
class BatchNumber
{
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_PARTIAL = 'Partial';

    public function isExpired(): bool;
    public function isExhausted(): bool;

    public static function find(string $batch_no): ?self;
    public static function findByItem(string $item_code): array;
}
```

### 4.3 WarehouseLocation API

```php
class WarehouseLocation
{
    public const TYPE_WAREHOUSE = 'Warehouse';
    public const TYPE_ZONE = 'Zone';
    public const TYPE_AISLE = 'Aisle';
    public const TYPE_RACK = 'Rack';
    public const TYPE_SHELF = 'Shelf';
    public const TYPE_BIN = 'Bin';

    public function getHierarchy(): array;
    public function getFullPath(): string;
    public function getChildren(): array;

    public static function find(string $location_code): ?self;
    public static function findByType(string $type): array;
    public static function getTree(): array;
}
```

---

## 5. UI Rendering

### 5.1 Page Structure

```php
// Main tabs
$tabs = [
    'locations' => 'Warehouse Locations',
    'serials' => 'Serial Numbers',
    'batches' => 'Batch Numbers',
    'movement' => 'Movement Log',
];

// Each tab renders its respective content
function ksf_inventory_render_locations() { ... }
function ksf_inventory_render_serials() { ... }
function ksf_inventory_render_batches() { ... }
function ksf_inventory_render_movement() { ... }
```

### 5.2 Shortcode Registration

```php
add_menu_entry('inventory', 'Inventory', 'inventory', 'ksf_inventory');
add_shortcode('ksf_inventory', 'ksf_inventory_render_page');
```

---

## 6. Error Handling

| Scenario | Handling |
|----------|----------|
| Serial not found | Return null, handle in UI |
| Duplicate serial | Database constraint violation |
| Invalid location | Return null from find() |
| Database error | Propagate exception |

---

## 7. Performance Considerations

### 7.1 Indexing Strategy
- `idx_item_code` on serial_numbers
- `idx_location_code` on serial_numbers
- `idx_serial_no` on location_log
- `idx_parent_code` on locations

### 7.2 Query Optimization
- Use indexes for location hierarchy queries
- Batch queries for list views
- Cached tree structures for getTree()

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*