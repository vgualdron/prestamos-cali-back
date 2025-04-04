<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
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
        'end_date',
        'area_id',
        'updated_at',
        'created_at',
    ];
}
