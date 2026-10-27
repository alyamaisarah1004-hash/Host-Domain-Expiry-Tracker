<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Hosting;
use App\Models\ReminderRead;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $soon  = $today->copy()->addDays(30);

        // ---------- Stats ----------
        $stats = [
            'total_domains' => Domain::count(),
            'total_hosting' => Hosting::count(),
            'expiring_soon' => Domain::whereBetween('expiry_date', [$today->toDateString(), $soon->toDateString()])->count()
                             + Hosting::whereBetween('expiry_date', [$today->toDateString(), $soon->toDateString()])->count(),
            'expired'       => Domain::where('expiry_date', '<', $today->toDateString())->count()
                             + Hosting::where('expiry_date', '<', $today->toDateString())->count(),
        ];

        // ---------- Expiring Soon ----------
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

        $expiringSoon = $expiringDomains
            ->concat($expiringHosting)
            ->sortBy('days_remaining')
            ->values();

        // ---------- Expired ----------
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

        $expired = $expiredDomains
            ->concat($expiredHosting)
            ->sortBy('days_remaining')
            ->values();

        // ---------- Bell items ----------
        $rawBellItems = $expired
            ->concat($expiringSoon)
            ->take(10)
            ->values();

        $bellItems = $this->addReadFlags($rawBellItems);

        // ---------- Unread count ----------
        $unreadCount = collect($bellItems)->where('read', false)->count();

        return view('dashboard', compact('stats', 'expiringSoon', 'expired', 'bellItems', 'unreadCount'));
    }

    /**
     * Add read flags and stage keys to each bell item.
     */
    private function addReadFlags($items)
    {
        $readKeys = ReminderRead::where('user_id', Auth::id())
            ->get()
            ->map(fn($r) => $r->record_type . '_' . $r->record_id . '_' . $r->stage)
            ->toArray();

        return $items->map(function ($item) use ($readKeys) {
            $typeKey   = strtolower($item['type']); // 'domain' or 'hosting'
            $stage     = $this->stageBucket($item['status']);
            $stageKey  = $typeKey . '_' . $item['record_id'] . '_' . $stage;

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