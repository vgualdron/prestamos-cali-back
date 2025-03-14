<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class Lending extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nameDebtor',
        'address',
        'phone',
        'firstDate',
        'endDate',
        'amount',
        'amountFees',
        'percentage',
        'period',
        'order',
        'status',
        'listing_id',
        'expense_id',
        'new_id',
        'created_at',
        'type',
        'has_double_interest',
        'doubleDate',
        'step',
        'date_step',
    ];

    public function payments() {
       return $this->hasMany(Payment::class, 'lending_id', 'id');
    }

    public function discounts() {
       return $this->hasMany(Discount::class, 'lending_id', 'id');
    }

    public function reddirections() {
       return $this->hasMany(Reddirection::class, 'lending_id', 'id');
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        static::updated(function ($lending) {
            if ($lending->isDirty('nameDebtor')) { // Solo si cambia el nameDebtor
                $newName = $lending->nameDebtor;
                $newId = $lending->new_id;

                Lending::withoutEvents(function () use ($newId, $newName) {
                    $lendings = Lending::where('new_id', $newId)->get();

                    foreach ($lendings as $l) {
                        $l->timestamps = false; // Desactiva temporalmente timestamps
                        $l->nameDebtor = $newName;
                        $l->save();
                    }
                });

                // Actualizar el nombre en la tabla news
                Novel::where('id', $newId)->update(['name' => $newName]);
            }

            if ($lending->isDirty('step')) {
                DB::table('lendings')
                    ->where('id', $lending->id)
                    ->update([
                        'date_step' => now(),
                        'updated_at' => $lending->getOriginal('updated_at')
                    ]);
            }

        });
    }
}
