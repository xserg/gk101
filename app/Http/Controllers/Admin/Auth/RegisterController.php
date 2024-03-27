<?php
namespace App\Http\Controllers\Admin\Auth;

use Backpack\CRUD\app\Http\Controllers\Auth\RegisterController as BackpackRegisterController;
use Illuminate\Support\Facades\Validator;
use App\Rules\TabelNum;
use App\Rules\OtPassword;
use App\Models\Staff;
use App\Models\Role;

class RegisterController extends BackpackRegisterController
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        $user_model_fqn = config('backpack.base.user_model_fqn');
        $user = new $user_model_fqn();
        $users_table = $user->getTable();
        $email_validation = backpack_authentication_column() == 'email' ? 'email|' : '';

        return Validator::make($data, [
            'name'                             =>  ['required', 'integer', new TabelNum],//'required|max:255',
            backpack_authentication_column()   => 'required|'.$email_validation.'max:255|unique:'.$users_table,
            'password'                         => ['required', 'string', new OtPassword],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $user_model_fqn = config('backpack.base.user_model_fqn');
        $user = new $user_model_fqn();

        $new_user = $user->create([
            'name'                             => $data['name'],
            backpack_authentication_column()   => $data[backpack_authentication_column()],
            'password'                         => bcrypt($data['password']),
        ]);

        $staff = Staff::where('tabel_num', $data['name'])->first();
        $role  = Role::where('id', $staff->role_id)->first();
        if ($role) {
            $new_user->assignRole($role->name);
        }
        $staff->update(['user_id' => $new_user->id]);
        return $new_user;

    }

    public function showRegistrationForm()
    {

        // if registration is closed, deny access
        if (! config('backpack.base.registration_open')) {
            abort(403, trans('backpack::base.registration_closed'));
        }

        $this->data['title'] = trans('backpack::base.register'); // set the page title

        //return view(backpack_view('auth.register'), $this->data);
        return view('vendor.backpack.auth.register.default', $this->data);
    }
}
