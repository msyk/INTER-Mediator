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
     * Implemented as a static method, so it can be invoked both as a plugin
     * event subscriber (when this package is installed as a dependency) and
     * as a Composer script callback (when this package is the root project).
     */
    public static function onPostInstallCmd(Event $event): void
    {
        self::runPostInstallTasks($event, false);
    }

    /**
     * Called after the "composer update" command.
     */
    public static function onPostUpdateCmd(Event $event): void
    {
        self::runPostInstallTasks($event, true);
    }

    /**
     * Composer script callback for the "clear" script.
     *
     * Removes the installed "node_modules" and "vendor" directories using
     * PHP's filesystem functions, so it works on every platform including
     * Windows. This replaces the UNIX-only "rm -rf node_modules vendor"
     * shell command.
     */
    public static function onClear(Event $event): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $baseDir = dirname($vendorDir);

        // node_modules is removed first and vendor last. The vendor directory
        // holds the Composer autoloader, but this class is loaded from
        // src/php (already in memory), so removal is safe.
        $targets = [
            $baseDir . '/node_modules',
            $vendorDir,
        ];
        foreach ($targets as $target) {
            if (self::removeDirectory($target)) {
                $io->write(sprintf('<info>INTER-Mediator: Removed "%s".</info>', $target));
            }
        }
    }

    /**
     * Execute the post-install/update tasks.
     *
     * @param bool $isUpdate True when called after "composer update",
     *                       false when called after "composer install".
     */
    protected static function runPostInstallTasks(Event $event, bool $isUpdate): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $baseDir = dirname($vendorDir);

        if (self::isInstalledAsDependency($composer)) {
            // INTER-Mediator was installed as a dependency via "composer require".
            $io->write('<info>INTER-Mediator: Running post-install tasks (dependency mode)...</info>');
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: install pnpm via PowerShell
                self::executeCommand($io, $baseDir, 'powershell -NoProfile -ExecutionPolicy Bypass -Command '
                    . '"Invoke-WebRequest https://get.pnpm.io/install.ps1 -UseBasicParsing | Invoke-Expression; '
                    . 'pnpm ci"'
                );
            } else {
                // macOS / Linux: install pnpm via the official shell installer
                self::executeCommand($io, $baseDir, 'curl -fsSL https://get.pnpm.io/install.sh | sh -');
                $pnpmHome = PHP_OS_FAMILY === 'Darwin'
                    ? '$HOME/Library/pnpm'
                    : '${XDG_DATA_HOME:-$HOME/.local/share}/pnpm';
                self::executeCommand($io, $baseDir, "cd ./vendor/inter-mediator/inter-mediator; {$pnpmHome}/bin/pnpm ci");
                self::executeCommand($io, $baseDir, "./vendor/inter-mediator/inter-mediator/dist-docs/generateminifyjshere.sh");
            }

            // As the final step, run the root project's post-install/update
            // hook script if it provides one (dependency mode only).
            self::runAfterScript($io, $baseDir, $isUpdate);
        } else {
            // INTER-Mediator is the root project (e.g., git clone + composer install).
            $io->write('<info>INTER-Mediator: Running post-install tasks (root project mode)...</info>');
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: install pnpm via PowerShell
                self::executeCommand($io, $baseDir, 'powershell -NoProfile -ExecutionPolicy Bypass -Command '
                    . '"Invoke-WebRequest https://get.pnpm.io/install.ps1 -UseBasicParsing | Invoke-Expression; '
                    . 'pnpm ci"'
                );
            } else {
                // macOS / Linux: install pnpm via the official shell installer
                self::executeCommand($io, $baseDir, 'curl -fsSL https://get.pnpm.io/install.sh | sh -');
                $pnpmHome = PHP_OS_FAMILY === 'Darwin'
                    ? '$HOME/Library/pnpm'
                    : '${XDG_DATA_HOME:-$HOME/.local/share}/pnpm';
                self::executeCommand($io, $baseDir, "{$pnpmHome}/bin/pnpm ci");
            }
        }
        @unlink($baseDir . '/__Did_you_run_composer_update.txt');

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
     * Run the root project's post-install/update hook script as the final
     * step, if it exists.
     *
     * When INTER-Mediator is installed as a dependency, the root project may
     * provide "lib/doafterinstall.sh" (executed after "composer install") or
     * "lib/doafterupdate.sh" (executed after "composer update"). On Windows,
     * the corresponding "lib/doafterinstall.ps1" / "lib/doafterupdate.ps1"
     * files are used instead. If the script file does not exist, it is
     * silently ignored.
     */
    protected static function runAfterScript(IOInterface $io, string $baseDir, bool $isUpdate): void
    {
        $scriptBaseName = $isUpdate ? 'doafterupdate' : 'doafterinstall';
        $isWindows = PHP_OS_FAMILY === 'Windows';
        $relativePath = 'lib/' . $scriptBaseName . ($isWindows ? '.ps1' : '.sh');

        if (!is_file($baseDir . '/' . $relativePath)) {
            // No hook script provided by the root project; silently skip.
            return;
        }

        if ($isWindows) {
            self::executeCommand($io, $baseDir,
                'powershell -NoProfile -ExecutionPolicy Bypass -File ' . $relativePath);
        } else {
            self::executeCommand($io, $baseDir, 'sh ' . $relativePath);
        }
    }

    /**
     * Recursively remove a directory and all of its contents.
     *
     * Implemented with PHP's filesystem functions so it works on every
     * platform, including Windows. Symbolic links (such as the many links a
     * pnpm "node_modules" tree contains) are removed without descending into
     * their targets. Returns true when the directory existed and was removed;
     * a non-existent directory is silently ignored.
     */
    protected static function removeDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($items as $item) {
            $pathname = $item->getPathname();
            if ($item->isLink()) {
                // Remove the link itself without following it. A directory
                // junction/symlink on Windows needs rmdir(), while unlink()
                // handles every link type on macOS / Linux.
                if (!@unlink($pathname)) {
                    @rmdir($pathname);
                }
            } elseif ($item->isDir()) {
                @rmdir($pathname);
            } else {
                @unlink($pathname);
            }
        }

        return @rmdir($path);
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
