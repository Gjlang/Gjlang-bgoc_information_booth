<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'date_in','deadline','assign_by_id','assign_to_id','type_label',
        'company_id','pic_name','product_id','status','remarks',
        'created_by','updated_by'
    ];

    protected $casts = [
        'date_in'  => 'date',
        'deadline' => 'date',
    ];
}
