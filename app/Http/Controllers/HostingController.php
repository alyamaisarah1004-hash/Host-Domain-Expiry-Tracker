<?php

namespace App\Http\Controllers;

use App\Models\Hosting;
use App\Models\Domain;
use App\Models\ReminderRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HostingController extends Controller
{
    /**
     * Show the hosting management page.
     */
    public function index(Request $request)
    {
        $query = Hosting::with('creator');

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
                $q->where('hosting_name', 'like', "%{$search}%")
                  ->orWhere('hosting_provider', 'like', "%{$search}%")
                  ->orWhere('plan', 'like', "%{$search}%");
            });
        }

        // Focus mode: when arriving from a bell item
        $focusId = $request->input('focus');

        if ($focusId) {
            $query->where('hosting_id', $focusId);

            // Record this stage as read
            $this->recordRead($focusId);
        }

        $hosting = $query->orderBy('expiry_date', 'asc')->get();

        // Counts for the status tabs
        $counts = [
            'all'     => Hosting::count(),
            'active'  => Hosting::where('expiry_date', '>', $today->copy()->addDays(30)->toDateString())->count(),
            'month'   => Hosting::whereBetween('expiry_date', [
                            $today->copy()->addDays(8)->toDateString(),
                            $today->copy()->addDays(30)->toDateString(),
                         ])->count(),
            'week'    => Hosting::whereBetween('expiry_date', [
                            $today->toDateString(),
                            $today->copy()->addDays(7)->toDateString(),
                         ])->count(),
            'expired' => Hosting::where('expiry_date', '<', $today->toDateString())->count(),
        ];

        // ---------- Bell items ----------
        $rawBellItems = $this->bellItems();
        $bellItems    = $this->addReadFlags($rawBellItems);
        $unreadCount  = collect($bellItems)->where('read', false)->count();

        return view('hosting', compact('hosting', 'counts', 'bellItems', 'unreadCount'));
    }

    /**
     * Record that this user has read the current stage of a hosting reminder.
     */
    private function recordRead(int $hostingId): void
    {
        $hosting = Hosting::find($hostingId);
        if (!$hosting) return;

        $stage = $this->stageBucket($hosting->status);
        if (!$stage) return;

        ReminderRead::firstOrCreate([
            'user_id'     => Auth::id(),
            'record_type' => 'hosting',
            'record_id'   => $hosting->hosting_id,
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
     * Store a new hosting record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hosting_name'     => ['required', 'string', 'max:255'],
            'hosting_provider' => ['required', 'string', 'max:150'],
            'plan'             => ['nullable', 'string', 'max:150'],
            'expiry_date'      => ['required', 'date'],
            'cost'             => ['nullable', 'numeric', 'min:0'],
            'auto_renew'       => ['nullable', 'boolean'],
            'notes'            => ['nullable', 'string'],
        ], [
            'hosting_name.required'     => 'Hosting name is required.',
            'hosting_provider.required' => 'Provider is required.',
            'expiry_date.required'      => 'Expiry date is required.',
            'expiry_date.date'          => 'Expiry date must be a valid date.',
            'cost.numeric'              => 'Cost must be a number.',
            'cost.min'                  => 'Cost cannot be negative.',
        ]);

        Hosting::create([
            'hosting_name'     => $request->hosting_name,
            'hosting_provider' => $request->hosting_provider,
            'plan'             => $request->plan,
            'expiry_date'      => $request->expiry_date,
            'cost'             => $request->cost,
            'auto_renew'       => $request->boolean('auto_renew'),
            'notes'            => $request->notes,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('hosting.index')->with('success', 'Hosting added successfully.');
    }

    /**
     * Update an existing hosting record.
     */
    public function update(Request $request, Hosting $hosting)
    {
        $request->validate([
            'hosting_name'     => ['required', 'string', 'max:255'],
            'hosting_provider' => ['required', 'string', 'max:150'],
            'plan'             => ['nullable', 'string', 'max:150'],
            'expiry_date'      => ['required', 'date'],
            'cost'             => ['nullable', 'numeric', 'min:0'],
            'auto_renew'       => ['nullable', 'boolean'],
            'notes'            => ['nullable', 'string'],
        ], [
            'hosting_name.required'     => 'Hosting name is required.',
            'hosting_provider.required' => 'Provider is required.',
            'expiry_date.required'      => 'Expiry date is required.',
            'expiry_date.date'          => 'Expiry date must be a valid date.',
            'cost.numeric'              => 'Cost must be a number.',
            'cost.min'                  => 'Cost cannot be negative.',
        ]);

        $hosting->update([
            'hosting_name'     => $request->hosting_name,
            'hosting_provider' => $request->hosting_provider,
            'plan'             => $request->plan,
            'expiry_date'      => $request->expiry_date,
            'cost'             => $request->cost,
            'auto_renew'       => $request->boolean('auto_renew'),
            'notes'            => $request->notes,
        ]);

        return redirect()->route('hosting.index')->with('success', 'Hosting updated.');
    }

    /**
     * Delete a hosting record.
     */
    public function destroy(Hosting $hosting)
    {
        $hosting->delete();

        return redirect()->route('hosting.index')->with('success', 'Hosting deleted.');
    }
}