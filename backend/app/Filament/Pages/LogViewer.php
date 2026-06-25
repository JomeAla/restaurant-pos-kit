<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class LogViewer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.log-viewer';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public array $logs = [];

    public ?string $search = '';

    public function mount(): void
    {
        $this->loadLogs();
    }

    public function loadLogs(): void
    {
        $path = storage_path('logs/laravel.log');
        if (!file_exists($path)) {
            $this->logs = [];
            return;
        }

        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        $parsed = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}.*?)\] (\w+)\.(\w+):/', $line, $m)) {
                if ($current) {
                    $parsed[] = $current;
                }
                $current = ['datetime' => $m[1], 'env' => $m[2], 'type' => $m[3], 'message' => substr($line, strpos($line, $m[3] . ':') + strlen($m[3]) + 2)];
            } elseif ($current) {
                $current['message'] .= "\n" . $line;
            }
        }
        if ($current) {
            $parsed[] = $current;
        }

        $parsed = array_reverse($parsed);

        if ($this->search) {
            $parsed = array_filter($parsed, fn ($log) => str_contains(strtolower($log['message']), strtolower($this->search)));
        }

        $this->logs = array_slice($parsed, 0, 100);
    }

    public function clearLogs(): void
    {
        file_put_contents(storage_path('logs/laravel.log'), '');
        Notification::make()->title('Logs cleared.')->success()->send();
        $this->loadLogs();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('clear')->label('Clear Logs')->action('clearLogs')->color('danger')->requiresConfirmation(),
        ];
    }
}
