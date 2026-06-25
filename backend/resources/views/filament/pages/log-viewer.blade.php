<x-filament-panels::page>
    <x-filament::input.wrapper class="mb-4">
        <x-filament::input type="text" wire:model.live="search" placeholder="Search logs..." />
    </x-filament::input.wrapper>

    <div class="space-y-2">
        @forelse($logs as $log)
            <div class="rounded-lg border p-3 @if(str_contains($log['type'], 'ERROR') || str_contains($log['type'], 'CRITICAL')) border-danger-500 bg-danger-50 @elseif(str_contains($log['type'], 'WARNING')) border-warning-500 bg-warning-50 @else border-gray-200 @endif">
                <div class="flex justify-between text-sm">
                    <span class="font-mono text-xs text-gray-500">{{ $log['datetime'] }}</span>
                    <span class="font-bold text-xs uppercase {{ str_contains($log['type'], 'ERROR') || str_contains($log['type'], 'CRITICAL') ? 'text-danger-600' : (str_contains($log['type'], 'WARNING') ? 'text-warning-600' : 'text-gray-600') }}">
                        {{ $log['env'] }}.{{ $log['type'] }}
                    </span>
                </div>
                <pre class="mt-1 text-xs font-mono whitespace-pre-wrap">{{ $log['message'] }}</pre>
            </div>
        @empty
            <p class="text-gray-500 text-center py-8">No log entries found.</p>
        @endforelse
    </div>
</x-filament-panels::page>
