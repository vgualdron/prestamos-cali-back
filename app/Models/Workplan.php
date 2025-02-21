<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workplan extends Model
{
    public $table = "workplans";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'step_id',
        'listing_id',
        'status',
        'registered_date',
        'approved_date',
        'registered_by',
        'approved_by',
    ];
}
