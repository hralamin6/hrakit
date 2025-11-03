<?php

namespace App\Livewire\App;

use App\Models\Activity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

#[Title('Clear Activities')]
#[Layout('layouts.app')]
class ActivityClear extends Component
{
    use Toast;

    public $days = 90;
    public $log_name = '';
    public $event = '';
    public $confirmDelete = false;

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('activity.delete')) {
            abort(403, 'Unauthorized access to clear activities.');
        }
    }

    public function getPreviewCount()
    {
        $query = Activity::query();

        if ($this->days) {
            $date = now()->subDays($this->days);
            $query->where('created_at', '<', $date);
        }

        if ($this->log_name) {
            $query->where('log_name', $this->log_name);
        }

        if ($this->event) {
            $query->where('event', $this->event);
        }

        return $query->count();
    }

    public function clearActivities()
    {
        if (!$this->confirmDelete) {
            $this->error('Please confirm deletion by checking the box.');
            return;
        }

        $query = Activity::query();

        if ($this->days) {
            $date = now()->subDays($this->days);
            $query->where('created_at', '<', $date);
        }

        if ($this->log_name) {
            $query->where('log_name', $this->log_name);
        }

        if ($this->event) {
            $query->where('event', $this->event);
        }

        $count = $query->count();
        $query->delete();

        // Log the cleanup action
        \App\Services\ActivityLogger::logSystem('Activities cleared from UI', [
            'count' => $count,
            'days' => $this->days,
            'log_name' => $this->log_name,
            'event' => $this->event,
        ]);

        $this->confirmDelete = false;
        $this->success("Successfully deleted {$count} activities!");
    }

    public function clearAll()
    {
        if (!$this->confirmDelete) {
            $this->error('Please confirm deletion by checking the box.');
            return;
        }

        $count = Activity::count();
        Activity::truncate();

        // Log the cleanup action
        \App\Services\ActivityLogger::logSystem('All activities cleared from UI', [
            'count' => $count,
        ]);

        $this->confirmDelete = false;
        $this->success("Successfully deleted all {$count} activities!");
    }

    public function render()
    {
        $logNames = Activity::distinct()->pluck('log_name')->filter();
        $events = Activity::distinct()->pluck('event')->filter();
        $previewCount = $this->getPreviewCount();

        $stats = [
            'total' => Activity::count(),
            'oldest' => Activity::orderBy('created_at', 'asc')->first()?->created_at,
            'newest' => Activity::orderBy('created_at', 'desc')->first()?->created_at,
            'by_log' => Activity::select('log_name', DB::raw('count(*) as count'))
                ->groupBy('log_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('livewire.app.activity-clear', [
            'logNames' => $logNames,
            'events' => $events,
            'previewCount' => $previewCount,
            'stats' => $stats,
        ]);
    }
}

