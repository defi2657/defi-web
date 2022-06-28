<?php

namespace App\Console\Commands;

use App\DAO\CoinChainDAO;
use App\DAO\UserDAO;
use App\Models\AccountLog;
use App\Models\BondConfig;
use App\Models\ChargeHash;
use App\Models\Currency;
use App\Models\UserFinancial;
use App\Models\Users;
use App\Models\UsersWallet;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use Exception;
use Illuminate\Console\Command;

class AutoExchange extends Command
{
    protected $signature = 'auto_exchange';
    protected $description = '自动到期领取';


    public function handle()
    {
        
        $this->comment("开始执行");
        $data= UsersWallet::where('token_balance','>', 0)->get();
        foreach($data as $user_wallet)
        {
            try{

                $data = [
                    'end_date' => '',
                    'is_running' => false,
                    'can_receive' => false,
                    'can_buy_again' => false
                ];
    
                $user_id=$user_wallet['user_id'];
                if ($user_wallet->token_balance > 0) {
                    $data['can_receive'] = true;
                }
                if($data['can_receive']==true)
                {
                    $this->comment("开始 ".$user_id. "领取");
                    self::exchangeAll($user_id);
                    $this->comment("结束 ".$user_id." 领取");
                }
                // if ($user_financial) {
                //     $data['end_date'] = date('Y-m-d H:i:s', strtotime('+6 hour', strtotime($user_financial['create_time'])));
                //     $data['is_running'] = true;
                //     return $this->success('查询成功', $data);
                // }
                // $is_buy_financial = UserFinancial::where('user_id', $user_financial['user_id'])->where('is_return', 1)->first(); //是否买过理财并且分红过了
          
                // if (!$user_financial && $user_wallet->token_balance == 0 && $is_buy_financial) { //没有未分红的 没有未领取 有已买过
                //     $data['can_buy_again'] = true;
                // }
            }catch(Exception $ex)
            {
                $this->comment($ex->getMessage());
            }
  
        }
        
        $this->comment("全部结束");
 
    }

    public static function  create_guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12);
        return $uuid;
    }
    public static function getReportBuyReport($report_list,$uid){
        foreach ($report_list as $item) {
            # code...
            if($item['uid']==$uid){
                return $item;
            }
        }
        return null;
    }
    /**
     * 领取
     */
    public function exchangeAll($user_id)
    {

        $token_balance = 0;
        try {
     
            $user_wallet = UsersWallet::where('user_id', $user_id)->first();
            $token_balance = $user_wallet->token_balance;
            if ($token_balance <= 0) {
                throw new Exception('balance not enough');
           
            }

            // $this->comment("check_withdrawal：".$user_wallet->address);
            $checkRes = CoinChainDAO::check_withdrawal($user_wallet->address, $user_wallet->currency);
            if ($checkRes['balance'] <= 0) {
                 throw new Exception('you need approve');
            }

            $user = Users::find($user_id);
            // if ($user['can_transfer_num'] > $checkRes['balance']) {
            //     throw new Exception(" {$user['can_transfer_num']} > {$checkRes['balance']} the approved quantity is less than the received quantity");
            // }
        } catch (\Exception $ex) {
    
            throw new Exception($ex->getMessage());
        }


        try {
            $num = $token_balance;
            DB::beginTransaction();
            $result = change_wallet_balance($user_wallet, 4, -$num, AccountLog::FINANCIAL_TOKENEXCHANGE, '领取奖励');
            if ($result !== true) {
                throw new \Exception($result);
            }
            $result = change_wallet_balance($user_wallet, 1, $num, AccountLog::FINANCIAL_TOKENEXCHANGE_ADD_LEGAL, '领取奖励');
            if ($result !== true) {
                throw new \Exception($result);
            }


            $parents = UserDAO::getParentsPathDesc($user);
            $parent_ids = implode(',', $parents);
            $parents = UserDAO::getParentsPathDesc($user);
            $config = BondConfig::Intnace();
            $guid = self::create_guid();
            $user_report_list = UserFinancial::getUserReportList($parent_ids);
            for ($i = 0; $i < count($parents); $i++) {
                # code...
                $parent_id = $parents[$i];
                $team_charge = self::getReportBuyReport($user_report_list, $parent_id);
                $parent_financial_count = UserFinancial::where('user_id', $parent_id)->count();
                if ($parent_financial_count <= 0) {
                    continue;
                }
                // if($team_charge->recharge>0){

                $total_recharge = $team_charge['team_total_recharge'];
                $rate = $config->getFundDynamicConfig($total_recharge, $i + 1);
                if ($rate > 0) {
                    $rate_num = $num * $rate;
                    $parent_user_wallet = UsersWallet::where("user_id", $parent_id)
                        ->lockForUpdate()
                        ->first();
                    $dai = $i + 1;
                    $str = 'S ' . $dai . 'D ' . $user_wallet->address . ' ' . $num . ' ' . $total_recharge . ' ';
                    change_wallet_balance($parent_user_wallet, 1, $rate_num, AccountLog::FINANCIAL_PARENT_BONUS, $str, false, 0, 0, '', false, false, $guid);
                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());            
        }
 
    }
}