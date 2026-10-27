<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Hosting;
use App\Models\ReminderRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DomainController extends Controller
{
    /**
     * Show the domains management page.
     */
    public function index(Request $request)
    {
        $query = Domain::with('creator');

        // Status filter
        $status = $request->input('status');
        $today = now()->startOfDay();

        if ($status === 'active') {
            $query->where('expiry_date', '>', $today->copy()->addDays(30)->toDateString());
        } elseif ($status === 'month') {
            $query->whereBetween('expiry_date', [
                $today->copy()->addDays(8)->toDateString(),
                $today->copy()->addDays(30)->toDateString(),
            ]);
        } elseif ($status === 'week') {
            $query->whereBetween('expiry_date', [
                $today->toDateString(),
                $today->copy()->addDays(7)->toDateString(),
            ]);
        } elseif ($status === 'expired') {
            $query->where('expiry_date', '<', $today->toDateString());
        }

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('domain_name', 'like', "%{$search}%")
                  ->orWhere('website_url', 'like', "%{$search}%")
                  ->orWhere('domain_provider', 'like', "%{$search}%");
            });
        }

        // Focus mode: when arriving from a bell item
        $focusId = $request->input('focus');

        if ($focusId) {
            $query->where('domain_id', $focusId);

            // Record this stage as read
            $this->recordRead($focusId);
        }

        $domains = $query->orderBy('expiry_date', 'asc')->get();

        // Counts for the status tabs
        $counts = [
            'all'     => Domain::count(),
            'active'  => Domain::where('expiry_date', '>', $today->copy()->addDays(30)->toDateString())->count(),
            'month'   => Domain::whereBetween('expiry_date', [
                            $today->copy()->addDays(8)->toDateString(),
                            $today->copy()->addDays(30)->toDateString(),
                         ])->count(),
            'week'    => Domain::whereBetween('expiry_date', [
                            $today->toDateString(),
                            $today->copy()->addDays(7)->toDateString(),
                         ])->count(),
            'expired' => Domain::where('expiry_date', '<', $today->toDateString())->count(),
        ];

        // ---------- Bell items ----------
        $rawBellItems = $this->bellItems();
        $bellItems    = $this->addReadFlags($rawBellItems);
        $unreadCount  = collect($bellItems)->where('read', false)->count();

        return view('domains', compact('domains', 'counts', 'bellItems', 'unreadCount'));
    }

    /**
     * Record that this user has read the current stage of a domain reminder.
     */
    private function recordRead(int $domainId): void
    {
        $domain = Domain::find($domainId);
        if (!$domain) return;

        $stage = $this->stageBucket($domain->status);
        if (!$stage) return;

        ReminderRead::firstOrCreate([
            'user_id'     => Auth::id(),
            'record_type' => 'domain',
            'record_id'   => $domain->domain_id,
            'stage'       => $stage,
        ]);
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
     * Store a new domain.
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain_name'     => ['required', 'string', 'max:255'],
            'website_url'     => ['required', 'string', 'max:500'],
            'domain_provider' => ['required', 'string', 'max:150'],
            'expiry_date'     => ['required', 'date'],
            'cost'            => ['nullable', 'numeric', 'min:0'],
            'auto_renew'      => ['nullable', 'boolean'],
            'notes'           => ['nullable', 'string'],
        ], [
            'domain_name.required'     => 'Domain name is required.',
            'website_url.required'     => 'Website URL is required.',
            'domain_provider.required' => 'Provider is required.',
            'expiry_date.required'     => 'Expiry date is required.',
            'expiry_date.date'         => 'Expiry date must be a valid date.',
            'cost.numeric'             => 'Cost must be a number.',
            'cost.min'                 => 'Cost cannot be negative.',
        ]);

        $url = $request->website_url;
        if ($url && !preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        Domain::create([
            'domain_name'     => $request->domain_name,
            'website_url'     => $url,
            'domain_provider' => $request->domain_provider,
            'expiry_date'     => $request->expiry_date,
            'cost'            => $request->cost,
            'auto_renew'      => $request->boolean('auto_renew'),
            'notes'           => $request->notes,
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('domains.index')->with('success', 'Domain added successfully.');
    }

    /**
     * Update an existing domain.
     */
    public function update(Request $request, Domain $domain)
    {
        $request->validate([
            'domain_name'     => ['required', 'string', 'max:255'],
            'website_url'     => ['required', 'string', 'max:500'],
            'domain_provider' => ['required', 'string', 'max:150'],
            'expiry_date'     => ['required', 'date'],
            'cost'            => ['nullable', 'numeric', 'min:0'],
            'auto_renew'      => ['nullable', 'boolean'],
            'notes'           => ['nullable', 'string'],
        ], [
            'domain_name.required'     => 'Domain name is required.',
            'website_url.required'     => 'Website URL is required.',
            'domain_provider.required' => 'Provider is required.',
            'expiry_date.required'     => 'Expiry date is required.',
            'expiry_date.date'         => 'Expiry date must be a valid date.',
            'cost.numeric'             => 'Cost must be a number.',
            'cost.min'                 => 'Cost cannot be negative.',
        ]);

        $url = $request->website_url;
        if ($url && !preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $domain->update([
            'domain_name'     => $request->domain_name,
            'website_url'     => $url,
            'domain_provider' => $request->domain_provider,
            'expiry_date'     => $request->expiry_date,
            'cost'            => $request->cost,
            'auto_renew'      => $request->boolean('auto_renew'),
            'notes'           => $request->notes,
        ]);

        return redirect()->route('domains.index')->with('success', 'Domain updated.');
    }

    /**
     * Delete a domain.
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();

        return redirect()->route('domains.index')->with('success', 'Domain deleted.');
    }
}