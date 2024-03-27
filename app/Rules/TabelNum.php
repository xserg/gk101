<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use App\Models\Staff;

class TabelNum implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //$fail('Error');
        $staff = Staff::where('tabel_num', $value)->first();
        if(!$staff) {
            $fail('The :attribute not found.');
        }
    }
}
