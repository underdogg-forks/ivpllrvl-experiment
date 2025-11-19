<?php

namespace Modules\Products\Tests\Unit;

use Modules\Products\Services\UnitService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractServiceTestCase;

#[CoversClass(UnitService::class)]
class UnitServiceTest extends AbstractServiceTestCase
{
    private UnitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UnitService();
    }

    #[Group('crud')]
    #[Test]
    public function it_returns_validation_rules(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('unit_name', $rules);
        $this->assertArrayHasKey('unit_name_plrl', $rules);
    }

    #[Group('smoke')]
    #[Test]
    public function it_returns_empty_string_when_unit_id_is_null(): void
    {
        $result = $this->service->getUnitName(null, 1);
        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_gets_unit_name(): void
    {
        /* Arrange */
        $this->cleanupTables(['ip_units']);

        $unit = \Modules\Products\Models\Unit::create([
            'unit_name'      => 'Hour',
            'unit_name_plrl' => 'Hours',
        ]);

        /** Act */
        $singularName = $this->service->getUnitName($unit->unit_id, 1);
        $pluralName   = $this->service->getUnitName($unit->unit_id, 2);

        /* Assert */
        $this->assertEquals('Hour', $singularName);
        $this->assertEquals('Hours', $pluralName);
    }
}
