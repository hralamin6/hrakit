<div>
    <x-header title="Push Notifications" subtitle="Test push notifications" separator />

    {{-- Status Check --}}
    <x-card title="Subscription Status" class="mb-6">
        <div id="status-container">
            <div class="text-center py-4">
                <span class="loading loading-spinner loading-lg"></span>
                <p class="mt-2">Checking status...</p>
            </div>
        </div>

        <div class="flex gap-2 mt-4">
            <button onclick="subscribeToPush()" class="btn btn-primary">
                <x-icon name="o-bell" class="w-5 h-5" />
                Subscribe
            </button>
            <button onclick="unsubscribeFromPush()" class="btn btn-error">
                <x-icon name="o-bell-slash" class="w-5 h-5" />
                Unsubscribe
            </button>
            <button onclick="refreshStatus()" class="btn btn-ghost">
                <x-icon name="o-arrow-path" class="w-5 h-5" />
                Refresh
            </button>
        </div>
    </x-card>

    {{-- Test Notification --}}
    <x-card title="Send Test Notification" class="mb-6">
        <div class="space-y-4">
            <x-input wire:model="testTitle" label="Title" placeholder="Test Notification" />
            <x-textarea wire:model="testBody" label="Message" placeholder="This is a test" rows="3" />

            <x-button
                wire:click="sendTest"
                class="btn-success w-full"
                spinner
                label="Send Test Notification"
                icon="o-paper-airplane"
            />
        </div>
    </x-card>

    {{-- Debug Tool --}}
    <x-card title="Debug Tools" class="mb-6">
        <div class="grid grid-cols-2 gap-4">
            <div class="stat bg-base-200 rounded">
                <div class="stat-title">Service Worker</div>
                <div class="stat-value text-sm" id="sw-status">Checking...</div>
            </div>
            <div class="stat bg-base-200 rounded">
                <div class="stat-title">Permission</div>
                <div class="stat-value text-sm" id="perm-status">Checking...</div>
            </div>
        </div>

        <a href="/debug-push" class="btn btn-outline btn-sm mt-4">
            <x-icon name="o-wrench" class="w-4 h-4" />
            Advanced Debug Tool
        </a>
    </x-card>

    @push('scripts')
    <script>
        async function refreshStatus() {
            const container = document.getElementById('status-container');

            try {
                // Check browser subscription
                const browserSub = await window.pushManager.getSubscription();

                // Check backend
                const response = await fetch('/api/push/status');
                const backend = await response.json();

                if (browserSub && backend.subscribed) {
                    container.innerHTML = `
                        <div class="alert alert-success">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <div>
                                <h3 class="font-bold">✅ Subscribed!</h3>
                                <div class="text-xs">Endpoint: ${browserSub.endpoint.substring(0, 60)}...</div>
                            </div>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="alert alert-warning">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h3 class="font-bold">Not Subscribed</h3>
                                <div class="text-xs">Click Subscribe to enable push notifications</div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                container.innerHTML = `
                    <div class="alert alert-error">
                        <span>❌ Error: ${error.message}</span>
                    </div>
                `;
            }
        }

        // Check SW and permission status
        async function updateDebugInfo() {
            if ('serviceWorker' in navigator) {
                const reg = await navigator.serviceWorker.getRegistration();
                document.getElementById('sw-status').textContent = reg ? '✅ Active' : '❌ Not Active';
            }

            document.getElementById('perm-status').textContent =
                Notification.permission === 'granted' ? '✅ Granted' :
                Notification.permission === 'denied' ? '❌ Denied' : '⚠️ Default';
        }

        // Run on load
        window.addEventListener('load', () => {
            setTimeout(() => {
                refreshStatus();
                updateDebugInfo();
            }, 1000);
        });

        // Listen for Livewire events
        Livewire.on('test-sent', () => {
            setTimeout(refreshStatus, 500);
        });
    </script>
    @endpush
</div>

