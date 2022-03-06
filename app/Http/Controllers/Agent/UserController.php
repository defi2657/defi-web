<?php

/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2018/12/4
 * Time: 16:36
 */

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use App\Models\{AccountLog,UsersWallet, Agent, Currency, Setting, Users, UsersWalletOut};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{

    public function postConf(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'way' => 'required|string', //增加 increment；减少 decrement
                'type' => 'required|integer|min:1',
                'conf_value' => 'required|numeric|min:0', //值
                'info' => 'required'
            ], [
                'required' => ':attribute 不能为空',
            ], [
                'info' => '调节备注'
            ]);

            $wallet = UsersWallet::find($request->get('id'));
            $user = Users::getById($wallet->user_id);

            //以上验证通过后 继续验证
            $validator->after(function ($validator) use ($wallet, $user) {
                if (empty($wallet)) {
                    return $validator->errors()->add('wallet', '没有此钱包');
                }

                if (empty($user)) {
                    return $validator->errors()->add('user', '没有此用户');
                }
            });

            //如果验证不通过
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $way = $request->get('way', 'increment');
            $type = $request->get('type', 1);
            $conf_value = $request->get('conf_value', 0);
            $info = $request->get('info', ':');

            $balance_type = ceil($type / 2);
            $is_lock = $type % 2 ? false : true;
            $scene_list=AccountLog::$scene_list;
        

            $way == 'decrement' &&  $conf_value = -$conf_value;

            $result = change_wallet_balance($wallet, $balance_type, $conf_value, $scene_list[$type], $info, $is_lock);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }


    /*
     * 调节账户
     * */
    public function conf(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UsersWallet::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }
        $account = Users::where('id', $result->user_id)->value('phone');
        if (empty($account)) {
            $account = Users::where('id', $result->user_id)->value('email');
        }
        $result['account'] = $account;
        $result['scene_list']=AccountLog::$scene_list;
        return view('agent.user.conf', ['results' => $result]);
    }
    public function wallet(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        return view("agent.user.user_wallet", ['user_id' => $id]);
    }

    public function walletList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = $request->get('user_id', null);
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $list = new UsersWallet();
        $list = $list->where('user_id', $user_id)->orderBy('id', 'desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }


    //用户管理
    public function index()
    { 
        $settingList = Setting::all()->toArray();
        $setting = [];
        foreach ($settingList as $key => $value) {
            $setting[$value['key']] = $value['value'];
        }
        //某代理商下用户时
        $parent_id = request()->get('parent_id', 0);
        $_self = Agent::getAgent();
        // echo(json_encode($_self ) );
        if($_self->is_admin==0)
        {
            $parent_id=$_self->id;
        }
        //法币
        $legal_currencies = Currency::where('is_legal', 1)->get();
        return view("agent.user.index", ['parent_id' => $parent_id, 'legal_currencies' => $legal_currencies,'setting'=>$setting]);
    }



    public function virtualUserProfit(Request $request, $id)
    {
        $user = Users::find($id);
        return view('agent.user.virtual_user_profit')->with('user', $user);
    }

    public function postvirtualUserProfit(Request $request, $id)
    {
        $virtual_user_profit = $request->get('virtual_user_profit', 0);
    
        $res= Users::where('id',$id)->update(['virtual_user_profit'=>$virtual_user_profit]);
   
        return $this->success('操作成功');
    }


    //用户列表
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $id = request()->input('id', 0);
        $parent_id = request()->input('parent_id', 0);
        $account_number = request()->input('account_number', '');
        $start = request()->input('start', '');
        $end = request()->input('end', '');

        $users = new Users();
 
        if ($id) {
            $users = $users->where('users.id', $id);
        }
        if ($parent_id > 0) {
            // $users = $users->where('users.agent_note_id', $parent_id);
        }
        if ($account_number) {
            $users = $users->where('users.account_number', $account_number);
        }
        if (!empty($start) && !empty($end)) {
            $users->whereBetween('users.time', [strtotime($start . ' 0:0:0'), strtotime($end . ' 23:59:59')]);
        }

        //获取下级代理？？？
        // $my_agent_list=Agent::getLevel4AgentId(Agent::getAgentId(),[Agent::getAgentId()]);

        // $users = $users->whereIn('users.agent_note_id', $my_agent_list);

        $agent_id = Agent::getAgentId();
        $users = $users->whereRaw("FIND_IN_SET($agent_id,users.agent_path)");

        $list = $users->select("users.*")->paginate($limit);

        return $this->layuiData($list);
    }

    /**
     * 获取用户管理的统计
     * @param Request $r
     */
    public function get_user_num(Request $request)
    {

        $id             = request()->input('id', 0);
        $account_number = request()->input('account_number', '');
        $parent_id            = request()->input('parent_id', 0);//代理商id
        $start = request()->input('start', '');
        $end = request()->input('end', '');
        $currency_id = request()->input('currency_id', '');

        $users = new Users();

        if ($id) {
            $users = $users->where('id', $id);
        }
        if ($parent_id > 0) {
            $users = $users->where('agent_note_id', $parent_id);
        }
        if ($account_number) {
            $users = $users->where('account_number', $account_number);
        }
        if (!empty($start) && !empty($end)) {
            $users->whereBetween('time', [strtotime($start . ' 0:0:0'), strtotime($end . ' 23:59:59')]);
        }

        // $my_agent_list = Agent::getLevel4AgentId(Agent::getAgentId(),[Agent::getAgentId()]);

        // $users = $users->whereIn('agent_note_id', $my_agent_list);

        $agent_id = Agent::getAgentId();
        $users = $users->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
        $users_id = $users->get()->pluck('id')->all();
        $_daili = 0;
        $_ru = 0.00;
        $_chu = 0.00;
        $_num = 0;

        $_num = $users->count();

        $_daili = $users->where('agent_id', '>', 0)->count();


        $_ru = AccountLog::where('type', AccountLog::CHAIN_RECHARGE)
            ->whereIn('user_id', $users_id)
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })->sum('value');

        $_chu = UsersWalletOut::where('status', 2)
            ->whereIn('user_id', $users_id)
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })->sum('real_number');

        $data = [];
        $data['_num'] = $_num;
        $data['_daili'] = $_daili;
        $data['_ru'] = $_ru;
        $data['_chu'] = $_chu;


        return $this->ajaxReturn($data);
    }

    //我的邀请二维码
    public function get_my_invite_code()
    {

        $_self = Agent::getAgent();

        if ($_self == null) {
            $this->outmsg('超时');
        }

        $use = Users::getById($_self->user_id);

        $code=Setting::getValueByKey('proxy_pop_domain');
     
        return $this->ajaxReturn(['invite_code' => $use->extension_code, 'is_admin' => $_self->is_admin,'invite_site'=>$code]);
    }

    public function update_nickname(Request $request){
        $id = request()->input('id', 0);
        $nickname = request()->input('nickname', '');
        if($id>0)
        {
            $res =Users::where('id',$id)->update(['nickname'=>$nickname]);
            return $this->success();
        }
        return $this->error('参数错误');

    }

    //代理商管理
    public function salesmenIndex()
    {
        return view("agent.salesmen.index");
    }

    //添加代理商页面
    public function salesmenAdd()
    {
        $data = request()->all();
        if(isset($data['id']))
        {
            $id=$data['id'];
            $agent= Agent::where('id',$id)->first();
            $data['custom_service_link']=$agent['custom_service_link'];
        }
        return view("agent.salesmen.add", ['d' => $data]);
    }

    public function salesmenEdit()
    {
        $data = request()->all();
        return view("agent.salesmen.add", ['d' => $data]);
    }
    //出入金管理
    public function transferIndex()
    {
        return view("agent.user.transfer");
    }
}