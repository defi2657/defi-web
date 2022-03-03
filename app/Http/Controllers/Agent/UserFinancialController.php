<?php


namespace App\Http\Controllers\Agent;

use App\Models\AccountLog;
use App\Models\Agent;
use App\Models\Setting;
use App\Models\Users;
use App\Models\UsersWallet;
use function foo\func;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\UserFinancial;
use App\Models\FinancialReturnsBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;
use Illuminate\Support\Facades\DB;

class UserFinancialController extends Controller
{
    /***
     * 用户矿机
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        return view("agent.user_financial.index");
    }
    public function bonusIndex(){
        return view("agent.user_financial.bonus");
    }
    public function bonusDetail(Request $request){
        $id = $request->get('id', null);
        return view("agent.user_financial.bonusdetail",['id' => $id]);
    }
    public function financial_user_bonus(){
        return view("agent.user_financial.financial_user_bonus");
    }
    public function invite_user(Request $request){
        $id = $request->get('id', null);
        return view("agent.user_financial.invite",['id'=>$id]);
    }

    /***
     * 矿机列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request){
        $limit=$request->input('limit','0');
      
        $agent_id =Agent::getAgentId();
        $list=UserFinancial
        ::join('users','users.id','=','user_financial.user_id')
        ->select('users.account_number','user_financial.*')
        ->whereHas('user',function($query) use($agent_id){
            $query->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
        })
        ->where(function ($query) use ($request) {
            $account_number=$request->input('account_number',null);
            $account_number && $query->where('account_number','like','%' . $account_number . '%');
            
        });
        // if (!empty($account_number)) {
        //     $users = Users::where('account_number', 'like', '%' . $account_number . '%')->get()->pluck('id');
        //     if (!empty($users)) {
        //         $list=$list->whereIn('user_id',$users);
        //     }
        // }
        $list=$list->orderBy('id','desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    /***
     * 分红列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bonusList(Request $request){
        $limit=$request->input('limit','0');
   
        $agent_id =Agent::getAgentId();

        $list=FinancialReturnsBonus
        ::join('users','users.id','=','financial_returns_bonus.user_id')
        ->select('users.account_number','financial_returns_bonus.*')
        ->whereHas('user',function($query) use($agent_id){
            $query->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
        })
        ->where(function ($query) use ($request) {
            $account_name=$request->input('account_name');
            $account_name && $query->where('account_number','like','%' . $account_name . '%');
           
            $start_time=$request->input('start_time');
            if(!empty($start_time)){
                $query->where('addtime','>=',strtotime($start_time));
            }
            $end_time=$request->input('end_time');
            if(!empty($end_time)){
                $query->where('addtime','<=',strtotime($end_time));
            }
            $type=$request->input('type');
            if(!empty($type)){
                $query->where('financial_returns_bonus.type',$type);
            }
        });


        // if(!empty($account_name)){
        //     $list=$list->whereHas('user',function($query) use ($account_name) {
        //         $query->where('account_number', 'like', '%' . $account_name . '%');
        //     });
        // }
        
      
        $list=$list->orderBy('id','desc')->paginate($limit);

        return $this->layuiData($list);

    }

    /***
     * 矿机分红详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userBonusList(Request $request){
        $limit=$request->input('limit','0');
        $id=$request->input('id');
        if(empty($id)){
            return $this->error('请选择矿机');
        }
        $list=FinancialReturnsBonus::where('user_financial_id',$id)->paginate($limit);
        return $this->layuiData($list);
    }

    /***
     * 提前到期
     * @param Request $request
     */
    public function acceleration(Request $request){
        $id=$request->input('id');
        $date = date('Ymd', time() -  24 * 60 * 60);
        if(empty($id)){
            return $this->error('请选择矿机');
        }
        //已分红数量
        $aready_bonus_num=FinancialReturnsBonus::where('user_financial_id',$id)->sum('num');
        //本金数量
        $user_financial=UserFinancial::find($id);
        $capital_num=$user_financial->num;
        //已获得邀请分红数量
        $parent_bonus_rate = Setting::getValueByKey('financial_invite_rate', '0');//上级邀请奖励比率
        $aready_invite_num=bcmul($parent_bonus_rate,$aready_bonus_num,8);

        //一次性给还本金和分红
        $bonus_num=bcsub($user_financial->bonus_num,$aready_bonus_num,8);
        if($bonus_num>0) {
            DB::beginTransaction();
            try {
                $invite_num = bcmul($bonus_num, $parent_bonus_rate, 8);
                $user_wallet = UsersWallet::where('user_id', $user_financial->user_id)
                    ->lockForUpdate()
                    ->where('currency', 3)
                    ->first();//USDT:3
                if (!$user_wallet) {
                    throw new \Exception('钱包不存在');
                }
                $user_financial->is_sum = 1;
                $user_financial->sum_time = time();
                $user_financial->is_return = 1;
                $user_financial->return_time = time();
                $user_financial->status = -1;
                $user_financial->remark = "提前到期";
                $user_financial->save();

                $mining_returns_bonus = new FinancialReturnsBonus();//分红
                $mining_returns_bonus->date = $date;
                $mining_returns_bonus->user_id = $user_financial->user_id;
                $mining_returns_bonus->user_financial_id = $id;
                $mining_returns_bonus->num = $bonus_num;
                $mining_returns_bonus->price = 1;
                $mining_returns_bonus->total = $user_financial->num;
                $mining_returns_bonus->rate = 1;
                $mining_returns_bonus->addtime = time();
                $mining_returns_bonus->is_return = 1;
                $mining_returns_bonus->return_time = time();
                $mining_returns_bonus->type = 1;
                $mining_returns_bonus->save();
                $result = change_wallet_balance($user_wallet, 2, $bonus_num, AccountLog::MINING_BONUS, '矿机分红-提前退还');
                if ($result !== true) {
                    throw new \Exception($result);
                }
                $mining_returns_bonus = new FinancialReturnsBonus();//本金
                $mining_returns_bonus->date = $date;
                $mining_returns_bonus->user_id = $user_financial->user_id;
                $mining_returns_bonus->user_financial_id = $id;
                $mining_returns_bonus->num = $user_financial->num;
                $mining_returns_bonus->price = 1;
                $mining_returns_bonus->total = $user_financial->num;
                $mining_returns_bonus->rate = 1;
                $mining_returns_bonus->addtime = time();
                $mining_returns_bonus->is_return = 1;
                $mining_returns_bonus->return_time = time();
                $mining_returns_bonus->type = 2;
                $mining_returns_bonus->save();
                $result = change_wallet_balance($user_wallet, 2, $user_financial->num, AccountLog::MINING_CAPITAL, '矿机本金退还-提前退还');
                if ($result !== true) {
                    throw new \Exception($result);
                }

                $user = Users::getById($user_financial->user_id);
                if ($user->parent_id != 0) {
                    $parent = Users::getById($user->parent_id);
                    $user_wallet = UsersWallet::where('user_id', $parent->id)
                        ->lockForUpdate()
                        ->where('currency', 3)
                        ->first();//USDT:3
                    //上级满足条件
                    $user_financial_num = Setting::getValueByKey('user_financial_num', '0');
                    $user_wallet_num=Setting::getValueByKey('user_wallet_num','0');
                    $user_ming_num=UserFinancial::where('user_id',$parent->id)->where('num','>=',$user_financial_num)->where('status',1)->count('num');
                    if($user_ming_num>0||$user_wallet->change_balance>$user_wallet_num) {
                        $result = change_wallet_balance($user_wallet, 2, $invite_num, AccountLog::MINING_PARENT_BONUS, '邀请分红-提前退还');
                        if ($result !== true) {
                            throw new \Exception($result);
                        }
                    }
                }
                DB::commit();
                return $this->success('操作成功');

            } catch (\Exception $exception) {
                return $this->error($exception);
                DB::rollBack();
            }
        }
        return $this->error('分红数小于0');

    }

    public function financialUserBonusList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        $start_time=$request->get('start_time',null);
        $end_time=$request->get('end_time',null);
        $list = Users::when($account != '', function ($query) use ($account) {
            $query->where("phone", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%')
                ->orWhere('account_number', 'like', '%' . $account . '%');
        })->orderBy('id', 'desc')
            ->paginate($limit);
        $res=array();
        foreach ($list->items() as $item){

            $buy_financial_num=UserFinancial::where('user_id',$item['id']);
            if(!empty($start_time)){
                $buy_financial_num=$buy_financial_num->where('create_time','>=',strtotime($start_time));
            }
            if(!empty($end_time)){
                $buy_financial_num=$buy_financial_num->where('create_time','<=',strtotime($end_time));
            }
            $buy_financial_num=$buy_financial_num->sum('num');

            $child_users=Users::where('parent_id',$item['id'])->get()->toArray();
            $idArr=array();
            foreach ($child_users as $child_item){
                array_push($idArr,$child_item['id']);
            }

            $child_financial_num=0;
            if(count($idArr)>0) {
                $child_financial_num = UserFinancial::whereIn('user_id', $idArr);
                if(!empty($start_time)){
                    $child_financial_num=$child_financial_num->where('create_time','>=',strtotime($start_time));
                }
                if(!empty($end_time)){
                    $child_financial_num=$child_financial_num->where('create_time','<=',strtotime($end_time));
                }
                $child_financial_num=$child_financial_num->sum('num');
            }

            $team_users=Users::where('parents_path','like', '%' . $item['id'] . '%')->get()->toArray();
            $member=Users::get()->toArray();
            $idArr=$this->getTeamUser($member,$item['id']);
//            $idArr=array();
//            foreach ($team_users as $child_item){
//                array_push($idArr,$child_item['id']);
//            }
            $team_financial_num=0;
            if(count($idArr)){
                $team_financial_num = UserFinancial::whereIn('user_id', $idArr);
                if(!empty($start_time)){
                    $team_financial_num=$team_financial_num->where('create_time','>=',strtotime($start_time));
                }
                if(!empty($end_time)){
                    $team_financial_num=$team_financial_num->where('create_time','<=',strtotime($end_time));
                }
                $team_financial_num=$team_financial_num->sum('num');
            }
            array_push($res, array(
                'id' => $item['id'],
                'phone' => $item['phone'],
                'email' => $item['email'],
                'buy_financial_num' => $buy_financial_num,//购买产品金额
                'child_financial_num'=>$child_financial_num,//直推总业绩
                'team_financial_num'=>$team_financial_num
            ));
        }
        return response()->json([
            'code' => 0,
            'data' => $res,
            'count' => $list->total(),
        ]);
    }
    public function getTeamUser($members, $mid){
        $Teams = array();//最终结果
        $mids = array($mid);//第一次执行时候的用户id
        do {
            $othermids = array();
            $state = false;
            foreach ($mids as $valueone) {
                foreach ($members as $key => $valuetwo) {
                    if ($valuetwo['parent_id'] == $valueone) //实名认证通过的团队人数
                    {
                        $Teams[] = $valuetwo['id'];//找到我的下级立即添加到最终结果中
                        $othermids[] = $valuetwo['id'];//将我的下级id保存起来用来下轮循环他的下级
                        //                        array_splice($members,$key,1);//从所有会员中删除他
                        $state = true;
                    }
                }
            }
            $mids = $othermids;//foreach中找到的我的下级集合,用来下次循环
        } while ($state == true);
        return $Teams;
    }

    public function invite_user_list(Request $request){
        $limit = $request->get('limit', 10);
        $user_id=$request->get('user_id');
        $list=Users::where('parent_id',$user_id)->orderBy('id', 'desc')
            ->paginate($limit);
        $res=array();
        foreach ($list->items() as $item){
            $buy_financial_num=UserFinancial::where('user_id',$item['id'])->sum('num');
            array_push($res, array(
                'id' => $item['id'],
                'phone' => $item['phone'],
                'email' => $item['email'],
                'buy_financial_num' => $buy_financial_num//购买产品金额
            ));
        }
        return response()->json([
            'code' => 0,
            'data' => $res,
            'count' => $list->total(),
        ]);

    }



}