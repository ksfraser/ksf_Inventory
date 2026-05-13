<?php

namespace Ksfraser\Inventory;

class SerialNumber
{
    public int $id;
    public string $serial_no;
    public string $item_code;
    public string $serialized_date;
    public ?string $purchase_date;
    public ?string $supplier_ref;
    public ?string $purchase_cost;
    public ?string $location_code;
    public string $status;
    public ?int $warranty_end_days;
    public string $notes;
    public string $created_at;
    public ?string $updated_at;

    public const STATUS_AVAILABLE = 'Available';
    public const STATUS_SOLD = 'Sold';
    public const STATUS_RESERVED = 'Reserved';
    public const STATUS_IN_REPAIR = 'In Repair';
    public const STATUS_SCRAPPED = 'Scrapped';

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->serial_no = $data['serial_no'] ?? '';
        $this->item_code = $data['item_code'] ?? '';
        $this->serialized_date = $data['serialized_date'] ?? date('Y-m-d');
        $this->purchase_date = $data['purchase_date'] ?? null;
        $this->supplier_ref = $data['supplier_ref'] ?? null;
        $this->purchase_cost = $data['purchase_cost'] ?? null;
        $this->location_code = $data['location_code'] ?? null;
        $this->status = $data['status'] ?? self::STATUS_AVAILABLE;
        $this->warranty_end_days = $data['warranty_end_days'] ?? null;
        $this->notes = $data['notes'] ?? '';
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function assignToLocation(string $location_code): bool
    {
        global $db;
        include_once 'db.inc';

        $sql = "UPDATE " . TB_PREF . "inventory_serial_numbers 
            SET location_code = " . db_escape($location_code) . ", 
                updated_at = NOW() 
            WHERE serial_no = " . db_escape($this->serial_no);

        return db_query($sql);
    }

    public function recordSale(string $customer_no): bool
    {
        global $db;
        include_once 'db.inc';

        $sql = "UPDATE " . TB_PREF . "inventory_serial_numbers 
            SET status = " . db_escape(self::STATUS_SOLD) . ",
                sold_to = " . db_escape($customer_no) . ",
                sold_date = NOW(),
                updated_at = NOW() 
            WHERE serial_no = " . db_escape($this->serial_no);

        return db_query($sql);
    }

    public function isUnderWarranty(): bool
    {
        if (!$this->warranty_end_days) {
            return false;
        }

        $end_date = strtotime("+{$this->warranty_end_days} days", strtotime($this->purchase_date));
        return time() < $end_date;
    }

    public static function find(string $serial_no): ?self
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_serial_numbers 
            WHERE serial_no = " . db_escape($serial_no);

        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return $row ? new self($row) : null;
    }

    public static function findByItem(string $item_code): array
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_serial_numbers 
            WHERE item_code = " . db_escape($item_code) . "
            ORDER BY serialized_date DESC";

        $result = db_query($sql);
        $serials = [];

        while ($row = db_fetch_assoc($result)) {
            $serials[] = new self($row);
        }

        return $serials;
    }

    public static function listByLocation(string $location_code): array
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_serial_numbers 
            WHERE location_code = " . db_escape($location_code);

        $result = db_query($sql);
        $serials = [];

        while ($row = db_fetch_assoc($result)) {
            $serials[] = new self($row);
        }

        return $serials;
    }
}

class BatchNumber
{
    public int $id;
    public string $batch_no;
    public string $item_code;
    public int $quantity;
    public string $batch_date;
    public ?string $expiry_date;
    public ?string $supplier_ref;
    public string $status;
    public string $notes;
    public string $created_at;

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_PARTIAL = 'Partial';

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->id = $data['id'] ?? 0;
            $this->batch_no = $data['batch_no'];
            $this->item_code = $data['item_code'];
            $this->quantity = $data['quantity'] ?? 0;
            $this->batch_date = $data['batch_date'] ?? date('Y-m-d');
            $this->expiry_date = $data['expiry_date'] ?? null;
            $this->supplier_ref = $data['supplier_ref'] ?? null;
            $this->status = $data['status'] ?? self::STATUS_ACTIVE;
            $this->notes = $data['notes'] ?? '';
            $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        }
    }

    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return strtotime($this->expiry_date) < time();
    }

    public function isExhausted(): bool
    {
        return $this->quantity <= 0;
    }

    public static function find(string $batch_no): ?self
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_batch_numbers 
            WHERE batch_no = " . db_escape($batch_no);

        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return $row ? new self($row) : null;
    }

    public static function findByItem(string $item_code): array
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_batch_numbers 
            WHERE item_code = " . db_escape($item_code) . "
            AND status = 'Active'
            ORDER BY batch_date DESC";

        $result = db_query($sql);
        $batches = [];

        while ($row = db_fetch_assoc($result)) {
            $batches[] = new self($row);
        }

        return $batches;
    }
}

class WarehouseLocation
{
    public int $id;
    public string $location_code;
    public string $location_name;
    public ?string $parent_code;
    public string $location_type;
    public string $address;
    public string $city;
    public string $state;
    public string $postal_code;
    public string $country;
    public bool $is_active;
    public string $created_at;

    public const TYPE_WAREHOUSE = 'Warehouse';
    public const TYPE_ZONE = 'Zone';
    public const TYPE_AISLE = 'Aisle';
    public const TYPE_RACK = 'Rack';
    public const TYPE_SHELF = 'Shelf';
    public const TYPE_BIN = 'Bin';

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->id = $data['id'] ?? 0;
            $this->location_code = $data['location_code'];
            $this->location_name = $data['location_name'];
            $this->parent_code = $data['parent_code'] ?? null;
            $this->location_type = $data['location_type'] ?? self::TYPE_WAREHOUSE;
            $this->address = $data['address'] ?? '';
            $this->city = $data['city'] ?? '';
            $this->state = $data['state'] ?? '';
            $this->postal_code = $data['postal_code'] ?? '';
            $this->country = $data['country'] ?? '';
            $this->is_active = $data['is_active'] ?? true;
            $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        }
    }

    public function getHierarchy(): array
    {
        $path = [$this];
        $current = $this;

        while ($current->parent_code) {
            $parent = self::find($current->parent_code);
            if ($parent) {
                array_unshift($path, $parent);
                $current = $parent;
            } else {
                break;
            }
        }

        return $path;
    }

    public function getFullPath(): string
    {
        $hierarchy = $this->getHierarchy();
        return implode(' > ', array_map(fn($loc) => $loc->location_name, $hierarchy));
    }

    public function getChildren(): array
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_warehouse_locations 
            WHERE parent_code = " . db_escape($this->location_code) . "
            AND is_active = 1
            ORDER BY location_type, location_name";

        $result = db_query($sql);
        $children = [];

        while ($row = db_fetch_assoc($result)) {
            $children[] = new self($row);
        }

        return $children;
    }

    public static function find(string $location_code): ?self
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_warehouse_locations 
            WHERE location_code = " . db_escape($location_code);

        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return $row ? new self($row) : null;
    }

    public static function findByType(string $type): array
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_warehouse_locations 
            WHERE location_type = " . db_escape($type) . "
            AND is_active = 1
            ORDER BY location_name";

        $result = db_query($sql);
        $locations = [];

        while ($row = db_fetch_assoc($result)) {
            $locations[] = new self($row);
        }

        return $locations;
    }

    public static function getTree(): array
    {
        global $db;
        include_once 'db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "inventory_warehouse_locations 
            WHERE is_active = 1 
            ORDER BY location_type, location_name";

        $result = db_query($sql);
        $locations = [];

        while ($row = db_fetch_assoc($result)) {
            $loc = new self($row);
            $locations[$loc->location_code] = $loc;
        }

        return $locations;
    }
}