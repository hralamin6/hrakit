<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Verify extends Component
{
  use Toast;
    public function resend()
    {
        if (Auth::user()->hasVerifiedEmail()) {
          $this->redirect(route('web.home'), navigate: true);

        }else{
          Auth::user()->sendEmailVerificationNotification();
          $this->success('A fresh verification link has been sent to your email address.');

        }

    }

    public function render()
    {
      if (Auth::user()->hasVerifiedEmail()) {
        $this->redirect(route('web.home'), navigate: true);

      }
        return view('livewire.auth.verify')->extends('layouts.auth');
    }
}
