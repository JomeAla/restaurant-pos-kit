<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">
                Business Hours
            </x-slot>

            <div class="space-y-2 sm:space-y-3">
                @foreach($businessHours as $index => $hours)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 p-2 rounded-lg @if($hours['is_closed']) bg-gray-100 @endif">
                        <div class="w-full sm:w-32 font-medium capitalize text-sm sm:text-base">{{ $hours['day_of_week'] }}</div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-filament::input.wrapper class="flex-1 sm:flex-none min-w-0">
                                <x-filament::input type="time" wire:model="businessHours.{{ $index }}.open_time" :disabled="$hours['is_closed']" class="text-xs sm:text-sm w-full" />
                            </x-filament::input.wrapper>
                            <span class="text-gray-400 text-xs sm:text-sm">to</span>
                            <x-filament::input.wrapper class="flex-1 sm:flex-none min-w-0">
                                <x-filament::input type="time" wire:model="businessHours.{{ $index }}.close_time" :disabled="$hours['is_closed']" class="text-xs sm:text-sm w-full" />
                            </x-filament::input.wrapper>
                        </div>
                        <x-filament::input.checkbox wire:model="businessHours.{{ $index }}.is_closed" label="Closed" class="text-xs sm:text-sm" />
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <div class="flex gap-4">
            <x-filament::button type="submit" color="primary">
                Save Settings
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
