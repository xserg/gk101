<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RegistryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Registry;
use App\Models\User;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Exports\RegistryExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class RegistryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RegistryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Winex01\BackpackFilter\Http\Controllers\Operations\FilterOperation;

    use \App\Traits\CrudPermissionTrait;
   
    
    use \Winex01\BackpackFilter\Http\Controllers\Operations\ExportOperation;
       
    // Optional: if you dont want to use the entity/export or user/export convention you can override the export route:

    /*    
    public function exportRoute()
    {
        return route('registry@export'); // if you define a route here then it will use instead of the auto
    }
    */

    public function export() 
    {   
        $this->setupListOperation();
        return Excel::download(new RegistryExport($this->crud->query), 'registry' . '-' . now() . '.xlsx');
    }
    

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Registry::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/registry');
        CRUD::setEntityNameStrings('', 'Реестр беременных');
        //CRUD::setEntityNameStrings('registry', 'Реестр беременных');
        $this->setAccessUsingPermissions();
        $this->crud->allowAccess('filters'); // Allow access
    }

    public function setupFilterOperation()
    {
        $division_id = request()->input('division_id');

        $divisions = DB::table('divisions');

        $user = backpack_user();
        $staff_user = Staff::where('user_id', $user->id)->first();
        
        //echo '<pre>';
        //print_r($staff_user->toArray());
        if($user->hasRole('head_division')) {
            if (isset($staff_user->division_id)) {
                //CRUD::addClause('where', 'division_id', '=', $staff_user->division_id);
                $divisions->where('id', $staff_user->division_id);
                $division_id = $staff_user->division_id;
            }
        }
        
        $divisions_arr =  $divisions->pluck('name', 'id')->toArray();
        //print_r( $divisions_arr);
;

        $staff = DB::table('staff')->distinct()->where('staff.user_id', '!=', '');
        if ($division_id) {
            $staff->where('division_id', $division_id);
        }
        $staff_arr = $staff->orderBy('lastname')
                    ->pluck('lastname', 'user_id')
                    ->toArray();



        $this->crud->field([
            'name' => 'division_id',
            'label' => __('validation.attributes.division'),
            'type' => 'select_from_array',
            
            //'entity'    => 'division',
            // optional - manually specify the related model and attribute
            //'model'     => "App\Models\Division", // related model
            //'attribute' => 'name', // foreign key attribute that is shown to user
            // optional - force the related options to be a custom query, instead of all();
            
            'options'   => $divisions_arr,
            //'allows_null' => false,
            
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
                
        ]);

       $this->crud->field([
            'name' => 'user_id',
            'label' => 'Врач',
            'type' => 'select_from_array',
            
            'options'   => $staff_arr,
            /*
            DB::table('staff')
                    ->where('staff.user_id', '!=', '')
                    ->orderBy('lastname')
                    ->pluck('lastname', 'user_id')->toArray(),
             */   
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
                
        ]);

    $this->crud->field([
            'name' => 'weeks',
            'label' => 'Срок, недель',
            'type' => 'number',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],         
        ]);

     $this->crud->field([
            'name' => 'baby_born',
            'label' => 'Дата родов',
            'type' => 'month',
            //'format' => 'l j F Y',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            
        ]);
    $this->crud->field([
            'name' => 'date_off',
            'label' => 'Дата снятия с учета',
            'type' => 'month',
            //'format' => 'l j F Y',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],  
        ]);

        $this->crud->field([
            'name' => 'polis',
            'label' => 'Полис',
            'type' => 'number',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],         
        ]);

        $this->crud->field([
            'name' => 'snils',
            'label' => 'СНИЛС',
            'type' => 'number',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],         
        ]);

        $this->crud->field([
            'name' => 'expect_born',
            'label' => 'Предполагаемая дата родов',
            'type' => 'month',
            'wrapper' => [ 'class' => 'form-group col-md-6' ],  
        ]);

       //Вывести незакрытые случаи
        $this->crud->field([
            'name' => 'opened',
            'label' => 'Вывести незакрытые случаи',
            'type' => 'checkbox',
            'wrapper' => [ 'class' => 'form-group col-md-6' ],  
        ]); 
    
        $this->crud->field([
            'name' => 'check',
            'label' => 'Проверка',
            'type' => 'checkbox',
            'wrapper' => [ 'class' => 'form-group col-md-6' ],  
        ]);  

        /*
        $this->crud->field([
            'name' => 'date_range',
            'label' => 'Добавлено',
            'type' => 'date_range',
            
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            
            //'default' => ['2019-03-28 01:01', '2019-04-05 02:00'], // default values for start_date & end_date
            'options' => [
                // options sent to daterangepicker.js
                'timePicker' => true,
                'locale' => [
                    'format' => 'dd-mm-yyyy',
                    'language' => 'ru',
                ]
            ]
        ]);
        */
  

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // set columns from db columns.

        //$this->crud->addColumn(
        CRUD::column(
        [
             'name'  => 'staff',
             'label' => 'Врач', // Table column heading
             'type'  => 'model_function',
             'function_name' => 'getNames',
             'orderable'  => true,
             'orderLogic' => function ($query, $column, $columnDirection) {
                     return $query->orderBy('user_id', $columnDirection);
             }
        ]);
        CRUD::column(
        [
             'name'  => 'division',
             'label' => __('validation.attributes.division'),
             'orderable'  => true,
             'orderLogic' => function ($query, $column, $columnDirection) {
                 //return $query->leftJoin('categories', 'categories.id', '=', 'articles.select')
                     return $query->orderBy('division_id', $columnDirection);//->select('articles.*');
             }
        ]);

            // if you use this method closure, validation is automatically applied.
        $this->filterQueries(function ($query) {
    
            $division_id = request()->input('division_id');
            $dates = request()->input('date_range');
            $opened = request()->input('opened');
            $user_id = request()->input('user_id');
            $baby_born = request()->input('baby_born');
            $date_off = request()->input('date_off');
            $weeks = request()->input('weeks');
            $expect_born = request()->input('expect_born');
            $polis = request()->input('polis');
            $snils = request()->input('snils');
            $check = request()->input('check');
            
            

            if ($division_id) {
                $query->where('registry.division_id', $division_id);
            }

            if ($user_id) {
                $query->where('registry.user_id', $user_id);
            }

            if ($weeks) {
                $query->where('weeks', $weeks);
            }

            if ($polis) {
                $query->where('polis', 'like', $polis . '%');
            }

            if ($snils) {
                $query->where('snils', 'like', $snils . '%');
            }

            if ($baby_born) {
                $query->whereMonth('baby_born', Carbon::parse($baby_born)->month);
                $query->whereYear('baby_born', Carbon::parse($baby_born)->year);
            }

           if ($date_off) {
                $query->whereMonth('date_off', Carbon::parse($date_off)->month);
                $query->whereYear('date_off', Carbon::parse($date_off)->year);
            }

            if ($expect_born) {
                $query->whereMonth('expect_born', Carbon::parse($expect_born)->month);
                $query->whereYear('expect_born', Carbon::parse($expect_born)->year);
            }

            if ($dates) {
                $dates = explode('-', $dates);
                $query->where('created_at',  '>=',  Carbon::parse($dates[0])->format('Y-m-d H:i:s'));
                $query->where('created_at',  '<=',   Carbon::parse($dates[1])->format('Y-m-d H:i:s'));        
            }

            if ($opened) {
                //$query->where('baby_born',  '<=',  Carbon::today()->format('Y-m-d'));
                //$query->where('expect_born',  '<=',  Carbon::today()->subDays(50)->format('Y-m-d'));
                $query->whereRaw('(baby_born <= "' . Carbon::today()->format('Y-m-d') 
                . '" OR expect_born <= "' . Carbon::today()->subDays(50)->format('Y-m-d') . '")');
            }

            if ($check) {
                $query->where('check', 1);
            }
        });


        //CRUD::column('division')->label(__('validation.attributes.division'));
        CRUD::column('lastname')->label(__('validation.attributes.lastname'));
        CRUD::column('name')->label(__('validation.attributes.name'));
        CRUD::column('fathername')->label(__('validation.attributes.fathername'));
        //CRUD::column('email')->label('Email');
        CRUD::column('polis')->label('Полис');
        //CRUD::column('birthdate')->type('date')->label('День рождения');
        CRUD::column('created_at')->type('date')->label('Добавлено');
        //CRUD::field('pregnancy_start')->type('date')->label('Начало беременности');
        //CRUD::field('phone')->label('Телефон');
        //CRUD::field('address')->label('Адрес');

        CRUD::allowAccess('export');

       /*
            CRUD::setAccessCondition('update', function ($entry) {
                //return $entry->user_id === backpack_user()->id; // Only owner can update
                return $entry->check ? false : true;
            });
        */
        

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        //CRUD::setFromDb(); // set fields from db columns.
        //$user = backpack_user();
        //CRUD::field('division')->label(__('validation.attributes.division'))->value($user->staff->division_id);

        if (backpack_user()->hasRole('head_division')) {
          CRUD::field(
          [
               'name'  => 'user_id',
               'label' => 'Врач', // Table column heading
               'type'  => 'select_from_array',
               'options'     => User::getDocs(
                 backpack_user()->staff->division_id
               ),
          ]);
        }

        $this->addFields();
        CRUD::setValidation(['polis' => 'required|digits:16']);

        Registry::creating(function($entry) {
            if (!$entry->user_id) {
                $entry->user_id = backpack_user()->id;
            }
            $entry->division_id = backpack_user()->staff->division_id;

            $entry->pregnancy_start = Carbon::now()->subWeeks($entry->weeks);// - $entry->weeks;
        });
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $entry = $this->crud->getCurrentEntry(); 
        //print_r($entry);
        
        if ($entry->check) {

            //echo 'AAA';

        }
        
        if (backpack_user()->hasRole('admin')) {
          CRUD::field(
          [
               'name'  => 'user_id',
               'label' => 'Врач', // Table column heading
               'type'  => 'select_from_array',
               'options'     => User::getDocs(),
          ]);
          CRUD::field('division')->label(__('validation.attributes.division'));

            if(backpack_user()->hasRole('admin') || backpack_user()->hasRole('head_institution')) {
                CRUD::field(
                    [
                        'name'  => 'check',
                        'label' => 'Проверка', // Table column heading
                        'type'  => 'checkbox',
                        //'options'     => User::getDocs(),
                    ]);
            } else {
                if ($entry->check) {
                    $this->crud->denyAccess('update');
                }
            }

            //$this->crud->denyAccess('update');
        }

        if (backpack_user()->hasRole('head_division')) {
          CRUD::field(
          [
               'name'  => 'user_id',
               'label' => 'Врач', // Table column heading
               'type'  => 'select_from_array',
               'options'     => User::getDocs(
                 backpack_user()->staff->division_id
               ),
          ]);
        }        

        $this->addFields();
        //$this->setupCreateOperation();
    }

    protected function addFields()
    {
      CRUD::field('lastname')->label(__('validation.attributes.lastname'));
      CRUD::field('name')->label(__('validation.attributes.name'));
      CRUD::field('fathername')->label(__('validation.attributes.fathername'));
      CRUD::field('polis')->label('Полис');
      CRUD::field('birthdate')->type('date')->label('День рождения');
      //CRUD::field('pregnancy_start')->type('date')->label('Начало беременности');
      CRUD::field('phone')->label('Телефон');
      CRUD::field('address')->label('Адрес');
      CRUD::field('weeks')->label('Срок беременности');
      CRUD::field('baby_born')->type('date')->label('Дата родов');
      CRUD::field('roddom')->label('Где прошли роды');
      CRUD::field('pregnancy_num')->label('Какая по счету беременность');
      CRUD::field('born_num')->label('Какие по счету роды');
      CRUD::field('expect_born')->type('date')->label('Предполагаемая дата родов');
      CRUD::field('snils')->label('СНИЛС');
      CRUD::field('date_off')->type('date')->label('Дата снятия с учета');
      CRUD::field('extra')->label('Дополнительная информация');

      CRUD::setValidation([
          //'division' => 'required',
          'name' => 'required|min:3',
          'lastname' => 'required|min:3',
          'fathername' => 'required|min:3',
          //'email' => 'required|email',
          'polis' => 'required|digits:16',
          'weeks' => 'required|numeric|max:41',
          'birthdate' => 'required',
          'address' => 'required',
          'expect_born' => 'required',
          'snils' => 'nullable|digits:11', 

          //'pregnancy_start' => 'required',
      ]);
    }

    /*
    protected function setupExportRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/export', [
            'as'        => $routeName.'.setupExportRoutes',
            'uses'      => $controller.'@export',
            'operation' => 'export',
        ]);
    }
    */
    public function export_csv()
    {
        //echo 'Export...';

        $this->setupListOperation();
       //$this->crud->query->join(env('DB_DATABASE_MAMAVIP') . '.lectures', 'lectures.id', 'lecture_id');


        
        if (backpack_user()->hasRole('head_division')) {
            $this->crud->query->where('registry.division_id', backpack_user()->staff->division_id);
        } else if (backpack_user()->hasRole('medic')) {
            $this->crud->query->where('registry.user_id', $this->crud->getRequest()->user_id);
        } else if (backpack_user()->hasRole('admin')) {
            //
        }

        $title =  "Подразделение, Фамилия, Имя, Отчество, Email, Полис, Дата рождения, Срок, Начало, Рождение, Роддом, " 
        . "Номер беременности, Детей, Дата снятия, Телефон, Адрес, Дополнительно, Проверка, Ожидаемая дата, СНИЛС, Дата добавления, Дата изменения\n";
    

        $watched = \App\Models\Registry::select(
            [
                 'divisions.name as division_name', 'lastname', 'registry.name', 'fathername', 'email', 'polis', 'birthdate', 'weeks', 'pregnancy_start', 'baby_born', 
                'roddom', 'pregnancy_num','born_num', 'date_off', 'phone', 'address', 'extra', 'check', 'expect_born', 'snils', 'created_at', 'updated_at'
            ]
        )->join('divisions', 'division_id', '=', 'divisions.id')->get()->toarray();
     
        //echo '<pre>';
        //print_r($watched);
        //exit;
  

        if ($watched) {
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=export_file.csv");
            echo $title;
        } else {
          echo 'no data';
        }
        $fp = fopen('php://output', 'w');
        // Write each row to the CSV file
        foreach ($watched as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        exit;

    }    
}
