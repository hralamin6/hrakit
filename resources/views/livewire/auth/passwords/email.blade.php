@section('title', __('Reset password'))

<div class="flex flex-col items-center justify-center min-h-screen">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a wire:navigate href="{{ route('web.home') }}">
            <x-logo class="w-auto h-16 mx-auto text-indigo-600" />
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center  leading-9">
            {{ __('Reset password') }}
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card class="p-6 ">
            @if ($emailSentMessage)
                <x-alert class="alert-success">
                    <x-icon name="o-check-circle" />
                    <span>{{ $emailSentMessage }}</span>
                </x-alert>
            @else
                <form wire:submit.prevent="sendResetPasswordLink" class="space-y-6">
                    <x-input
                        label="{{ __('Email address') }}"
                        type="email"
                        wire:model.lazy="email"
                        :placeholder="__('Enter your email')"
                        required
                        x-init="$nextTick(() => $el.focus())"
                    />
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <x-button spinner="sendResetPasswordLink" type="submit" class="w-full btn-primary">
                        {{ __('Send password reset link') }}
                    </x-button>
                </form>
            @endif
        </x-card>
    </div>
</div>
