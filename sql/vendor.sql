-- Extended Vendor Management Table
CREATE TABLE IF NOT EXISTS {{MDB}}vendor_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_no VARCHAR(50) NOT NULL,
    order_count INT DEFAULT 0,
    total_spend DECIMAL(15,2) DEFAULT 0,
    avg_order_value DECIMAL(15,2) DEFAULT 0,
    on_time_deliveries INT DEFAULT 0,
    late_deliveries INT DEFAULT 0,
    damage_claims INT DEFAULT 0,
    quality_score DECIMAL(5,2) DEFAULT 0,
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vendor (vendor_no)
);

-- Vendor Categories
CREATE TABLE IF NOT EXISTS {{MDB}}vendor_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(20) NOT NULL UNIQUE,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE
);

-- Vendor Price Lists
CREATE TABLE IF NOT EXISTS {{MDB}}vendor_price_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_no VARCHAR(50) NOT NULL,
    item_code VARCHAR(50) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    min_quantity INT DEFAULT 1,
    valid_from DATE,
    valid_to DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_price (vendor_no, item_code, valid_from)
);

-- Sample vendor categories
INSERT INTO {{MDB}}vendor_categories (category_code, category_name, description) VALUES
('ELECTRONICS', 'Electronics', 'Electronic components and equipment'),
('OFFICE', 'Office Supplies', 'Office furniture and supplies'),
('RAW_MATERIAL', 'Raw Materials', 'Manufacturing raw materials'),
('SERVICES', 'Services', 'Professional services'),
('MAINTENANCE', 'Maintenance', 'Equipment maintenance and repair'),
('LOGISTICS', 'Logistics', 'Shipping and logistics');