<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\Staff;
use App\Models\File;
use App\Models\Field;
use App\Models\Division;
use App\Models\DivisionResult;
use App\Models\PumpCategories;
use App\Models\PumpSubCat;
use App\Models\Pump;
/**
 * Class StaffCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UploadController extends CrudController
{
  public function setup()
  {
      CRUD::setModel(\App\Models\Staff::class);
      CRUD::setRoute(config('backpack.base.route_prefix'));
      CRUD::setEntityNameStrings(__('labels.staff'), __('labels.staffs'));
      //$this->setAccessUsingPermissions();
  }

  public function saveUpload(Request $request)
  {
    $validator = $this->validator($request->all())->validate();
    //$request = $this->crud->validateRequest();
    $type = strtolower($_POST['type']);
    $str_arr = file($_FILES['file']['tmp_name']);
    //$csv = array_map('str_getcsv', $str_arr);
    $csv = [];
    foreach ($str_arr as $str) {
        $csv[] = str_getcsv(iconv('CP1251', 'UTF-8', $str), ';');
    }

    //echo '<pre>';
    //print_r($csv);
    //exit;

    if ($csv) {
        $divisions = self::setDepartments();
        $fields = Field::where($type, 1)->pluck('field_name', 'csv_name')->toarray();
        //print_r($fields);
        //exit;

        if (!$this->checkHead($csv[0], array_keys($fields))) {
            $error = 'Ошибка полей!';
            //\Alert::error($error)->flash();
            return \Redirect::to('/admin/upload');
        } else {

            foreach ($csv as $n => $row) {
                if (!$row[0]) {
                  continue;
                }
                foreach ($row as $k => $v) {
                    //echo "$k => $v\n";
                    if ($n == 0) {
                      //$field_nums[$k] = $fields[$v] ?? $v;
                      $field_nums[$k] = $fields[$v] ?? '';
                    } else {
                      $res[$n]['file'] = $type;
                      if ($v && $field_nums[$k] == 'division_id') {
                          $res[$n]['division_id'] = $divisions[trim($v)] ?? 0;
                      } else if ($field_nums[$k]) {
                          $res[$n][$field_nums[$k]] = $v ? strtr($v, ',', '.') : 0;
                      } else {
                        continue;
                      }
                    }
                }
            }
            //print_r($field_nums);
            //print_r($res);
            if ($type == 'pump') {
                $i = $this->savePump($res);
            } else {
                $i = $this->saveResult($res);
            }
            //exit;
        }
    }
    \Alert::success($i." records updated")->flash();
    return redirect('/admin/upload');

  }


  public function uploadForm()
  {

      $this->data['title'] = 'Загрузка файла CSV'; // set the page title
      //$this->crud->hasAccessOrFail('update');
      //$this->crud->hasOperationSetting('showCancelButton');
      $this->crud->allowAccess('upload');
      $this->crud->setOperation('upload');
      $this->data['crud'] = $this->crud;
      // get the info for that entry
      //$this->data['entry'] = $this->crud->getEntry($id);
      $file_arr = File::all()->pluck('name', 'name')->toarray();
      //print_r($file_arr);

      CRUD::field([
        'name' => 'type',
        'label' => 'Тип',
        'attributes' => [
            //'required' => 'required',
        ],
        'type'    => 'select_from_array',
        'options' => $file_arr
          /*[
          'workdata' => 'Итоги',
          'staff' => 'Работники',
          'division' => 'Подразделения',
          'pump' => 'ПУМП',
          ]*/
        ],
    );

      CRUD::field([
        'name' => 'file',
        'label' => 'Файл',
        'type' => 'upload',
        'attributes' => [
            'required' => 'required',
        ],
      ]);
      /*
      ->withFiles([
          'disk' => 'public', // the disk where file will be stored
          'path' => 'uploads', // the path inside the disk where file will be stored
      ]);
      */
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
      $error = '';
      //$csv_labels = Field::where('file_id', $file_id)->pluck('cvs_name', 'field_name')->toarray();
        foreach ($csv_labels as $title) {
            if (!in_array($title, $head)) {
                $error = "\nЗаголовок '" . $title . "' не найден!";
                \Alert::error($error)->flash();
            }
        }
        if ($error) {
            //echo $error;
            //\Alert::error($error)->flash();
            //\Alert::add('error', $error)->flash();
            return false;
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

  public function setDepartments()
  {
    return Division::all()->pluck('id', 'name')->toarray();
  }

  public function importPumpCategories($name)
  {
      $cat = PumpCategories::updateOrCreate(['name' => $name], ['name' => $name]);
      return $cat->id;
  }

  public function importSubCat($row, $allcat)
  {
      //$cat = PumpCategories::updateOrCreate(['name' => $row['name']], ['name' => $row['name']]);
      $sub_cat = PumpSubсat::updateOrCreate(['name' => $row['pump_subcat_id']],
       [
         'name' => $row['pump_subcat_id'],
         'code' => $row['code'],
         'pump_category_id' => $allcat[$row['name']] ?? 0,
       ]);
       return $sub_cat->id;
  }

  public function imporPump($row)
  {
      //$cat = PumpCategories::updateOrCreate(['name' => $row['name']], ['name' => $row['name']]);
      $sub_cat = Pump::updateOrCreate([
            'year' => $row['year'],
            'month' => $row['month'],
            'division_id' => $row['division_id'],
            'code' => $row['code'],
            'tabel_num' => $row['tabel_num']
          ], $row);

       return $sub_cat->id;
  }

  public function savePump($res)
  {
    set_time_limit(300);
    $allcat = PumpCategories::all()->pluck('id', 'name')->toarray();
    //print_r($allcat);
    $i = 0;
    foreach ($res as $row) {
        if ($row['tabel_num'] == 123456) {
            $row['tabel_num'] = 0;
            //continue;
        }
        $row['pump_subcat_id'] = $this->importSubCat($row, $allcat);
        $this->imporPump($row);
        $i++;
    }
    return $i;
  }

  public function saveResult($res)
  {
    $data_obj = new DivisionResult();
    $i = 0;
    foreach ($res as $row) {
          $data_obj->csvRow($row);
          $i++;
    }
    return $i;
  }

}
