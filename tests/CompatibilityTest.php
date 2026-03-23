<?php
 
use Smartsheet\Resources\Resource;
use Smartsheet\Resources\Row;

class CompatibilityTest extends TestCase
{
    public function testResourceConstructionDoesNotEmitDynamicPropertyDeprecations(): void
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

    public function testDeclaredPropertiesAreStillHydratedFromPayload(): void
    {
        $resource = new class (['id' => 'sheet-123']) extends Resource {
            protected string $id;

            public function getId(): string
            {
                return $this->id;
            }
        };

        $this->assertSame('sheet-123', $resource->getId());
        $this->assertSame('sheet-123', $resource->get('id'));
    }

    public function testLoadingRowClassDoesNotEmitImplicitNullableDeprecations(): void
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
