<?php

namespace App\Http\Controllers\Admin;

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
        return view('admin.virtual_profit.index');
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        // $account_number = $request->get('account_number', '');
        $result = new VirtualProfit();
        // if (!empty($account_number)) {
        //     $users = Users::where('account_number', 'like', '%' . $account_number . '%')->get()->pluck('id');
        //     $result = $result->whereIn('user_id', $users);
        // }
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function add(){
        $id = Input::get('id',null);
        if(empty($id)) {
            $mining_machine = new VirtualProfit();
        }else{
            $mining_machine = VirtualProfit::find($id);
            if($mining_machine == null) {
                abort(404);
            }
        }
        return view('admin.virtual_profit.add', ['financial' => $mining_machine]);
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

//        $this->validate($request, [
//            'mining_name' => 'required|min:1|max:64',
//            'describe'=>'required|min:1|max:125',
//            'price'=>'required',
//            'stock_num'=>'required',
//            'out_num'=>'required',
//            'sorts'=>'required',
//            'bonus'=>'',
//            'days'=>''
//        ]);
        // $validator = VirtualProfit::make(Input::all(), [
        //     'address'=>'required|min:1|max:125',
        //     'rate'=>'required|numeric'
        // ]);
        // if($validator->fails()) {
        //     return $this->error($validator->errors()->first());
        // }
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