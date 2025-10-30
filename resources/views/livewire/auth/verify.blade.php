@section('title', __('Verify your email address'))

<div class="flex flex-col items-center justify-center min-h-screen">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a wire:navigate href="{{ route('web.home') }}">
            <x-logo class="w-auto h-16 mx-auto text-indigo-600" />
        </a>

        <h2 class="mt-6 text-3xl font-extrabold text-center text-base-content leading-9">
            {{ __('Verify your email address') }}
        </h2>

        <div class="mt-2 text-sm text-center text-gray-600 leading-5 max-w">
            {{ __('Or') }}
            <a wire:navigate href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                {{ __('sign out') }}
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <x-card class="p-6 bg-base-300 text-base-content/80">

            <div class="">
                <p>{{ __('Before proceeding, please check your email for a verification link.') }}</p>

                <p class="mt-3">
                    {{ __('If you did not receive the email,') }}
                    <x-button spinner="resend" wire:click="resend" class="text-primary cursor-pointer hover:underline transition duration-150">
                        {{ __('click here to request another') }}
                    </x-button>.
                </p>
            </div>
        </x-card>
    </div>
</div>
