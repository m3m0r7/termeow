<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Pnl;

use Termeow\Domain\Value\WorkingDirectoryInterface;
use Termeow\Application\Port\TerminalRuntimeInitializerInterface;

readonly class PnlBindingLoader implements TerminalRuntimeInitializerInterface
{
    private const AUTOLOAD_PATH = '/@pnlx/autoload.php';

    public function __construct(private WorkingDirectoryInterface $package)
    {
    }

    public function initialize(): void
    {
        $autoload = $this->package->path() . self::AUTOLOAD_PATH;
        if (!is_file($autoload)) {
            throw new PnlBindingException(
                'Native bindings are missing. Run vendor/bin/pnl install in the Termeow package directory.',
            );
        }
        require_once $autoload;
    }
}
