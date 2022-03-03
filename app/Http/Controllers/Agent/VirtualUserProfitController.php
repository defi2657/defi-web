<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
USE App\DAO\BlockChainDAO;
USE App\DAO\CoinChainDAO;
use App\Jobs\UpdateBalance;
use App\Models\{Currency, VirtualProfit};
use Illuminate\Support\Facades\Input;

class VirtualProfitController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return view('agent.virtual_user_profit.index');
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        // $account_number = $request->get('account_number', '');
        $result = new VirtualProfit();
 
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function add(){
        $id = Input::get('id',null);
        if(empty($id)) {
            $mining_machine = new VirtualProfit();
            $randomNum=$this->GetRandStr(40);
            $mining_machine->address='0x'.$randomNum;
            $rdx=rand(12340114,99999999);
            $rdz=rand(0,100);
            $rdn=bcadd($rdz.'.'.$rdx,0,8);
            $mining_machine->money=$rdn;
        }else{
            $mining_machine = VirtualProfit::find($id);
            if($mining_machine == null) {
                abort(404);
            }
        }
        return view('agent.virtual_user_profit.add', ['financial' => $mining_machine]);
    }
    function GetRandStr($length){
        //字符组合
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($str)-1;
        $randstr = '';
        for ($i=0;$i<$length;$i++) {
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }
    public function  postAdd(Request $request){
        $id=$request->get('id');
        if (empty($id)){
            $mining_machine=new VirtualProfit();
        }else{
            $mining_machine=VirtualProfit::find($id);
            if ($mining_machine==null){
                return redirect()->back();
            }
        }

 
        $mining_machine->address=$request->input('address');
        $mining_machine->money=$request->input('money');
        $mining_machine->create_time=time();
        try {
            $mining_machine->save();
        }catch (\Exception $ex){
            // $validator->errors()->add('error', $ex->getMessage());
            // return $this->error($validator->errors()->first());
        }
        if (empty($id)){
            return $this->success('添加成功');
        }
        return $this->success('编辑成功');
    }

    public function del(Request $request)
    {
        $id = $request->get('id');
        $user = VirtualProfit::getById($id);
        if (empty($user)) {
            $this->error("虚拟数据");
        }
        try {
            $user->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
}