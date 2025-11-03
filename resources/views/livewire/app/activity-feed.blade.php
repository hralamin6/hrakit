<div>
    <x-header title="Activity Feed" subtitle="All system activities" separator />

    {{-- Filters --}}
    <x-card title="Filters" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input
                wire:model.live.debounce="filters.search"
                label="Search"
                placeholder="Search activities..."
                icon="o-magnifying-glass"
            />

            <x-select
                wire:model.live="filters.log_name"
                label="Log Type"
                :options="$logNames->map(fn($name) => ['id' => $name, 'name' => ucfirst($name)])"
                placeholder="All types"
            />

            <x-select
                wire:model.live="filters.event"
                label="Event"
                :options="$events->map(fn($event) => ['id' => $event, 'name' => ucfirst(str_replace('_', ' ', $event))])"
                placeholder="All events"
            />

            <x-input
                wire:model.live="filters.date_from"
                label="From Date"
                type="date"
            />

            <x-input
                wire:model.live="filters.date_to"
                label="To Date"
                type="date"
            />

            <div class="flex items-end">
                <x-button
                    wire:click="clearFilters"
                    label="Clear Filters"
                    icon="o-x-mark"
                    class="btn-ghost w-full"
                />
            </div>
        </div>
    </x-card>

    {{-- Activity Timeline --}}
    <x-card>
        <div class="space-y-4">
            @forelse($activities as $activity)
                <div class="flex gap-4 pb-4 border-b border-base-300 last:border-0">
                    {{-- Icon --}}
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-{{ $this->getEventColor($activity->event) }} flex items-center justify-center">
                            <x-icon name="{{ $this->getEventIcon($activity->event) }}" class="w-5 h-5 text-white" />
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-base-content">
                                    {{ $activity->description }}
                                </p>
                                <div class="flex items-center gap-2 mt-1 text-sm text-base-content/60">
                                    @if($activity->causer)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="o-user" class="w-4 h-4" />
                                            {{ $activity->causer->name }}
                                        </span>
                                        <span>•</span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <x-icon name="o-clock" class="w-4 h-4" />
                                        {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                    @if($activity->log_name)
                                        <span>•</span>
                                        <span class="badge badge-sm badge-ghost">{{ $activity->log_name }}</span>
                                    @endif
                                </div>

                                {{-- Properties/Changes --}}
                                @if($activity->properties && (isset($activity->properties['attributes']) || isset($activity->properties['old'])))
                                    <div class="mt-2">
                                        <button
                                            onclick="document.getElementById('details-{{ $activity->id }}').classList.toggle('hidden')"
                                            class="text-xs text-primary hover:underline"
                                        >
                                            View Details
                                        </button>
                                        <div id="details-{{ $activity->id }}" class="hidden mt-2 p-3 bg-base-200 rounded text-xs">
                                            @if($activity->event === 'updated' && isset($activity->properties['old']))
                                                <div class="font-semibold mb-1">Changes:</div>
                                                @foreach($activity->properties['attributes'] as $key => $value)
                                                    @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $value)
                                                        <div class="mb-1">
                                                            <span class="text-base-content/60">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                            <span class="line-through text-error">{{ $activity->properties['old'][$key] }}</span>
                                                            →
                                                            <span class="text-success">{{ $value }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <pre class="whitespace-pre-wrap">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- IP & User Agent --}}
                                @if($activity->ip_address || $activity->user_agent)
                                    <div class="mt-1 text-xs text-base-content/40">
                                        @if($activity->ip_address)
                                            <span>IP: {{ $activity->ip_address }}</span>
                                        @endif
                                        @if($activity->user_agent)
                                            <span class="ml-2">{{ \Illuminate\Support\Str::limit($activity->user_agent, 50) }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-base-content/60">
                    <x-icon name="o-inbox" class="w-12 h-12 mx-auto mb-2" />
                    <p>No activities found</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $activities->links() }}
        </div>
    </x-card>
</div>

@script
<script>
    // Helper methods would be better in the component, but for quick implementation:
    window.getEventColor = (event) => {
        const colors = {
            'created': 'success',
            'updated': 'info',
            'deleted': 'error',
            'login': 'primary',
            'logout': 'warning',
            'failed_login': 'error',
        };
        return colors[event] || 'base-300';
    };

    window.getEventIcon = (event) => {
        const icons = {
            'created': 'o-plus-circle',
            'updated': 'o-pencil-square',
            'deleted': 'o-trash',
            'login': 'o-arrow-right-on-rectangle',
            'logout': 'o-arrow-left-on-rectangle',
            'failed_login': 'o-exclamation-triangle',
        };
        return icons[event] || 'o-document-text';
    };
</script>
@endscript

