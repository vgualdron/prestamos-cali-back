<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Listing extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id_collector',
        'user_id_leader',
        'user_id_authorized',
        'user_id',
        'city_id',
        'order',
        'status'
    ];

   public function userCollector() {
    return $this->hasOne(User::class, 'id', 'user_id_collector');
   }

   public function userLeader() {
    return $this->hasOne(User::class, 'id', 'user_id_leader');
   }

   public function userAuthorized() {
    return $this->hasOne(User::class, 'id', 'user_id_authorized');
   }

   public function lendings()
   {
       return $this->hasMany(Lending::class, 'listing_id', 'id');
   }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];
}
