-- Serial Numbers Table
CREATE TABLE IF NOT EXISTS {{MDB}}inventory_serial_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_no VARCHAR(50) NOT NULL UNIQUE,
    item_code VARCHAR(50) NOT NULL,
    serialized_date DATE NOT NULL,
    purchase_date DATE,
    supplier_ref VARCHAR(50),
    purchase_cost DECIMAL(12,2),
    location_code VARCHAR(20),
    status VARCHAR(20) DEFAULT 'Available',
    sold_to VARCHAR(50),
    sold_date DATETIME,
    warranty_end_days INT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_item (item_code),
    INDEX idx_location (location_code),
    INDEX idx_status (status)
);

-- Batch Numbers Table
CREATE TABLE IF NOT EXISTS {{MDB}}inventory_batch_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_no VARCHAR(50) NOT NULL UNIQUE,
    item_code VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    batch_date DATE NOT NULL,
    expiry_date DATE,
    supplier_ref VARCHAR(50),
    status VARCHAR(20) DEFAULT 'Active',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_item (item_code),
    INDEX idx_batch (batch_no),
    INDEX idx_status (status)
);

-- Warehouse Locations Table (Shelf/Bin)
CREATE TABLE IF NOT EXISTS {{MDB}}inventory_warehouse_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_code VARCHAR(20) NOT NULL UNIQUE,
    location_name VARCHAR(100) NOT NULL,
    parent_code VARCHAR(20),
    location_type VARCHAR(20) DEFAULT 'Warehouse',
    address VARCHAR(200),
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'US',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_parent (parent_code),
    INDEX idx_type (location_type),
    INDEX idx_active (is_active)
);

-- Initial warehouse locations (Home Depot style)
INSERT INTO {{MDB}}inventory_warehouse_locations (location_code, location_name, location_type) VALUES
('WH1', 'Main Warehouse', 'Warehouse'),
('WH1-A1', 'Aisle 1', 'Aisle'),
('WH1-A1-R1', 'Rack 1', 'Rack'),
('WH1-A1-R1-S1', 'Shelf 1', 'Shelf'),
('WH1-A1-R1-S1-B1', 'Bin 1-A1-R1-S1', 'Bin');

-- Cross-reference serial to location tracking
CREATE TABLE IF NOT EXISTS {{MDB}}inventory_serial_location_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_no VARCHAR(50) NOT NULL,
    location_code VARCHAR(20) NOT NULL,
    moved_by VARCHAR(50),
    moved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    INDEX idx_serial (serial_no),
    INDEX idx_location (location_code)
);