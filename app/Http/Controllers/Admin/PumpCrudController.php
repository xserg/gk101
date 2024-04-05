<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PumpRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Field;
use App\Models\Division;

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

        $user = request()->user();
        CRUD::setHeading($user->staff->lastname . ' ' . $user->staff->name);

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if ($this->crud->getRequest()->has('tabel_num')){
            CRUD::addClause('where', 'tabel_num', $this->crud->getRequest()->tabel_num);
        }
        if ($this->crud->getRequest()->has('division_id')){
            CRUD::addClause('where', 'division_id', $this->crud->getRequest()->division_id);
        }
        if ($month = $this->crud->getRequest()->month){
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
              //'id',
              'institutions.name',
              'year', 'month', 'fio',
              'total')
            ->selectRaw('count(pump.id) as count, round(sum(total)) as sum')
            ->join('divisions', 'divisions.id', 'pump.division_id')
            ->join('institutions', 'divisions.institution_id', 'institutions.id')
            ->where('institution_id', $this->crud->getRequest()->institution_id)
            ->groupby('institutions.id', 'month')->orderBy('month');
            CRUD::column('name')->label(__('validation.attributes.institution'));
            CRUD::column('count')->label('Всего услуг');
            CRUD::column('sum')->label('Сумма');
            CRUD::removeAllButtons();
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
        //CRUD::setFromDb();
        $fields = Field::where('pump', 1)->pluck('rus_name', 'field_name')->toarray();
        //echo '<pre>';
        //print_r($fields);
        CRUD::column('pumpsubcat')->label('Услуга');
        foreach ($fields as $field => $label) {
            CRUD::column($field)->label($label)->hint('help');
        }

    }
}
