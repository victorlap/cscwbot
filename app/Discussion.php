<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    const STATE_ADD_ARGUMENTS = 'add_arguments';
    const STATE_RATE_ARGUMENTS = 'rate_arguments';
    const STATE_VOTING = 'voting';
    const STATE_CLOSED = 'closed';

    protected $guarded = [];

    public function viewpoints()
    {
        return $this->hasMany(Viewpoint::class);
    }

    public function isValidState(string $state)
    {
        return in_array($state, [
            self::STATE_ADD_ARGUMENTS,
            self::STATE_RATE_ARGUMENTS,
            self::STATE_VOTING,
            self::STATE_CLOSED
        ]);
    }

    public function canMoveState(string $toState)
    {
         switch ($toState) {
            case self::STATE_ADD_ARGUMENTS:
                return true;
            case self::STATE_RATE_ARGUMENTS:
                return $this->state == self::STATE_ADD_ARGUMENTS;
            case self::STATE_VOTING:
                return $this->state == self::STATE_RATE_ARGUMENTS;
            case self::STATE_CLOSED:
                return $this->state == self::STATE_VOTING;
             default:
                 return false;
        }
    }

    public function getStateNameAttribute()
    {
        switch ($this->state) {
            case self::STATE_ADD_ARGUMENTS:
                return "debating round";
            case self::STATE_RATE_ARGUMENTS:
                return "rating round";
            case self::STATE_VOTING:
                return "voting round";
            default:
                return "";
        }
    }

    public function close($result)
    {
        return $this->update([
            'result' => $result,
            'state' => self::STATE_CLOSED,
        ]);
    }
}
