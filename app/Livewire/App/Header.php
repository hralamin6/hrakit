<?php

namespace App\Livewire\App;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Header extends Component
{
    public function switchLanguage($locale)
    {
        if (in_array($locale, ['en', 'ar', 'bn'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            //          $this->redirect(back(), navigate: true);
            $this->redirect(url()->previous(), navigate: true);
            // Refresh the page to apply language changes
            //            $this->dispatch('language-switched');
        }
    }

    public function render()
    {
        return view('livewire.app.header');
    }
}
