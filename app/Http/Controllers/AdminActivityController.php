<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $activities = DB::table('admin_activity_logs')
            ->leftJoin('users', 'users.id', '=', 'admin_activity_logs.user_id')
            ->select(['admin_activity_logs.*', 'users.name as user_name', 'users.email as user_email'])
            ->when($search !== '', fn ($query) => $query->where(function ($activities) use ($search): void {
                $activities->where('summary', 'like', '%'.$search.'%')
                    ->orWhere('action', 'like', '%'.$search.'%')
                    ->orWhere('users.name', 'like', '%'.$search.'%');
            }))
            ->latest('admin_activity_logs.created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity', compact('activities', 'search'));
    }
}
