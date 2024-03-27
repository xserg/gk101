<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'divisions';
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
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
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

    public $csv_labels = [
      'Департамент',
      'Учреждение',
      'Подразделения',
      'Группа',
      'Код'
    ];

    public function checkHead($head)
    {
          foreach ($head as $title) {
              if (!in_array($title, $this->csv_labels)) {
                  echo "Заголовок '" . $title . "' не найден!";
                  return false;
              }
          }
          return true;
    }

    public function csvRow($row)
    {
      $update = [
        'name' => $row['Подразделения'],
        'group_id' => $row['Группа'],
        'code' => $row['Код'],
        'department_id' =>   1,
        'institution_id' =>   1,
      ];
      $this->updateOrCreate(['code' => $row['Код']], $update);
      //$this->updateOrCreate($update);
    }
}
