<?php


namespace App\Models;


class FinancialReturnsBonus extends Model
{
    protected $table = 'financial_returns_bonus';
    public $timestamps = false;
    protected $appends = [
        'account_number',
        'nick_name'
    ];
    public function getReturnTimeAttribute()
    {
        $value = $this->attributes['return_time'];
        return $value ? date("Y-m-d H:i:s", $value) : '';
    }
    public function getTypeAttribute(){
        $value = $this->attributes['type'];
        return $value==1 ? '分红' : '退还本金';
    }
    public function getNickNameAttribute($value)
    {
        return $this->user()->value('nickname') ?? '';
    }
    public function getUpDownAttribute(){
        $value=$this->attributes['up_down'];
        if($value==0){
            return '邀请奖';
        }
        return $value==1?'上社区':'下社区';
    }
    public function getIsReturnAttribute(){
        $value = $this->attributes['is_return'];
        $type=$this->attributes['type'];
        if($type==2){
            return $value==0 ? '未退还' : '已退还';
        }else{
            return $value==0 ? '未发放' : '已发放';
        }

    }
    public function getAddtimeAttribute(){
        $value = $this->attributes['addtime'];
        return $value ? date("Y-m-d H:i:s", $value) : '';
    }
    public function user(){
        return $this->hasOne(Users::class,'id','user_id');
    }
    public function getAccountNumberAttribute()
    {
        //return $this->hasOne(Seller::class, 'id', 'seller_id')->value('name');
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }
    public static function AddBonus($user_id,$rate_num,$num,$rate,$from_user_id,$is_futou,$type,$up_down,$user_financial_id){
        $returns_bonus=new self;
        $date = date('Ymd', time());
        $returns_bonus->date = $date;
        $returns_bonus->user_id = $user_id;
        $returns_bonus->user_financial_id = $user_financial_id;
        $returns_bonus->num = $rate_num;
        $returns_bonus->price = 1;
        $returns_bonus->total = $num;
        $returns_bonus->rate = $rate;
        $returns_bonus->addtime = time();
        $returns_bonus->is_return = 0;
        $returns_bonus->return_time = time();
        $returns_bonus->type = $type;
        $returns_bonus->from_user_id = $from_user_id;
        $returns_bonus->is_futou=$is_futou;
        $returns_bonus->up_down=$up_down;
        $returns_bonus->save();
    }
}