<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FieldRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
//use App\Models\Role;
use App\Models\Field;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Class FieldCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FieldCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Field::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/field');
        CRUD::setEntityNameStrings('field', 'fields');
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
        //CRUD::column('file')->label('Файл');
        CRUD::column('csv_name');
        CRUD::column('field_name');
        CRUD::column('rus_name');
        CRUD::column(
          [
             // run a function on the CRUD model and show its return value
             'name'  => 'file_id',
             'label' => 'Файлы', // Table column heading
             'type'  => 'model_function',
             'function_name' => 'getFiles', // the method in your Model
             // 'function_parameters' => [$one, $two], // pass one/more parameters to that method
             // 'limit' => 100, // Limit the number of characters shown
             // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
          ],
        );
        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
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
        //CRUD::field('file')->label('Файл');
        CRUD::field('csv_name')->hint('csv_name');
        CRUD::field('field_name');
        CRUD::field('rus_name');
        CRUD::field('description');

        CRUD::field('podrazdelenia')->type('checkbox');
        CRUD::field('itog')->type('checkbox');
        CRUD::field('pump')->type('checkbox');
        CRUD::field('rabotniki')->type('checkbox');

        /*
        for ($i = 1; $i < 6; $i++) {
          CRUD::field('role_'.$i)->type('checkbox');
        }
        */
        CRUD::field('group_id');

        $roles = Role::all()->pluck('rus_name', 'id')->toarray();
        //CRUD::field('role_1')->type('checkbox')->label($roles[$i]);

        for ($i = 1; $i < 6; $i++) {
            CRUD::field('role_' . $i)->type('checkbox')->label($roles[$i]);
        }

        Field::saving(function($entry) {
            $permission = Permission::updateOrCreate(
              ['name' => 'division_results.' . $entry->field_name],
              ['name' => 'division_results.' . $entry->field_name],
            );
            for ($i = 1; $i < 6; $i++) {
                $role = Role::find($i);
                $role_name = 'role_' . $i;
                if ($entry->$role_name) {
                  $role->givePermissionTo($permission);
                } else {
                  $role->revokePermissionTo($permission);
                }
            }
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
        $this->setupCreateOperation();
    }
}
