<div>
    <x-header title="Notification Center" subtitle="View and manage your notifications" separator>
        <x-slot:actions>
            <x-button
                label="Mark All Read"
                icon="o-check-circle"
                class="btn-primary btn-sm"
                wire:click="markAllAsRead"
                spinner
            />
            <x-button
                label="Delete All"
                icon="o-trash"
                class="btn-error btn-sm"
                wire:click="deleteAll"
                wire:confirm="Are you sure you want to delete all notifications?"
                spinner
            />
        </x-slot:actions>
    </x-header>

    <div class="grid gap-6">
        {{-- Tabs --}}
        <x-card class="shadow-lg">
            <div role="tablist" class="tabs tabs-boxed">
                <a
                    role="tab"
                    class="tab {{ $selectedTab === 'all' ? 'tab-active' : '' }}"
                    wire:click="$set('selectedTab', 'all')"
                >
                    All
                </a>
                <a
                    role="tab"
                    class="tab {{ $selectedTab === 'unread' ? 'tab-active' : '' }}"
                    wire:click="$set('selectedTab', 'unread')"
                >
                    Unread
                    @if($unreadCount > 0)
                        <x-badge value="{{ $unreadCount }}" class="badge-primary ml-2" />
                    @endif
                </a>
                <a
                    role="tab"
                    class="tab {{ $selectedTab === 'read' ? 'tab-active' : '' }}"
                    wire:click="$set('selectedTab', 'read')"
                >
                    Read
                </a>
            </div>
        </x-card>

        {{-- Notifications List --}}
        <div class="space-y-3">
            @forelse($notifications as $notification)
                <x-card class="shadow-md {{ is_null($notification->read_at) ? 'bg-base-200' : '' }}">
                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="flex-shrink-0">
                            @php
                                $data = $notification->data;
                                $iconName = $data['icon'] ?? 'o-bell';
                                $type = $data['type'] ?? 'info';
                                $iconClass = match($type) {
                                    'success' => 'text-success',
                                    'error' => 'text-error',
                                    'warning' => 'text-warning',
                                    default => 'text-info',
                                };
                            @endphp
                            <div class="p-3 rounded-full bg-base-300">
                                <x-icon :name="$iconName" class="w-6 h-6 {{ $iconClass }}" />
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-base-content">
                                        {{ $data['title'] ?? 'Notification' }}
                                    </h3>
                                    <p class="text-sm text-base-content/70 mt-1">
                                        {{ $data['message'] ?? '' }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-2">
                                        <span class="text-xs text-base-content/50">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if(is_null($notification->read_at))
                                            <x-badge value="New" class="badge-primary badge-sm" />
                                        @endif
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2">
                                    @if($data['action_url'] ?? null)
                                        <a
                                            href="{{ $data['action_url'] }}"
                                            class="btn btn-primary btn-sm"
                                            @if(is_null($notification->read_at))
                                                wire:click="markAsRead('{{ $notification->id }}')"
                                            @endif
                                        >
                                            {{ $data['action_text'] ?? 'View' }}
                                        </a>
                                    @endif

                                    @if(is_null($notification->read_at))
                                        <x-button
                                            icon="o-check"
                                            class="btn-ghost btn-sm btn-circle"
                                            wire:click="markAsRead('{{ $notification->id }}')"
                                            tooltip="Mark as read"
                                            spinner
                                        />
                                    @endif

                                    <x-button
                                        icon="o-trash"
                                        class="btn-ghost btn-sm btn-circle text-error"
                                        wire:click="deleteNotification('{{ $notification->id }}')"
                                        wire:confirm="Are you sure you want to delete this notification?"
                                        tooltip="Delete"
                                        spinner
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            @empty
                <x-card class="shadow-md">
                    <div class="text-center py-12">
                        <x-icon name="o-bell-slash" class="w-16 h-16 mx-auto text-base-content/30" />
                        <h3 class="mt-4 text-lg font-semibold text-base-content/70">No notifications</h3>
                        <p class="mt-2 text-sm text-base-content/50">
                            You're all caught up! Check back later for updates.
                        </p>
                    </div>
                </x-card>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

