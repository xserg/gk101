<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Watched extends Pivot
{
    use CrudTrait;
    protected $connection = 'mamavip';

    protected $table = 'user_to_watched_lectures';
    //protected $table = 'user_to_list_watched_lectures';

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public static function getListName($user)
    {
      return $user->staff->lastname . ' ' . mb_substr($user->staff->name, 0, 1) . '. ' . mb_substr($user->staff->fathername, 0, 1) . '.';
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'user_id', 'user_id')->withDefault();
    }

    public function getNames()
    {
      if ($this->user_id == 1) {
          return 'admin';
      }
      return $this->staff->lastname . ' ' . mb_substr($this->staff->name, 0, 1) . '. ' . mb_substr($this->staff->fathername, 0, 1) . '.';// . '</a>';
    }
}
