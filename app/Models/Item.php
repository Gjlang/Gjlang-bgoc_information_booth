<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
     * (Optional) Safety net: keep updated_by synced automatically.
     * Controller already sets these, but this makes it resilient.
     */
    protected static function booted(): void
    {
        static::creating(function (Item $item) {
            if (Auth::check()) {
                $item->created_by = $item->created_by ?? Auth::id();
                $item->updated_by = Auth::id();
            }
        });

        static::updating(function (Item $item) {
            if (Auth::check()) {
                $item->updated_by = Auth::id();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
