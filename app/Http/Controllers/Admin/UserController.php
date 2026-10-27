<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\ReminderRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Show the users management page.
     */
    public function users()
    {
        $users = User::orderBy('user_role')->orderBy('user_name')->get();

        // ---------- Bell items ----------
        $rawBellItems = $this->bellItems();
        $bellItems    = $this->addReadFlags($rawBellItems);
        $unreadCount  = collect($bellItems)->where('read', false)->count();

        return view('admin.users', compact('users', 'bellItems', 'unreadCount'));
    }

    /**
     * Build the raw bell items array (without read flags).
     */
    private function bellItems(): array
    {
        $today = now()->startOfDay();
        $soon  = $today->copy()->addDays(30);

        $expiringDomains = Domain::whereBetween('expiry_date', [$today->toDateString(), $soon->toDateString()])
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($d) {
                return [
                    'type'           => 'Domain',
                    'name'           => $d->domain_name,
                    'record_id'      => $d->domain_id,
                    'expiry_date'    => $d->expiry_date,
                    'days_remaining' => $d->days_remaining,
                    'status'         => $d->status,
                    'url'            => route('domains.index') . '?' . http_build_query(array_filter([
                        'status' => $this->statusParam($d->status),
                        'focus'  => $d->domain_id,
                    ])),
                ];
            });

        $expiringHosting = Hosting::whereBetween('expiry_date', [$today->toDateString(), $soon->toDateString()])
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($h) {
                return [
                    'type'           => 'Hosting',
                    'name'           => $h->hosting_name,
                    'record_id'      => $h->hosting_id,
                    'expiry_date'    => $h->expiry_date,
                    'days_remaining' => $h->days_remaining,
                    'status'         => $h->status,
                    'url'            => route('hosting.index') . '?' . http_build_query(array_filter([
                        'status' => $this->statusParam($h->status),
                        'focus'  => $h->hosting_id,
                    ])),
                ];
            });

        $expiringSoon = $expiringDomains->concat($expiringHosting)
            ->sortBy('days_remaining')
            ->values();

        $expiredDomains = Domain::where('expiry_date', '<', $today->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($d) {
                return [
                    'type'           => 'Domain',
                    'name'           => $d->domain_name,
                    'record_id'      => $d->domain_id,
                    'expiry_date'    => $d->expiry_date,
                    'days_remaining' => $d->days_remaining,
                    'status'         => $d->status,
                    'url'            => route('domains.index') . '?' . http_build_query(array_filter([
                        'status' => $this->statusParam($d->status),
                        'focus'  => $d->domain_id,
                    ])),
                ];
            });

        $expiredHosting = Hosting::where('expiry_date', '<', $today->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($h) {
                return [
                    'type'           => 'Hosting',
                    'name'           => $h->hosting_name,
                    'record_id'      => $h->hosting_id,
                    'expiry_date'    => $h->expiry_date,
                    'days_remaining' => $h->days_remaining,
                    'status'         => $h->status,
                    'url'            => route('hosting.index') . '?' . http_build_query(array_filter([
                        'status' => $this->statusParam($h->status),
                        'focus'  => $h->hosting_id,
                    ])),
                ];
            });

        $expired = $expiredDomains->concat($expiredHosting)
            ->sortBy('days_remaining')
            ->values();

        return $expired->concat($expiringSoon)
            ->take(10)
            ->values()
            ->all();
    }

    /**
     * Add read flags and stage keys to each bell item.
     */
    private function addReadFlags(array $items): array
    {
        $readKeys = ReminderRead::where('user_id', Auth::id())
            ->get()
            ->map(fn($r) => $r->record_type . '_' . $r->record_id . '_' . $r->stage)
            ->toArray();

        return collect($items)->map(function ($item) use ($readKeys) {
            $typeKey  = strtolower($item['type']);
            $stage    = $this->stageBucket($item['status']);
            $stageKey = $typeKey . '_' . $item['record_id'] . '_' . $stage;

            $item['stage_key'] = $stageKey;
            $item['read']      = in_array($stageKey, $readKeys);

            return $item;
        })->all();
    }

    /**
     * Map a model status to a stage bucket.
     */
    private function stageBucket(string $status): ?string
    {
        return match ($status) {
            'warn'    => 'warning',
            'danger'  => 'urgent',
            'expired' => 'expired',
            default   => null,
        };
    }

    /**
     * Map a model status to the URL status param.
     */
    private function statusParam(string $status): ?string
    {
        return match ($status) {
            'expired' => 'expired',
            'danger'  => 'week',
            'warn'    => 'month',
            default   => null,
        };
    }

    /**
     * Store a new staff user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_name'     => ['required', 'string', 'max:100'],
            'user_email'    => ['required', 'email', 'max:150', 'unique:users,user_email'],
            'user_password' => ['required', 'string', 'min:8', 'regex:/[0-9]/'],
        ], [
            'user_name.required'     => 'Name is required.',
            'user_email.required'    => 'Email is required.',
            'user_email.email'       => 'Please enter a valid email address.',
            'user_email.unique'      => 'This email is already registered.',
            'user_password.required' => 'Password is required.',
            'user_password.min'      => 'Password must be at least 8 characters.',
            'user_password.regex'    => 'Password must contain at least one number.',
        ]);

        User::create([
            'user_name'          => $request->user_name,
            'user_email'         => $request->user_email,
            'user_password_hash' => Hash::make($request->user_password),
            'user_role'          => 'staff',
            'is_active'          => true,
            'created_by'         => Auth::id(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        if ($user->user_role === 'admin' && $user->user_id !== Auth::id()) {
            return back()->with('error', 'You cannot edit another admin.');
        }

        $request->validate([
            'user_name'  => ['required', 'string', 'max:100'],
            'user_email' => ['required', 'email', 'max:150', 'unique:users,user_email,' . $user->user_id . ',user_id'],
            'user_role'  => ['required', 'in:staff,admin'],
        ], [
            'user_name.required'  => 'Name is required.',
            'user_email.required' => 'Email is required.',
            'user_email.email'    => 'Please enter a valid email address.',
            'user_email.unique'   => 'This email is already registered.',
            'user_role.in'        => 'Invalid role selected.',
        ]);

        if ($user->user_role === 'admin' && $request->user_role === 'staff') {
            return back()->with('error', 'Admin accounts cannot be demoted.');
        }

        $user->update([
            'user_name'  => $request->user_name,
            'user_email' => $request->user_email,
            'user_role'  => $request->user_role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    /**
     * Toggle a staff user's active status.
     */
    public function toggleActive(Request $request, User $user)
    {
        if ($user->user_role === 'admin') {
            return back()->with('error', 'Admin accounts cannot be deactivated.');
        }

        if ($user->user_id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $message = $user->is_active ? 'User reactivated.' : 'User deactivated.';

        return back()->with('success', $message);
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'new_password.required'  => 'Password is required.',
            'new_password.min'       => 'Password must be at least 8 characters.',
            'new_password.regex'     => 'Password must contain at least one number.',
            'new_password.confirmed' => 'Passwords do not match.',
        ]);

        $user->update([
            'user_password_hash' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password reset for ' . $user->user_name . '.');
    }
}