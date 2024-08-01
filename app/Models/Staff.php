<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Department;
use App\Models\Institution;
use App\Models\Division;

class Staff extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $connection = 'mysql';

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'staff';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class)->select(['id', 'name', 'rus_name']);
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

    public $departments = [];
    public $institutions = [];
    public $divisions = [];


    public $csv_labels = [
      'Табельный номер',
      'Имя',
      'Фамилия',
      'Отчество',
      'Должность',
      'Уровень пользователя',
      'Группа подразделений',
      'Подразделение'
    ];

    public static function convertArr($arr)
    {
      foreach ($arr as $item) {
          $nameArray[$item['name']] = $item['id'];
      }
      return $nameArray;
    }

    public function setDepartments()
    {
      //$this->departments = self::convertArr(Department::all()->select('id', 'name')->toarray());
      //$this->institutions = self::convertArr(Institution::all()->select('id', 'name')->toarray());
      $this->divisions = self::convertArr(Division::all()->select('id', 'name')->toarray());
    }

    public function csvRow($row)
    {
      $update = [
        'tabel_num' => $row['Табельный номер'],
        'name' => $row['Имя'],
        'lastname' => $row['Фамилия'],
        'fathername' => $row['Отчество'],
        'position' =>   $row['Должность'],
        'role_id' =>   $row['Уровень пользователя'],
        'division_group' =>  $row['Группа подразделений'],
        'division_id' =>  $this->divisions[$row['Подразделение']] ?? 0,
        //'division2_id' =>  $row['Подразделение2'],
        'ot_password' => $row['Пароль'],
        //'' => $row['Наименование учреждения'],
        'rate_num' => $row['Количство ставок'],
      ];
      $this->updateOrCreate($update, ['tabel_num' => $row['Табельный номер']]);
    }

    public function getNameWithLink()
    {
      //return '<a href=" ' . url('division-results/?tabel_num=' . $this->tabel_num) . '" target="_blank">'
      return $this->lastname . ' ' . $this->name . ' ' . $this->fathername;// . '</a>';
    }
}
