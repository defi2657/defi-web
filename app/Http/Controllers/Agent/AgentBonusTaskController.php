<?php

namespace App\Http\Controllers\Agent;


use App\Models\AgentBonusTask;
use App\Models\AdminModuleAction;
use App\Models\AdminRole;
use App\Models\AdminRolePermission;
use App\Models\Agent;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;

class AgentBonusTaskController extends Controller{

    public function index(){
        return view('agent.agent.index');
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $address = $request->get('address', '');       

        $list = AgentBonusTask::query();
        
        if(!empty($address)){
            $list=$list->where('address','like','%'.$address.'%');
        }
        $agent_id =Agent::getAgentId();
        $list=$list->whereHas('user',function($query) use($agent_id){
            $query->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
        });
      
        $list = $list->orderBy('id', 'desc')->paginate($limit);
        //dd($list->items());
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
}
?>