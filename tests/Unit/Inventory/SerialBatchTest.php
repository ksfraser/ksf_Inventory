<?php

declare(strict_types=1);

namespace Ksfraser\Tests\Unit\Inventory;

use DateTime;
use Ksfraser\Inventory\SerialBatch;
use PHPUnit\Framework\TestCase;

class SerialBatchTest extends TestCase
{
    private function createSerialNumber(array $data): SerialNumber
    {
        $class = new \ReflectionClass(SerialNumber::class);
        $serial = $class->newInstanceWithoutConstructor();
        
        foreach ($data as $key => $value) {
            if ($class->hasProperty($key)) {
                $prop = $class->getProperty($key);
                $prop->setAccessible(true);
                $prop->setValue($serial, $value);
            }
        }
        
        return $serial;
    }
    
    private function createBatchNumber(array $data): BatchNumber
    {
        $class = new \ReflectionClass(BatchNumber::class);
        $batch = $class->newInstanceWithoutConstructor();
        
        foreach ($data as $key => $value) {
            if ($class->hasProperty($key)) {
                $prop = $class->getProperty($key);
                $prop->setAccessible(true);
                $prop->setValue($batch, $value);
            }
        }
        
        return $batch;
    }
    
    private function createWarehouseLocation(array $data): WarehouseLocation
    {
        $class = new \ReflectionClass(WarehouseLocation::class);
        $location = $class->newInstanceWithoutConstructor();
        
        foreach ($data as $key => $value) {
            if ($class->hasProperty($key)) {
                $prop = $class->getProperty($key);
                $prop->setAccessible(true);
                $prop->setValue($location, $value);
            }
        }
        
        return $location;
    }

    public function testSerialNumberConstructionWithData(): void
    {
        $data = [
            'id' => 1,
            'serial_no' => 'SN-001',
            'item_code' => 'ITEM-001',
            'serialized_date' => '2024-01-01',
            'purchase_date' => '2024-01-15',
            'supplier_ref' => 'SUP-001',
            'purchase_cost' => '99.99',
            'location_code' => 'WH-01',
            'status' => 'Available',
            'warranty_end_days' => 365,
            'notes' => 'Test note',
        ];

        $serial = new SerialNumber($data);

        $this->assertEquals(1, $serial->id);
        $this->assertEquals('SN-001', $serial->serial_no);
        $this->assertEquals('ITEM-001', $serial->item_code);
        $this->assertEquals('2024-01-01', $serial->serialized_date);
        $this->assertEquals('2024-01-15', $serial->purchase_date);
        $this->assertEquals('SUP-001', $serial->supplier_ref);
        $this->assertEquals('99.99', $serial->purchase_cost);
        $this->assertEquals('WH-01', $serial->location_code);
        $this->assertEquals('Available', $serial->status);
        $this->assertEquals(365, $serial->warranty_end_days);
        $this->assertEquals('Test note', $serial->notes);
    }

    public function testSerialNumberConstructionWithDefaults(): void
    {
        $data = [
            'serial_no' => 'SN-002',
            'item_code' => 'ITEM-002',
        ];

        $serial = new SerialNumber($data);

        $this->assertEquals(0, $serial->id);
        $this->assertEquals('SN-002', $serial->serial_no);
        $this->assertEquals('ITEM-002', $serial->item_code);
        $this->assertEquals(SerialNumber::STATUS_AVAILABLE, $serial->status);
        $this->assertEquals('', $serial->notes);
        $this->assertNull($serial->purchase_date);
        $this->assertNull($serial->supplier_ref);
    }

    public function testSerialNumberEmptyConstruction(): void
    {
        $serial = new SerialNumber([]);

        $this->assertEquals(0, $serial->id);
        $this->assertEquals('', $serial->serial_no);
    }

    public function testSerialNumberStatusConstants(): void
    {
        $this->assertEquals('Available', SerialNumber::STATUS_AVAILABLE);
        $this->assertEquals('Sold', SerialNumber::STATUS_SOLD);
        $this->assertEquals('Reserved', SerialNumber::STATUS_RESERVED);
        $this->assertEquals('In Repair', SerialNumber::STATUS_IN_REPAIR);
        $this->assertEquals('Scrapped', SerialNumber::STATUS_SCRAPPED);
    }

    public function testSerialNumberIsUnderWarrantyWithValidWarranty(): void
    {
        $data = [
            'serial_no' => 'SN-003',
            'item_code' => 'ITEM-003',
            'purchase_date' => date('Y-m-d'),
            'warranty_end_days' => 30,
        ];

        $serial = new SerialNumber($data);

        $this->assertTrue($serial->isUnderWarranty());
    }

    public function testSerialNumberIsUnderWarrantyWithExpiredWarranty(): void
    {
        $data = [
            'serial_no' => 'SN-004',
            'item_code' => 'ITEM-004',
            'purchase_date' => date('Y-m-d', strtotime('-60 days')),
            'warranty_end_days' => 30,
        ];

        $serial = new SerialNumber($data);

        $this->assertFalse($serial->isUnderWarranty());
    }

    public function testSerialNumberIsUnderWarrantyWithNoWarrantyDays(): void
    {
        $data = [
            'serial_no' => 'SN-005',
            'item_code' => 'ITEM-005',
            'purchase_date' => date('Y-m-d'),
            'warranty_end_days' => null,
        ];

        $serial = new SerialNumber($data);

        $this->assertFalse($serial->isUnderWarranty());
    }

    public function testBatchNumberConstructionWithData(): void
    {
        $data = [
            'id' => 1,
            'batch_no' => 'BATCH-001',
            'item_code' => 'ITEM-001',
            'quantity' => 100,
            'batch_date' => '2024-01-01',
            'expiry_date' => '2025-01-01',
            'supplier_ref' => 'SUP-001',
            'status' => 'Active',
            'notes' => 'Batch note',
        ];

        $batch = new BatchNumber($data);

        $this->assertEquals(1, $batch->id);
        $this->assertEquals('BATCH-001', $batch->batch_no);
        $this->assertEquals('ITEM-001', $batch->item_code);
        $this->assertEquals(100, $batch->quantity);
        $this->assertEquals('2024-01-01', $batch->batch_date);
        $this->assertEquals('2025-01-01', $batch->expiry_date);
        $this->assertEquals('SUP-001', $batch->supplier_ref);
        $this->assertEquals('Active', $batch->status);
        $this->assertEquals('Batch note', $batch->notes);
    }

    public function testBatchNumberConstructionWithDefaults(): void
    {
        $data = [
            'batch_no' => 'BATCH-002',
            'item_code' => 'ITEM-002',
        ];

        $batch = new BatchNumber($data);

        $this->assertEquals(0, $batch->id);
        $this->assertEquals('BATCH-002', $batch->batch_no);
        $this->assertEquals('ITEM-002', $batch->item_code);
        $this->assertEquals(0, $batch->quantity);
        $this->assertEquals(BatchNumber::STATUS_ACTIVE, $batch->status);
    }

    public function testBatchNumberIsExpiredWithFutureDate(): void
    {
        $data = [
            'batch_no' => 'BATCH-003',
            'item_code' => 'ITEM-003',
            'expiry_date' => date('Y-m-d', strtotime('+30 days')),
        ];

        $batch = new BatchNumber($data);

        $this->assertFalse($batch->isExpired());
    }

    public function testBatchNumberIsExpiredWithPastDate(): void
    {
        $data = [
            'batch_no' => 'BATCH-004',
            'item_code' => 'ITEM-004',
            'expiry_date' => date('Y-m-d', strtotime('-1 day')),
        ];

        $batch = new BatchNumber($data);

        $this->assertTrue($batch->isExpired());
    }

    public function testBatchNumberIsExpiredWithNoExpiryDate(): void
    {
        $data = [
            'batch_no' => 'BATCH-005',
            'item_code' => 'ITEM-005',
            'expiry_date' => null,
        ];

        $batch = new BatchNumber($data);

        $this->assertFalse($batch->isExpired());
    }

    public function testBatchNumberIsExhaustedWithZeroQuantity(): void
    {
        $data = [
            'batch_no' => 'BATCH-006',
            'item_code' => 'ITEM-006',
            'quantity' => 0,
        ];

        $batch = new BatchNumber($data);

        $this->assertTrue($batch->isExhausted());
    }

    public function testBatchNumberIsExhaustedWithNegativeQuantity(): void
    {
        $data = [
            'batch_no' => 'BATCH-007',
            'item_code' => 'ITEM-007',
            'quantity' => -5,
        ];

        $batch = new BatchNumber($data);

        $this->assertTrue($batch->isExhausted());
    }

    public function testBatchNumberIsNotExhaustedWithPositiveQuantity(): void
    {
        $data = [
            'batch_no' => 'BATCH-008',
            'item_code' => 'ITEM-008',
            'quantity' => 50,
        ];

        $batch = new BatchNumber($data);

        $this->assertFalse($batch->isExhausted());
    }

    public function testBatchNumberStatusConstants(): void
    {
        $this->assertEquals('Active', BatchNumber::STATUS_ACTIVE);
        $this->assertEquals('Closed', BatchNumber::STATUS_CLOSED);
        $this->assertEquals('Partial', BatchNumber::STATUS_PARTIAL);
    }

    public function testWarehouseLocationConstructionWithData(): void
    {
        $data = [
            'id' => 1,
            'location_code' => 'WH-01-A-01',
            'location_name' => 'Warehouse 1 - Aisle A - Rack 01',
            'parent_code' => 'WH-01-A',
            'location_type' => 'Rack',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'is_active' => true,
        ];

        $location = new WarehouseLocation($data);

        $this->assertEquals(1, $location->id);
        $this->assertEquals('WH-01-A-01', $location->location_code);
        $this->assertEquals('Warehouse 1 - Aisle A - Rack 01', $location->location_name);
        $this->assertEquals('WH-01-A', $location->parent_code);
        $this->assertEquals('Rack', $location->location_type);
        $this->assertEquals('123 Main St', $location->address);
        $this->assertEquals('New York', $location->city);
        $this->assertEquals('NY', $location->state);
        $this->assertEquals('10001', $location->postal_code);
        $this->assertEquals('USA', $location->country);
        $this->assertTrue($location->is_active);
    }

    public function testWarehouseLocationConstructionWithDefaults(): void
    {
        $data = [
            'location_code' => 'WH-02',
            'location_name' => 'Warehouse 2',
        ];

        $location = new WarehouseLocation($data);

        $this->assertEquals(0, $location->id);
        $this->assertEquals('WH-02', $location->location_code);
        $this->assertEquals('Warehouse 2', $location->location_name);
        $this->assertNull($location->parent_code);
        $this->assertEquals(WarehouseLocation::TYPE_WAREHOUSE, $location->location_type);
        $this->assertEquals('', $location->address);
        $this->assertEquals('', $location->city);
        $this->assertTrue($location->is_active);
    }

    public function testWarehouseLocationTypeConstants(): void
    {
        $this->assertEquals('Warehouse', WarehouseLocation::TYPE_WAREHOUSE);
        $this->assertEquals('Zone', WarehouseLocation::TYPE_ZONE);
        $this->assertEquals('Aisle', WarehouseLocation::TYPE_AISLE);
        $this->assertEquals('Rack', WarehouseLocation::TYPE_RACK);
        $this->assertEquals('Shelf', WarehouseLocation::TYPE_SHELF);
        $this->assertEquals('Bin', WarehouseLocation::TYPE_BIN);
    }

    public function testWarehouseLocationGetFullPathReturnsString(): void
    {
        $location = new WarehouseLocation([
            'location_code' => 'WH',
            'location_name' => 'Warehouse',
        ]);

        $path = $location->getFullPath();

        $this->assertIsString($path);
    }
}