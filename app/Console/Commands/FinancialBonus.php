<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{AccountLog,
    Currency,
    LegalDeal,
    FinancialMachine,
    FinancialReturnsBonus,
    Setting,
    UserFinancial,
    Users,
    UsersWallet};

class FinancialBonus extends Command
{
     /**
     * 矿机分红
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FinancialBonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '矿机分红';

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
         $this->info('开始执行理财分红脚本-' . $now->toDateTimeString());
         $timeout = Setting::getValueByKey('legal_timeout', 0);
         if ($timeout <= 0) {
             return;
         }
         $before = $now->subMinutes(15)->timestamp;
 //        DB::beginTransaction();
 //        try {
 //
 //            DB::commit();
 //            $this->info('执行成功');
 //        } catch (\Exception $exception) {
 //            DB::rollback();
 //            $this->error($exception->getMessage());
 //        }
         $this->sum_financial();
    }

    public function  sum_financial_old(){

        $now = Carbon::now();
        $date = date('Ymd', time() -  6 * 60 * 60);
        $end_date=date('Ymd',time());
        $time = time();
        $this->info('统计分红开始-' . $now->toDateTimeString());

//        $user_machine=Userfinancial::where('start_date','<=',$date)
//            ->where('end_date','>=',$date)
//            ->where('is_sum',0)->select()->toArray();

        //统计分红比率
        DB::beginTransaction();
        try{
            //统计分红率
//            DB::update("UPDATE user_mining set bonus_num=rate*num WHERE `end_date`< $date AND `is_sum`=0");
            //写入分红
            DB::insert("INSERT INTO financial_returns_bonus (`date`,`user_id`,`user_financial_id`,`num`,`price`,`total`,`rate`,`addtime`,`is_return`,`return_time`,`type`)".
            "SELECT {$date},`user_id`,`id`,`num`*`rate`,1,`num`,`rate`,{$time},0,0,1 FROM user_financial ".
            "WHERE `start_date` <= {$date}  AND `is_sum` = 0");
            //写入本金
            DB::insert("INSERT INTO financial_returns_bonus (`date`,`user_id`,`user_financial_id`,`num`,`price`,`total`,`rate`,`addtime`,`is_return`,`return_time`,`type`)".
                "SELECT {$date},`user_id`,`id`,`num`,1,`num`,1,{$time},0,0,2 FROM user_financial ".
                "WHERE `end_date` <= {$end_date} AND `is_sum` = 0");
            //修改统计状态
            DB::update("UPDATE user_financial SET `is_sum` = 1,`sum_time` = {$time},status=0 WHERE `end_date` <= {$end_date} AND `is_sum` = 0");
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
        $this->info('统计分红结束-' . $now->toDateTimeString());
        $this->return_financial();
    }
    public function  sum_financial(){

        $now = Carbon::now();
        // $date = date('Ymd', time() -  24 * 60 * 60);
        $date = date('Ymd', time());
        $end_date=date('Ymd',time());
        $time = time();
        $this->info('统计分红开始-' . $now->toDateTimeString());

//        $user_machine=Userfinancial::where('start_date','<=',$date)
//            ->where('end_date','>=',$date)
//            ->where('is_sum',0)->select()->toArray();

        //统计分红比率
        DB::beginTransaction();
        try{
            $financial_list= UserFinancial::where('is_sum',0)->get();
            // $yestoday_returns=FinancialReturnsBonus::where('date',$date)->get();
            // $today_returns=FinancialReturnsBonus::where('date',$end_date)->get();
            foreach ($financial_list as $item) {
                # code...
                // if($this->had_returned($yestoday_returns,$item)) continue;//昨天分过                
                // $create_time=date('Y-m-d H:i:s',$item['create_time']);
                $diff_time=($time-strtotime($item['create_time']))/3600;

                // if($diff_time<12){
                if($diff_time<6){
                    //相差时间不到12小时
                    $this->info("({$diff_time})相差时间不到6小时 {$item->id} UserId:{$item->user_id}");
                    continue;
                }

                $create_seconde=date('H:i:s',strtotime($item['create_time']));
                $create_time_date=date('Ymd',strtotime($item['create_time']));
                // if($create_time_date==$end_date) continue;//当天不分
                //  if($create_seconde<date('H:i:s')){
                    {
                    //写入分红
                    $fenhong= new FinancialReturnsBonus();
                    $fenhong->date=$date;
                    $fenhong->user_id=$item['user_id'];
                    $fenhong->user_financial_id=$item['id'];
                    $fenhong->num=$item['num']*$item['rate'];
                    $fenhong->price=1;
                    $fenhong->total=$item['num'];
                    $fenhong->rate=$item['rate'];
                    $fenhong->addtime=$time;
                    $fenhong->is_return=0;
                    $fenhong->return_time=0;
                    $fenhong->type=1;
                    $fenhong->save();

                    // if($item['end_date']<=$end_date){
                    // if($create_seconde<date('H:i:s')){
                        // //写入本金
                        // $benj=new FinancialReturnsBonus();
                        // $benj->date=$date;
                        // $benj->user_id=$item['user_id'];
                        // $benj->user_financial_id=$item['id'];
                        // $benj->num=$item['num'];
                        // $benj->price=1;
                        // $benj->total=$item['num'];
                        // $benj->rate=1;
                        // $benj->addtime=$time;
                        // $benj->is_return=0;
                        // $benj->return_time=0;
                        // $benj->type=2;
                        // $benj->save();
                        //修改统计状态
                        $item_id=$item['id'];
                        DB::update("UPDATE user_financial SET `is_sum` = 1,`sum_time` = {$time},status=0 WHERE id= {$item_id} ");
                    // }
                    

                }
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
        $this->info('统计分红结束-' . $now->toDateTimeString());
        $this->return_financial();
    }
    public function had_returned($today_returns,$financial_item){
        foreach ($today_returns as $item) {
            # code...
            if($item['user_financial_id']==$financial_item['id']){
                return true;//有分
            }
        }
        return false;//今日未分
    }

    public function  return_financial(){
        $now = Carbon::now();
        //查询退还列表
        $returns= FinancialReturnsBonus::where('is_return',0)->get();
        $this->info('退还开始-' . $now->toDateTimeString());
        DB::beginTransaction();
        try{
            foreach ($returns as $return){
                $type=$return['type'];
                $user_wallet = UsersWallet::where('user_id', $return['user_id'])
                    ->lockForUpdate()
                    ->first();//TRX:18
                if (!$user_wallet) {
                    throw new \Exception('钱包不存在');
                }
                if($type=="分红"){
                    $from_account_log_type=AccountLog::FINANCIAL_BONUS;
                    $memo='理财分红';
                }else{
                    $from_account_log_type=AccountLog::FINANCIAL_CAPITAL;
                    $memo='理财本金退还';
                }

//                $memo = '矿机分红 矿机ID:'.$return['user_mining_id'];

                $number=$return['num'];
                $result = change_wallet_balance($user_wallet, 4, $number, $from_account_log_type, $memo);
                if ($result !== true) {
                    throw new \Exception($result);
                }
                $this->info('退还 用户：'.$return['user_id'].'理财ID：'.$return['user_financial_id'] .'时间：'. $now->toDateTimeString());
                $userMining=  UserFinancial::find($return['user_financial_id']);
                if($type=="分红"){
                    $userMining->bonus_num=bcadd($userMining->bonus_num,$number,8);
                }
                $userMining->is_return=1;
                $userMining->return_time=time();
                $userMining->save();

                $miningReturnsBonus=FinancialReturnsBonus::find($return['id']);
                $miningReturnsBonus->is_return=1;
                $miningReturnsBonus->return_time=time();
                $miningReturnsBonus->save();

            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
        $this->info('退还结束-' . $now->toDateTimeString());
    }
}