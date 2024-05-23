<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PumpRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Field;
use App\Models\Division;
use App\Models\Staff;

/**
 * Class PumpCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PumpCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Pump::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/pump');
        CRUD::setEntityNameStrings('ПУМП', 'ПУМП');
        $this->setAccessUsingPermissions();

        //$user = request()->user();
        $user = backpack_user();
        //CRUD:: setHeading('ПУМП ' . $user->staff->lastname . ' ' . $user->staff->name);

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if ($this->crud->getRequest()->has('tabel_num')) {
            $pump_user = Staff::where('tabel_num', $this->crud->getRequest()->tabel_num)->first();
            if ($pump_user) {
                CRUD:: setHeading('ПУМП ' . $pump_user->lastname . ' ' . $pump_user->name);
            }
            CRUD::addClause('where', 'tabel_num', $this->crud->getRequest()->tabel_num);
        }
        if ($this->crud->getRequest()->division_id){
            CRUD::addClause('where', 'division_id', $this->crud->getRequest()->division_id);
        }
        if ($month = $this->crud->getRequest()->month){
            //echo $month;
            $month_arr = explode('-', $this->crud->getRequest()->month);
            CRUD::addClause('where', 'year', $month_arr[0]);
            CRUD::addClause('where', 'month', $month_arr[1]);
        }


        CRUD::column('year')->label(__('validation.attributes.year'));
        CRUD::column('month')->label(__('validation.attributes.month'));

        if ($this->crud->getRequest()->institution_id) {
            //$divisions = Division::where('institution_id', $this->crud->getRequest()->institution_id)->pluck('id')->toarray();
            //CRUD::addClause('wherein', 'division_id', $divisions);
        //}
        //$this->crud->select('division_id', 'sum(total)');
        //$this->crud->groupBy('division_id', 'tabel_num');
            //CRUD::groupBy(['division_id']);
            $this->crud->query->select(
              'institutions.name',
              'year', 'month', 'fio',
              'total', 'pump_subcat_id',
              'institutions.id as institution_id',
              'division_id')
            ->selectRaw('sum(quantity) as count, round(sum(total)) as sum')
            ->join('divisions', 'divisions.id', 'pump.division_id')
            ->join('institutions', 'divisions.institution_id', 'institutions.id')
            ->where('institution_id', $this->crud->getRequest()->institution_id);


            if ($this->crud->getRequest()->service) {
              $this->crud->query->select(
                'institutions.name',
                'year', 'month', 'fio',
                'total', 'pump_subcat_id',
                'institutions.id as institution_id',
                'division_id',
                'pump_subcat.name as pump_subcat_name')
                ->selectRaw('sum(quantity) as count, round(sum(total)) as sum');
                $this->crud->query->join('pump_subcat', 'pump_subcat.id', '=', 'pump_subcat_id');
                $this->crud->query->groupby('institutions.id', 'month', 'pump_subcat_id');

                CRUD::column('pump_subcat_name')->label('Услуга')->limit(120)
                ->searchLogic(function ($query, $column, $searchTerm) {
                    $query->orWhere('pump_subcat.name', 'like', '%'.$searchTerm.'%');
                });
                CRUD::column('count')->label('Всего услуг')->orderable(true)
                ->orderLogic(function ($query, $column, $columnDirection) {
                    return $query->orderBy('count', $columnDirection);
                });
                CRUD::column('sum')->label('Сумма')
                ->orderable(true)
                ->orderLogic(function ($query, $column, $columnDirection) {
                    return $query->orderBy('sum', $columnDirection);
                });
            } else {
                $this->crud->query->groupby('institutions.id', 'month')->orderBy('month');
                CRUD::column('name')->label(__('validation.attributes.institution'))->searchLogic('text');
                CRUD::column('count')->label('Всего услуг');
                CRUD::column('sum')->label('Сумма');
                CRUD::column([
                    'name'     => 'pump_subcat_id',
                    'label'    => 'Услуги',
                    'type'     => 'custom_html',
                    'value'    => 'Услуги',
                    'wrapper' =>
                    [
                        'href' => function ($crud, $column, $entry, $related_key) {
                            return backpack_url('pump/?institution_id='. $entry['institution_id']
                            //. '&division_id=' . $entry['division_id']
                            //. '&pump_subcat_id=' . $entry['pump_subcat_id']
                            . '&service=1'
                            . '&month=' . $entry['year'] . '-'
                            . ($entry['month'] < 10 ? '0' : '') . $entry['month']);
                        },
                    ],
                ]);
            }
            CRUD::removeAllButtons();

      // Divisions stat ////////////////////////////////////////////////////
        } else if ($this->crud->getRequest()->division_id) {

          $this->crud->query->select(
            'divisions.name',
            'year', 'month', 'fio',
            'total', 'pump_subcat_id', 'fio', 'tabel_num',
            //'institutions.id as institution_id',
            'division_id')
          ->selectRaw('sum(quantity) as count, round(sum(total)) as sum')
          ->join('divisions', 'divisions.id', 'pump.division_id')
        //  ->join('institutions', 'divisions.institution_id', 'institutions.id')
          ->where('division_id', $this->crud->getRequest()->division_id);

          // Работники статистика
          if ($this->crud->getRequest()->staff) {
            $this->crud->query->select(
              'year', 'month', 'fio',
              'total', 'pump_subcat_id',
              'division_id', 'tabel_num',
              'pump_subcat.name as pump_subcat_name')
              ->selectRaw('sum(quantity) as count, round(sum(total)) as sum');
              $this->crud->query->join('pump_subcat', 'pump_subcat.id', '=', 'pump_subcat_id');
              //$this->crud->query->where('tabel_num', $this->crud->getRequest()->tabel_num);
              $this->crud->query->groupby('tabel_num', 'month');
              //$this->crud->query->groupby('month', 'pump_subcat_id');
              CRUD::column('fio')->label('fio');

              CRUD::column('count')->label('Всего услуг')->orderable(true)
              ->orderLogic(function ($query, $column, $columnDirection) {
                  return $query->orderBy('count', $columnDirection);
              });
              CRUD::column('sum')->label('Сумма')
              ->orderable(true)
              ->orderLogic(function ($query, $column, $columnDirection) {
                  return $query->orderBy('sum', $columnDirection);
              });
              CRUD::column([
                  'name'     => 'pump_subcat_id',
                  'label'    => 'Услуги',
                  'type'     => 'custom_html',
                  'value'    => 'Услуги',
                  'wrapper' =>
                  [
                      'href' => function ($crud, $column, $entry, $related_key) {
                          return backpack_url('pump/?division_id='. $entry['division_id']
                          //. '&division_id=' . $entry['division_id']
                          //. '&pump_subcat_id=' . $entry['pump_subcat_id']
                          . '&service=1'
                          . '&month=' . $entry['year'] . '-'
                          . ($entry['month'] < 10 ? '0' : '') . $entry['month'])
                          . '&tabel_num='.$entry['tabel_num'];
                      },
                  ],
              ]);
              CRUD::removeAllButtons();
            // ПУМП статистика подразделения по месяцам
          }
          // Услуги суммарно
          else if ($this->crud->getRequest()->service) {
            $this->crud->query->select(
              'year', 'month', 'fio',
              'total', 'pump_subcat_id',
              'division_id',
              'pump_subcat.name as pump_subcat_name')
              ->selectRaw('sum(quantity) as count, round(sum(total)) as sum');
              $this->crud->query->join('pump_subcat', 'pump_subcat.id', '=', 'pump_subcat_id');
              //$this->crud->query->where('tabel_num', $this->crud->getRequest()->tabel_num);
              //$this->crud->query->groupby('tabel_num', 'month', 'pump_subcat_id');
              $this->crud->query->groupby('month', 'pump_subcat_id');

              CRUD::column('pump_subcat_name')->label('Услуга')->limit(100)
              ->searchLogic(function ($query, $column, $searchTerm) {
                  $query->orWhere('pump_subcat.name', 'like', '%'.$searchTerm.'%');
              });
              CRUD::column('count')->label('Всего услуг')->orderable(true)
              ->orderLogic(function ($query, $column, $columnDirection) {
                  return $query->orderBy('count', $columnDirection);
              });
              CRUD::column('sum')->label('Сумма')
              ->orderable(true)
              ->orderLogic(function ($query, $column, $columnDirection) {
                  return $query->orderBy('sum', $columnDirection);
              });
              CRUD::removeAllButtons();
            // ПУМП статистика подразделения по месяцам
            } else {
                  $this->crud->query->groupby('month')->orderBy('month');
                  CRUD::column('name')->label(__('validation.attributes.institution'))->searchLogic('text');
                  //CRUD::column('fio')->label('fio');
                  CRUD::column('count')->label('Всего услуг');
                  CRUD::column('sum')->label('Сумма');
                  CRUD::column([
                      'name'     => 'pump_subcat_id',
                      'label'    => 'Услуги',
                      'type'     => 'custom_html',
                      'value'    => 'Услуги',
                      'wrapper' =>
                      [
                          'href' => function ($crud, $column, $entry, $related_key) {
                              return backpack_url('pump/?division_id='. $entry['division_id']
                              //. '&division_id=' . $entry['division_id']
                              //. '&pump_subcat_id=' . $entry['pump_subcat_id']
                              . '&service=1'
                              . '&month=' . $entry['year'] . '-'
                              . ($entry['month'] < 10 ? '0' : '') . $entry['month'])
                              ;//. '&tabel_num='.$entry['tabel_num'];
                          },
                      ],
                  ]);

                  CRUD::column([
                      'name'     => 'staff',
                      'label'    => 'Работники',
                      'type'     => 'custom_html',
                      'value'    => 'Работники',
                      'wrapper' =>
                      [
                          'href' => function ($crud, $column, $entry, $related_key) {
                              return backpack_url('pump/?division_id='. $entry['division_id']
                              //. '&division_id=' . $entry['division_id']
                              //. '&pump_subcat_id=' . $entry['pump_subcat_id']
                              . '&staff=1'
                              . '&month=' . $entry['year'] . '-'
                              . ($entry['month'] < 10 ? '0' : '') . $entry['month'])
                              ;//. '&tabel_num='.$entry['tabel_num'];
                          },
                      ],
                  ]);

              }
              CRUD::removeAllButtons();
        // END Divisions stat ////////////////////////////////////////////////////
        } else {
            CRUD::column('division')->label(__('validation.attributes.division'));
            CRUD::column('fio')->label(__('validation.attributes.fio'));
            CRUD::column('total')->label(__('validation.attributes.total'));
            CRUD::column('pumpsubcat')->label('Услуга');
        }

        CRUD::button('date')->stack('top')->view('crud::buttons.date')->meta(['month' => $month ?? '']);
        CRUD::allowAccess('date');

         CRUD::removeButtons([
           'create',
           'update',
           //'delete'
         ]);
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

    protected function setupShowOperation()
    {
        //$user = request()->user();
        $user = backpack_user();
        $fields = Field::where('pump', 1)->pluck('rus_name', 'field_name')->toarray();
        $common_fields = ['year', 'month', 'division_id', 'tabel_num'];
        CRUD::column('pumpsubcat')->label('Услуга');
        foreach ($fields as $field => $label) {
            //CRUD::column($field)->label($label)->hint('help');
            if(in_array($field, $common_fields)
            || $user->can('division_results.' . $field)
            || $user->hasRole('admin')) {
                CRUD::column($field)->label($label);
            }
        }

    }
}
