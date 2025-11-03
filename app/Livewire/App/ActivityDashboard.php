<?php

namespace App\Livewire\App;

use App\Models\Activity;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Title('Activity Dashboard')]
#[Layout('layouts.app')]
class ActivityDashboard extends Component
{
    public $timeRange = '7'; // days

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('activity.dashboard')) {
            abort(403, 'Unauthorized access to activity dashboard.');
        }
    }

    public function render()
    {
        $startDate = now()->subDays($this->timeRange);

        // Statistics
        $totalActivities = Activity::where('created_at', '>=', $startDate)->count();
        $uniqueUsers = Activity::where('created_at', '>=', $startDate)
            ->whereNotNull('causer_id')
            ->distinct('causer_id')
            ->count('causer_id');

        // Activities by log name
        $activitiesByLog = Activity::where('created_at', '>=', $startDate)
            ->select('log_name', DB::raw('count(*) as count'))
            ->groupBy('log_name')
            ->orderBy('count', 'desc')
            ->get();

        // Activities by event
        $activitiesByEvent = Activity::where('created_at', '>=', $startDate)
            ->select('event', DB::raw('count(*) as count'))
            ->groupBy('event')
            ->orderBy('count', 'desc')
            ->get();

        // Top users
        $topUsers = Activity::where('created_at', '>=', $startDate)
            ->whereNotNull('causer_id')
            ->select('causer_id', DB::raw('count(*) as count'))
            ->groupBy('causer_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->user = User::find($item->causer_id);
                return $item;
            });

        // Recent activities
        $recentActivities = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Activity timeline (per day)
        $timeline = Activity::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return view('livewire.app.activity-dashboard', [
            'totalActivities' => $totalActivities,
            'uniqueUsers' => $uniqueUsers,
            'activitiesByLog' => $activitiesByLog,
            'activitiesByEvent' => $activitiesByEvent,
            'topUsers' => $topUsers,
            'recentActivities' => $recentActivities,
            'timeline' => $timeline,
        ]);
    }
}
