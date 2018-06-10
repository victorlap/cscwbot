<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $guarded = [];

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }
}
