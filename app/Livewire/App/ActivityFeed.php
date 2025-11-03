<?php

namespace App\Livewire\App;

use App\Models\Activity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Activity Feed')]
#[Layout('layouts.app')]
class ActivityFeed extends Component
{
    use WithPagination;

    public $filters = [
        'log_name' => '',
        'event' => '',
        'search' => '',
        'date_from' => '',
        'date_to' => '',
        'causer_id' => '',
    ];

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('activity.feed')) {
            abort(403, 'Unauthorized access to activity feed.');
        }
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->filters = [
            'log_name' => '',
            'event' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => '',
            'causer_id' => '',
        ];
        $this->resetPage();
    }

    public function getEventColor($event)
    {
        return match($event) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'error',
            'login' => 'primary',
            'logout' => 'warning',
            'failed_login' => 'error',
            default => 'base-300',
        };
    }

    public function getEventIcon($event)
    {
        return match($event) {
            'created' => 'o-plus-circle',
            'updated' => 'o-pencil-square',
            'deleted' => 'o-trash',
            'login' => 'o-arrow-right-on-rectangle',
            'logout' => 'o-arrow-left-on-rectangle',
            'failed_login' => 'o-exclamation-triangle',
            default => 'o-document-text',
        };
    }

    public function render()
    {
        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->filters['log_name'], fn($q) => $q->where('log_name', $this->filters['log_name']))
            ->when($this->filters['event'], fn($q) => $q->where('event', $this->filters['event']))
            ->when($this->filters['causer_id'], fn($q) => $q->where('causer_id', $this->filters['causer_id']))
            ->when($this->filters['search'], fn($q) => $q->where('description', 'like', '%' . $this->filters['search'] . '%'))
            ->when($this->filters['date_from'], fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to'], fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $logNames = Activity::distinct()->pluck('log_name')->filter();
        $events = Activity::distinct()->pluck('event')->filter();

        return view('livewire.app.activity-feed', [
            'activities' => $activities,
            'logNames' => $logNames,
            'events' => $events,
        ]);
    }
}
