<?php

namespace Infrastructure\FrameworkCore\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class CoreWatchCommand extends Command
{
    protected $signature = 'core:watch {--lang=en}';
    protected $description = 'Starts the development server and automatically synchronizes the database on entity changes.';

    public function handle()
    {
        // Set Local Language for the Watcher session
        app()->setLocale($this->option('lang'));
        $lang = $this->option('lang');

        $this->info(__('core::messages.watcher_started'));

        // Start Laravel Development Server using the absolute PHP path for reliability
        $phpFinder = new PhpExecutableFinder();
        $phpPath = $phpFinder->find() ?? 'php';
        $process = new Process([$phpPath, 'artisan', 'serve', '--port=8000']);
        $process->start();

        // 1. Initial Sync on startup
        $this->call('core:migrate', ['--lang' => $lang]);
        $this->info('🚀 Starting Magic REST Server on http://127.0.0.1:8000...');

        // 2. Monitoring the Domain folder
        $domainPath = base_path('Domain');
        $lastHash = $this->getDirectoryHash($domainPath);

        while (true) {
            // Real-time server output (Non-blocking)
            if ($process->isRunning()) {
                $output = $process->getIncrementalOutput();
                if ($output) {
                    $this->output->write($output);
                }
            } else {
                $this->error('Error: The server process unexpected stopped.');
                break;
            }

            // Detect changes in the Domain
            $currentHash = $this->getDirectoryHash($domainPath);

            if ($currentHash !== $lastHash) {
                // Update hash BEFORE launching migrate to prevent race conditions or loops
                $lastHash = $currentHash;

                $this->newLine();
                $this->info(__('core::messages.file_changed'));
                $this->info('⏳ Waiting for changes to settle...');
                
                // Debouncing (Wait 1s for file writes to finish)
                sleep(1);

                $this->info('🚀 Auto-syncing database metadata...');

                // IMPORTANT: Use a new process to avoid PHP class caching
                $phpFinder = new PhpExecutableFinder();
                $phpPath = $phpFinder->find();

                $syncProcess = new Process([$phpPath, 'artisan', 'core:migrate', '--lang=' . $lang]);
                $syncProcess->run();

                if ($syncProcess->isSuccessful()) {
                    $this->info($syncProcess->getOutput());
                } else {
                    $this->error($syncProcess->getErrorOutput());
                }

                $this->newLine();
            }

            usleep(200000); // 200ms CPU protection
        }
    }

    /**
     * Recursively generates a hash of the folder to detect any modified file.
     * Pure PHP implementation - works on Windows, Linux, and macOS.
     */
    protected function getDirectoryHash(string $directory): string
    {
        if (!is_dir($directory)) {
            return md5('');
        }

        $hashes = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Use path + modification time for fast and reliable change detection
                $hashes[] = $file->getPathname() . ':' . $file->getMTime();
            }
        }

        sort($hashes);
        return md5(implode('|', $hashes));
    }
}

