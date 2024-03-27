<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

use App\Models\Staff;

class OtPassword implements DataAwareRule, ValidationRule
{
      /**
    * All of the data under validation.
    *
    * @var array<string, mixed>
    */
    protected $data = [];

    // ...

    /**
    * Set the data under validation.
    *
    * @param  array<string, mixed>  $data
    */
    public function setData(array $data): static
    {
      $this->data = $data;

      return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
      //print_r($this->data);
      //exit;
        $staff = Staff::where('ot_password', $value)->first();

        //if($this->data['name'] $staff->ot_password != $value) {
        if(!$staff) {
            $fail(':attribute error.');
        } else if ($staff->tabel_num != $this->data['name']) {
        //if($staff && $staff->tabel_num != $this->data['name']) {
            $fail(':attribute tabel num error.');
        }
    }
}
