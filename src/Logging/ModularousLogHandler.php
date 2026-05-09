<?php

namespace Unusualify\Modularous\Logging;

use Illuminate\Support\Facades\Notification;
use Modules\SystemNotification\Notifications\LogNotification;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class ModularousLogHandler extends AbstractProcessingHandler
{
    protected $logPath;

    protected $maxFiles;

    public function __construct($level = Level::Debug, $maxFiles = 14)
    {
        parent::__construct($level);
        $this->maxFiles = $maxFiles;
        $this->logPath = $this->getDailyLogPath();
    }

    protected function getDailyLogPath(): string
    {
        return storage_path('logs/modularous-' . date('Y-m-d') . '.log');
    }

    protected function rotateOldFiles(): void
    {
        $logDir = storage_path('logs');
        // Only match files with the exact date pattern: modularous-YYYY-MM-DD.log
        $files = glob($logDir . '/modularous-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log');

        if (count($files) > $this->maxFiles) {
            // Sort files by modification time (oldest first)
            usort($files, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Delete oldest files
            $filesToDelete = array_slice($files, 0, count($files) - $this->maxFiles);
            foreach ($filesToDelete as $file) {
                @unlink($file);
            }
        }
    }

    protected function write(LogRecord $record): void
    {
        // Handle emergency, alert, and critical levels with email
        if ($record->level->value >= Level::Critical->value) {
            $this->sendEmailNotification($record);
        }

        // Write to custom log file for debug and above
        if ($record->level->value >= Level::Debug->value) {
            $this->writeToFile($record);
        }
    }

    protected function writeToFile(LogRecord $record): void
    {
        $formattedMessage = sprintf(
            "[%s] %s.%s: %s\n",
            $record->datetime->format('Y-m-d H:i:s'),
            $record->level->name,
            $record->channel,
            $record->message
        );

        if (! empty($record->context)) {
            $formattedMessage .= 'Context: ' . json_encode($record->context, JSON_PRETTY_PRINT) . "\n";
        }

        file_put_contents(
            $this->logPath,
            $formattedMessage,
            FILE_APPEND | LOCK_EX
        );

        $this->rotateOldFiles();

    }

    protected function sendEmailNotification(LogRecord $record): void
    {
        Notification::route('mail', 'oguzhan@olmadikprojeler.com')
            ->notify(new LogNotification($record));
    }
}
