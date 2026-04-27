<?php

namespace Ksfraser\Vendor;

class VendorManagement
{
    public int $id;
    public string $vendor_no;
    public string $name;
    public string $contact;
    public string $email;
    public string $phone;
    public string $address;
    public string $city;
    public string $state;
    public string $postal_code;
    public string $country;
    public string $tax_id;
    public string $payment_terms;
    public string $credit_limit;
    public string $currency;
    public float $rating;
    public string $category;
    public bool $approved;
    public string $notes;
    public string $created_at;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->id = $data['id'] ?? 0;
            $this->vendor_no = $data['vendor_no'];
            $this->name = $data['name'];
            $this->contact = $data['contact'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->phone = $data['phone'] ?? '';
            $this->address = $data['address'] ?? '';
            $this->city = $data['city'] ?? '';
            $this->state = $data['state'] ?? '';
            $this->postal_code = $data['postal_code'] ?? '';
            $this->country = $data['country'] ?? '';
            $this->tax_id = $data['tax_id'] ?? '';
            $this->payment_terms = $data['payment_terms'] ?? 'Net 30';
            $this->credit_limit = $data['credit_limit'] ?? '0';
            $this->currency = $data['currency'] ?? 'USD';
            $this->rating = is_numeric($data['rating'] ?? 0) ? (float)$data['rating'] : 0;
            $this->category = $data['category'] ?? '';
            $this->approved = !isset($data['approved']) || $data['approved'] == 1;
            $this->notes = $data['notes'] ?? '';
            $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        }
    }

    public function getPurchaseHistory(): array
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT po.*, po.order_date as date 
            FROM " . TB_PREF . "purchase_orders po
            WHERE po.vendor = " . db_escape($this->vendor_no) . "
            ORDER BY po.order_date DESC
            LIMIT 20";

        $result = db_query($sql);
        $orders = [];

        while ($row = db_fetch_assoc($result)) {
            $orders[] = $row;
        }

        return $orders;
    }

    public function getTotalSpend(): float
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT SUM(total) as total 
            FROM " . TB_PREF . "purchase_orders 
            WHERE vendor = " . db_escape($this->vendor_no) . "
            AND status = 'Completed'";

        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return (float)($row['total'] ?? 0);
    }

    public static function find(string $vendor_no): ?self
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "suppliers_master 
            WHERE supplier_id = " . db_escape($vendor_no);

        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return $row ? new self($row) : null;
    }

    public static function search(string $query, int $limit = 20): array
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "suppliers_master 
            WHERE supplier_id LIKE " . db_escape('%' . $query . '%') . "
            OR supp_name LIKE " . db_escape('%' . $query . '%') . "
            OR email LIKE " . db_escape('%' . $query . '%') . "
            ORDER BY supp_name
            LIMIT " . db_escape($limit);

        $result = db_query($sql);
        $vendors = [];

        while ($row = db_fetch_assoc($result)) {
            $vendors[] = new self($row);
        }

        return $vendors;
    }

    public static function getTopVendors(int $limit = 10): array
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT sm.*, SUM(po.total) as total_spend
            FROM " . TB_PREF . "suppliers_master sm
            LEFT JOIN " . TB_PREF . "purchase_orders po ON sm.supplier_id = po.vendor
            GROUP BY sm.supplier_id
            ORDER BY total_spend DESC
            LIMIT " . db_escape($limit);

        $result = db_query($sql);
        $vendors = [];

        while ($row = db_fetch_assoc($result)) {
            $vendors[] = new self($row);
        }

        return $vendors;
    }

    public static function getByCategory(string $category): array
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT * FROM " . TB_PREF . "suppliers_master 
            WHERE category = " . db_escape($category) . "
            ORDER BY supp_name";

        $result = db_query($sql);
        $vendors = [];

        while ($row = db_fetch_assoc($result)) {
            $vendors[] = new self($row);
        }

        return $vendors;
    }
}

class VendorPerformance
{
    public string $vendor_no;
    public int $order_count;
    public float $total_spend;
    public float $avg_order_value;
    public int $on_time_deliveries;
    public int $late_deliveries;
    public int $damage_claims;
    public float $quality_score;

    public function __construct(string $vendor_no)
    {
        $this->vendor_no = $vendor_no;
        $this->calculateMetrics();
    }

    private function calculateMetrics()
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT 
            COUNT(*) as order_count,
            SUM(total) as total_spend,
            AVG(total) as avg_order_value,
            SUM(CASE WHEN delivery_date <= required_date THEN 1 ELSE 0 END) as on_time,
            SUM(CASE WHEN delivery_date > required_date THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN quality_issue = 'Yes' THEN 1 ELSE 0 END) as damage
            FROM " . TB_PREF . "purchase_orders 
            WHERE vendor = " . db_escape($this->vendor_no);

        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        $this->order_count = (int)($row['order_count'] ?? 0);
        $this->total_spend = (float)($row['total_spend'] ?? 0);
        $this->avg_order_value = (float)($row['avg_order_value'] ?? 0);
        $this->on_time_deliveries = (int)($row['on_time'] ?? 0);
        $this->late_deliveries = (int)($row['late'] ?? 0);
        $this->damage_claims = (int)($row['damage'] ?? 0);

        $total = $this->on_time_deliveries + $this->late_deliveries;
        $this->quality_score = $total > 0 ? ($this->on_time_deliveries / $total) * 100 : 0;
    }

    public function getOnTimeRate(): float
    {
        $total = $this->on_time_deliveries + $this->late_deliveries;
        return $total > 0 ? round(($this->on_time_deliveries / $total) * 100, 1) : 0;
    }
}