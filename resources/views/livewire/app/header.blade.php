<x-nav sticky full-width class="bg-base-100" x-data="{ open: false }">

  <x-slot:brand>
    {{-- Drawer toggle for "main-drawer" --}}
    <label for="main-drawer" class="lg:hidden mr-3">
      <x-icon name="o-bars-3" class="cursor-pointer" />
    </label>

    {{-- Brand --}}
    <div>{{ __('App') }}</div>
  </x-slot:brand>

  {{-- Right side actions --}}
  <x-slot:actions>
    <x-theme-toggle class="btn btn-circle btn-ghost" x-cloak />

    {{-- Language Switcher --}}
    <div class="dropdown dropdown-end"  @click="open = !open" >
      <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
        </svg>
      </label>
      <ul tabindex="0" x-show="open" @click.away="open = false" x-cloak class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-200 rounded-box w-52 mt-2">
        <li>
          <a wire:click="switchLanguage('en')" @click="open = false" class="flex items-center gap-2 {{ app()->getLocale() === 'en' ? 'active' : '' }}">
            <span class="fi fi-gb"></span>
            <span>English</span>
            @if(app()->getLocale() === 'en')
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-auto">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            @endif
          </a>
        </li>
        <li>
          <a wire:click="switchLanguage('ar')" @click="open = false" class="flex items-center gap-2 {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
            <span class="fi fi-sa"></span>
            <span>العربية</span>
            @if(app()->getLocale() === 'ar')
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-auto">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            @endif
          </a>
        </li>
        <li>
          <a wire:click="switchLanguage('bn')" @click="open = false" class="flex items-center gap-2 {{ app()->getLocale() === 'bn' ? 'active' : '' }}">
            <span class="fi fi-bd"></span>
            <span>বাংলা</span>
            @if(app()->getLocale() === 'bn')
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-auto">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            @endif
          </a>
        </li>
      </ul>
    </div>

    <x-button :label="__('Messages')" icon="o-envelope" link="###" class="btn-ghost btn-sm" responsive />
    <x-button :label="__('Notifications')" icon="o-bell" link="###" class="btn-ghost btn-sm" responsive />
  </x-slot:actions>
</x-nav>

<script>
  document.addEventListener('livewire:initialized', () => {
    Livewire.on('language-switched', () => {
      window.location.reload();
    });
  });
</script>

<style>
  [x-cloak] { display: none !important; }
</style>
