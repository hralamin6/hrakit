<div>
    <x-header title="Activity Dashboard" subtitle="System activity analytics and statistics" separator />

    {{-- Time Range Filter --}}
    <div class="mb-6 flex gap-2">
        <x-button
            wire:click="$set('timeRange', '1')"
            :class="$timeRange == '1' ? 'btn-primary' : 'btn-ghost'"
            label="Today"
        />
        <x-button
            wire:click="$set('timeRange', '7')"
            :class="$timeRange == '7' ? 'btn-primary' : 'btn-ghost'"
            label="7 Days"
        />
        <x-button
            wire:click="$set('timeRange', '30')"
            :class="$timeRange == '30' ? 'btn-primary' : 'btn-ghost'"
            label="30 Days"
        />
        <x-button
            wire:click="$set('timeRange', '90')"
            :class="$timeRange == '90' ? 'btn-primary' : 'btn-ghost'"
            label="90 Days"
        />
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card>
            <div class="stat">
                <div class="stat-figure text-primary">
                    <x-icon name="o-chart-bar" class="w-8 h-8" />
                </div>
                <div class="stat-title">Total Activities</div>
                <div class="stat-value text-primary">{{ number_format($totalActivities) }}</div>
                <div class="stat-desc">Last {{ $timeRange }} days</div>
            </div>
        </x-card>

        <x-card>
            <div class="stat">
                <div class="stat-figure text-secondary">
                    <x-icon name="o-users" class="w-8 h-8" />
                </div>
                <div class="stat-title">Active Users</div>
                <div class="stat-value text-secondary">{{ number_format($uniqueUsers) }}</div>
                <div class="stat-desc">Unique users</div>
            </div>
        </x-card>

        <x-card>
            <div class="stat">
                <div class="stat-figure text-accent">
                    <x-icon name="o-arrow-trending-up" class="w-8 h-8" />
                </div>
                <div class="stat-title">Avg per Day</div>
                <div class="stat-value text-accent">{{ $timeRange > 0 ? number_format($totalActivities / $timeRange, 1) : 0 }}</div>
                <div class="stat-desc">Activities/day</div>
            </div>
        </x-card>

        <x-card>
            <div class="stat">
                <div class="stat-figure text-info">
                    <x-icon name="o-fire" class="w-8 h-8" />
                </div>
                <div class="stat-title">Peak Activity</div>
                <div class="stat-value text-info">{{ $timeline->max('count') ?? 0 }}</div>
                <div class="stat-desc">Max in a day</div>
            </div>
        </x-card>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Activities by Type --}}
        <x-card title="Activities by Type">
            <div class="space-y-3">
                @forelse($activitiesByLog as $log)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium">{{ ucfirst($log->log_name ?? 'Unknown') }}</span>
                            <span class="badge badge-primary">{{ $log->count }}</span>
                        </div>
                        <progress
                            class="progress progress-primary w-full"
                            value="{{ $log->count }}"
                            max="{{ $activitiesByLog->max('count') }}"
                        ></progress>
                    </div>
                @empty
                    <p class="text-center text-base-content/60 py-4">No data available</p>
                @endforelse
            </div>
        </x-card>

        {{-- Activities by Event --}}
        <x-card title="Activities by Event">
            <div class="space-y-3">
                @forelse($activitiesByEvent as $event)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $event->event ?? 'Unknown')) }}</span>
                            <span class="badge badge-secondary">{{ $event->count }}</span>
                        </div>
                        <progress
                            class="progress progress-secondary w-full"
                            value="{{ $event->count }}"
                            max="{{ $activitiesByEvent->max('count') }}"
                        ></progress>
                    </div>
                @empty
                    <p class="text-center text-base-content/60 py-4">No data available</p>
                @endforelse
            </div>
        </x-card>
    </div>

    {{-- Top Users and Timeline --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Top Users --}}
        <x-card title="Most Active Users">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activities</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topUsers as $item)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                <span class="text-xs">{{ substr($item->user?->name ?? 'U', 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <span>{{ $item->user?->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-ghost">{{ $item->count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-base-content/60">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Activity Timeline --}}
        <x-card title="Activity Timeline">
            <div class="h-64">
                <canvas id="timelineChart"></canvas>
            </div>
        </x-card>
    </div>

    {{-- Recent Activities --}}
    <x-card title="Recent Activities">
        <div class="space-y-3">
            @forelse($recentActivities as $activity)
                <div class="flex items-start gap-3 pb-3 border-b border-base-300 last:border-0">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                            <x-icon name="o-document-text" class="w-4 h-4 text-primary" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium">{{ $activity->description }}</p>
                        <p class="text-sm text-base-content/60">
                            @if($activity->causer)
                                {{ $activity->causer->name }} •
                            @endif
                            {{ $activity->created_at->diffForHumans() }}
                            @if($activity->log_name)
                                • <span class="badge badge-xs">{{ $activity->log_name }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-center text-base-content/60 py-4">No recent activities</p>
            @endforelse
        </div>

        <div class="mt-4">
            <a href="{{ route('app.activity.feed') }}" wire:navigate class="btn btn-ghost btn-sm w-full">
                View All Activities
                <x-icon name="o-arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </x-card>
</div>

@script
<script>
    // Simple timeline chart using Chart.js (you'll need to include it)
    const ctx = document.getElementById('timelineChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($timeline->pluck('date')),
                datasets: [{
                    label: 'Activities',
                    data: @json($timeline->pluck('count')),
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
</script>
@endscript

