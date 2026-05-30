<?php

namespace INTERMediator\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class InstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    private const PACKAGE_NAME = 'inter-mediator/inter-mediator';

    /**
     * Apply plugin modifications to Composer.
     *
     * Note: state is intentionally not stored on the instance because the
     * post-install/update logic is invoked statically as a Composer script
     * callback as well (Composer scripts cannot call instance methods).
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Remove any hooks from Composer.
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Prepare the plugin to be uninstalled.
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array<string, string|array{0: string, 1?: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => ['onPostInstallCmd', 0],
            ScriptEvents::POST_UPDATE_CMD => ['onPostUpdateCmd', 0],
        ];
    }

    /**
     * Called after the "composer install" command.
     *
     * Implemented as a static method so it can be invoked both as a plugin
     * event subscriber (when this package is installed as a dependency) and
     * as a Composer script callback (when this package is the root project).
     */
    public static function onPostInstallCmd(Event $event): void
    {
        self::runPostInstallTasks($event);
    }

    /**
     * Called after the "composer update" command.
     */
    public static function onPostUpdateCmd(Event $event): void
    {
        self::runPostInstallTasks($event);
    }

    /**
     * Execute the post-install/update tasks.
     */
    protected static function runPostInstallTasks(Event $event): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $baseDir = dirname($vendorDir);

        if (self::isInstalledAsDependency($composer)) {
            // INTER-Mediator was installed as a dependency via "composer require".
            $io->write('<info>INTER-Mediator: Running post-install tasks (dependency mode)...</info>');

            // TODO: Add commands for dependency installation here.

        } else {
            // INTER-Mediator is the root project (e.g. git clone + composer install).
            $io->write('<info>INTER-Mediator: Running post-install tasks (root project mode)...</info>');

            // TODO: Add commands for root project installation here.
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: install pnpm via PowerShell
                self::executeCommand(
                    $io,
                    $baseDir,
                    'powershell -NoProfile -ExecutionPolicy Bypass -Command '
                    . '"Invoke-WebRequest https://get.pnpm.io/install.ps1 -UseBasicParsing | Invoke-Expression; '
                    . 'pnpm ci"'
                );
            } else {
                // macOS / Linux: install pnpm via the official shell installer
                self::executeCommand($io, $baseDir, 'curl -fsSL https://get.pnpm.io/install.sh | sh -');
                self::executeCommand($io, $baseDir, 'pnpm ci');
            }
            @unlink($baseDir . '/__Did_you_run_composer_update.txt');
        }

        $io->write('<info>INTER-Mediator: Post-install tasks completed.</info>');
    }

    /**
     * Determine whether INTER-Mediator is installed as a Composer dependency
     * (true) or is being used as the root project (false).
     */
    protected static function isInstalledAsDependency(Composer $composer): bool
    {
        return $composer->getPackage()->getName() !== self::PACKAGE_NAME;
    }

    /**
     * Execute a shell command in the given directory.
     */
    protected static function executeCommand(IOInterface $io, string $cwd, string $command): int
    {
        $io->write(sprintf('  > %s', $command));

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $cwd
        );

        if (!is_resource($process)) {
            $io->writeError(sprintf('<error>Failed to execute: %s</error>', $command));
            return 1;
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($stdout) {
            $io->write($stdout);
        }
        if ($exitCode !== 0 && $stderr) {
            $io->writeError($stderr);
        }

        return $exitCode;
    }
}
