<div class="space-y-6">
  <x-header :title="__('Profile')" :subtitle="__('Manage your personal info, password, and photo.')" separator>
  </x-header>

  <div class="grid lg:grid-cols-4 gap-6 px-0 mx-0">

    <div class="lg:col-span-1 space-y-6">

      <x-card class="p-5">
        <div class="flex items-center gap-4">
          <x-avatar :image="$avatarUrl" alt="{{ auth()->user()->name }}" class="w-16 h-16 ring-2 ring-primary/20" />
          <div>
            <div class="font-semibold text-base-content/90">{{ $name }}</div>
            <div class="text-sm text-base-content/60">{{ $email }}</div>
          </div>
        </div>
      </x-card>

      <x-menu class="bg-base-100 rounded-lg shadow">
        <x-menu-item
          :title="__('General')"
          icon="o-user"
          wire:click="$set('tab', 'general')"
          :active="$tab === 'general'"
        />
        <x-menu-item
          :title="__('Password')"
          icon="o-lock-closed"
          wire:click="$set('tab', 'password')"
          :active="$tab === 'password'"
        />
        <x-menu-item
          :title="__('Image')"
          icon="o-photo"
          wire:click="$set('tab', 'image')"
          :active="$tab === 'image'"
        />
      </x-menu>
    </div>

    <div class="lg:col-span-3">

      @if($tab === 'general')
        <x-card>
          <x-header :title="__('General Information')" :subtitle="__('Update your name and email address.')" class="mb-5" />
          <form wire:submit="saveGeneral" class="space-y-5">
            <x-input :label="__('Full name')" icon="o-user" wire:model.defer="name" required :placeholder="__('Your name')" />
            <x-input :label="__('Email')" icon="o-envelope" wire:model.defer="email" type="email" required :placeholder="__('you@example.com')" />

            @can('profile.update')
              <div class="flex gap-2">
                <x-button type="submit" spinner="saveGeneral" class="btn-primary" icon="o-check">{{ __('Save changes') }}</x-button>
                <x-button type="button" class="btn-ghost" icon="o-arrow-path" wire:click="$refresh">{{ __('Reset') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

      @if($tab === 'password')
        <x-card>
          <x-header :title="__('Security')" :subtitle="__('Change your password.')" class="mb-5" />
          <form wire:submit="savePassword" class="space-y-5">
            <x-input :label="__('Current password')" icon="o-lock-closed" wire:model.defer="current_password" type="password" required />
            <x-input :label="__('New password')" icon="o-key" wire:model.defer="password" type="password" required />
            <x-input :label="__('Confirm new password')" icon="o-key" wire:model.defer="password_confirmation" type="password" required />

            @can('profile.update')
              <div class="flex gap-2">
                <x-button type="submit" spinner="savePassword" class="btn-primary" icon="o-check">{{ __('Change password') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

      @if($tab === 'image')
        <x-card class="relative">
          <div wire:loading.flex wire:target="photo,savePhoto,savePhotoFromUrl,removePhoto" class="absolute inset-0 z-10 bg-base-100/70 backdrop-blur-sm items-center justify-center rounded-lg">
            <div class="flex items-center gap-3">
              <x-loading class="loading-spinner loading-lg text-primary" />
              <span class="text-sm">{{ __('Processing image...') }}</span>
            </div>
          </div>

          <x-header :title="__('Profile Image')" :subtitle="__('Manage your avatar.')" class="mb-5" />

          <div class="max-w-md">
            <x-file :label="__('Upload new photo')" wire:model="photo" accept="image/*" crop-after-change>
              <x-avatar :image="$photo?->temporaryUrl() ?? $avatarUrl" alt="Preview" class="w-24 h-24 ring-4 ring-primary/20" />
            </x-file>

            @can('profile.update')
              <div class="mt-3 flex flex-wrap gap-2">
                <x-button class="btn-primary" icon="o-check" wire:click="savePhoto" spinner="savePhoto">{{ __('Save image') }}</x-button>
                <x-button class="btn-ghost" icon="o-x-mark" wire:click="$set('photo', null)">{{ __('Clear selection') }}</x-button>
              </div>
            @endcan
          </div>

          <x-hr :text="__('OR')" class="my-6" />

          <div class="max-w-md">
            <x-input :label="__('Paste an image URL')" icon="o-link" wire:model.defer="photo_url" type="url" :placeholder="__('https://example.com/avatar.jpg')" />
            @can('profile.update')
              <div class="mt-3 flex flex-wrap gap-2">
                <x-button class="btn-primary" icon="o-arrow-up-tray" wire:click="savePhotoFromUrl" spinner="savePhotoFromUrl">{{ __('Save from URL') }}</x-button>
                <x-button class="btn-ghost" icon="o-x-mark" wire:click="$set('photo_url', '')">{{ __('Clear URL') }}</x-button>
              </div>
            @endcan
          </div>

          <x-hr class="my-6" />

          @can('profile.update')
            <div>
              <x-header :title="__('Remove Image')" :subtitle="__('Revert to the default avatar.')" level="3" class="mb-2" />
              <x-button class="btn-error" icon="o-trash" wire:click="removePhoto" spinner="removePhoto" :confirm="__('Are you sure you want to remove your avatar?')">
                {{ __('Remove current') }}
              </x-button>
            </div>
          @endcan
        </x-card>
      @endif

    </div> </div>
</div>
