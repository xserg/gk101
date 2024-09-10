<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ReglogRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\User;
use App\Models\Reglog;

/**
 * Class ReglogCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ReglogCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Reglog::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/reglog');
        CRUD::setEntityNameStrings('Целевое значение', 'Целевые значения');
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

      //$this->crud->query->select('id', 'year', 'month', 'reglog.division_id', 'doc_id', 'plan');
      //->selectRaw('count(*) as count, MONTH(registry.created_at) as reg_month')
      //->leftjoin('registry', 'user_id', 'doc_id')
      //->leftjoin('registry', function($join)
      //                   {
      //                       $join->on('user_id', '=', 'doc_id');
      //                       $join->on('reg_month','=', 'month');
      //                   })
      //->having('month', 'reg_month')
      //->groupby('user_id', 'reg_month');

        //CRUD::column('year')->label(__('validation.attributes.year'));
        //CRUD::column('month')->label(__('validation.attributes.month'));
        //CRUD::column('division')->label(__('validation.attributes.division'));
        CRUD::column(
        [
             'name'  => 'doc',
             'label' => 'Врач', // Table column heading
             'type'  => 'model_function',
             'function_name' => 'getNames',
             'orderable'  => true,
             'orderLogic' => function ($query, $column, $columnDirection) {
                     return $query->orderBy('doc_id', $columnDirection);
             }
        ]);
        CRUD::column('doc')->label('Врач');
        CRUD::column('plan')->label('План');
        //CRUD::column('reg_month');
        //CRUD::column('count')->label('Факт');
        CRUD::column(
        [
             'name'  => 'count',
             'label' => 'Факт', // Table column heading
             'type'  => 'model_function_attribute',
             'function_name' => 'getCount',
             'attribute' => 'count',
             'orderable'  => true,
        ]);

    }

    protected function setupShowOperation()
    {
      CRUD::column('division')->label(__('validation.attributes.division'));
      $this->setupListOperation();
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        //CRUD::field('year')->default(date("Y"))->label(__('validation.attributes.year'));
        //CRUD::field('month')->default(date("m"))->label(__('validation.attributes.month'));

        //CRUD::field('division')->label(__('validation.attributes.division'));
        $this->addFields();

        Reglog::creating(function($entry) {

            if (backpack_user()->hasRole('admin')) {
                $user = User::find($entry->doc_id);
                $entry->division_id = $user->staff->division_id;
            }

            $entry->head_id = backpack_user()->id;
            $entry->division_id = backpack_user()->staff->division_id ?? $entry->division_id;
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
      //CRUD::field('year')->label(__('validation.attributes.year'));
      //CRUD::field('month')->label(__('validation.attributes.month'));
      CRUD::field('division')->label(__('validation.attributes.division'))->attributes(['disabled'    => 'disabled']);
      CRUD::field(
      [
           'name'  => 'doc_id',
           'label' => 'Врач', // Table column heading
           'type'  => 'select_from_array',
           'options'     =>['' => '-'] + User::getDocs(
             backpack_user()->staff->division_id ?? null
           ),
           'attributes' => ['disabled'    => 'disabled']
      ]);
      //CRUD::field('doc')->label('Врач');
      CRUD::field('plan')->label('План');

      //$this->addFields();
    }

    protected function addFields()
    {
      //CRUD::field('division')->label(__('validation.attributes.division'));

      CRUD::field(
      [
           'name'  => 'doc_id',
           'label' => 'Врач', // Table column heading
           'type'  => 'select_from_array',
           'options'     =>['' => '-'] + User::getDocs(
             backpack_user()->staff->division_id ?? null
           ),
      ]);
      //CRUD::field('doc')->label('Врач');
      CRUD::field('plan')->label('План');



      CRUD::setValidation([
          //'division' => 'required',
          //'year' => 'required|min:3',
          //'month' => 'required',
          'doc_id' => 'required',
          'plan' => 'required|numeric',

      ]);
    }
}
