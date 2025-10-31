<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'date_in','deadline','assign_by_id','assign_to_id','type_label',
        'company_id','task','pic_name','product_id','status','remarks',
        'created_by','updated_by',
    ];

    /**
     * Force type-safety even if DB column is varchar.
     * Casting to integer works fine for '1'/'2' strings coming from MySQL.
     */
    protected $casts = [
        'date_in'    => 'date',
        'deadline'   => 'date',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Append computed attributes to JSON/array serialization.
     * This makes is_overdue and derived_status available when you do $item->toArray().
     */
    protected $appends = ['is_overdue', 'derived_status'];

    /**
     * (Optional) Safety net: keep updated_by synced automatically.
     * Controller already sets these, but this makes it resilient.
     */
    protected static function booted(): void
    {
        // 🔴 AUTO CLEAR CACHE - Before any operation
        static::creating(function (Item $item) {
            if (Auth::check()) {
                $item->created_by = $item->created_by ?? Auth::id();
                $item->updated_by = Auth::id();
            }
            self::clearQueryCache();
        });

        // 🔴 AUTO CLEAR CACHE - After created
        static::created(function () {
            self::clearQueryCache();
        });

        // 🔴 AUTO CLEAR CACHE - Before updating
        static::updating(function (Item $item) {
            if (Auth::check()) {
                $item->updated_by = Auth::id();
            }
            self::clearQueryCache();
        });

        // 🔴 AUTO CLEAR CACHE - After updated
        static::updated(function () {
            self::clearQueryCache();
        });

        // 🔴 AUTO CLEAR CACHE - Before deleting
        static::deleting(function () {
            self::clearQueryCache();
        });

        // 🔴 AUTO CLEAR CACHE - After deleted
        static::deleted(function () {
            self::clearQueryCache();
        });
    }

    // 🔴 FUNCTION CLEAR QUERY CACHE ONLY (Ringan & Aman)
    protected static function clearQueryCache()
    {
        try {
            DB::statement('RESET QUERY CACHE');
        } catch (\Throwable $e) {
            // ignore kalau server tidak izinkan
        }
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ========== Smart, Reusable Helpers ==========

    /**
     * Check if this item is overdue.
     * Uses Laravel's accessor pattern so you can call $item->is_overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        // No deadline? Not overdue.
        if (!$this->deadline) return false;

        // Use your app timezone so "end of today" logic works as humans expect
        $tz = config('app.timezone', 'Asia/Kuala_Lumpur');

        // Consider it overdue only after the day has fully passed,
        // and only if it's not already completed/cancelled
        $status = strtolower((string) $this->status);
        $isClosed = in_array($status, ['completed', 'done', 'cancelled']);

        return Carbon::parse($this->deadline, $tz)
            ->endOfDay()
            ->lt(Carbon::now($tz)) && !$isClosed;
    }

    /**
     * Get the derived status (shows "Expired" for overdue pending items).
     * Uses Laravel's accessor pattern so you can call $item->derived_status.
     */
    public function getDerivedStatusAttribute(): string
    {
        // If it's overdue and still "pending", show "Expired"
        $status = trim((string) $this->status);
        if ($this->is_overdue && strtolower($status) === 'pending') {
            return 'Expired';
        }
        return $status === '' ? 'Pending' : $status;
    }
}
