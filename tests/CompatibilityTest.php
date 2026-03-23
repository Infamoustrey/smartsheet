<?php

use Smartsheet\Resources\Resource;
use Smartsheet\Resources\Row;

class HydratedResource extends Resource
{
    protected string $id;

    public function getId(): string
    {
        return $this->id;
    }
}

class CompatibilityTest extends TestCase
{
    public function test_resource_construction_does_not_emit_dynamic_property_deprecations(): void
    {
        $deprecations = [];

        set_error_handler(function (int $severity, string $message) use (&$deprecations): bool {
            if ($severity === E_DEPRECATED) {
                $deprecations[] = $message;

                return true;
            }

            return false;
        });

        try {
            $resource = new Resource(['foo' => 'bar']);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations);
        $this->assertSame('bar', $resource->get('foo'));
    }

    public function test_declared_properties_are_still_hydrated_from_payload(): void
    {
        $resource = new HydratedResource(['id' => 'sheet-123']);

        $this->assertSame('sheet-123', $resource->getId());
        $this->assertSame('sheet-123', $resource->get('id'));
    }

    public function test_loading_row_class_does_not_emit_implicit_nullable_deprecations(): void
    {
        $deprecations = [];

        set_error_handler(function (int $severity, string $message) use (&$deprecations): bool {
            if ($severity === E_DEPRECATED) {
                $deprecations[] = $message;

                return true;
            }

            return false;
        });

        try {
            $reflection = new ReflectionMethod(Row::class, '__construct');
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations);

        $parameter = $reflection->getParameters()[2];

        $this->assertTrue($parameter->allowsNull());
        $this->assertTrue($parameter->isDefaultValueAvailable());
        $this->assertNull($parameter->getDefaultValue());
    }
}
