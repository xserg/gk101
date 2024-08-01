<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Registry;

class Reglog extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'reglog';
    //protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'user_id', 'doc_id')->withDefault();
    }
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function getNames()
    {
      if ($this->user_id == 1) {
          return 'admin';
      }
      return $this->staff->lastname . ' ' . mb_substr($this->staff->name, 0, 1) . '. ' . mb_substr($this->staff->fathername, 0, 1) . '.';// . '</a>';
    }

    public function getCount()
    {
        $res = Registry::selectRaw('count(*) as count')
        ->where('user_id', $this->doc_id)
        ->whereraw('MONTH(created_at)='.$this->month)
        ->whereraw('YEAR(created_at)='.$this->year)
        ->first();
        return $res;
    }
}
