# Use Case - ksf_Inventory

## Document Information
- **Module**: ksf_Inventory
- **Version**: 1.0.0
- **Date**: 2026-05-13
- **Status**: Implemented
- **Author**: KSFII Development Team

---

## 1. Use Case Overview

| Use Case ID | Use Case Name | Actor | Priority |
|-------------|---------------|-------|----------|
| UC-INV-001 | Track Serialized Item | Warehouse Staff | High |
| UC-INV-002 | Assign Item to Location | Warehouse Staff | High |
| UC-INV-003 | Record Item Sale | Sales | High |
| UC-INV-004 | Check Warranty | Support | High |
| UC-INV-005 | Track Batch Inventory | Warehouse Staff | Medium |
| UC-INV-006 | View Expiring Batches | Manager | Medium |
| UC-INV-007 | Manage Warehouse Locations | Admin | High |
| UC-INV-008 | View Vendor Performance | Procurement | Medium |
| UC-INV-009 | Find Top Vendors | Procurement | Low |

---

## 2. Use Case Details

### UC-INV-001: Track Serialized Item

**Actor**: Warehouse Staff
**Priority**: High
**Preconditions**: Item with serial number received

**Basic Flow**:
1. Staff receives item with serial "SN12345"
2. Opens Inventory > Serial Numbers
3. Enters item code: "LAPTOP001"
4. System creates serial record
5. Initial location assigned

**Postconditions**: Serial tracked in system

---

### UC-INV-002: Assign Item to Location

**Actor**: Warehouse Staff
**Priority**: High
**Preconditions**: Serial number in system

**Basic Flow**:
1. Staff scans/plugs in serial "SN12345"
2. Opens serial details
3. Selects new location "WH1-A1-R1-S2-B3"
4. Clicks "Assign Location"
5. System updates location_code
6. System logs movement

**Postconditions**: Item location updated

---

### UC-INV-003: Record Item Sale

**Actor**: Sales
**Priority**: High
**Preconditions**: Customer purchases serialized item

**Basic Flow**:
1. Sales creates invoice for customer
2. System calls recordSale(customer_no)
3. Status changes to "Sold"
4. sold_date recorded
5. Warranty clock starts

**Postconditions**: Item marked as sold

---

### UC-INV-004: Check Warranty

**Actor**: Support Staff
**Priority**: High
**Preconditions**: Customer claims warranty

**Basic Flow**:
1. Customer provides serial "SN12345"
2. Support enters serial in search
3. System finds serial record
4. System calls isUnderWarranty()
5. System calculates from purchase_date + warranty_end_days
6. Result: true/false returned

**Postconditions**: Warranty status determined

---

### UC-INV-005: Track Batch Inventory

**Actor**: Warehouse Staff
**Priority**: Medium
**Preconditions**: Bulk items received

**Basic Flow**:
1. Receive 500 units of "PART001" in batch "BATCH2026-001"
2. Create batch record with quantity 500
3. System tracks quantity
4. As items picked, quantity decrements
5. When quantity reaches 0, status = "Closed"

**Postconditions**: Batch tracked with quantity

---

### UC-INV-006: View Expiring Batches

**Actor**: Manager
**Priority**: Medium
**Preconditions**: Batches with expiry dates exist

**Basic Flow**:
1. Manager opens Inventory > Batches
2. System shows all batches
3. System calculates isExpired() for each
4. Expired batches highlighted in red
5. Manager takes action (use/dispose)

**Postconditions**: Expiring items identified

---

### UC-INV-007: Manage Warehouse Locations

**Actor**: Admin
**Priority**: High
**Preconditions**: None

**Basic Flow**:
1. Admin opens Inventory > Locations
2. Views hierarchical tree
3. Creates new location "WH1-A1-R2" under "WH1-A1"
4. Sets type: Rack
5. System validates code uniqueness
6. Location created

**Postconditions**: Location exists in hierarchy

---

### UC-INV-008: View Vendor Performance

**Actor**: Procurement
**Priority**: Medium
**Preconditions**: Purchase orders exist

**Basic Flow**:
1. Procurement opens Reporting > Vendor Scorecard
2. System creates VendorPerformance for vendor "SUP001"
3. System calculates from purchase_orders
4. Displays on_time_rate, quality_score, total_spend

**Postconditions**: Performance metrics visible

---

### UC-INV-009: Find Top Vendors

**Actor**: Procurement
**Priority**: Low
**Preconditions**: Purchase history exists

**Basic Flow**:
1. Procurement searches for top 10 vendors
2. System calls getTopVendors(10)
3. System joins suppliers_master with purchase_orders
4. Orders by SUM(total) DESC
5. Returns ranked list

**Postconditions**: Top vendors identified

---

## 3. Sequence Diagrams

### UC-INV-004: Check Warranty

```
Support        UI           SerialNumber       Database
  │             │                │                │
  │ Enter SN    │                │                │
  │────────────>│                │                │
  │             │                │                │
  │             │ find("SN12345")│                │
  │             │───────────────>│                │
  │             │                │                │
  │             │                │ SELECT query   │
  │             │                │───────────────>│
  │             │                │                │
  │             │                │<───────────────│
  │             │                │ Serial data    │
  │             │<───────────────│                │
  │             │                │                │
  │             │ isUnderWarranty()              │
  │             │───────────────>│                │
  │             │                │                │
  │             │ Calculate:     │                │
  │             │ purchase + days │                │
  │             │ vs now          │                │
  │             │                │                │
  │             │ Result: true    │                │
  │ Display     │<───────────────│                │
  │<────────────│                │                │
```

---

## 4. Activity Diagram

### Serial Item Sale Process

```
[Start] ──> [Scan Serial Number]
                    │
                    ▼
          ┌───────────────────┐
          │ Confirm Item      │
          │ Available?        │
          └───────────────────┘
                 │        │
               Yes       No
                 │        │
                 ▼        ▼
         ┌──────────┐  ┌──────────┐
         │ Proceed  │  │ Show Err │
         │ Sale     │  │          │
         └──────────┘  └──────────┘
                 │
                 ▼
         [Create Invoice]
                 │
                 ▼
         ┌───────────────────┐
         │ recordSale()      │
         │ - Status = Sold  │
         │ - sold_date = now │
         │ - customer_no     │
         └───────────────────┘
                 │
                 ▼
         ┌───────────────────┐
         │ Calculate Warranty│
         │ Warranty = purchase│
         │ + warranty_days   │
         └───────────────────┘
                 │
                 ▼
           [Update Done]
                 │
                 ▼
               [End]
```

---

## 5. Use Case Traceability

| UC ID | Related FR | Related Test |
|-------|------------|-------------|
| UC-INV-001 | FR-INV-001 | INV-SERIAL-001 |
| UC-INV-002 | FR-INV-004 | INV-LOC-001 |
| UC-INV-003 | FR-INV-005 | INV-SERIAL-002 |
| UC-INV-004 | FR-INV-006 | INV-SERIAL-003 |
| UC-INV-005 | FR-INV-010 | INV-BATCH-001 |
| UC-INV-006 | FR-INV-011 | INV-BATCH-002 |
| UC-INV-007 | FR-INV-020 | INV-LOC-002 |
| UC-INV-008 | FR-INV-040 | INV-VEND-001 |
| UC-INV-009 | FR-INV-032 | INV-VEND-002 |

---

*Document Version: 1.0.0*
*Last Updated: 2026-05-13*