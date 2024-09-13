<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\WatchedRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;
use App\Models\Watched;

/**
 * Class WatchedCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class WatchedCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    use \App\Traits\CrudPermissionTrait;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Watched::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/watched');
        CRUD::setEntityNameStrings('Просмотр', __('labels.watched'));
        $this->setAccessUsingPermissions();
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

        $tableName = $this->crud->query->getModel()->getTable();
        $month = $this->crud->getRequest()->month ?? '';

        if ($month){
            $month_arr = explode('-', $this->crud->getRequest()->month);
            //CRUD::whereraw('MONTH(created_at)='.$month_arr[1]);
            //CRUD::whereraw('YEAR(created_at)='.$month_arr[0]);
            //CRUD::addClause('where', 'year', $month_arr[0]);
            //CRUD::addClause('where', 'month', $month_arr[1]);
            //CRUD::addClause('whereraw', 'YEAR(' . $tableName . '.created_at)='.$month_arr[0]);
            //CRUD::addClause('whereraw', 'MONTH('. $tableName . '.created_at)='.$month_arr[1]);
        }

        $this->crud->query->select(
          '*',
          'registry.user_id',
          'registry.division_id',
          $tableName . '.created_at',
          //'user_to_list_watched_lectures.created_at'
          )
        //->selectRaw('count(*) as count')
        ->join(env('DB_DATABASE_MAMAVIP') . '.users as mamas', 'mamas.id', 'user_id')
        ->join(env('DB_DATABASE') . '.registry as registry', 'mamas.polis', 'registry.polis');

        //$this->crud->query->groupby('division_id');
        //CRUD::column('division_id')->label(__('labels.division'));
        //CRUD::column('count')->label('Всего');

        //$this->crud->query->groupby('division_id', 'registry.user_id');
      //  ->join('institutions', 'divisions.institution_id', 'institutions.id')

        if ($this->crud->getRequest()->division_id) {
            $this->crud->query->selectRaw('count(*) as count');
            $this->crud->query->where('division_id', $this->crud->getRequest()->division_id);
            $this->crud->query->groupby('division_id', 'registry.user_id');
            CRUD::column('division_id')->label(__('labels.division'));
            CRUD::column('count')->label(__('labels.watched'));
            //CRUD::column('user.staff.lastname')->label(__('Врач'));
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
            CRUD::column([
                'name'     => 'more',
                'label'    => '',
                'type'     => 'custom_html',
                'value'    => 'Видео',
                'wrapper' =>
                [
                    'href' => function ($crud, $column, $entry, $related_key) {
                        return backpack_url('watched/?user_id='. $entry['user_id']
                        . '&month=' . $crud->getRequest()->month);
                        //. '&month=' . $entry['year'] . '-'
                        //. ($entry['month'] < 10 ? '0' : '') . $entry['month']);
                    },
                ],
            ]);
        } else if ($this->crud->getRequest()->user_id) {
          CRUD::column('division_id')->label(__('labels.division'));
          $this->crud->query->where('registry.user_id', $this->crud->getRequest()->user_id);
          //$this->crud->query->groupby('division_id', 'registry.user_id');
          //CRUD::column('user.staff.lastname')->label(__('Врач'));
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
               'name'  => 'lastname',
               'label' => __('validation.attributes.lastname'), // Table column heading
               'type'  => 'model_function',
               'function_name' => 'getPatient',
               'orderable'  => true,
               'orderLogic' => function ($query, $column, $columnDirection) {
                       return $query->orderBy('user_id', $columnDirection);
               }
          ]);


          //CRUD::column('lastname')->label(__('validation.attributes.lastname'));

          CRUD::column('lecture.title')->label('Название');
          CRUD::column('created_at')->type('date')->label('Дата');
          CRUD::allowAccess('export');
        } else {
          $this->crud->query->selectRaw('count(*) as count');
          $this->crud->query->groupby('division_id');

          CRUD::column('division')->label(__('labels.division'));
          CRUD::column('count')->label(__('labels.watched'));
          CRUD::column([
              'name'     => 'more',
              'label'    => '',
              'type'     => 'custom_html',
              'value'    => 'Подробнее',
              'wrapper' =>
              [
                  'href' => function ($crud, $column, $entry, $related_key) {
                      return backpack_url('watched/?division_id='. $entry['division_id']
                        . '&month=' . $crud->getRequest()->month);

                  },
              ],
          ]);

        }


        CRUD::removeAllButtons();

        CRUD::button('date')->stack('top')->view('crud::buttons.date')->meta(['month' => $month ?? '']);
        CRUD::allowAccess('date');

        CRUD::button('export')->stack('bottom')
        ->view('crud::buttons.quick')

        ->meta([
            'wrapper' => [
                'href' => function ($entry, $crud) {
                    return backpack_url("watched/export?".$this->crud->getRequest()->getQueryString());
                },
            ],
        ]);
        //->meta(['month' => $month ?? '']);
        //CRUD::allowAccess('export');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupExportRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/export', [
            'as'        => $routeName.'.setupExportRoutes',
            'uses'      => $controller.'@export',
            'operation' => 'export',
        ]);
    }

    public function export()
    {
        //echo 'Export...';

        $this->setupListOperation();
        $this->crud->query->join(env('DB_DATABASE_MAMAVIP') . '.lectures', 'lectures.id', 'lecture_id');

        if (backpack_user()->hasRole('head_division')) {
            //$this->crud->query->where('registry.division_id', backpack_user()->staff->division_id);
        } else if (backpack_user()->hasRole('medic')) {
            $this->crud->query->where('registry.user_id', $this->crud->getRequest()->user_id);
        }

        $watched = $this->crud->query->get()->toarray();

        $ret = '';
        foreach ($watched as $row) {
            $ret .= $row['division_id'] . ','
            . $row['lastname'] . ','
            . $row['name'] . ','
            . $row['fathername'] . ','
            . $row['polis'] . ','
            . $row['birthdate'] . ','
            . $row['email'] . ','
            . $row['phone'] . ','
            . $row['address'] . ','
            //. $row['pregnancy_start'] . ','
            . $row['created_at'] . ','
            . $row['title'] . "\n";
        }

        //echo '<pre>';
        //print_r($watched);
        //echo $ret;
        //exit;
        $title =
        "Подразделение, Фамилия, Имя, Отчество, Полис, Дата рождения, Емайл, Телефон, Адрес, Дата промотра, Название видео\n";


        if ($ret) {
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=export_file.csv");
            echo $title . $ret;
        } else {
          echo 'no data';
        }
        exit;

    }
}
