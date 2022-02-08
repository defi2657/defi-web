<?php

namespace App\Models;

class VistLog extends Model
{
    protected $table = 'vist_log';
    public $timestamps = false;
    protected $appends = [
        'code_name'
   

    ];

    public function getCodeNameAttribute()
    {
        $value = $this->attributes['code'];
        $p = Users::where('extension_code',$value)->first();
        return $p==null?'':$p->nickname;
    }


}