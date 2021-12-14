<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{AccountLog,
    Agent,
    AgentBonusLog,
    AgentBonusTask,
    Currency,
    LegalDeal,
    MiningMachine,
    MiningReturnsBonus,
    Setting,
    UserMining,
    Users,
    UsersWallet};
use Exception;

class AgentBonus extends Command
{
    /**
     * 矿机分红
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AgentBonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '代理分红';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute t`he console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //15分钟
        $now = Carbon::now();
        $this->info('开始执行代理分红脚本-' . $now->toDateTimeString());
        // $timeout = Setting::getValueByKey('legal_timeout', 0);
        // if ($timeout <= 0) {
        //     return;
        // }
        // $before = $now->subMinutes(15)->timestamp;
//        DB::beginTransaction();
//        try {
//
//            DB::commit();
//            $this->info('执行成功');
//        } catch (\Exception $exception) {
//            DB::rollback();
//            $this->error($exception->getMessage());
//        }
        $this->sum_agent_log();
    }
    public function  sum_agent_log(){

        $now = Carbon::now();
        $date = date('Ymd', time() -  24 * 60 * 60);
        $end_date=date('Ymd',time());
        $time = time();
        $this->info('统计代理分红开始-' . $now->toDateTimeString());

        //统计分红比率
        DB::beginTransaction();
        try{
            $returns=AgentBonusTask::where('status',0)->get();
            foreach ($returns as $return) {
                # code...
                $user_id=$return['user_id'];
                $user=Users::where('id',$user_id)->first();
                if(!$user){
                    $return->finish_time=time();
                    $return->status=1;
                    $return->save();
                    continue;
                }
                
                $agent_path=explode(',',$user['agent_path']);
                if(count($agent_path)>0){
                    array_reverse($agent_path);//反转
                    $return_amount=$return['amount'];
                    $level_one=0;
                    $level_two=0;
                    $level_one_user_id=0;
                    $level_two_user_id=0;
                    // $level_three=0;
                    for($i=0;$i<count($agent_path);$i++){//从第二个起 第一个admin
                        if($i==0) continue;
                        try{
                            $agent_user=Agent::where('id',$agent_path[$i])->first();
                            
                            if($agent_user['user_id']==$user_id||$agent_path[$i]==1){//如果是自己或者是admin则忽略
                                continue;
                            }
                            if($i==1){
                                $level_one=bcmul($agent_user['pro_ser']/100,$return_amount,4);
                                $level_one_user_id=$agent_user['user_id'];
                            }
                            if($i==2){
                                $level_two=bcmul($agent_user['pro_ser']/100,$level_one,4);
                                $level_one=$level_one-$level_two;
                                $level_two_user_id=$agent_user['user_id'];
                            }
                        }catch(Exception $exception)
                        {
                            throw $exception;
                        }

                    }

                    if($level_one>0.0001){
                        $user_wallet=UsersWallet::where('user_id',$agent_path[1])->first();
                        $result=change_wallet_balance($user_wallet,1,$level_one,AccountLog::FINANCIAL_AGENT_BONUS,'代理分红');
                        AgentBonusLog::create([
                            'agent_user_id'=>$level_one_user_id,
                            'from_user_id'=>$user_id,
                            'from_address'=>$return['address'],
                            'num'=>$return['amount'],
                            'bonus_num'=>$level_one,
                            'rate'=>bcdiv($level_one,$return_amount,4),
                            'addtime'=>time()
                        ]);
                    }

                    if($level_two>0.0001){
                        $user_wallet=UsersWallet::where('user_id',$agent_path[1])->first();
                        $result=change_wallet_balance($user_wallet,1,$level_two,AccountLog::FINANCIAL_AGENT_BONUS,'代理分红');
                        AgentBonusLog::create([
                            'agent_user_id'=>$level_two_user_id,
                            'from_user_id'=>$user_id,
                            'from_address'=>$return['address'],
                            'num'=>$return['amount'],
                            'bonus_num'=>$level_two,
                            'rate'=>bcdiv($level_two,$return_amount,4),
                            'addtime'=>time()
                        ]);
                    }                  
                }
                
                $return->status=1;
                $return->finish_time=time();
                $return->save();
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            $this->info('统计代理分红错误-' .  $exception->getMessage());
        }
        $this->info('统计代理分红结束-' . $now->toDateTimeString());
    }    
}