<?php

namespace Ksfraser\Reporting;

class ReportingDashboard
{
    public static function renderKPIs(): string
    {
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
            echo '<h4>' . $label . '</h4>';
            echo '<div class="kpi-value">' . number_format($value, 2) . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }

    private static function getTotalRevenue(): float
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT SUM(total) as total FROM " . TB_PREF . "debtor_trans WHERE type = 10";
        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return (float)($row['total'] ?? 0);
    }

    private static function getTotalExpenses(): float
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT SUM(total) as total FROM " . TB_PREF . "supplier_trans WHERE type = 20";
        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return (float)($row['total'] ?? 0);
    }

    private static function getNetProfit(): float
    {
        return self::getTotalRevenue() - self::getTotalExpenses();
    }

    private static function getOutstandingInvoices(): float
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT SUM(due_value) as total FROM " . TB_PREF . "debtor_trans 
            WHERE type = 10 AND due_value > 0";
        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return (float)($row['total'] ?? 0);
    }

    private static function getOpenTickets(): int
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT COUNT(*) as cnt FROM " . TB_PREF . "fa_st_tickets 
            WHERE status != 'Closed'";
        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return (int)($row['cnt'] ?? 0);
    }

    private static function getActiveEmployees(): int
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT COUNT(*) as cnt FROM " . TB_PREF . "ksf_employees WHERE active = 1";
        $result = db_query($sql);
        $row = db_fetch_assoc($result);

        return (int)($row['cnt'] ?? 0);
    }

    public static function renderCharts(): string
    {
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
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT MONTH(trans_date) as month, SUM(total) as revenue 
            FROM " . TB_PREF . "debtor_trans 
            WHERE type = 10 AND trans_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY MONTH(trans_date)
            ORDER BY month";

        $result = db_query($sql);
        
        $data = [];
        while ($row = db_fetch_assoc($result)) {
            $data[] = "['" . date('M', mktime(0, 0, 0, $row['month'])) . "', " . $row['revenue'] . "]";
        }

        return '<script>var data = [' . implode(',', $data) . '];</script>';
    }

    private static function getTopCustomersChart(): string
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT dm.name, SUM(dt.total) as revenue 
            FROM " . TB_PREF . "debtor_trans dt
            JOIN " . TB_PREF . "debtors_master dm ON dt.debtor_no = dm.debtor_no
            WHERE dt.type = 10
            GROUP BY dm.debtor_no
            ORDER BY revenue DESC
            LIMIT 5";

        $result = db_query($sql);
        
        $data = [];
        while ($row = db_fetch_assoc($result)) {
            $data[] = "['" . substr($row['name'], 0, 20) . "', " . $row['revenue'] . "]";
        }

        return '<script>var data = [' . implode(',', $data) . '];</script>';
    }

    public static function renderVendorScorecard(): string
    {
        global $db;
        include_once '../../../includes/db.inc';

        $sql = "SELECT sm.supp_name, vp.on_time_deliveries, vp.late_deliveries, vp.quality_score
            FROM " . TB_PREF . "suppliers_master sm
            LEFT JOIN " . TB_PREF . "vendor_performance vp ON sm.supplier_id = vp.vendor_no
            ORDER BY vp.total_spend DESC
            LIMIT 10";

        $result = db_query($sql);
        
        $html = '<table class="table"><thead><tr><th>Vendor</th><th>On-Time</th><th>Late</th><th>Quality %</th></tr></thead><tbody>';
        
        while ($row = db_fetch_assoc($result)) {
            $onTime = (int)($row['on_time_deliveries'] ?? 0);
            $late = (int)($row['late_deliveries'] ?? 0);
            $total = $onTime + $late;
            $rate = $total > 0 ? round(($onTime / $total) * 100, 1) : 0;
            
            $html .= '<tr>';
            $html .= '<td>' . $row['supp_name'] . '</td>';
            $html .= '<td>' . $onTime . '</td>';
            $html .= '<td>' . $late . '</td>';
            $html .= '<td>' . $rate . '%</td>';
            $html .= '</tr>';
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

        $filename = $type . '_' . date('Ymd') . '.' . $format;

        return (new \Ksfraser\DataIO\ExportService())->toCsv($data, $filename);
    }
}