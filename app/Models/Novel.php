<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novel extends Model
{
    public $table = "news";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'document_number',
        'name',
        'phone',
        'address',
        'sector',
        'district',
        'occupation',
        'observation',
        'status',
        'user_send',
        'client_id',
        'attempts',
        'family_reference_document_number',
        'family_reference_name',
        'family_reference_address',
        'family_reference_phone',
        'family_reference_relationship',
        'personal_reference_document_number',
        'personal_reference_name',
        'personal_reference_address',
        'personal_reference_phone',
        'personal_reference_relationship',
        'guarantor_document_number',
        'guarantor_name',
        'guarantor_address',
        'guarantor_phone',
        'guarantor_relationship',
        'facebook',
        'quantity',
        'house',
        'period',
        'lent_by',
        'approved_by',
        'approved_date',
        'made_by',
    ];
}
