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

    public static function findByNameOrId($nameOrId, $discussionId = null): self
    {
        return static::when($discussionId, function($query) use ($discussionId) {
            $query->where('discussion_id', $discussionId);
        })->where(function($query) use ($nameOrId) {
            $query->where('id', $nameOrId)->orWhere('viewpoint', $nameOrId);
        })->first();
    }
}
