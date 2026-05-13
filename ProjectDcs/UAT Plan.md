# UAT Plan - ksf_Inventory

## Document Information
- **Module**: ksf_Inventory
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Ready for UAT
- **Author**: KSFII Development Team

---

## 1. UAT Objectives

### 1.1 Purpose
Validate that ksf_Inventory correctly tracks serial numbers, batches, warehouse locations, and vendor performance.

### 1.2 Objectives
1. Verify serial number creation and tracking
2. Confirm batch management works
3. Validate warehouse location hierarchy
4. Test vendor performance calculations
5. Ensure reporting dashboard displays correctly

---

## 2. Test Scenarios

### 2.1 Serial Number Management

#### UAT-INV-001: Create Serial Number
**Scenario**: Receive serialized item into inventory

**Preconditions**: Item with serial number received

**Test Steps**:
1. Navigate to Inventory > Serial Numbers
2. Click "Add Serial"
3. Enter serial number: "SN-TEST-001"
4. Enter item code: "LAPTOP001"
5. Set serialized date: Today
6. Click "Save"
7. Verify serial appears in list

**Expected Result**: Serial created and visible

**Pass Criteria**: [ ] Created [ ] In list [ ] Status Available

---

#### UAT-INV-002: Assign Serial to Location
**Scenario**: Move item from receiving to storage

**Preconditions**: Serial exists in system

**Test Steps**:
1. Search for serial "SN-TEST-001"
2. Click on serial to open details
3. Click "Assign Location"
4. Select location: "WH1-A1-R1-S2-B3"
5. Confirm assignment
6. Verify location updated

**Expected Result**: Location updated in system

**Pass Criteria**: [ ] Location changed [ ] Log entry created [ ] Updated timestamp

---

#### UAT-INV-003: Record Sale of Serialized Item
**Scenario**: Sell item to customer

**Preconditions**: Serial available

**Test Steps**:
1. Open serial "SN-TEST-001"
2. Click "Record Sale"
3. Enter customer number: "CUST-001"
4. Confirm sale
5. Verify status changes to "Sold"
6. Verify sold_date recorded

**Expected Result**: Item marked as sold

**Pass Criteria**: [ ] Status = Sold [ ] Date recorded [ ] Customer linked

---

#### UAT-INV-004: Check Item Warranty
**Scenario**: Customer calls about warranty

**Preconditions**: Serial with warranty exists

**Test Steps**:
1. Enter serial "SN-TEST-001"
2. System displays warranty status
3. Verify "Under Warranty" or "Expired" shown
4. Verify warranty expiry date calculated

**Expected Result**: Warranty status clear

**Pass Criteria**: [ ] Status shown [ ] Expiry date correct [ ] Clear indication

---

### 2.2 Batch Management

#### UAT-INV-005: Create Batch
**Scenario**: Receive bulk items

**Preconditions**: Bulk items received

**Test Steps**:
1. Navigate to Inventory > Batch Numbers
2. Click "Add Batch"
3. Enter batch: "BATCH-2026-001"
4. Enter item: "PART001"
5. Enter quantity: 500
6. Set batch date: Today
7. Set expiry date: 6 months from now
8. Save

**Expected Result**: Batch created with quantity

**Pass Criteria**: [ ] Created [ ] Quantity correct [ ] Status = Active

---

#### UAT-INV-006: View Expiring Batches
**Scenario**: Check for items nearing expiry

**Preconditions**: Batches with past/future expiry dates

**Test Steps**:
1. Navigate to Inventory > Batch Numbers
2. View batch list
3. Locate batch with expired or expiring soon status
4. Verify color coding (red for expired)
5. Verify quantity shown

**Expected Result**: Expiring batches identified

**Pass Criteria**: [ ] Expired shown red [ ] Quantity visible [ ] Dates accurate

---

### 2.3 Warehouse Locations

#### UAT-INV-007: View Location Hierarchy
**Scenario**: View warehouse structure

**Preconditions**: Locations exist

**Test Steps**:
1. Navigate to Inventory > Warehouse Locations
2. View location tree
3. Expand/collapse levels
4. Verify hierarchy: Warehouse > Zone > Aisle > Rack > Shelf > Bin

**Expected Result**: Hierarchical tree displayed

**Pass Criteria**: [ ] Tree structure [ ] Expand works [ ] Path correct

---

#### UAT-INV-008: Full Path Display
**Scenario**: See complete location path

**Preconditions**: Nested locations exist

**Test Steps**:
1. Click on "Shelf" location
2. Verify "Full Path" shown
3. Example: "Main Warehouse > Zone A > Aisle 1 > Rack 2 > Shelf 3"
4. Verify path is readable

**Expected Result**: Full path formatted correctly

**Pass Criteria**: [ ] Path shown [ ] Format correct [ ] All levels included

---

#### UAT-INV-009: Find Locations by Type
**Scenario**: Find all shelves

**Preconditions**: Multiple location types

**Test Steps**:
1. Filter by location type: "Shelf"
2. Verify only shelves listed
3. Verify other types not shown

**Expected Result**: Filtered results correct

**Pass Criteria**: [ ] Only shelves [ ] Count accurate [ ] Other types hidden

---

### 2.4 Vendor Performance

#### UAT-INV-010: View Vendor Scorecard
**Scenario**: Review vendor performance

**Preconditions**: Purchase orders with vendor exist

**Test Steps**:
1. Navigate to Reporting > Vendor Scorecard
2. Select vendor: "Acme Corp"
3. Verify metrics displayed:
   - Total spend
   - Order count
   - On-time rate
   - Quality score

**Expected Result**: Performance metrics visible

**Pass Criteria**: [ ] All metrics shown [ ] Values calculated [ ] Format clear

---

#### UAT-INV-011: Find Top Vendors
**Scenario**: Identify top-spending vendors

**Preconditions**: Multiple vendors with orders

**Test Steps**:
1. Navigate to Vendors > Top Vendors
2. View top 10 list
3. Verify ordered by total spend DESC
4. Verify spend amounts shown

**Expected Result**: Ranked vendor list

**Pass Criteria**: [ ] Ordered correctly [ ] Amounts accurate [ ] Top 10 shown

---

#### UAT-INV-012: Search Vendors
**Scenario**: Find specific vendor

**Preconditions**: Multiple vendors exist

**Test Steps**:
1. Enter search term: "Acme"
2. Verify matching vendors returned
3. Verify non-matching not shown
4. Click vendor to view details

**Expected Result**: Search results accurate

**Pass Criteria**: [ ] Matches found [ ] Correct vendor [ ] Details accessible

---

### 2.5 Reporting Dashboard

#### UAT-INV-013: View KPI Dashboard
**Scenario**: Overview of inventory health

**Preconditions**: Data in system

**Test Steps**:
1. Navigate to Reporting > Dashboard
2. View KPI cards
3. Verify metrics calculated correctly
4. Verify visual presentation

**Expected Result**: Dashboard displays KPIs

**Pass Criteria**: [ ] KPIs shown [ ] Values correct [ ] Visual appeal

---

## 3. Test Execution Schedule

### 3.1 Phase 1: Serial & Batch (Day 1)
| Test | Focus |
|------|-------|
| UAT-INV-001 | Serial creation |
| UAT-INV-002 | Serial location |
| UAT-INV-003 | Serial sale |
| UAT-INV-004 | Warranty check |
| UAT-INV-005 | Batch creation |
| UAT-INV-006 | Batch expiry |

### 3.2 Phase 2: Locations (Day 1)
| Test | Focus |
|------|-------|
| UAT-INV-007 | Hierarchy view |
| UAT-INV-008 | Full path |
| UAT-INV-009 | Type filter |

### 3.3 Phase 3: Vendors & Reporting (Day 2)
| Test | Focus |
|------|-------|
| UAT-INV-010 | Vendor scorecard |
| UAT-INV-011 | Top vendors |
| UAT-INV-012 | Vendor search |
| UAT-INV-013 | Dashboard |

---

## 4. Success Criteria

### 4.1 Functional Criteria

| Criteria | Target | Actual |
|----------|--------|--------|
| Serial operations | 100% | - |
| Batch operations | 100% | - |
| Location management | 100% | - |
| Vendor tracking | 100% | - |
| Reporting | 100% | - |

### 4.2 Test Summary

| Category | Total | Passed | Failed |
|----------|-------|--------|--------|
| Serial | 4 | - | - |
| Batch | 2 | - | - |
| Location | 3 | - | - |
| Vendor | 3 | - | - |
| Reporting | 1 | - | - |
| **Total** | **13** | **-** | **-** |

---

## 5. Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Business Owner | | | |
| Operations Manager | | | |
| QA Lead | | | |
| Technical Lead | | | |

---

## 6. Appendix

### 6.1 Test Data

| Type | Value |
|------|-------|
| Serial | SN-TEST-001 |
| Item | LAPTOP001 |
| Batch | BATCH-2026-001 |
| Location | WH1-A1-R1-S2-B3 |
| Vendor | Acme Corp |

### 6.2 Sample Locations Hierarchy

```
Main Warehouse (WH1)
├── Zone A (WH1-A1)
│   ├── Aisle 1 (WH1-A1-A1)
│   │   ├── Rack 1 (WH1-A1-A1-R1)
│   │   │   ├── Shelf 1 (WH1-A1-A1-R1-S1)
│   │   │   │   ├── Bin 1 (WH1-A1-A1-R1-S1-B1)
│   │   │   │   └── Bin 2 (WH1-A1-A1-R1-S1-B2)
│   │   │   └── Shelf 2
│   │   └── Rack 2
│   └── Aisle 2
└── Zone B (WH1-A2)
```

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*