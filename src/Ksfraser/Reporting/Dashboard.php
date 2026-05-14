<?php

namespace Ksfraser\Reporting;

class ReportingDashboard
{
    private static bool $initialized = false;
    private static bool $faAvailable = false;

    private static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        self::$faAvailable = function_exists('db_query') && defined('TB_PREF');
        
        if (self::$faAvailable) {
            @include_once '../../../includes/db.inc';
            self::$faAvailable = function_exists('db_query');
        }
    }

    public static function renderKPIs(): string
    {
        self::initialize();
        ob_start();
        
        $kpis = [
            'total_revenue' => self::getTotalRevenue(),
            'total_expenses' => self::getTotalExpenses(),
            'net_profit' => self::getNetProfit(),
            'outstanding_invoices' => self::getOutstandingInvoices(),
            'open_tickets' => self::getOpenTickets(),
            'active_employees' => self::getActiveEmployees(),
        ];
        
        echo '<div class="dashboard-kpis">';
        
        foreach ($kpis as $key => $value) {
            $label = str_replace('_', ' ', ucwords($key, '_'));
            echo '<div class="kpi-card">';
            echo '<h4>' . htmlspecialchars($label) . '</h4>';
            echo '<div class="kpi-value">' . number_format($value, 2) . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }

    private static function getTotalRevenue(): float
    {
        self::initialize();
        if (!self::$faAvailable) {
            return 0.0;
        }

        $sql = "SELECT SUM(total) as total FROM " . TB_PREF . "debtor_trans WHERE type = 10";
        $result = @db_query($sql);
        if (!$result) {
            return 0.0;
        }
        $row = db_fetch_assoc($result);
        return (float)($row['total'] ?? 0);
    }

    private static function getTotalExpenses(): float
    {
        self::initialize();
        if (!self::$faAvailable) {
            return 0.0;
        }

        $sql = "SELECT SUM(total) as total FROM " . TB_PREF . "supplier_trans WHERE type = 20";
        $result = @db_query($sql);
        if (!$result) {
            return 0.0;
        }
        $row = db_fetch_assoc($result);
        return (float)($row['total'] ?? 0);
    }

    private static function getNetProfit(): float
    {
        return self::getTotalRevenue() - self::getTotalExpenses();
    }

    private static function getOutstandingInvoices(): float
    {
        self::initialize();
        if (!self::$faAvailable) {
            return 0.0;
        }

        $sql = "SELECT SUM(due_value) as total FROM " . TB_PREF . "debtor_trans 
            WHERE type = 10 AND due_value > 0";
        $result = @db_query($sql);
        if (!$result) {
            return 0.0;
        }
        $row = db_fetch_assoc($result);
        return (float)($row['total'] ?? 0);
    }

    private static function getOpenTickets(): int
    {
        self::initialize();
        if (!self::$faAvailable) {
            return 0;
        }

        $sql = "SELECT COUNT(*) as cnt FROM " . TB_PREF . "fa_st_tickets 
            WHERE status != 'Closed'";
        $result = @db_query($sql);
        if (!$result) {
            return 0;
        }
        $row = db_fetch_assoc($result);
        return (int)($row['cnt'] ?? 0);
    }

    private static function getActiveEmployees(): int
    {
        self::initialize();
        if (!self::$faAvailable) {
            return 0;
        }

        $sql = "SELECT COUNT(*) as cnt FROM " . TB_PREF . "ksf_employees WHERE active = 1";
        $result = @db_query($sql);
        if (!$result) {
            return 0;
        }
        $row = db_fetch_assoc($result);
        return (int)($row['cnt'] ?? 0);
    }

    public static function renderCharts(): string
    {
        self::initialize();
        ob_start();
        
        echo '<div class="dashboard-charts">';
        echo '<h3>Charts</h3>';
        
        echo '<div class="chart-row">';
        echo '<div class="chart">';
        echo '<h4>Revenue by Month</h4>';
        echo self::getRevenueByMonthChart();
        echo '</div>';
        
        echo '<div class="chart">';
        echo '<h4>Top Customers</h4>';
        echo self::getTopCustomersChart();
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        return ob_get_clean();
    }

    private static function getRevenueByMonthChart(): string
    {
        self::initialize();
        if (!self::$faAvailable) {
            return '<script>var data = [];</script>';
        }

        $sql = "SELECT MONTH(trans_date) as month, SUM(total) as revenue 
            FROM " . TB_PREF . "debtor_trans 
            WHERE type = 10 AND trans_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY MONTH(trans_date)
            ORDER BY month";

        $result = @db_query($sql);
        if (!$result) {
            return '<script>var data = [];</script>';
        }
        
        $data = [];
        while ($row = db_fetch_assoc($result)) {
            $monthName = date('M', mktime(0, 0, 0, (int)$row['month'], 1));
            $data[] = "['" . $monthName . "', " . ((float)$row['revenue']) . "]";
        }

        return '<script>var data = [' . implode(',', $data) . '];</script>';
    }

    private static function getTopCustomersChart(): string
    {
        self::initialize();
        if (!self::$faAvailable) {
            return '<script>var data = [];</script>';
        }

        $sql = "SELECT dm.name, SUM(dt.total) as revenue 
            FROM " . TB_PREF . "debtor_trans dt
            JOIN " . TB_PREF . "debtors_master dm ON dt.debtor_no = dm.debtor_no
            WHERE dt.type = 10
            GROUP BY dm.debtor_no
            ORDER BY revenue DESC
            LIMIT 5";

        $result = @db_query($sql);
        if (!$result) {
            return '<script>var data = [];</script>';
        }
        
        $data = [];
        while ($row = db_fetch_assoc($result)) {
            $name = htmlspecialchars(substr($row['name'], 0, 20));
            $data[] = "['" . $name . "', " . ((float)$row['revenue']) . "]";
        }

        return '<script>var data = [' . implode(',', $data) . '];</script>';
    }

    public static function renderVendorScorecard(): string
    {
        self::initialize();
        if (!self::$faAvailable) {
            return '<table class="table"><thead><tr><th>Vendor</th><th>On-Time</th><th>Late</th><th>Quality %</th></tr></thead><tbody><tr><td colspan="4">No vendor data available</td></tr></tbody></table>';
        }

        $sql = "SELECT sm.supp_name, vp.on_time_deliveries, vp.late_deliveries, vp.quality_score
            FROM " . TB_PREF . "suppliers_master sm
            LEFT JOIN " . TB_PREF . "vendor_performance vp ON sm.supplier_id = vp.vendor_no
            ORDER BY vp.total_spend DESC
            LIMIT 10";

        $result = @db_query($sql);
        
        $html = '<table class="table"><thead><tr><th>Vendor</th><th>On-Time</th><th>Late</th><th>Quality %</th></tr></thead><tbody>';
        
        if ($result) {
            while ($row = db_fetch_assoc($result)) {
                $onTime = (int)($row['on_time_deliveries'] ?? 0);
                $late = (int)($row['late_deliveries'] ?? 0);
                $total = $onTime + $late;
                $rate = $total > 0 ? round(($onTime / $total) * 100, 1) : 0;
                
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['supp_name'] ?? 'Unknown') . '</td>';
                $html .= '<td>' . $onTime . '</td>';
                $html .= '<td>' . $late . '</td>';
                $html .= '<td>' . $rate . '%</td>';
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }

    public static function exportReport(string $type, string $format): bool
    {
        $data = match($type) {
            'revenue' => self::getRevenueByMonthChart(),
            'customers' => self::getTopCustomersChart(),
            'vendors' => self::renderVendorScorecard(),
            default => null,
        };

        if (!$data) {
            return false;
        }

        if (!class_exists('\Ksfraser\DataIO\ExportService')) {
            return false;
        }

        $filename = $type . '_' . date('Ymd') . '.' . $format;
        return (new \Ksfraser\DataIO\ExportService())->toCsv($data, $filename);
    }
}