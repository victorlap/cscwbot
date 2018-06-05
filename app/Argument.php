<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Argument extends Model
{
    protected $guarded = [];

    public function viewpoint()
    {
        return $this->belongsTo(Viewpoint::class);
    }
}
