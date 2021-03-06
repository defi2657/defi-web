<?php


namespace App\Http\Controllers\Agent;

use App\Models\Agent;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Financial;
use App\Models\Users;
use App\Models\VistLog;
use DB;
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

        $list=DB::table('vist_log')->join('users','vist_log.code','=','users.extension_code')
        ->select(DB::raw("vist_log.*,users.nickname as code_name")  );
        $agent_id =Agent::getAgentId();
        $agent= Agent::find( $agent_id);
        $uid =$agent->user_id;
        
        $list= $list->where(function ($query) use ($request,$uid) {
          
            $user  = Users::find($uid);
            $extension_code=$user->extension_code;
            $agent_path = '%,'.$user->agent_path.',';

            $keyword = ($request->input('account', null));
            $start_time = ($request->input('start_time', null));
            $end_time = ($request->input('end_time', null));
            // $scene != -1 && $query->where('scene', $scene);
            $keyword && $query->where('ip','like','%'.$keyword.'%');
            // $account && $query->where('address',$account); 
            $extension_code && $query->whereRaw(DB::raw("CONCAT(',',agent_path,',') like '".$agent_path."' "));
            $start_time && $query->where('time', '>=',strtotime($start_time) );
            $end_time && $query->where('time', '<=', strtotime($end_time));
 
        });
        $list=$list->where('address','<>','null');
        // die($list->toSql());
        $list=$list->orderBy('id', 'desc')->paginate($limit);
        // $list=$list->orderBy('','')->paginate($limit);

        // $bindings = $list->getBindings();
        // $sql = str_replace('?', '%s', $list->toSql());
        // $query_sql = sprintf($sql, ...$bindings);

        foreach($list as &$item)
        {
            $item->time=date('Y-m-d H:i',$item->time);
        }
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
      

}