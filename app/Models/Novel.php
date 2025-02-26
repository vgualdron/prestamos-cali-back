<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Novel extends Model
{
    public $table = "news";

    protected function serializeDate(Carbon $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

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
        'address_house',
        'address_house_district',
        'address_work',
        'address_work_district',
        'site_visit',
        'sector',
        'district',
        'occupation',
        'observation',
        'status',
        'user_send',
        'client_id',
        'attempts',
        'family_reference_district',
        'family_reference_name',
        'family_reference_address',
        'family_reference_phone',
        'family_reference_relationship',
        'family2_reference_district',
        'family2_reference_name',
        'family2_reference_address',
        'family2_reference_phone',
        'family2_reference_relationship',
        'guarantor_district',
        'guarantor_document_number',
        'guarantor_occupation',
        'guarantor_name',
        'guarantor_address',
        'guarantor_phone',
        'guarantor_relationship',
        'extra_reference',
        'facebook',
        'quantity',
        'type_house',
        'type_work',
        'period',
        'lent_by',
        'approved_by',
        'approved_date',
        'made_by',
        'visit_start_date',
        'visit_end_date',
        'account_type',
        'account_number',
        'account_type_third',
        'account_number_third',
        'account_name_third',
        'account_active',
        'type_cv',
        'has_letter',
        'who_received_letter',
        'date_received_letter',
        'who_returned_letter',
        'date_returned_letter',
        'score',
        'score_observation',
        'created_at',
    ];
}
