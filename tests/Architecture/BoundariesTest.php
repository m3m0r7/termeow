<?php

declare(strict_types=1);

namespace Termeow\Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

final class BoundariesTest extends TestCase
{
    public function testProductionClassesKeepThePublicContracts(): void
    {
        foreach ($this->sources() as [$path, $source]) {
            $relative = substr($path, strlen(dirname(__DIR__, 2) . '/src/'));
            $className = 'Termeow\\' . str_replace('/', '\\', substr($relative, 0, -4));
            if (!class_exists($className)) {
                continue;
            }
            $reflection = new ReflectionClass($className);
            if ($reflection->isEnum()) {
                continue;
            }
            self::assertNotEmpty($reflection->getInterfaceNames(), "{$className} has no interface");
            self::assertFalse($reflection->isFinal() && $reflection->isReadOnly(), "{$className} is final readonly");
            $constructor = $reflection->getConstructor();
            if (
                $constructor === null
                || !$constructor->isPublic()
                || $constructor->getDeclaringClass()->getName() !== $className
            ) {
                continue;
            }
            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();
                self::assertNotNull($type, "{$className} constructor has an untyped parameter");
                self::assertTrue(
                    str_ends_with((string) $type, 'Interface'),
                    "{$className} constructor depends on {$type}",
                );
            }
        }
    }

    public function testAdaptersDoNotImportOtherAdapters(): void
    {
        foreach ($this->sources() as [$path, $source]) {
            $relative = substr($path, strlen(dirname(__DIR__, 2) . '/src/'));
            if (!str_starts_with($relative, 'Infrastructure/')) {
                if (str_starts_with($relative, 'Application/') || str_starts_with($relative, 'Domain/')) {
                    self::assertStringNotContainsString('Termeow\\Infrastructure\\', $source, $relative);
                }
                continue;
            }
            $module = explode('/', $relative)[1];
            if ($module === 'Composition') {
                continue;
            }
            preg_match_all('/^use Termeow\\\\Infrastructure\\\\([^\\\\;]+)/m', $source, $matches);
            foreach ($matches[1] as $dependency) {
                self::assertSame($module, $dependency, "{$relative} crosses an adapter boundary");
            }
        }
    }

    public function testNativeBindingsStayInsideTheirAdapters(): void
    {
        foreach ($this->sources() as [$path, $source]) {
            $relative = substr($path, strlen(dirname(__DIR__, 2) . '/src/'));
            if (!str_starts_with($relative, 'Infrastructure/Sdl/')) {
                self::assertStringNotContainsString('Pnlx\\Libsdl\\', $source, $relative);
                self::assertStringNotContainsString('Pnlx\\LibsdlTtf\\', $source, $relative);
            }
            if (!str_starts_with($relative, 'Infrastructure/Emulation/')) {
                self::assertStringNotContainsString('Pnlx\\Libvterm\\', $source, $relative);
            }
        }
    }

    /** @return iterable<array{string, string}> */
    private function sources(): iterable
    {
        $root = dirname(__DIR__, 2) . '/src';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            self::assertIsString($source);
            yield [$file->getPathname(), $source];
        }
    }
}
