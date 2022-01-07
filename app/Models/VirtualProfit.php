<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;

class VirtualProfit extends Model
{
    protected $table = 'virtual_profit';
    public $timestamps = false;

    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->first();
    }
}