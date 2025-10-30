@section('title', 'Sign in to your account')

<div class="flex flex-col items-center justify-center min-h-screen">
    <div class="w-full max-w-md space-y-6">
        <div class="text-center">
            <a wire:navigate href="{{ route('web.home') }}">
                <x-logo class="w-auto h-16 mx-auto text-primary" />
            </a>

            <h2 class="mt-6 text-3xl font-bold text-base-content">
               @lang(' Sign in to your account')
            </h2>

            @if (Route::has('register'))
                <p class="mt-2 text-sm text-gray-600">
                    @lang('Or')
                    <a wire:navigate href="{{ route('register') }}" class="text-primary hover:underline">
                        @lang('create a new account')
                    </a>
                </p>
            @endif
        </div>

        <x-card class="p-6">
            <form wire:submit="authenticate" class="space-y-5">
                <x-input :label="__('Email Address')" wire:model.lazy="email" type="email" required icon="o-envelope" placeholder="Enter your email" x-init="$nextTick(() => $el.focus())"
                />

                <x-input :label="__('Password')" wire:model.lazy="password" type="password" required icon="o-lock-closed" placeholder="Enter your password"
                />

                <div class="flex items-center justify-between">
                    <x-checkbox class=""
                        :label="__('Remember')"
                        wire:model.lazy="remember"
                    />

                    <a wire:navigate href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                        @lang('Forgot your password?')
                    </a>
                </div>
                <x-button spinner="authenticate" type="submit" class="w-full btn btn-primary" color="primary">
                    @lang('Sign in')
                </x-button>
            </form>
            <div class="flex justify-center gap-4 mt-6">
                <x-button  wire:click="quickLogin('admin')" icon="o-user-circle" class="btn-accent btn-md capitalize shadow-sm hover:shadow-md transition duration-150">
                    @lang('Admin')
                </x-button>

                <x-button         wire:click="quickLogin('user')"
                        icon="o-user"
                        class="btn-secondary btn-md capitalize shadow-sm hover:shadow-md transition duration-150"
                >
                    @lang('User')
                </x-button>
            </div>

            <!-- Social Login -->
            <div class="flex flex-col gap-3 mt-6">
                <x-button no-wire-navigate
                        tag="a"
                        link="{{ route('socialite.auth', 'google') }}"
                        class="btn-outline btn-md w-full justify-center bg-base-100 border-base-300 hover:bg-base-200 dark:bg-base-200 dark:border-base-300 transition duration-150"
                >
                    <x-icon name="fab.google" class="text-red-500" />
                    <span class="ml-2">@lang('Login with Google')</span>
                </x-button>

                <x-button
                        tag="a"
                        link="{{ route('socialite.auth', 'github') }}"
                        icon="fab.github"
                        class="btn-outline btn-md w-full justify-center bg-base-100 border-base-300 hover:bg-base-200 dark:bg-base-200 dark:border-base-300 transition duration-150"
                >
                    <span class="ml-2">@lang('Login with GitHub')</span>
                </x-button>
            </div>
        </x-card>
    </div>
</div>
