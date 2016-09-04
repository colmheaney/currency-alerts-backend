<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['from_currency', 'to_currency', 'lower_rate', 'upper_rate', 'user_id', 'status', 'symbol'];

    public function user()
    {
      return $this->belongsTo('App\User');
    }
}
