# Functional Requirements - ksf_Inventory

## Document Information
- **Module**: ksf_Inventory
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Overview

This document details functional requirements for the ksf_Inventory module covering serial tracking, batch management, warehouse locations, and vendor performance.

---

## 2. Serial Number Management

### FR-INV-001: Create Serial Number
**Priority**: High
**Description**: System shall track individual items by serial number.

**Acceptance Criteria**:
- [ ] Each item has unique serial number
- [ ] Linked to item_code (product)
- [ ] Serialized date recorded
- [ ] Initial status: Available

**Test Data**:
| Input | Expected |
|-------|----------|
| serial_no="SN001", item_code="ITEM001" | Record created |
| duplicate serial_no | Error (unique constraint) |

---

### FR-INV-002: Find by Serial Number
**Priority**: High
**Description**: System shall retrieve item by serial number.

**Acceptance Criteria**:
- [ ] SerialNumber::find(serial) returns entity
- [ ] Returns null if not found
- [ ] All properties loaded

---

### FR-INV-003: Find Serials by Item
**Priority**: High
**Description**: System shall list all serials for an item.

**Acceptance Criteria**:
- [ ] findByItem(item_code) returns array
- [ ] Ordered by serialized_date DESC
- [ ] Empty array if none found

---

### FR-INV-004: Assign to Location
**Priority**: High
**Description**: System shall move serial item to warehouse location.

**Acceptance Criteria**:
- [ ] assignToLocation(code) updates location_code
- [ ] Updates timestamp
- [ ] Returns bool (success/failure)
- [ ] Location log entry created

---

### FR-INV-005: Record Sale
**Priority**: High
**Description**: System shall mark serial item as sold.

**Acceptance Criteria**:
- [ ] recordSale(customer_no) sets status to Sold
- [ ] Records sold_date
- [ ] Links to customer
- [ ] Updates timestamp

---

### FR-INV-006: Warranty Tracking
**Priority**: Medium
**Description**: System shall track warranty status.

**Acceptance Criteria**:
- [ ] isUnderWarranty() calculates from purchase_date + warranty_end_days
- [ ] Returns false if no warranty set
- [ ] Returns false if warranty expired
- [ ] Returns true if within warranty

---

## 3. Batch Management

### FR-INV-010: Create Batch
**Priority**: High
**Description**: System shall track bulk quantities by batch.

**Acceptance Criteria**:
- [ ] Unique batch number
- [ ] Linked to item_code
- [ ] Quantity tracked
- [ ] Initial status: Active

---

### FR-INV-011: Batch Expiry
**Priority**: High
**Description**: System shall detect expired batches.

**Acceptance Criteria**:
- [ ] isExpired() checks expiry_date vs now
- [ ] Returns true if past expiry
- [ ] Returns false if no expiry date
- [ ] Returns false if not yet expired

---

### FR-INV-012: Batch Exhausted
**Priority**: Medium
**Description**: System shall detect when batch is depleted.

**Acceptance Criteria**:
- [ ] isExhausted() checks quantity <= 0
- [ ] Used for FIFO picking logic

---

### FR-INV-013: Batch Status
**Priority**: Medium
**Description**: System shall track batch status.

**Status Values**:
| Status | Meaning |
|--------|---------|
| Active | Batch available, quantity > 0 |
| Partial | Partially consumed |
| Closed | Fully depleted or expired |

---

## 4. Warehouse Locations

### FR-INV-020: Location Hierarchy
**Priority**: High
**Description**: System shall support hierarchical locations.

**Location Types** (top to bottom):
1. Warehouse
2. Zone
3. Aisle
4. Rack
5. Shelf
6. Bin

**Acceptance Criteria**:
- [ ] Each location can have parent_code
- [ ] Root locations have null parent
- [ ] getHierarchy() returns full ancestry
- [ ] getFullPath() returns formatted string

---

### FR-INV-021: Location Lookup
**Priority**: High
**Description**: System shall find locations by code or type.

**Acceptance Criteria**:
- [ ] find(code) returns single location
- [ ] findByType(type) returns array
- [ ] getTree() returns all locations
- [ ] is_active filter on queries

---

### FR-INV-022: Location Children
**Priority**: Medium
**Description**: System shall retrieve child locations.

**Acceptance Criteria**:
- [ ] getChildren() returns sub-locations
- [ ] Only active children returned
- [ ] Ordered by location_type, then name

---

## 5. Vendor Management

### FR-INV-030: Vendor Lookup
**Priority**: High
**Description**: System shall find vendors by number.

**Acceptance Criteria**:
- [ ] VendorManagement::find(vendor_no) returns entity
- [ ] Uses supplier_master table
- [ ] Returns null if not found

---

### FR-INV-031: Vendor Search
**Priority**: Medium
**Description**: System shall search vendors.

**Acceptance Criteria**:
- [ ] search(query) searches supplier_id, name, email
- [ ] Returns array of matches
- [ ] Limit parameter supported
- [ ] Ordered by name

---

### FR-INV-032: Top Vendors
**Priority**: Medium
**Description**: System shall rank vendors by spend.

**Acceptance Criteria**:
- [ ] getTopVendors(n) returns top n by total spend
- [ ] Joins with purchase_orders
- [ ] Includes total_spend calculation

---

### FR-INV-033: Vendors by Category
**Priority**: Low
**Description**: System shall filter vendors by category.

**Acceptance Criteria**:
- [ ] getByCategory(category) returns filtered list
- [ ] Ordered by name

---

## 6. Vendor Performance

### FR-INV-040: Performance Metrics
**Priority**: High
**Description**: System shall calculate vendor performance.

**Metrics Calculated**:
| Metric | Description |
|--------|-------------|
| order_count | Total orders |
| total_spend | Sum of order totals |
| avg_order_value | Total / Count |
| on_time_deliveries | Delivered on/before required |
| late_deliveries | Delivered after required |
| quality_score | On-time rate percentage |

---

### FR-INV-041: On-Time Rate
**Priority**: High
**Description**: System shall calculate delivery reliability.

**Acceptance Criteria**:
- [ ] getOnTimeRate() returns percentage
- [ ] Formula: (on_time / total_deliveries) * 100
- [ ] Returns 0 if no orders
- [ ] Rounded to 1 decimal place

---

### FR-INV-042: Purchase History
**Priority**: Medium
**Description**: System shall show vendor purchase history.

**Acceptance Criteria**:
- [ ] getPurchaseHistory() returns recent POs
- [ ] Limit 20 most recent
- [ ] Ordered by date DESC

---

## 7. UI Features

### FR-INV-050: Inventory Panel
**Priority**: High
**Description**: System shall render inventory UI panel.

**Acceptance Criteria**:
- [ ] ksf-inventory-panel CSS class
- [ ] ui-tabs-nav for navigation
- [ ] Table-based data display
- [ ] Action links for details

---

### FR-INV-051: Tab Navigation
**Priority**: High
**Description**: System shall support tabbed interface.

**Tabs**:
| Tab | Content |
|-----|----------|
| locations | Warehouse location tree |
| serials | Serial number tracking |
| batches | Batch number tracking |
| movement | Location change history |

---

### FR-INV-052: Serial Search
**Priority**: Medium
**Description**: System shall search serials by item.

**Acceptance Criteria**:
- [ ] Form with item code input
- [ ] Table results with all fields
- [ ] Warranty status shown
- [ ] Location shown

---

## 8. Reporting

### FR-INV-060: KPI Dashboard
**Priority**: Medium
**Description**: System shall display key indicators.

**KPIs**:
- Total inventory value
- Items expiring soon
- Low stock alerts
- Vendor performance summary

---

### FR-INV-061: Vendor Scorecard
**Priority**: Medium
**Description**: System shall render vendor scorecard.

**Content**:
- Vendor name
- Total spend
- On-time rate
- Quality score
- Order count

---

## 9. Acceptance Test Matrix

| FR ID | Requirement | Test Cases | Status |
|-------|-------------|------------|--------|
| FR-INV-001 | Create Serial | INV-SERIAL-001 | ✓ |
| FR-INV-002 | Find Serial | INV-SERIAL-002 | ✓ |
| FR-INV-004 | Assign Location | INV-LOC-001 | ✓ |
| FR-INV-006 | Warranty | INV-SERIAL-003 | ✓ |
| FR-INV-011 | Batch Expiry | INV-BATCH-001 | ✓ |
| FR-INV-020 | Location Hierarchy | INV-LOC-002 | ✓ |
| FR-INV-040 | Performance | INV-VEND-001 | ✓ |
| FR-INV-041 | On-Time Rate | INV-VEND-002 | ✓ |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*