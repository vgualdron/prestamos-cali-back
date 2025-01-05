<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    public $table = "deliveries";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'listing_id',
        'transfers_count',
        'transfers_amount',
        'advances_count',
        'advances_amount',
        'articles_count',
        'articles_amount',
        'renovations_count',
        'renovations_amount',
        'expenses_news_count',
        'expenses_news_amount',
        'expenses_renovations_count',
        'expenses_renovations_amount',
        'collection_secre',
        'collection_street',
        'clients',
        'capital',
        'created_at',
        'updated_at',
    ];
}
