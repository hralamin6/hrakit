<?php

namespace App\Livewire\Web;

use App\Models\Page as PageModel;
use Livewire\Component;

class Page extends Component
{
    public ?PageModel $page = null;

    public ?string $slug = null;

    public function mount($slug = null)
    {
        if ($slug) {
            $this->slug = $slug;
            $this->page = PageModel::where('slug', $slug)
                ->published()
                ->firstOrFail();
        }
    }

    public function render()
    {
        return view('livewire.web.page')
            ->layout('layouts.auth');
    }
}
