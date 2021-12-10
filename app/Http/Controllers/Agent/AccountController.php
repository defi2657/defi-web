<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2018/12/4
 * Time: 19:04
 */

namespace App\Http\Controllers\Agent;

use App\Models\AccountLog;
use App\Models\AgentMoneylog;
use App\Models\Users;
use Illuminate\Support\Facades\Input;
use Request;

class AccountController extends Controller
{

    /**结算流水
     * @return \Illuminate\Http\JsonResponse
     */
    public function moneyLog()
    {
        $start = request()->input('start', '');
        $end   = request()->input('end', '');

        $agentMoneyLog = new AgentMoneylog();

        if ($start && $end) {
            $start         = strtotime($start);
            $end           = strtotime($end);
            $agentMoneyLog = $agentMoneyLog->whereBetween('created_time', [$start, $end]);
        }

        $user = Users::find(Users::getUserId());

        $list = $agentMoneyLog->where('agent_id', $user->agent_id)->paginate();
        return $this->layuiData($list);
    }


    public function list(Request $request)
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

}