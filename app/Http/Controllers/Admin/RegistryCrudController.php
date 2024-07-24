<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RegistryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Registry;
use App\Models\User;
use Carbon\Carbon;

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

    use \App\Traits\CrudPermissionTrait;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Registry::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/registry');
        CRUD::setEntityNameStrings('беременную', 'Реестр беременных');
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
        //CRUD::column('division')->label(__('validation.attributes.division'));
        CRUD::column('lastname')->label(__('validation.attributes.lastname'));
        CRUD::column('name')->label(__('validation.attributes.name'));
        CRUD::column('fathername')->label(__('validation.attributes.fathername'));
        CRUD::column('email')->label('Email');
        CRUD::column('polis')->label('Полис');
        CRUD::column('birthdate')->type('date')->label('День рождения');
        //CRUD::field('pregnancy_start')->type('date')->label('Начало беременности');
        //CRUD::field('phone')->label('Телефон');
        //CRUD::field('address')->label('Адрес');


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
        CRUD::setValidation(['polis' => 'required|digits:16|unique:registry']);

        Registry::creating(function($entry) {
            $entry->user_id = backpack_user()->id;
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
        if (backpack_user()->hasRole('admin')) {
          CRUD::field(
          [
               'name'  => 'user_id',
               'label' => 'Врач', // Table column heading
               'type'  => 'select_from_array',
               'options'     => User::getDocs(),
          ]);
          CRUD::field('division')->label(__('validation.attributes.division'));
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
          //'pregnancy_start' => 'required',
      ]);
    }
}
