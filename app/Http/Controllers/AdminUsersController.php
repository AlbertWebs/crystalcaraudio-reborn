<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    public function index(): View
    {
        return view('admin.users', [
            'users' => User::query()->orderByRaw('CASE WHEN admin_role IS NULL THEN 1 ELSE 0 END')->orderBy('name')->paginate(25),
            'roles' => $this->roles(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'admin_role' => ['nullable', 'string', 'in:super_admin,store_manager,sales_administrator,inventory_manager,installation_coordinator,marketing_manager,bi_analyst'],
        ]);
        $previousRole = $user->admin_role;
        $user->admin_role = $data['admin_role'] ?? null;
        $user->save();

        DB::table('admin_activity_logs')->insert([
            'user_id' => $request->user()->id,
            'action' => 'admin.role_updated',
            'subject_type' => 'user',
            'subject_id' => (string) $user->id,
            'summary' => 'Changed '.$user->name.' role from '.($previousRole ?: 'customer').' to '.($user->admin_role ?: 'customer').'.',
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Account role updated.');
    }

    /** @return array<string, string> */
    private function roles(): array
    {
        return [
            'super_admin' => 'Super administrator',
            'store_manager' => 'Store manager',
            'sales_administrator' => 'Sales administrator',
            'inventory_manager' => 'Inventory manager',
            'installation_coordinator' => 'Installation coordinator',
            'marketing_manager' => 'Marketing manager',
            'bi_analyst' => 'BI analyst',
        ];
    }
}
