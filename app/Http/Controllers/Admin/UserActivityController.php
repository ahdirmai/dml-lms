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
    public function index()
    {
        $activities = UserActivityLog::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.user_activity.index', compact('activities'));
    }
}
