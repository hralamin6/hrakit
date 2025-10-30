@section('title', __('Confirm your password'))

<div class="flex flex-col items-center justify-center min-h-screen">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a wire:navigate href="{{ route('web.home') }}">
            <x-logo class="w-auto h-16 mx-auto text-indigo-600" />
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center leading-9">
            {{ __('Confirm your password') }}
        </h2>
        <p class="mt-2 text-sm text-center text-gray-600 leading-5 max-w">
            {{ __('Please confirm your password before continuing') }}
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card class="p-6">
            <form wire:submit.prevent="confirm" class="space-y-6">
                <x-password
                    label="{{ __('Password') }}"
                    wire:model.lazy="password"
                    required
                    placeholder="{{ __('Enter your password') }}"
                    x-init="$nextTick(() => $el.focus())"
                />
                <div class="flex items-center justify-end">
                    <div class="text-sm leading-5">
                        <a wire:navigate href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                            {{ __('Forgot your password?') }}
                        </a>
                    </div>
                </div>

                <x-button spinner="confirm" type="submit" class="w-full btn-primary">
                    {{ __('Confirm password') }}
                </x-button>
            </form>
        </x-card>
    </div>
</div>
