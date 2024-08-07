<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zip extends Model
{
    public $table = "zips";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'registered_by',
        'registered_date',
    ];
}
