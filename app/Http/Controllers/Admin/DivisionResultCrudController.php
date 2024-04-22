<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DivisionResultRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Field;
use App\Models\Division;
use App\Models\Role;

/**
 * Class DivisionResultCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DivisionResultCrudController extends CrudController
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
        CRUD::setModel(\App\Models\DivisionResult::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/division-result');
        CRUD::setEntityNameStrings('Результат', 'Результаты');
        //CRUD::addClause('where', 'file', 'podrazdelenia');
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
        // set columns from db columns.

        if (!$this->crud->getRequest()->has('order') && !$this->crud->getRequest()->institution_id){
            $this->crud->orderBy('division_results.id');
        }
        if ($this->crud->getRequest()->has('file')){
            CRUD::addClause('where', 'file', $this->crud->getRequest()->file);
        }
        if ($this->crud->getRequest()->has('tabel_num')){
            CRUD::addClause('where', 'tabel_num', $this->crud->getRequest()->tabel_num);
        }
        if ($this->crud->getRequest()->division_id){
            CRUD::addClause('where', 'division_id', $this->crud->getRequest()->division_id);
        }

        if ($month = $this->crud->getRequest()->month){
            $month_arr = explode('-', $this->crud->getRequest()->month);
            CRUD::addClause('where', 'year', $month_arr[0]);
            CRUD::addClause('where', 'month', $month_arr[1]);
        }
        CRUD::column('year')->label(__('validation.attributes.year'));
        CRUD::column('month')->label(__('validation.attributes.month'));

        // Статистика по учереждению
        //if ($this->crud->getRequest()->has('institution_id')) {
        if ($this->crud->getRequest()->institution_id) {

            $this->crud->query->select('division_id', 'year', 'month', 'institutions.name')
            ->selectRaw('round(sum(visit_rate)) sum_visit_rate')
            ->selectRaw('sum(visits) sum_visits')
            ->selectRaw('round(sum(retes_count)) sum_retes_count')
            ->selectRaw('round(sum(no_worked_rates)) sum_no_worked_rates')
            ->selectRaw('sum(pump_account) sum_pump_account')
            ->selectRaw('round(avg(account_hour)) avg_account_hour')
            //->selectRaw('sum(max_account_hour) sum_max_account_hour')
            ->selectRaw('max(account_hour) sum_max_account_hour')
            ->selectRaw('sum(account_reserve) sum_account_reserve')
            //, sum(retes_count) sum_retes_count, sum(no_worked_rates) sum_no_worked_rates, sum(pump_account) sum_pump_account'
            ->join('divisions', 'divisions.id', 'division_results.division_id')
            ->join('institutions', 'divisions.institution_id', 'institutions.id')
            ->where('institution_id', $this->crud->getRequest()->institution_id)
            ->groupby('institutions.id', 'month')->orderBy('month');
            CRUD::column('name')->label(__('validation.attributes.institution'));
            CRUD::column('sum_visit_rate')->label(__('validation.attributes.visit_rate'));
            CRUD::column('sum_visits')->label(__('validation.attributes.visits'));
            CRUD::column('sum_retes_count')->label(__('validation.attributes.retes_count'));
            CRUD::column('sum_no_worked_rates')->label(__('validation.attributes.no_worked_rates'));
            CRUD::column('avg_account_hour')->label(__('validation.attributes.avg_account_hour'));
            CRUD::column('sum_max_account_hour')->label(__('validation.attributes.max_account_hour'));
            CRUD::column('sum_account_reserve')->label(__('validation.attributes.account_reserve'));
            CRUD::removeAllButtons();
        } else {
        //CRUD::column('file')->label(__('validation.attributes.file'));
            CRUD::column('division')->label(__('validation.attributes.division'));
            CRUD::column('staff.lastname')->label(__('validation.attributes.lastname'));
            CRUD::column('staff.name')->label(__('validation.attributes.name'));
            CRUD::column('position')->label(__('validation.attributes.position'));
        }

        //CRUD::button('month')->stack('top')->view('crud::buttons.quick');
        //CRUD::removeAllButtons();
        //CRUD::addButtonFromView('top', 'date', 'crud::buttons.date', 1)->meta(['month' => $month ?? '']);
        CRUD::button('date')->stack('top')->view('crud::buttons.date')->meta(['month' => $month ?? '']);
        //date('Y-m')


        CRUD::allowAccess('date');
        CRUD::removeButtons([
          'create',
          'update',
          //'delete'
        ]);

        // column that shows the parent's first name
        /*
        $this->crud->addColumn([
           'label'     => 'Parent First Name', // Table column heading
           'type'      => 'select',
           'name'      => 'tabel_num', // the column that contains the ID of that connected entity;
           'entity'    => 'staff', // the method that defines the relationship in your Model
           'attribute' => 'name', // foreign key attribute that is shown to user
           'model'     => 'App\Models\Staff', // foreign key model
        ]);

        // column that shows the parent's last name
        $this->crud->addColumn([
           'label'     => 'Parent Last Name', // Table column heading
           'type'      => 'select',
           'name'      => 'tabel_num', // the column that contains the ID of that connected entity;
           'key'       => 'parent_last_name', // the column that contains the ID of that connected entity;
           'entity'    => 'staff', // the method that defines the relationship in your Model
           'attribute' => 'lastname', // foreign key attribute that is shown to user
           'model'     => 'App\Models\Staff', // foreign key model
        ]);
        */
        //CRUD::setFromDb();
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
        CRUD::field('month')->type('month')->label(__('validation.attributes.month'));
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
        $user = request()->user();
        $roles = $user->getRoleNames()->toarray();
        $all_roles = Role::all()->pluck('rus_name', 'id')->toarray();
        $res_roles = array_keys(array_intersect($all_roles, $roles));

        $fields = Field::where($this->crud->getCurrentEntry()->file, 1)->orderby('sort', 'desc')
        ->pluck('rus_name', 'field_name')->toarray();

        CRUD::column('staff.lastname')->label(__('validation.attributes.lastname'));
        CRUD::column('staff.name')->label(__('validation.attributes.name'));
        CRUD::column('staff.fathername')->label(__('validation.attributes.fathername'));

        $common_fields = ['year', 'month', 'division_id', 'tabel_num'];

        foreach ($fields as $field => $label) {
        //foreach ($fields as $field_arr) {
          //print_r($field);
            //$field = $field_arr['field_name'];
            //$label = $field_arr['rus_name'];
            if(in_array($field, $common_fields)
            || $user->can('division_results.' . $field)
            || $user->hasRole('admin')) {
                CRUD::column($field)->label($label);
            }
        }

    }
}
