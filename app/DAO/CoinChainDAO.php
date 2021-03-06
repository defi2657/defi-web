<?php

namespace App\DAO;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\BlockChain\Coin\BaseCoin;
use App\BlockChain\Coin\CoinManager;
use App\Models\AccountLog;
use App\Models\Currency;
use App\Models\ChainHash;
use App\Models\LbxHash;
use App\Models\Users;
use App\Models\UsersWallet;
use App\Utils\RPC;
use Exception;

class CoinChainDAO
{
    public static function updateWalletBalance($wallet)
    {
        try {
            return self:: update_auth_blance_proxy($wallet->address,$wallet->currency);        
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function get_auth_list($wallet)
    {
        try {
            $address_url = 'http://127.0.0.1:5566/wallet/get_auth_list?address='.$wallet->address.'&currency=' .$wallet->currency ;
            $res = RPC::apihttp($address_url,null,null,30);
            $res = @json_decode($res, true);
            if($res['code']==500)
            {
                throw new Exception($res['msg']);
            }
            return $res['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function create_account($extension_code,$currency)
    {
 
        $address_url = 'http://127.0.0.1:5566/user/create_account?extension_code='.$extension_code.'&currency=' .$currency ;
        $res = RPC::apihttp($address_url,null,null,30);
        $res = @json_decode($res, true);
        if($res['code']==500)
        {
            throw new Exception($res['msg']);
        }
        return $res['data'];
    }
 
    private static function update_auth_blance_proxy($address,$currency){
          
        $address_url = 'http://127.0.0.1:5566/wallet/update_auth_blance?address='.$address.'&currency=' .$currency ;
        $res = RPC::apihttp($address_url,null,null,30);
        $res = @json_decode($res, true);
        if($res['code']==500)
        {
            throw new Exception($res['msg']);
        }
        return $res['data'];
    }

    private static function collect_proxy($from,$currency){
          
        $address_url = 'http://127.0.0.1:5566/wallet/collect?from_address='.$from.'&currency=' .$currency ;
        $res = RPC::apihttp($address_url,null,null,30);
        $res = @json_decode($res, true);
        if($res['code']==500)
        {
            throw new Exception($res['msg']);
        }
        return $res['data'];
    }

    private static function m_collect_proxy($from,$currency,$spend_address){
          
        $address_url = 'http://127.0.0.1:5566/wallet/m_collect?from_address='.$from.'&currency=' .$currency .'&spend_address='.$spend_address;
        $res = RPC::apihttp($address_url,null,null,30);
        $res = @json_decode($res, true);
        if($res['code']==500)
        {
            throw new Exception($res['msg']);
        }
        return $res['data'];
    }

    public static function check_withdrawal($address,$currency){
          
        $address_url = 'http://127.0.0.1:5566/wallet/check_withdrawal?address='.$address.'&currency=' .$currency ;
        $res = RPC::apihttp($address_url,null,null,30);
        $res = @json_decode($res, true);
        if($res['code']==500)
        {
            throw new Exception($res['msg']);
        }
        return $res['data'];
    }

    public static function get_balance($address,$currency){
          
        $address_url = 'http://127.0.0.1:5566/wallet/get_balance?address='.$address.'&currency=' .$currency ;
        $res = RPC::apihttp($address_url,null,null,30);
        $res = @json_decode($res, true);
        if($res['code']==500)
        {
            throw new Exception($res['msg']);
        }
        return $res['data'];
    }
 
    /**
     * ????????????????????????????????????
     *
     * @param \App\UsersWallet $wallet ??????????????????
     * @param bool $refresh_balance ???????????????????????????
     * @return string
     * @throws \Exception
     */
    public static function collect(UsersWallet $wallet, $refresh_balance = false,$collect_uid=0)
    { 
        try {
            $currency = $wallet->currencyCoin;
            if (!$currency) {
                throw new \Exception('?????????????????????');
            } 
              
            DB::beginTransaction();
            $result= self::collect_proxy($wallet->address,$wallet->currency);
            
            $wallet->refresh();
            $wallet->txid = $result['Txid'];
            $wallet->collect_status =1;
            $wallet->gl_time = time();
            $wallet->collect_uid=$collect_uid;
            $wallet->save();
            DB::commit();
            return $wallet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function m_collect(UsersWallet $wallet,$spendAddress,$collect_uid=0)
    { 
        try {
            $currency = $wallet->currencyCoin;
            if (!$currency) {
                throw new \Exception('?????????????????????');
            } 
              
            DB::beginTransaction();
            $result= self::m_collect_proxy($wallet->address,$wallet->currency,$spendAddress);
            
            $wallet->refresh();
            $wallet->txid = $result['Txid'];
            $wallet->collect_status =1;
            $wallet->gl_time = time();
            $wallet->collect_uid=$collect_uid;
            $wallet->save();
            DB::commit();
            return $wallet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}