<?php

namespace App\Http\Controllers\Agent;

use App\Models\AccountLog;
use App\Models\AdminToken;
use App\Models\Agent;
use App\Models\ChargeHash;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\Users;
use App\Models\UsersWallet;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;

class AccountLogController extends Controller
{

    public function index()
    {
        //获取type类型
        $type = array(
            AccountLog::ADMIN_LEGAL_BALANCE => '后台调节矿机账户余额',
            AccountLog::ADMIN_LOCK_LEGAL_BALANCE => '后台调节矿机账户锁定余额',
            AccountLog::ADMIN_CHANGE_BALANCE => '后台调节主板账户余额',//后台调节币币账户余额
            AccountLog::ADMIN_LOCK_CHANGE_BALANCE => '后台调节主板账户锁定余额',//后台调节币币账户锁定余额
            // AccountLog::ADMIN_LEVER_BALANCE => '后台调节杠杆账户余额',
            // AccountLog::ADMIN_LOCK_LEVER_BALANCE => '后台调节杠杆账户锁定余额',
            // AccountLog::WALLET_CURRENCY_OUT => '法币账户转出至交易账户',
            // AccountLog::WALLET_CURRENCY_IN => '交易账户转入至法币账户',
            // AccountLog::TRANSACTIONOUT_SUBMIT_REDUCE => '提交卖出，扣除',
            // AccountLog::TRANSACTIONIN_REDUCE => '买入扣除',
            AccountLog::INVITATION_TO_RETURN => '邀请返佣金',
            AccountLog::ETH_EXCHANGE => '链上充币',
            AccountLog::MINING_BALANCE_OUT=>'购买矿机',
            AccountLog::MINING_BONUS=>'矿机分红',
            AccountLog::MINING_PARENT_BONUS=>'矿机邀请分红',

            AccountLog:: FINANCIAL_BALANCE_OUT=>'理财购买扣除',//理财购买扣除
            AccountLog:: FINANCIAL_BONUS=>'理财分红',//理财分红
            AccountLog:: FINANCIAL_PARENT_BONUS=>'理财邀请分红',//理财邀请分红
            AccountLog:: FINANCIAL_CAPITAL=>'理财本金',//理财本金
            AccountLog:: FINANCIAL_REDEEM=>'理财赎回'//赎回
//            AccountLog::MINING_CAPITAL=>'矿机本金',
            // AccountLog::ETH_TRANSFER_FEE => '打入ETH',
            // AccountLog::BTC_TRANSFER_FEE => '打入BTC',
            // AccountLog::TOKENS_WRAPPING => '代币归拢',

        );
        $currency_type = Currency::all();
        return view("agent.account.index", [
            'types' => $type,
            'currency_type' => $currency_type
        ]);
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        $start_time = strtotime($request->get('start_time', 0));
        $end_time = strtotime($request->get('end_time', 0));
        $currency = $request->get('currency_type', 0);
        $type = $request->get('type', 0);

        $list = AccountLog::query();
        $list = $list->with(['user', 'walletLog']);
        if (!empty($currency)) {
            $list = $list->where('currency', $currency);
        }
        if (!empty($type)) {
            $list = $list->where('type', $type);
        }
        if (!empty($start_time)) {
            $list = $list->where('created_time', '>=', $start_time);
        }
        if (!empty($end_time)) {
            $list = $list->where('created_time', '<=', $end_time);
        }

        $agent_id =Agent::getAgentId();
        $list =$list -> whereHas('user',function($query) use($agent_id){
            $query->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
        });
         


        //根据关联模型的时间
        /*if(!empty($start_time)){
            $list = $list->whereHas('walletLog', function ($query) use ($start_time) {
                $query->where('create_time','>=',$start_time);
            });
        }
        if(!empty($end_time)){
            $list = $list->whereHas('walletLog', function ($query) use ($end_time) {
                $query->where('create_time','<=',$end_time);
            });
        }*/
        if (!empty($account)) {
            /*
            $list = $list->whereHas('user', function ($query) use ($account) {
                $query->where("phone", $account)->orWhere('email', $account);
            });
            */
            $user = Users::where("phone", 'like', '%' . $account . '%')->orWhere('email', '%' . $account . '%')->first();
            $list = $list->where(function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            });

        }

      /* if (!empty($account_number)) {
            $list = $list->whereHas('user', function($query) use ($account_number) {
            $query->where('account_number','like','%'.$account_number.'%');
             } );
        }*/

        //$list = $list->orderBy('id', 'desc')->toSql();
        //dd($list);
        $list = $list->orderBy('id', 'desc')->paginate($limit);
        //dd($list->items());
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    public function view(Request $request)
    {
        $id = $request->get('id', null);
        $results = new AccountLog();
        $results = $results->where('id', $id)->first();
        if (empty($results)) {
            return $this->error('无此记录');
        }
        return view('admin.account.viewDetail', ['results' => $results]);
    }

    public function recharge()
    {
        $currencies = Currency::CurrencyData();
        return view('admin.account.recharge')->with('currencies', $currencies);
    }


    public function rechargeAudit()
    {
        $currencies = Currency::CurrencyData();
        return view('admin.account.rechargeAudit')->with('currencies', $currencies);
    }
 
    public function rechargeAuditForm(Request $request){

        // Setting::getBondConfig();
 

         $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        $result =ChargeHash::findOrFail($id);
        return view('admin.account.rechargeAuditForm', [
            'result' => $result,           
        ]);
  
    }



    public function rechargeRejectAudit(){
        try {
            DB::beginTransaction();

            $id = Input::get("id");                 
            if (empty($id)) {
                return $this->error("参数错误");
            }
            $reject_reason=Input::get('reject_reason');
            if(empty($reject_reason))
            {
                return $this->error("请填写拒绝理由");
            }
            // 充值审核记录
            $chargeAudit = ChargeHash::findOrFail($id);  
            if($chargeAudit->status==1)
            {
                return $this->error("当前记录已处理，请勿重新提交");
            }
            $chargeAudit->audit_status=2;
            $chargeAudit->status=1;
            $chargeAudit->audit_user=session()->get('admin_id');
            $chargeAudit->audit_time=time();
            $chargeAudit->reject_reason=$reject_reason;  
            $chargeAudit->save();
            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function rechargeAdoptAudit()
    {                  
        try {     
            $id = Input::get("id");     
            if (empty($id)) {
                return $this->error("参数错误");
            }
            ChargeHash::rechargeAdoptAudit($id,session()->get('admin_id'));          
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function rechargeAuditList(Request $request)
    {
        $limit = Input::get('limit',10) ;
        $input=Input::all();
        $lists=ChargeHash::query();
        if (Input::filled('user_id')) {
            $lists=$lists->where('user_id', $input['user_id']);
        }
        if (Input::filled('currency_id')) {
            $lists= $lists->where('currency_id', $input['currency_id']);
         }
         if (Input::filled('sender')) {
            $lists=$lists->where('sender', $input['sender']);
         }
         if (Input::filled('recipient')) {
            $lists=$lists->where('recipient', $input['recipient']);
         }
         if (Input::filled('status')) {
            $lists=$lists->where('status', $input['status']);
         }
         if (Input::filled('audit_status')) {
            $lists=$lists->where('audit_status', $input['audit_status']);
         }
         if (Input::filled('account_number')) { 
             $lists=$lists->whereHas('user', function ($query) use ($request) {
         
                $query->where('account_number','like','%'.Input::get('account_number').'%');
             });
         }
         if (Input::filled('start_time')) {
            $lists=$lists->where('created_at', '>=',$input['start_time']);
         }
         if (Input::filled('end_time')) {
            $lists=$lists->where('created_at','<', $input['end_time']);
         }
         if (Input::filled('start_audit_time')) {
            $lists=$lists->where('audit_time', '>=',$input['start_audit_time']);
         }
         if (Input::filled('end_audit_time')) {
            $lists=$lists->where('audit_time','<', $input['end_audit_time']);
         }
         if($input['status']==1)
         {
            $lists= $lists->orderBy('audit_time', 'desc')->paginate($limit);
         }else{
            $lists= $lists->orderBy('id', 'desc')->paginate($limit);
         }

        // $lists = AccountLog::where(function ($query) {
        //         $query->where('type', AccountLog::ETH_EXCHANGE)->where('user_id', '>', 0);
        //     })->whereHas('user', function ($query) use ($request) {
        //         $account_number = $request->input('account_number', '');
        //         $account_number != '' && $query->where('account_number', $account_number);
        //     })->where(function ($query) use ($request) {
        //         $currency = $request->input('currency', -1);
        //         $start_time = strtoti    me($request->input('start_time', null));
        //         $end_time = strtotime($request->input('end_time', null));
        //         $currency != -1 && $query->where('currency', $currency);
        //         $start_time && $query->where('created_time', '>=', $start_time);
        //         $end_time && $query->where('created_time', '<=', $end_time);
        //     })->orderBy('id', 'desc')->paginate($limit);
        $sum = $lists->sum('amount');
        return $this->layuiData($lists,$sum);
    }

    public function rechargeList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $lists = AccountLog::where(function ($query) {
                $query->where('type', AccountLog::ETH_EXCHANGE)->where('user_id', '>', 0);
            })->whereHas('user', function ($query) use ($request) {
                $account_number = $request->input('account_number', '');
                $account_number != '' && $query->where('account_number', $account_number);
            })->where(function ($query) use ($request) {
                $currency = $request->input('currency', -1);
                $start_time = strtotime($request->input('start_time', null));
                $end_time = strtotime($request->input('end_time', null));
                $currency != -1 && $query->where('currency', $currency);
                $start_time && $query->where('created_time', '>=', $start_time);
                $end_time && $query->where('created_time', '<=', $end_time);
            })->orderBy('id', 'desc')->paginate($limit);
        $sum = $lists->sum('value');
        return $this->layuiData($lists,$sum);
    }

    public function indexprofits()
    {
        $scene_list = AccountLog::where("type", "=", AccountLog::PROFIT_LOSS_RELEASE)->orderBy("created_time", "desc")->get()->toArray();
//        var_dump($scene_list);die;
        return view('admin.profits.index')->with('scene_list', $scene_list);
    }

    public function listsprofits(Request $request)
    {
        $limit = $request->input('limit', 10);
        $prize_pool = AccountLog::whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number');
            if ($account_number) {
                $query->where('account_number', $account_number);
            }
        })->where(function ($query) use ($request) {
//            $scene = $request->input('scene', -1);
            $start_time = strtotime($request->input('start_time', null));
            $end_time = strtotime($request->input('end_time', null));
//            $scene != -1 && $query->where('scene', $scene);
            $start_time && $query->where('created_time', '>=', $start_time);
            $end_time && $query->where('created_time', '<=', $end_time);
        })->where("type", AccountLog::PROFIT_LOSS_RELEASE)->orderBy('id', 'desc')->paginate($limit);

        return $this->layuiData($prize_pool);
    }

    public function countprofits(Request $request)
    {
        $count_data = AccountLog::selectRaw('1 as user_count')
            ->selectRaw('sum(`value`) as value')
            ->whereHas('user', function ($query) use ($request) {
                $account_number = $request->input('account_number');
                if ($account_number) {
                    $query->where('account_number', $account_number)
                        ->orWhere('phone', $account_number)
                        ->orWhere('email', $account_number);
                }
            })->where(function ($query) use ($request) {
                //$scene = $request->input('scene', -1);
                $start_time = strtotime($request->input('start_time', null));
                $end_time = strtotime($request->input('end_time', null));
                //$scene != -1 && $query->where('scene', $scene);
                $start_time && $query->where('created_time', '>=', $start_time);
                $end_time && $query->where('created_time', '<=', $end_time);
            })->where("type", AccountLog::PROFIT_LOSS_RELEASE)->groupBy('user_id')->get();
        $user_count = $count_data->pluck('user_count')->sum();
        $reward_total = 0;
        $count_data->pluck('value')->each(function ($item, $key) use (&$reward_total) {
            $reward_total = bc_add($reward_total, $item);
        });
        return response()->json([
            'user_count' => $user_count,
            'reward_total' => $reward_total,
        ]);
    }
}