<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reddirection extends Model
{
    public $table = "reddirections";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'collector_id',
        'registered_by',
        'registered_date',
        'approved_by',
        'approved_date',
        'lending_id',
        'start_date',
        'end_date',
        'address',
        'district_id',
        'type_ref',
        'description_ref',
        'value',
        'file_id',
        'file2_id',
        'file3_id',
        'status',
        'attended',
        'solution',
        'observation',
    ];
}
