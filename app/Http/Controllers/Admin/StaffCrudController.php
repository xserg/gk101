<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;
use App\Http\Requests\StaffRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Division;

/**
 * Class StaffCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StaffCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Staff::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/staff');
        CRUD::setEntityNameStrings(__('labels.staff'), __('labels.staffs'));
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

        //$user = backpack_user()->with('staff')->first();
        /*
        $user = backpack_user();
        $staff = Staff::where('user_id', $user->id)->first();
        //echo '<pre>';
        //print_r($staff->toArray());
        if($user->hasRole('head_division')) {
            if (isset($staff->division_id)) {
                CRUD::addClause('where', 'division_id', '=', $staff->division_id);
            }
        }
        */
        if (! $this->crud->getRequest()->has('order')){
            $this->crud->orderBy('id');
        }
        if ($this->crud->getRequest()->has('tabel_num')){
            CRUD::addClause('where', 'tabel_num', $this->crud->getRequest()->tabel_num);
        }
        if ($this->crud->getRequest()->has('division_id')){
            CRUD::addClause('where', 'division_id', $this->crud->getRequest()->division_id);
        }
        if ($this->crud->getRequest()->has('user_id')){
            CRUD::addClause('where', 'user_id', $this->crud->getRequest()->user_id);
        }
        //CRUD::column('user')->label('Пользователь');
        CRUD::column('user.email')->label('Email');
        CRUD::column('lastname')->label(__('validation.attributes.lastname'));
        //CRUD::column('name')->label(__('validation.attributes.name'));
        //CRUD::column('fathername')->label(__('validation.attributes.fathername'));
        //CRUD::column('tabel_num')->label(__('validation.attributes.tabel_num'));
        CRUD::column('division')->label(__('validation.attributes.division'));
        /*
        CRUD::column([
             'name'  => 'name',
             'label' => 'ФИО', // Table column heading
             'type'  => 'model_function',
             'function_name' => 'getNameWithLink', // the method in your Model
             'wrapper'   => [
                  // 'element' => 'a', // the element will default to "a" so you can skip it here
                  'href' => function ($crud, $column, $entry, $related_key) {
                      return backpack_url('division-result/?file=itog&tabel_num=' . $entry['tabel_num']);
                        //'article/'.$related_key.'/show');
                  },
                  // 'target' => '_blank',
                  // 'class' => 'some-class',
              ],
        ]);
        */
        CRUD::column([
            'name'     => 'result',
            'label'    => 'Результат',
            'type'     => 'custom_html',
            'value'    => 'Результат',
            'wrapper' =>
            [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('division-result/?file=itog&tabel_num=' . $entry['tabel_num']);
                    //return backpack_url('division-result/?file=podrazdelenia&division_id=' . $entry['id']);
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
                    return backpack_url('pump/?&tabel_num=' . $entry['tabel_num']);
                },
            ],
        ]);

        CRUD::column('role.rus_name')->label(__('validation.attributes.role_id'));

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
        CRUD::field('division')->label(__('validation.attributes.division'));
        CRUD::field('lastname')->label(__('validation.attributes.lastname'));
        CRUD::field('name')->label(__('validation.attributes.name'));
        CRUD::field('fathername')->label(__('validation.attributes.fathername'));
        CRUD::field('role.rus_name')->label(__('validation.attributes.role_id'));
        CRUD::field('division_group')->label(__('validation.attributes.division_group'));
        CRUD::field('tabel_num')->label(__('validation.attributes.tabel_num'));
        CRUD::field('position')->label(__('validation.attributes.position'));

        CRUD::setValidation([
            'division' => 'required',
            'name' => 'required|min:3',
            'lastname' => 'required|min:3',
            'fathername' => 'required|min:3',
            //'role_id' => 'required',
        ]);

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
        //$this->setupListOperation();
        CRUD::column('user.email')->label('Email');
        CRUD::column('lastname')->label(__('validation.attributes.lastname'));
        CRUD::column('name')->label(__('validation.attributes.name'));
        CRUD::column('fathername')->label(__('validation.attributes.fathername'));
        CRUD::column('tabel_num')->label(__('validation.attributes.tabel_num'));
        CRUD::column('division')->label(__('validation.attributes.division'));
        CRUD::column('role.rus_name')->label(__('validation.attributes.role_id'));
    }
    /*
    protected function setupUploadRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/upload', [
            'as'        => $routeName.'.setupUploadRoutes',
            'uses'      => $controller.'@uploadForm',
            'operation' => 'upload',
        ]);

        Route::post($segment.'/upload', [
            'as'        => $routeName.'.setupUploadRoutes',
            'uses'      => $controller.'@saveUpload',
            'operation' => 'upload',
        ]);
    }
    */
    public function saveUpload(Request $request)
    {
      //$this->validator($request->all())->validate();
      $request = $this->crud->validateRequest();

      $type = $_POST['type'];
      $str_arr = file($_FILES['file']['tmp_name']);
      $csv = array_map('str_getcsv', $str_arr);
      array_walk($csv, function(&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
      });
      $head = array_shift($csv);
      /*
      echo '<pre>';
      echo $type;
      print_r($csv);
      exit;
      */
      switch ($type) {
          case 'staff':
            $data_obj = new Staff();
            $data_obj->setDepartments();
            break;
          case 'division':
              $data_obj = new Division();
              break;
          default:
            return;
      }
      /*
      if ($type == 'staff') {
          $data_obj = new Staff();
          $data_obj->setDepartments();
      }

      if ($type == 'division') {
          $data_obj = new Division();
      }
      */
      if (!$this->checkHead($head, $data_obj->csv_labels)) {
          $error = 'Ошибка полей!';
          //\Alert::error($error)->flash();
          return \Redirect::to($this->crud->route . '/upload');
          //return redirect('/admin/staff/upload');
      }

      $i = 0;
      foreach ($csv as $row) {
            $data_obj->csvRow($row);
            $i++;
      }
      \Alert::success($i." records updated")->flash();
      return redirect('/admin/'.$type);
      //return $i." records updated";
    }


    public function uploadForm()
    {

        $this->data['title'] = 'Загрузка файла CSV'; // set the page title
        $this->crud->hasAccessOrFail('update');
        $this->crud->hasOperationSetting('showCancelButton');
        $this->crud->allowAccess('upload');
        $this->crud->setOperation('upload');
        $this->data['crud'] = $this->crud;
        // get the info for that entry
        //$this->data['entry'] = $this->crud->getEntry($id);

        CRUD::field([
          'name' => 'type',
          'label' => 'Тип',
          'attributes' => [
              'required' => 'required',
          ],
          'type'    => 'select_from_array',
          'options' => [
            'workdata' => 'Итоги',
            'staff' => 'Работники',
            'division' => 'Подразделения',
            'pump' => 'ПУМП',
            ]
          ],
      );

        CRUD::field([
          'name' => 'file',
          'label' => 'Файл',
          'type' => 'upload',
          'attributes' => [
              'required' => 'required',
          ],
        ])
        ->withFiles([
            'disk' => 'public', // the disk where file will be stored
            'path' => 'uploads', // the path inside the disk where file will be stored
        ]);

        return view('vendor.backpack.crud.upload', $this->data);
    }

    //protected function setupUploadDefaults()
    protected function setupUploadOperation()
    {
      CRUD::setValidation([
          'type' => 'required',
          'file' => 'required',
      ]);
    }


    public function checkHead($head, $csv_labels)
    {
          foreach ($csv_labels as $title) {
              if (!in_array($title, $head)) {
                  $error = "Заголовок '" . $title . "' не найден!";
                  \Alert::error($error)->flash();
                  return false;
              }
          }
          return true;
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
          'type' => 'required',
          'file' => 'required',
        ]);
    }


}
