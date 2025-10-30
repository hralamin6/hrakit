@section('title', __('Reset password'))

<div class="flex flex-col items-center justify-center min-h-screen">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a wire:navigate href="{{ route('web.home') }}">
            <x-logo class="w-auto h-16 mx-auto text-indigo-600" />
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center text-base-content leading-9">
            {{ __('Reset password') }}
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card class="p-6 bg-base-100 text-base-content/80">
            <form wire:submit.prevent="resetPassword" class="space-y-6">
                <input wire:model="token" type="hidden">

                <x-input
                    label="{{ __('Email address') }}"
                    type="email"
                    wire:model.lazy="email"
                    required
                    x-init="$nextTick(() => $el.focus())"
                />
                <x-password
                    label="{{ __('Password') }}"
                    wire:model.lazy="password"
                    required
                    clearable
                />

                <x-password
                    label="{{ __('Confirm Password') }}"
                    wire:model.lazy="passwordConfirmation"
                    required
                    clearable
                />

                <x-button spinner="resetPassword" type="submit" class="w-full btn-primary">
                    {{ __('Reset password') }}
                </x-button>
            </form>
        </x-card>
    </div>
</div>
