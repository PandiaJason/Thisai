<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-4">
            <x-filament::button wire:click="import" color="primary" icon="heroicon-o-arrow-up-tray">
                Import Questions
            </x-filament::button>

            @if(!empty($importResults))
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600"/>
                    <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">
                        Imported: {{ $importResults['imported'] }} | Skipped: {{ $importResults['skipped'] }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
