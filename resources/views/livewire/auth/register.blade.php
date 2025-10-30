@section('title', __('Create a new account'))

<div class="flex flex-col items-center justify-center min-h-screen">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a wire:navigate href="{{ route('web.home') }}">
            <x-logo class="w-auto h-16 mx-auto text-indigo-600"/>
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900 leading-9">
            {{ __('Create a new account') }}
        </h2>

        <p class="mt-2 text-sm text-center text-gray-600 leading-5 max-w">
            {{ __('Or') }}
            <a wire:navigate href="{{ route('login') }}"
               class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                {{ __('sign in to your account') }}
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card>
            <form wire:submit.prevent="register" class="space-y-6">
                <x-input  label="{{ __('Name') }}" wire:model.lazy="name" type="text" required
                              placeholder="{{ __('Enter your name') }}" x-init="$nextTick(() => $el.focus())"/>
                <x-input  label="{{ __('Email Address') }}" wire:model.lazy="email" type="email" required
                              placeholder="{{ __('Enter your email') }}"/>
                <x-password  label="{{ __('Password') }}" wire:model.lazy="password" required
                                 placeholder="{{ __('Enter your password') }}"/>
                <x-password  label="{{ __('Confirm Password') }}" wire:model.lazy="passwordConfirmation" required
                                 placeholder="{{ __('Enter your password') }}"/>
                <x-button spinner="register" type="submit" class="w-full btn btn-primary">{{ __('Register') }}</x-button>
            </form>
        </x-card>
    </div>
</div>
