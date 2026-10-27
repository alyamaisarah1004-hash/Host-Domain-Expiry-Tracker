<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\ReminderRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the profile page.
     */
    public function show()
    {
        $user = Auth::user();

        // ---------- Bell items ----------
        $rawBellItems = $this->bellItems();
        $bellItems    = $this->addReadFlags($rawBellItems);
        $unreadCount  = collect($bellItems)->where('read', false)->count();

        return view('profile', compact('user', 'bellItems', 'unreadCount'));
    }

    /**
     * Update the user's name and email.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'user_name'  => ['required', 'string', 'max:100'],
            'user_email' => ['required', 'email', 'max:150', 'unique:users,user_email,' . $user->user_id . ',user_id'],
        ], [
            'user_name.required'  => 'Name is required.',
            'user_email.required' => 'Email is required.',
            'user_email.email'    => 'Please enter a valid email address.',
            'user_email.unique'   => 'This email is already registered.',
        ]);

        $user->update([
            'user_name'  => $request->user_name,
            'user_email' => $request->user_email,
        ]);

        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required'     => 'New password is required.',
            'new_password.min'          => 'New password must be at least 8 characters.',
            'new_password.regex'        => 'New password must contain at least one number.',
            'new_password.confirmed'    => 'Passwords do not match.',
        ]);

        if (!Hash::check($request->current_password, $user->user_password_hash)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $user->update([
            'user_password_hash' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password updated.');
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
}