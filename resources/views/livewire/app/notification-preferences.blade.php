<div>
    <x-header title="Notification Preferences" subtitle="Manage how you receive notifications" separator>
        <x-slot:actions>
            <x-button
                label="Enable All"
                icon="o-check-circle"
                class="btn-success btn-sm"
                wire:click="enableAll"
                spinner
            />
            <x-button
                label="Disable All"
                icon="o-x-circle"
                class="btn-error btn-sm"
                wire:click="disableAll"
                spinner
            />
        </x-slot:actions>
    </x-header>

    <x-card class="shadow-lg">
        <div class="space-y-6">
            {{-- Info Alert --}}
            <div class="alert alert-info">
                <x-icon name="o-information-circle" class="w-5 h-5" />
                <div>
                    <h3 class="font-bold">About Notification Channels</h3>
                    <div class="text-sm">
                        <strong>Push:</strong> Browser notifications •
                        <strong>Email:</strong> Email messages •
                        <strong>Database:</strong> In-app notification center
                    </div>
                </div>
            </div>

            {{-- Preferences Table --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-center">
                                <div class="flex flex-col items-center">
                                    <x-icon name="o-bell" class="w-5 h-5" />
                                    <span class="text-xs">Push</span>
                                </div>
                            </th>
                            <th class="text-center">
                                <div class="flex flex-col items-center">
                                    <x-icon name="o-envelope" class="w-5 h-5" />
                                    <span class="text-xs">Email</span>
                                </div>
                            </th>
                            <th class="text-center">
                                <div class="flex flex-col items-center">
                                    <x-icon name="o-inbox" class="w-5 h-5" />
                                    <span class="text-xs">Database</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category => $details)
                            <tr>
                                <td>
                                    <div>
                                        <div class="font-semibold">{{ $details['name'] }}</div>
                                        <div class="text-sm text-base-content/60">{{ $details['description'] }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <x-checkbox
                                        wire:model="preferences.{{ $category }}.push_enabled"
                                        class="checkbox-primary"
                                    />
                                </td>
                                <td class="text-center">
                                    <x-checkbox
                                        wire:model="preferences.{{ $category }}.email_enabled"
                                        class="checkbox-secondary"
                                    />
                                </td>
                                <td class="text-center">
                                    <x-checkbox
                                        wire:model="preferences.{{ $category }}.database_enabled"
                                        class="checkbox-accent"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <x-button
                    label="Save Preferences"
                    icon="o-check"
                    class="btn-primary"
                    wire:click="save"
                    spinner
                />
            </div>
        </div>
    </x-card>

    {{-- Additional Info --}}
    <div class="grid gap-4 md:grid-cols-3 mt-6">
        <x-card class="shadow-md">
            <div class="text-center">
                <x-icon name="o-bell" class="w-12 h-12 mx-auto text-primary" />
                <h3 class="mt-3 font-semibold">Push Notifications</h3>
                <p class="mt-2 text-sm text-base-content/70">
                    Instant browser notifications even when the app is closed
                </p>
            </div>
        </x-card>

        <x-card class="shadow-md">
            <div class="text-center">
                <x-icon name="o-envelope" class="w-12 h-12 mx-auto text-secondary" />
                <h3 class="mt-3 font-semibold">Email Notifications</h3>
                <p class="mt-2 text-sm text-base-content/70">
                    Receive notifications directly in your inbox
                </p>
            </div>
        </x-card>

        <x-card class="shadow-md">
            <div class="text-center">
                <x-icon name="o-inbox" class="w-12 h-12 mx-auto text-accent" />
                <h3 class="mt-3 font-semibold">In-App Center</h3>
                <p class="mt-2 text-sm text-base-content/70">
                    View all notifications in your notification center
                </p>
            </div>
        </x-card>
    </div>
</div>

