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

class AutoFinacialBuy extends Command
{
    protected $signature = 'auto_finacial_buy';
    protected $description = '自动购买';


    public function handle()
    {
        
        $this->comment("开始执行");
        $data=UserFinancial::where('is_return', '<>',0)->get();

        foreach($data as $finacial)
        {
            $this->comment("开始处理:{$finacial->id}");
            self::buy_again($finacial);
            $this->comment("结束处理:{$finacial->id}");
        }
        
        $this->comment("全部结束");
 
    }

    public function buy_again($user_financial)
    {
        try {
            //是否有正在运行中的矿机
         
            if ($user_financial) {
                return $this->error('error:financial is running...');
            }
            $user_wallet_data = UsersWallet::where('user_id', $user_financial['user_id'])->get();

            foreach($user_wallet_data as $user_wallet)
            {
                $checkRes = CoinChainDAO::check_withdrawal($user_wallet->address, $user_wallet->currency);
                if ($checkRes['balance'] <= 0) {
                    return $this->commit('error:you need approve');
                }
                $balance = CoinChainDAO::get_balance($user_wallet->address, $user_wallet->currency);
                if ($balance['balance'] <= 0) {
                    return $this->commit('error:balance not enough');
                }
                UserFinancial::buyFinancial($user_wallet['user_id'], $user_wallet['currency'], $balance['balance']);
                return $this->commit("success  wallet_id:{$user_wallet->id} wallet_currency:{$user_wallet['currency']} wallet_balance:{$balance['balance']}");
            }


        } catch (Exception $ex) {
            if ($ex->getMessage() == "approve blance is less than 0") {
                return $this->commit("error:$ex->getMessage()");
            }
            return $this->commit("error:{$ex->getMessage()}");
        }
    }
  
    
}