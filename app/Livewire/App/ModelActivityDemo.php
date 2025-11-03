    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('activity.feed')) {
            abort(403, 'Unauthorized access to activity demo.');
        }
    }

