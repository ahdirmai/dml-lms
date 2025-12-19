<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserActivityLog::with('user');

        // Filter by Search (User Name or Description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('activity_type', 'like', "%{$search}%");
            });
        }

        // Filter by Activity Type
        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(20)->withQueryString();

        // Get unique activity types for the filter dropdown
        $types = UserActivityLog::select('activity_type')->distinct()->pluck('activity_type');

        return view('admin.user_activity.index', compact('activities', 'types'));
    }
}
