<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
//implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    use CrudTrait;
    use HasRoles;

      protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class)->withDefault();
    }

    public function getNameWithLink()
    {
      //return '<a href=" ' . url('division-results/?tabel_num=' . $this->tabel_num) . '" target="_blank">'
      return $this->staff->lastname . ' ' . $this->staff->name . ' ' . $this->staff->fathername;// . '</a>';
    }

    public static function getListName($user)
    {
      return $user->staff->lastname . ' ' . mb_substr($user->staff->name, 0, 1) . '. ' . mb_substr($user->staff->fathername, 0, 1) . '.';
    }

    public static function getDocs($division_id = null)
    {
        $users = User::role('medic')->get();

        foreach ($users as $user) {
          if ($user->staff->lastname) {
            if ($division_id && $division_id != $user->staff->division_id) {
                continue;
            }
            $ret[$user->id] = self::getListName($user);
            //$user->staff->lastname;
          } //$this->getNameWithLink();
        }
        return $ret;
    }
}
