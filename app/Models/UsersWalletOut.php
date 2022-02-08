<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;

use App\Events\WithdrawAuditEvent;
use DB;

class UsersWalletOut extends Model
{
    protected $table = 'users_wallet_out';
    public $timestamps = false;
    protected $appends = [
        'currency_name',
        'account_number',
        //'real_number',
        'currency_type',
        'nationality',
        'parent_agent_name',
        'parent_parent_agent_name'
    ];

    //节点等级
    const TO_BE_AUDITED = 1;
    const ToO_EXAMINE_ADOPT = 2;
    const ToO_EXAMINE_FAIL = 3;

    public function getCurrencyNameAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'currency')->value('name');
    }

    public function getParentParentAgentNameAttribute()
    {
        $value = $this->user()->value('agent_note_id');
        if ($value > 0) {
            $agent=Agent::getAgentById($value);
            $value=$agent->parent_agent_id;
            if($value>0)
            {
                $p = Agent::getAgentById($value);
                return $p?$p->username:'';
            }else{
                return '无';
            }

        } else {
            return '无';
        }

        // return $this->user()->value('parent_agent_name');
    }


    public function getParentAgentNameAttribute()
    {
        $value = $this->user()->value('agent_note_id');
        if ($value > 0) {
            $p = Agent::getAgentById($value);
            return $p?$p->username:'';
        } else {
            return '无';
        }

        // return $this->user()->value('parent_agent_name');
    }

    public function getCurrencyTypeAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'currency')->value('type');
    }

    public function getAccountNumberAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }

    // public function getRealNumberAttribute()
    // {
    //     // return $this->attributes['number']*(1-$this->attributes['rate']);
    //     return bcmul($this->attributes['number'], (1 - $this->attributes['rate'] / 100), 8);
    // }

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getUpdateTimeAttribute()
    {
        $value = $this->attributes['update_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getNationalityAttribute()
    {
        return $this->user()->value('nationality');
    }
    
    public function getStatusNameAttribute(){
        $value=$this->attributes['status'];
        if($value==1){
            return 'submitted';
        }elseif($value==2){
            return 'successful';
        }else{
            return 'fail';
        }
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withDefault();
    }

    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }
    public static function getOutCount($userid){
        return self::where('user_id',$userid)->count('number');
    }
    public static function GetAllOutCount(){
        return self::sum('number');
    }

    public static function AuditWithdraw($id,$method,$notes='system_audit',$txid=''){
        $balance_type = [2, 'change', '币币'];
        // $field_name = $balance_type[1] . '_balance';
        $type = $balance_type[0];

        try {
            DB::beginTransaction();

            throw_if(empty($id), new \Exception('参数错误'));

            $wallet_out = UsersWalletOut::lockForUpdate()->findOrFail($id);
            $number = $wallet_out->number;
            $real_number = bc_mul($wallet_out->number, bc_sub(1, bc_div($wallet_out->rate, 100)));
            $user_id = $wallet_out->user_id;
            $currency = $wallet_out->currency;
            $currency_type = $wallet_out->currency_type;
            $type=$wallet_out->type;
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->firstOrFail();

            $currency_model = Currency::find($currency);
            $contract_address = $currency_model->contract_address;
            $total_account = $currency_model->total_account;
            //$key = $currency_model->origin_key;

            if ($method == 'done') {
                //确认提币
                // if (empty($total_account) ) {
                //     throw new \Exception('请检查您的币种设置:(');
                // }
                if (!in_array($currency_type, ['eth', 'erc20', 'usdt', 'btc','eos','xrp','trx'])) {
                    throw new \Exception('币种类型暂不支持:(');
                }
                if ($currency_type == 'erc20' && empty($contract_address)) {
                    throw new \Exception('币种设置缺少合约地址:(');
                }
                
                // throw_if(empty($verificationcode), new \Exception('请填写验证码'));
                // $ga = new PHPGangsta_GoogleAuthenticator();
                // // $secret = $ga->createSecret();
                // $secret='7M5QZM3NOIURZDEM';                
                // $oneCode = $ga->getCode($secret);
                // $checkResult = $ga->verifyCode($secret, $verificationcode, 2);
                // if(false && !$checkResult){
                //     throw new \Exception('验证码错误');
                // }
             
                $change_result = change_wallet_balance($user_wallet, $type, -$number, AccountLog::WALLETOUTDONE, '提币成功', true);
            
                if ($change_result !== true) {
                    throw new \Exception($change_result);
                }
                 
                $use_chain_api = Setting::getValueByKey('use_chain_api', 0);
                // if ($use_chain_api == 0) {
                //     if ( empty($txid)   ) {
                //         throw new \Exception('当前提币没有使用接口,请填写交易哈希以便于用户查询');
                //     }
                //     $wallet_out->txid = $txid;
                // } 
                // else {
                //     throw_if(empty($verificationcode), new \Exception('请填写验证码'));
                // }

                if ( empty($txid)   ) {
                    throw new \Exception('当前提币没有使用接口,请填写交易哈希以便于用户查询');
                }
                $wallet_out->txid = $txid;
                $wallet_out->txid_status=1; 

                $wallet_out->use_chain_api = $use_chain_api ;
                $wallet_out->status = 2; //提币成功状态
            } else {
                $change_result = change_wallet_balance($user_wallet, $type, -$number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额减少', true);
                if ($change_result !== true) {
                    throw new \Exception($change_result);
                }
                $change_result = change_wallet_balance($user_wallet, $type, $number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额撤回');
                if ($change_result !== true) {
                    throw new \Exception($change_result);
                }
                $wallet_out->status = 3; //提币失败状态
            }
            $wallet_out->notes = $notes;//反馈的信息
            // $wallet_out->verificationcode = $verificationcode;
            $wallet_out->verificationcode='meta';
            $wallet_out->update_time = time();
            $wallet_out->save();
            event(new WithdrawAuditEvent($wallet_out));
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
 
        }
    }
}