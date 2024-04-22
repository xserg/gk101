<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionResult extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'division_results';
    // protected $primaryKey = 'id';
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

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'tabel_num', 'tabel_num')->select('tabel_num', 'lastname', 'name', 'fathername');
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

    public function csvRow($row)
    {

      $this->updateOrCreate([
        'year' => $row['year'],
        'month' => $row['month'],
        'division_id' => $row['division_id'],
        'file' => $row['file'],
        'tabel_num' => $row['tabel_num'],
        'position' => $row['position'],
      ], $row);

      return;
    }
}
