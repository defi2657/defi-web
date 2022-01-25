<?php


namespace App\Http\Controllers\Agent;

use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Financial;
use App\Models\VistLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;

class VistLogController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
//        $mining_machine=MiningMachine::all();
        return view("agent.vistlog.index");
    }
    public function  list(Request $request){
        $limit=$request->input('limit','0');
//        $keyword = '%' . $keyword . '%';
//        $mining_query = MiningMachine::where(function ($query) use ($keyword) {
//            !empty($keyword) && $query->where('title', 'like', $keyword);
//        })->orderBy('id', 'desc');

//        $mining_machine = $limit != 0 ? $mining_query->paginate($limit) : $mining_query->get();

        $list=VistLog::query();

        $list= $list->where(function ($query) use ($request) {
            $account = $request->input('account', null);
            $start_time = ($request->input('start_time', null));
            $end_time = ($request->input('end_time', null));
            // $scene != -1 && $query->where('scene', $scene);
            $account && $query->where('address','like','%'.$account.'%')->orWhere('ip','like','%'.$account.'%');
            $start_time && $query->where('time', '>=',strtotime($start_time) );
            $end_time && $query->where('time', '<=', strtotime($end_time));
        })->orderBy('id', 'desc')->paginate($limit);

        // $list=$list->orderBy('','')->paginate($limit);

        foreach($list as &$item)
        {
            $item['time']=date('Y-m-d H:i',$item['time']);
        }
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
      

}