<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    public $table = "tasks";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'description',
        'observation',
        'status',
        'priority',
        'registered_by',
        'area_id',
        'updated_at',
        'created_at',
    ];
}
