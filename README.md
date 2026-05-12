# ksf_Inventory - Stock Management

## Features
- Serial number tracking with warranty
- Batch number tracking with expiry dates  
- Warehouse locations (shelf/bin hierarchy)
- Vendor performance tracking
- Advanced reporting dashboard
- AI-powered marketing

## Installation
```bash
composer require ksfraser/ksf_inventory
```

## Tables Created
- `inventory_serial_numbers` - Individual item tracking
- `inventory_batch_numbers` - Bulk quantity tracking
- `inventory_warehouse_locations` - Location hierarchy
- `inventory_serial_location_log` - Movement history
- `vendor_performance` - Vendor metrics
- `vendor_categories` - Vendor types
- `vendor_price_list` - Contract pricing

## Usage
```php
// Serial Numbers
use Ksfraser\Inventory\SerialNumber;

$serial = SerialNumber::find('SN12345');
$serial->assignToLocation('WH1-A1-R1-S2-B3');

// Warehouse
use Ksfraser\Inventory\WarehouseLocation;

$loc = new WarehouseLocation(['location_code' => 'WH1-A1', 'location_name' => 'Aisle 1']);
$hierarchy = $loc->getHierarchy(); // Full path

// Vendor Performance
use Ksfraser\Vendor\VendorPerformance;

$perf = new VendorPerformance('SUP001');
echo $perf->getOnTimeRate() . '%';

// Reporting
use Ksfraser\Reporting\ReportingDashboard;

echo ReportingDashboard::renderKPIs();
echo ReportingDashboard::renderVendorScorecard();
```

## Shortcodes
- `[ksf_inventory]` - Main inventory UI

## API Endpoints
- `/api/inventory/serials`
- `/api/inventory/locations`
- `/api/inventory/vendors`