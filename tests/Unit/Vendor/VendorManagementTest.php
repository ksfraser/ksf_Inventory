<?php

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\Vendor;

use Ksfraser\Vendor\VendorManagement;
use PHPUnit\Framework\TestCase;

class VendorManagementTest extends TestCase
{
    public function testVendorManagementConstructionWithData(): void
    {
        $data = [
            'id' => 1,
            'vendor_no' => 'V-001',
            'name' => 'Acme Corp',
            'contact' => 'John Doe',
            'email' => 'john@acme.com',
            'phone' => '555-1234',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'tax_id' => '12-3456789',
            'payment_terms' => 'Net 30',
            'credit_limit' => '50000',
            'currency' => 'USD',
            'rating' => 4.5,
            'category' => 'Electronics',
            'approved' => true,
            'notes' => 'Preferred supplier',
        ];

        $vendor = new VendorManagement($data);

        $this->assertEquals(1, $vendor->id);
        $this->assertEquals('V-001', $vendor->vendor_no);
        $this->assertEquals('Acme Corp', $vendor->name);
        $this->assertEquals('John Doe', $vendor->contact);
        $this->assertEquals('john@acme.com', $vendor->email);
        $this->assertEquals('555-1234', $vendor->phone);
        $this->assertEquals('123 Main St', $vendor->address);
        $this->assertEquals('New York', $vendor->city);
        $this->assertEquals('NY', $vendor->state);
        $this->assertEquals('10001', $vendor->postal_code);
        $this->assertEquals('USA', $vendor->country);
        $this->assertEquals('12-3456789', $vendor->tax_id);
        $this->assertEquals('Net 30', $vendor->payment_terms);
        $this->assertEquals('50000', $vendor->credit_limit);
        $this->assertEquals('USD', $vendor->currency);
        $this->assertEquals(4.5, $vendor->rating);
        $this->assertEquals('Electronics', $vendor->category);
        $this->assertTrue($vendor->approved);
        $this->assertEquals('Preferred supplier', $vendor->notes);
    }

    public function testVendorManagementConstructionWithDefaults(): void
    {
        $data = [
            'vendor_no' => 'V-002',
            'name' => 'Basic Vendor',
        ];

        $vendor = new VendorManagement($data);

        $this->assertEquals(0, $vendor->id);
        $this->assertEquals('V-002', $vendor->vendor_no);
        $this->assertEquals('Basic Vendor', $vendor->name);
        $this->assertEquals('', $vendor->contact);
        $this->assertEquals('', $vendor->email);
        $this->assertEquals('', $vendor->phone);
        $this->assertEquals('', $vendor->address);
        $this->assertEquals('Net 30', $vendor->payment_terms);
        $this->assertEquals('0', $vendor->credit_limit);
        $this->assertEquals('USD', $vendor->currency);
        $this->assertEquals(0.0, $vendor->rating);
        $this->assertEquals('', $vendor->category);
        $this->assertTrue($vendor->approved);
        $this->assertEquals('', $vendor->notes);
    }

    public function testVendorManagementEmptyConstruction(): void
    {
        $vendor = new VendorManagement([]);

        $this->assertEquals(0, $vendor->id);
        $this->assertEquals('', $vendor->vendor_no);
    }

    public function testVendorManagementRatingIsNumeric(): void
    {
        $data = [
            'vendor_no' => 'V-003',
            'name' => 'Rating Test',
            'rating' => '4.2',
        ];

        $vendor = new VendorManagement($data);

        $this->assertIsFloat($vendor->rating);
        $this->assertEquals(4.2, $vendor->rating);
    }

    public function testVendorManagementRatingNonNumericConvertsToZero(): void
    {
        $data = [
            'vendor_no' => 'V-004',
            'name' => 'Invalid Rating',
            'rating' => 'not-a-number',
        ];

        $vendor = new VendorManagement($data);

        $this->assertEquals(0.0, $vendor->rating);
    }

    public function testVendorManagementApprovedDefaultWhenNotSet(): void
    {
        $data = [
            'vendor_no' => 'V-005',
            'name' => 'Default Approved',
        ];

        $vendor = new VendorManagement($data);

        $this->assertTrue($vendor->approved);
    }

    public function testVendorManagementApprovedWhenExplicitlySet(): void
    {
        $data = [
            'vendor_no' => 'V-006',
            'name' => 'Not Approved',
            'approved' => false,
        ];

        $vendor = new VendorManagement($data);

        $this->assertFalse($vendor->approved);
    }

    public function testVendorManagementApprovedWhenZero(): void
    {
        $data = [
            'vendor_no' => 'V-007',
            'name' => 'Zero Approved',
            'approved' => 0,
        ];

        $vendor = new VendorManagement($data);

        $this->assertFalse($vendor->approved);
    }

    public function testVendorManagementGetPurchaseHistoryReturnsArray(): void
    {
        $vendor = new VendorManagement([
            'vendor_no' => 'V-008',
            'name' => 'Test Vendor',
        ]);

        $history = $vendor->getPurchaseHistory();

        $this->assertIsArray($history);
    }

    public function testVendorManagementGetTotalSpendReturnsFloat(): void
    {
        $vendor = new VendorManagement([
            'vendor_no' => 'V-009',
            'name' => 'Test Vendor',
        ]);

        $totalSpend = $vendor->getTotalSpend();

        $this->assertIsFloat($totalSpend);
        $this->assertGreaterThanOrEqual(0, $totalSpend);
    }

    public function testVendorManagementSearchReturnsArray(): void
    {
        $results = VendorManagement::search('test');

        $this->assertIsArray($results);
    }

    public function testVendorManagementGetTopVendorsReturnsArray(): void
    {
        $results = VendorManagement::getTopVendors(5);

        $this->assertIsArray($results);
    }

    public function testVendorManagementGetByCategoryReturnsArray(): void
    {
        $results = VendorManagement::getByCategory('Electronics');

        $this->assertIsArray($results);
    }
}
