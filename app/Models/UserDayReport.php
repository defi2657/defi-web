<?php

namespace App\Models;


use App\Utils\RPC;

class UserDayReport extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_day_report';

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'id');
    }
 
}