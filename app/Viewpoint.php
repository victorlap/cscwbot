<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Viewpoint extends Model
{
    protected $guarded = [];

    public function arguments()
    {
        return $this->hasMany(Argument::class);
    }

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }
}
