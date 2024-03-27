<?php

namespace App\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Staff;
use App\Models\Division;

/**
 * CrudPermissionTrait: use Permissions to configure Backpack
 */
trait CrudPermissionTrait
{
    // the operations defined for CRUD controller
    public array $operations = ['list', 'show', 'create', 'update', 'delete'];


    /**
     * set CRUD access using spatie Permissions defined for logged in user
     *
     * @return void
     */
    public function setAccessUsingPermissions()
    {
      $user = request()->user();
      if ($user->hasRole('admin')) {
            return;
      }

      //$user->staff = Staff::where('user_id', $user->id)->with('division')->first()->toarray();
      //->with('institution')->with('department')->
      //echo '<pre>';
      //print_r($user->staff->division['institution_id']);
      //echo $user->staff['division_id'];
        // default
        $this->crud->denyAccess($this->operations);


        // get context
        $table = CRUD::getModel()->getTable();

        $tables = ['staff', 'divisions', 'division_results', 'pump'];
        if (!in_array($table, $tables)) {
            return;
        }

        if($user->hasRole('head_institution')) {
            if ($value = $user->staff->division['institution_id']) {
                $divisions = Division::where('institution_id', $value)->pluck('id')->toarray();
                if ($table != 'divisions') {
                    CRUD::addClause('wherein', 'division_id', $divisions);
                } else {
                    CRUD::addClause('where', 'institution_id', $value);
                }
            }
        } else if($user->hasRole('head_group')) {

            if ($value = $user->staff['division_group']) {
                $divisions = Division::where('group_id', $value)->pluck('id')->toarray();
                if ($table != 'divisions') {
                    CRUD::addClause('wherein', 'division_id', $divisions);
                } else {
                    CRUD::addClause('where', 'group_id', $value);
                }
                //CRUD::addClause('where', 'division_group', '=', $user->staff['division_group']);
            }
        } else if($user->hasRole('head_division')) {

            if ($value = $user->staff['division_id']) {
              if ($table != 'divisions') {
                  CRUD::addClause('where', 'division_id', $value);
              } else {
                  CRUD::addClause('where', 'id', $value);
              }
                //CRUD::addClause('where', 'division_id', '=', $user->staff['division_id']);
            }
        } else if($user->hasRole('medic')) {
            $tables = ['staff', 'division_results', 'pump'];
            if (!in_array($table, $tables)) {
                return;
            }
            if ($value = $user->staff['tabel_num']) {
                CRUD::addClause('where', 'tabel_num', $value);
            }
        }
        // double check if no authenticated user
        if (!$user) {
            return; // allow nothing
        }

        // enable operations depending on permission
        foreach ([
            // permission level => [crud operations]
            'see' => ['list', 'show'], // e.g. permission 'users.see' allows to display users
            'edit' => ['list', 'show', 'create', 'update', 'delete'], // e.g. 'users.edit' permission allows all operations
        ] as $level => $operations) {
            //echo "$table.$level";
            if ($user->can("$table.$level")) {
                $this->crud->allowAccess($operations);
            }
        }
    }
}
