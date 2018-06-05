<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    protected $guarded = [];

    public function viewpoints()
    {
        return $this->hasMany(Viewpoint::class);
    }
}
