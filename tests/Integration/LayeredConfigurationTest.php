<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Termeow\Domain\Value\WorkingDirectory;
use Termeow\Infrastructure\Config\ConfigurationPaths;
use Termeow\Infrastructure\Config\LayeredTomlConfigurationLoader;
use Termeow\Infrastructure\Config\Exception\ConfigurationParseException;
use Termeow\Infrastructure\Config\Exception\ConfigurationReadException;

final class LayeredConfigurationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/termeow-config-' . bin2hex(random_bytes(6));
        foreach (['package', 'home', 'cwd'] as $name) {
            mkdir($this->root . '/' . $name, 0700, true);
        }
    }

    protected function tearDown(): void
    {
        foreach (['package', 'home', 'cwd'] as $name) {
            $path = $this->root . '/' . $name . '/termeow.toml';
            if (is_file($path)) {
                unlink($path);
            }
            rmdir($this->root . '/' . $name);
        }
        rmdir($this->root);
    }

    public function testHomeAndCurrentDirectoryOverrideOnlySpecifiedKeys(): void
    {
        $this->write('package', "[font]\nfamily = 'Package Font'\nsize = 11\n[window]\ntitle = 'Default'\n");
        $this->write('home', "[font]\nfamily = 'Home Font'\n[window]\ntitle = 'Home'\n");
        $this->write('cwd', "[font]\nsize = 14\n[window]\ntitle = 'Current'\n");
        $config = $this->loader()->load();
        $values = $config->values();
        self::assertIsArray($values['font']);
        self::assertIsArray($values['window']);
        self::assertSame('Home Font', $values['font']['family']);
        self::assertSame(14, $values['font']['size']);
        self::assertSame('Current', $values['window']['title']);
        self::assertSame($this->root . '/home', $config->sourceDirectory('font.family'));
    }

    public function testCurrentDirectoryFontPathKeepsItsOwnBaseDirectory(): void
    {
        $this->write('package', "[font]\nfamily = 'Package Font'\n");
        $this->write('home', "[font]\nfamily = 'Home Font'\n");
        $this->write('cwd', "[font]\nfamily = 'fonts/current.ttf'\n");
        $config = $this->loader()->load();
        $font = $config->values()['font'];
        self::assertIsArray($font);
        self::assertSame('fonts/current.ttf', $font['family']);
        self::assertSame($this->root . '/cwd', $config->sourceDirectory('font.family'));
    }

    public function testMissingOptionalFilesAreIgnored(): void
    {
        $this->write('package', "[font]\nfamily = 'Package Font'\n");
        $font = $this->loader()->load()->values()['font'];
        self::assertIsArray($font);
        self::assertSame('Package Font', $font['family']);
    }

    public function testMissingDefaultFileHasSpecificException(): void
    {
        $this->expectException(ConfigurationReadException::class);
        $this->loader()->load();
    }

    public function testInvalidTomlHasSpecificException(): void
    {
        $this->write('package', '[font');
        $this->expectException(ConfigurationParseException::class);
        $this->loader()->load();
    }

    private function write(string $directory, string $contents): void
    {
        file_put_contents($this->root . '/' . $directory . '/termeow.toml', $contents);
    }

    private function loader(): LayeredTomlConfigurationLoader
    {
        return new LayeredTomlConfigurationLoader(new ConfigurationPaths(
            WorkingDirectory::at($this->root . '/package'),
            WorkingDirectory::at($this->root . '/home'),
            WorkingDirectory::at($this->root . '/cwd'),
        ));
    }
}
