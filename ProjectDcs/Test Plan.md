# Test Plan - ksf_Inventory

## Document Information
- **Module**: ksf_Inventory
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Test Overview

### 1.1 Test Objectives
- Verify serial number creation and tracking
- Validate batch management
- Confirm warehouse location hierarchy
- Test vendor performance calculations
- Ensure reporting dashboard works

---

## 2. Test Cases

### 2.1 Serial Number Tests

#### INV-SERIAL-001: Create Serial Number
**Test ID**: INV-SERIAL-001
**Priority**: High

**Test Steps**:
1. Create SerialNumber with data
2. Assert id set
3. Assert serial_no correct
4. Assert status === "Available"

---

#### INV-SERIAL-002: Find Serial by Number
**Test ID**: INV-SERIAL-002
**Priority**: High

**Test Steps**:
1. Create serial in database
2. Call SerialNumber::find(serial_no)
3. Assert returns entity
4. Assert all properties loaded

---

#### INV-SERIAL-003: Find Serials by Item
**Test ID**: INV-SERIAL-003
**Priority**: High

**Test Steps**:
1. Create 3 serials for same item_code
2. Create 2 serials for different item
3. Call findByItem(item_code)
4. Assert returns 3 results
5. Assert ordered by serialized_date DESC

---

#### INV-SERIAL-004: Assign Location
**Test ID**: INV-SERIAL-004
**Priority**: High

**Test Steps**:
1. Create serial
2. Call assignToLocation("WH1-A1")
3. Verify location_code updated
4. Verify updated_at changed

---

#### INV-SERIAL-005: Record Sale
**Test ID**: INV-SERIAL-005
**Priority**: High

**Test Steps**:
1. Create serial
2. Call recordSale("CUST001")
3. Assert status === "Sold"
4. Assert sold_date set
5. Assert customer_no linked

---

#### INV-SERIAL-006: Warranty Check - Within Warranty
**Test ID**: INV-SERIAL-006
**Priority**: High

**Test Steps**:
1. Create serial with:
   - purchase_date = today - 30 days
   - warranty_end_days = 365
2. Call isUnderWarranty()
3. Assert returns true

---

#### INV-SERIAL-007: Warranty Check - Expired
**Test ID**: INV-SERIAL-007
**Priority**: High

**Test Steps**:
1. Create serial with:
   - purchase_date = today - 400 days
   - warranty_end_days = 365
2. Call isUnderWarranty()
3. Assert returns false

---

#### INV-SERIAL-008: Warranty Check - No Warranty
**Test ID**: INV-SERIAL-008
**Priority**: Medium

**Test Steps**:
1. Create serial with warranty_end_days = null
2. Call isUnderWarranty()
3. Assert returns false

---

### 2.2 Batch Number Tests

#### INV-BATCH-001: Create Batch
**Test ID**: INV-BATCH-001
**Priority**: High

**Test Steps**:
1. Create BatchNumber
2. Assert batch_no set
3. Assert quantity = 0 initially (or provided)
4. Assert status === "Active"

---

#### INV-BATCH-002: Batch Expiry - Not Expired
**Test ID**: INV-BATCH-002
**Priority**: High

**Test Steps**:
1. Create batch with expiry_date = future
2. Call isExpired()
3. Assert returns false

---

#### INV-BATCH-003: Batch Expiry - Expired
**Test ID**: INV-BATCH-003
**Priority**: High

**Test Steps**:
1. Create batch with expiry_date = past
2. Call isExpired()
3. Assert returns true

---

#### INV-BATCH-004: Batch Exhausted
**Test ID**: INV-BATCH-004
**Priority**: Medium

**Test Steps**:
1. Create batch with quantity = 0
2. Call isExhausted()
3. Assert returns true
4. Create batch with quantity = 10
5. Assert returns false

---

### 2.3 Warehouse Location Tests

#### INV-LOC-001: Create Location
**Test ID**: INV-LOC-001
**Priority**: High

**Test Steps**:
1. Create WarehouseLocation
2. Assert location_code set
3. Assert location_type defaults to "Warehouse"
4. Assert is_active = true

---

#### INV-LOC-002: Location Hierarchy
**Test ID**: INV-LOC-002
**Priority**: High

**Test Steps**:
1. Create hierarchy: Warehouse > Zone > Aisle > Rack
2. Get deepest location
3. Call getHierarchy()
4. Assert returns array of 4 locations
5. Assert first is root, last is deepest

---

#### INV-LOC-003: Full Path
**Test ID**: INV-LOC-003
**Priority**: High

**Test Steps**:
1. Create hierarchy: "Main WH" > "Zone A" > "Aisle 1"
2. Get "Aisle 1" location
3. Call getFullPath()
4. Assert returns "Main WH > Zone A > Aisle 1"

---

#### INV-LOC-004: Get Children
**Test ID**: INV-LOC-004
**Priority**: Medium

**Test Steps**:
1. Create parent location with 2 children
2. Call getChildren()
3. Assert returns 2 locations
4. Assert both is_active = true

---

#### INV-LOC-005: Find by Type
**Test ID**: INV-LOC-005
**Priority**: Medium

**Test Steps**:
1. Create 3 shelves and 2 racks
2. Call findByType("Shelf")
3. Assert returns 3 results

---

### 2.4 Vendor Tests

#### INV-VEND-001: Find Vendor
**Test ID**: INV-VEND-001
**Priority**: High

**Test Steps**:
1. Create vendor data in database
2. Call VendorManagement::find(vendor_no)
3. Assert returns entity
4. Assert all properties correct

---

#### INV-VEND-002: Search Vendors
**Test ID**: INV-VEND-002
**Priority**: Medium

**Test Steps**:
1. Create vendors: "Acme Corp", "Beta Inc", "Acme Supplies"
2. Call search("Acme")
3. Assert returns 2 results
4. Assert contains "Acme Corp" and "Acme Supplies"

---

#### INV-VEND-003: Get Top Vendors
**Test ID**: INV-VEND-003
**Priority**: Medium

**Test Steps**:
1. Set up vendors with different total spend
2. Call getTopVendors(2)
3. Assert returns 2 vendors
4. Assert ordered by total_spend DESC

---

### 2.5 Performance Tests

#### INV-PERF-001: On-Time Rate Calculation
**Test ID**: INV-PERF-001
**Priority**: High

**Test Steps**:
1. Create VendorPerformance("V001")
2. Set up orders:
   - 8 on-time deliveries
   - 2 late deliveries
3. Call getOnTimeRate()
4. Assert returns 80.0

---

#### INV-PERF-002: On-Time Rate - No Orders
**Test ID**: INV-PERF-002
**Priority**: Medium

**Test Steps**:
1. Create VendorPerformance for vendor with no orders
2. Call getOnTimeRate()
3. Assert returns 0.0

---

### 2.6 Edge Cases

#### INV-EDGE-001: Serial Not Found
**Test ID**: INV-EDGE-001
**Priority**: Medium

**Test Steps**:
1. Call SerialNumber::find("NONEXISTENT")
2. Assert returns null

---

#### INV-EDGE-002: Location Not Found
**Test ID**: INV-EDGE-002
**Priority**: Medium

**Test Steps**:
1. Call WarehouseLocation::find("INVALID")
2. Assert returns null

---

## 3. Test Data

### 3.1 Serial Numbers

```php
$serials = [
    [
        'serial_no' => 'SN001',
        'item_code' => 'LAPTOP001',
        'serialized_date' => '2026-01-15',
        'purchase_date' => '2026-01-15',
        'warranty_end_days' => 365,
        'status' => 'Available',
    ],
    [
        'serial_no' => 'SN002',
        'item_code' => 'LAPTOP001',
        'serialized_date' => '2026-02-01',
        'status' => 'Sold',
    ],
];
```

### 3.2 Batches

```php
$batches = [
    [
        'batch_no' => 'BATCH-2026-001',
        'item_code' => 'PART001',
        'quantity' => 500,
        'expiry_date' => '2026-12-31',
    ],
];
```

### 3.3 Locations

```php
$locations = [
    ['code' => 'WH1', 'name' => 'Main Warehouse', 'type' => 'Warehouse'],
    ['code' => 'WH1-A1', 'name' => 'Zone A', 'type' => 'Zone', 'parent' => 'WH1'],
    ['code' => 'WH1-A1-R1', 'name' => 'Rack 1', 'type' => 'Rack', 'parent' => 'WH1-A1'],
];
```

---

## 4. Pass Criteria

| Category | Target |
|----------|--------|
| Serial operations | 100% |
| Batch operations | 100% |
| Location operations | 100% |
| Vendor operations | 100% |
| Performance calculations | 100% |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*