<?php

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\Reporting;

use Ksfraser\Reporting\ReportingDashboard;
use PHPUnit\Framework\TestCase;

class ReportingDashboardTest extends TestCase
{
    public function testRenderKPIsReturnsString(): void
    {
        $kpis = ReportingDashboard::renderKPIs();

        $this->assertIsString($kpis);
        $this->assertStringContainsString('dashboard-kpis', $kpis);
    }

    public function testRenderKPIsContainsExpectedLabels(): void
    {
        $kpis = ReportingDashboard::renderKPIs();

        $this->assertStringContainsString('Total Revenue', $kpis);
        $this->assertStringContainsString('Total Expenses', $kpis);
        $this->assertStringContainsString('Net Profit', $kpis);
        $this->assertStringContainsString('Outstanding Invoices', $kpis);
        $this->assertStringContainsString('Open Tickets', $kpis);
        $this->assertStringContainsString('Active Employees', $kpis);
    }

    public function testRenderKPIsContainsKpiValueClass(): void
    {
        $kpis = ReportingDashboard::renderKPIs();

        $this->assertStringContainsString('kpi-value', $kpis);
    }

    public function testRenderChartsReturnsString(): void
    {
        $charts = ReportingDashboard::renderCharts();

        $this->assertIsString($charts);
        $this->assertStringContainsString('dashboard-charts', $charts);
    }

    public function testRenderChartsContainsChartContainers(): void
    {
        $charts = ReportingDashboard::renderCharts();

        $this->assertStringContainsString('Revenue by Month', $charts);
        $this->assertStringContainsString('Top Customers', $charts);
    }

    public function testRenderVendorScorecardReturnsString(): void
    {
        $scorecard = ReportingDashboard::renderVendorScorecard();

        $this->assertIsString($scorecard);
        $this->assertStringContainsString('<table', $scorecard);
    }

    public function testRenderVendorScorecardContainsExpectedHeaders(): void
    {
        $scorecard = ReportingDashboard::renderVendorScorecard();

        $this->assertStringContainsString('Vendor', $scorecard);
        $this->assertStringContainsString('On-Time', $scorecard);
        $this->assertStringContainsString('Late', $scorecard);
        $this->assertStringContainsString('Quality %', $scorecard);
    }

    public function testExportReportReturnsBool(): void
    {
        $result = ReportingDashboard::exportReport('invalid_type', 'csv');
        $this->assertFalse($result);
    }

    public function testExportReportReturnsFalseForInvalidType(): void
    {
        $result = ReportingDashboard::exportReport('nonexistent', 'csv');
        $this->assertFalse($result);
    }

    public function testExportReportReturnsFalseForMissingType(): void
    {
        $result = ReportingDashboard::exportReport('', 'csv');
        $this->assertFalse($result);
    }

    public function testRenderKPIsContainsNumericValues(): void
    {
        $kpis = ReportingDashboard::renderKPIs();

        preg_match_all('/kpi-value[^>]*>([\d,]+\.?\d*)/', $kpis, $matches);
        $this->assertGreaterThanOrEqual(6, count($matches[0]));
    }

    public function testRenderChartsContainsChartRowClass(): void
    {
        $charts = ReportingDashboard::renderCharts();

        $this->assertStringContainsString('chart-row', $charts);
        $this->assertStringContainsString('chart', $charts);
    }

    public function testRenderVendorScorecardContainsTheadAndTbody(): void
    {
        $scorecard = ReportingDashboard::renderVendorScorecard();

        $this->assertStringContainsString('<thead>', $scorecard);
        $this->assertStringContainsString('<tbody>', $scorecard);
    }

    public function testExportReportWithInvalidFormat(): void
    {
        $result = ReportingDashboard::exportReport('revenue', 'invalid');
        $this->assertFalse($result);
    }

    public function testExportReportWithCustomerType(): void
    {
        $result = ReportingDashboard::exportReport('customers', 'csv');
        $this->assertFalse($result);
    }

    public function testExportReportWithVendorType(): void
    {
        $result = ReportingDashboard::exportReport('vendors', 'csv');
        $this->assertFalse($result);
    }

    public function testRenderKPIsHtmlStructure(): void
    {
        $kpis = ReportingDashboard::renderKPIs();

        $this->assertStringContainsString('<div', $kpis);
        $this->assertStringContainsString('<h4>', $kpis);
    }

    public function testRenderChartsContainsChartData(): void
    {
        $charts = ReportingDashboard::renderCharts();

        $this->assertStringContainsString('var data =', $charts);
    }

    public function testRenderVendorScorecardWithNoDb(): void
    {
        $scorecard = ReportingDashboard::renderVendorScorecard();

        $this->assertStringContainsString('<thead>', $scorecard);
        $this->assertStringContainsString('<tbody>', $scorecard);
        $this->assertStringContainsString('</tbody>', $scorecard);
    }
}