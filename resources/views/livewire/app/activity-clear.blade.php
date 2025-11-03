<div>
    <x-header title="Clear Activities" subtitle="Remove old or unnecessary activity logs" separator />

    {{-- Warning Alert --}}
    <x-alert icon="o-exclamation-triangle" class="alert-warning mb-6">
        <strong>Warning:</strong> This action is permanent and cannot be undone. Deleted activities cannot be recovered.
    </x-alert>

    {{-- Statistics Overview --}}
    <x-card title="📊 Activity Statistics" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat bg-base-200 rounded">
                <div class="stat-title">Total Activities</div>
                <div class="stat-value text-primary">{{ number_format($stats['total']) }}</div>
            </div>

            <div class="stat bg-base-200 rounded">
                <div class="stat-title">Oldest Activity</div>
                <div class="stat-value text-sm">{{ $stats['oldest']?->diffForHumans() ?? 'N/A' }}</div>
                <div class="stat-desc">{{ $stats['oldest']?->format('Y-m-d') ?? 'No activities' }}</div>
            </div>

            <div class="stat bg-base-200 rounded">
                <div class="stat-title">Newest Activity</div>
                <div class="stat-value text-sm">{{ $stats['newest']?->diffForHumans() ?? 'N/A' }}</div>
                <div class="stat-desc">{{ $stats['newest']?->format('Y-m-d') ?? 'No activities' }}</div>
            </div>

            <div class="stat bg-base-200 rounded">
                <div class="stat-title">Will Delete</div>
                <div class="stat-value text-error">{{ number_format($previewCount) }}</div>
                <div class="stat-desc">Based on filters</div>
            </div>
        </div>

        <div class="mt-4">
            <h4 class="font-semibold mb-2">Top 5 Activity Types:</h4>
            <div class="space-y-2">
                @foreach($stats['by_log'] as $log)
                    <div class="flex justify-between items-center">
                        <span class="badge badge-ghost">{{ $log->log_name }}</span>
                        <span class="text-sm">{{ number_format($log->count) }} activities</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Selective Clear --}}
        <x-card title="🗑️ Clear Activities by Filters">
            <div class="space-y-4">
                <x-input
                    wire:model.live="days"
                    label="Delete activities older than (days)"
                    type="number"
                    min="1"
                    hint="Leave empty to skip age filter"
                />

                <x-select
                    wire:model.live="log_name"
                    label="Log Type"
                    :options="$logNames->map(fn($name) => ['id' => $name, 'name' => ucfirst($name)])"
                    placeholder="All types"
                    hint="Optional: Filter by specific log type"
                />

                <x-select
                    wire:model.live="event"
                    label="Event Type"
                    :options="$events->map(fn($event) => ['id' => $event, 'name' => ucfirst(str_replace('_', ' ', $event))])"
                    placeholder="All events"
                    hint="Optional: Filter by specific event"
                />

                <div class="divider"></div>

                <div class="bg-info/10 p-4 rounded">
                    <p class="font-semibold mb-2">Preview:</p>
                    <p class="text-lg">
                        <span class="text-error font-bold">{{ number_format($previewCount) }}</span>
                        activities will be deleted
                    </p>
                    @if($days)
                        <p class="text-sm text-base-content/60 mt-1">
                            Older than {{ $days }} days (before {{ now()->subDays($days)->format('Y-m-d') }})
                        </p>
                    @endif
                    @if($log_name)
                        <p class="text-sm text-base-content/60">Log type: {{ $log_name }}</p>
                    @endif
                    @if($event)
                        <p class="text-sm text-base-content/60">Event: {{ str_replace('_', ' ', $event) }}</p>
                    @endif
                </div>

                <x-checkbox
                    wire:model="confirmDelete"
                    label="I confirm that I want to delete these activities permanently"
                    class="checkbox-error"
                />

                <x-button
                    wire:click="clearActivities"
                    label="Delete Selected Activities"
                    icon="o-trash"
                    class="btn-error w-full"
                    :disabled="!$confirmDelete || $previewCount === 0"
                    spinner
                />
            </div>
        </x-card>

        {{-- Clear All --}}
        <x-card title="💥 Danger Zone" class="border-2 border-error">
            <div class="space-y-4">
                <x-alert icon="o-exclamation-circle" class="alert-error">
                    <strong>DANGER!</strong> This will delete ALL activities permanently. This action cannot be undone.
                </x-alert>

                <div class="bg-error/10 p-4 rounded border-2 border-error">
                    <p class="font-bold text-lg mb-2">Clear All Activities</p>
                    <p class="text-sm mb-3">This will permanently delete:</p>
                    <ul class="list-disc list-inside text-sm space-y-1 mb-3">
                        <li>All {{ number_format($stats['total']) }} activity records</li>
                        <li>All log types and events</li>
                        <li>Complete activity history</li>
                    </ul>
                    <p class="text-xs text-error font-semibold">⚠️ This cannot be recovered!</p>
                </div>

                <x-checkbox
                    wire:model="confirmDelete"
                    label="I understand this will delete ALL activities and cannot be undone"
                    class="checkbox-error"
                />

                <x-button
                    wire:click="clearAll"
                    label="DELETE ALL ACTIVITIES"
                    icon="o-exclamation-triangle"
                    class="btn-error w-full"
                    :disabled="!$confirmDelete || $stats['total'] === 0"
                    spinner
                />
            </div>
        </x-card>
    </div>

    {{-- Command Reference --}}
    <x-card title="💻 Command Line Alternative" class="mt-6 bg-base-200/50">
        <div class="space-y-3 text-sm">
            <p>You can also clear activities using the command line:</p>

            <div class="bg-base-300 p-3 rounded font-mono">
                # Delete activities older than 90 days<br>
                php artisan activities:clean --days=90
            </div>

            <div class="bg-base-300 p-3 rounded font-mono">
                # Delete activities older than 30 days<br>
                php artisan activities:clean --days=30
            </div>

            <p class="text-xs text-base-content/60 mt-2">
                💡 Tip: You can schedule automatic cleanup in your <code>app/Console/Kernel.php</code> file
            </p>
        </div>
    </x-card>
</div>

