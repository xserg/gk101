<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DivisionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class DivisionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DivisionCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Division::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/division');
        CRUD::setEntityNameStrings('Подразделене', 'Подразделения');
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
      if (! $this->crud->getRequest()->has('order')){
          $this->crud->orderBy('id');
      }
        CRUD::column('department')->label('Департамент');
        CRUD::column('institution')->label('Учереждение');
        CRUD::column('group_id')->label('Группа');
        CRUD::column('name')->label('Название');

        CRUD::column([
            'name'     => 'staff',
            'label'    => 'Работники',
            'type'     => 'custom_html',
            'value'    => 'Работники',
            'wrapper' =>
            [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('staff/?division_id=' . $entry['id']);
                },
            ],
        ]);


        CRUD::column([
            'name'     => 'result',
            'label'    => 'Результат',
            'type'     => 'custom_html',
            'value'    => 'Результат',
            'wrapper' =>
            [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('division-result/?file=podrazdelenia&division_id=' . $entry['id']);
                },
            ],
        ]);

        CRUD::column([
            'name'     => 'pump',
            'label'    => 'ПУМП',
            'type'     => 'custom_html',
            'value'    => 'ПУМП',
            'wrapper' =>
            [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('pump/?division_id=' . $entry['id']);
                },
            ],
        ]);
        //CRUD::column('code')->label('Код');
        if(!backpack_user()->hasRole('admin')) {
            CRUD::removeAllButtons();
        }//CRUD::setFromDb(); // set columns from db columns.

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

        CRUD::field('department')->label('Департамент');
        CRUD::field('institution')->label('Учереждение');
        CRUD::field('group_id')->label('Группа');
        CRUD::field([   // Text
          'name'  => 'name',
          'label' => "Название",//__('messages.name'),//"Название",
          'type'  => 'text',
        ]);
        CRUD::field([   // Text
          'name'  => 'code',
          'label' => "Код",//__('messages.name'),//"Название",
          'type'  => 'text',
        ]);

        $this->crud->setValidation([
            'institution' => 'required',
            'department' => 'required',
            'name' => 'required|min:2',
            'code' => 'required',
        ]);

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
        $this->setupListOperation();
    }
}
