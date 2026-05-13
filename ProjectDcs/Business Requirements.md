# Business Requirements - ksf_Inventory

## Document Information
- **Module**: ksf_Inventory
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Project Overview

### 1.1 Purpose
The ksf_Inventory module provides comprehensive inventory management functionality including serial number tracking, batch number management, warehouse location hierarchy, vendor performance tracking, and advanced reporting dashboards.

### 1.2 Business Problem Statement
Organizations need to track individual items and batches through their supply chain. The ksf_Inventory module provides:
- Individual item tracking with serial numbers
- Batch tracking with expiry date management
- Hierarchical warehouse location management
- Vendor performance metrics
- Warranty tracking
- AI-powered marketing insights
- Advanced reporting dashboard

### 1.3 Scope

| Category | Included |
|----------|----------|
| Serial Number Tracking | Yes |
| Batch Number Tracking | Yes |
| Warehouse Locations | Yes |
| Serial Location History | Yes |
| Vendor Performance | Yes |
| Vendor Price Lists | Yes |
| Reporting Dashboard | Yes |
| AI Marketing | Yes |

---

## 2. Module Architecture

### 2.1 Namespace Structure
```
Ksfraser\Inventory\
├── SerialBatch.php       # SerialNumber, BatchNumber, WarehouseLocation
└── Entity (future)

Ksfraser\Vendor\
├── VendorManagement.php  # VendorManagement, VendorPerformance

Ksfraser\Reporting\
├── Dashboard.php          # ReportingDashboard
```

### 2.2 Core Entities

#### SerialNumber Entity
Tracks individual serialized items:

| Property | Type | Description |
|----------|------|-------------|
| id | int | Primary key |
| serial_no | string | Unique serial number |
| item_code | string | Product/item reference |
| serialized_date | string | Date item was serialized |
| purchase_date | ?string | Purchase date |
| supplier_ref | ?string | Supplier reference |
| purchase_cost | ?string | Cost at purchase |
| location_code | ?string | Current warehouse location |
| status | string | Available, Sold, Reserved, In Repair, Scrapped |
| warranty_end_days | ?int | Warranty period in days |
| notes | string | Additional notes |

#### BatchNumber Entity
Tracks bulk quantities by batch:

| Property | Type | Description |
|----------|------|-------------|
| id | int | Primary key |
| batch_no | string | Batch identifier |
| item_code | string | Product reference |
| quantity | int | Items in batch |
| batch_date | string | Date batch created |
| expiry_date | ?string | Expiration date |
| supplier_ref | ?string | Supplier reference |
| status | string | Active, Closed, Partial |

#### WarehouseLocation Entity
Hierarchical location management:

| Property | Type | Description |
|----------|------|-------------|
| id | int | Primary key |
| location_code | string | Unique code (e.g., WH1-A1-R1) |
| location_name | string | Display name |
| parent_code | ?string | Parent location code |
| location_type | string | Warehouse, Zone, Aisle, Rack, Shelf, Bin |
| address, city, state, postal_code, country | string | Physical address |
| is_active | bool | Active status |

---

## 3. Functional Features

### 3.1 Serial Number Management

| Feature | Description |
|---------|-------------|
| Find by Serial | Look up item by serial number |
| Find by Item | List all serials for an item |
| Assign to Location | Move item to warehouse location |
| Record Sale | Mark serial as sold |
| Warranty Check | isUnderWarranty() method |
| Location History | Track movement via log |

### 3.2 Batch Management

| Feature | Description |
|---------|-------------|
| Find by Batch | Look up batch details |
| Find by Item | List batches for item |
| Expiry Tracking | isExpired() method |
| Quantity Tracking | Manage batch quantities |
| Status Management | Active, Closed, Partial |

### 3.3 Warehouse Hierarchy

| Feature | Description |
|---------|-------------|
| Location Types | Warehouse > Zone > Aisle > Rack > Shelf > Bin |
| Full Path | getFullPath() returns "WH1 > Zone A > Aisle 1..." |
| Hierarchy Tree | getHierarchy() returns ancestry |
| Child Locations | getChildren() returns sub-locations |
| Tree View | getTree() returns all locations |

### 3.4 Vendor Performance

| Feature | Description |
|---------|-------------|
| Purchase History | View PO history with vendor |
| Total Spend | Calculate lifetime spend |
| On-Time Rate | Percentage of on-time deliveries |
| Quality Score | Based on damage claims |
| Category Filter | Filter by vendor category |
| Top Vendors | Rank by total spend |

### 3.5 Reporting Dashboard

| Feature | Description |
|---------|-------------|
| KPI Display | Key performance indicators |
| Vendor Scorecard | Vendor performance summary |
| Stock Levels | Inventory quantities |
| Movement History | Location changes |
| Expiry Alerts | Items expiring soon |

---

## 4. Integration Dependencies

### 4.1 Depends On

| Module | Dependency Type | Purpose |
|--------|-----------------|---------|
| FrontAccounting | Required | Database, Purchase Orders |
| ksf_FA_Inventory | UI Adapter | FrontAccounting integration |

### 4.2 Provided To

| Module | Data/Events |
|--------|-------------|
| ksf_FA_Inventory | Inventory data |
| Reporting | Metrics and KPIs |

---

## 5. Database Schema

### 5.1 Tables Created

| Table | Purpose |
|-------|---------|
| inventory_serial_numbers | Individual item tracking |
| inventory_batch_numbers | Bulk quantity tracking |
| inventory_warehouse_locations | Location hierarchy |
| inventory_serial_location_log | Movement history |
| vendor_performance | Vendor metrics |
| vendor_categories | Vendor types |
| vendor_price_list | Contract pricing |

---

## 6. Configuration

### 6.1 Shortcodes

| Shortcode | Purpose |
|-----------|---------|
| [ksf_inventory] | Main inventory UI |

### 6.2 Menu Integration

| Menu Entry | Function |
|------------|----------|
| inventory | Main inventory menu |

---

## 7. UI Tabs

| Tab | Function |
|-----|----------|
| locations | Warehouse location management |
| serials | Serial number tracking |
| batches | Batch number tracking |
| movement | Movement log history |

---

## 8. Non-Functional Requirements

### 8.1 Performance
- Serial lookup: < 50ms
- Batch queries: < 100ms
- Dashboard load: < 500ms

### 8.2 Scalability
- Support 100,000+ serial numbers
- Efficient batch queries

---

## 9. Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Business Analyst | | | |
| Technical Lead | | | |
| QA Lead | | | |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*