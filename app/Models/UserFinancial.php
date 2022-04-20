<?php


namespace App\Models;
use App\DAO\UserDAO;
use App\Utils\RPC;
use Exception;

class UserFinancial extends Model
{
    protected $table = 'user_financial';
    public $timestamps = false;
    protected $appends = [
        'account_number',
        'nick_name'
    ];
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }
    public function getCreateTimeAttribute($value){
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }

    public function getNickNameAttribute($value)
    {
        return $this->user()->value('nickname') ?? '';
    }

    public static function buyFinancial($user_id,$currency_id,$num){
        $financial_id=1;
        $buy_num=1;
        // $financial_machine=Financial::find($financial_id);
        // if (empty($financial_machine)){
        //     throw new \Exception('产品不存在');
        // }
        $user_wallet = UsersWallet::where("user_id", $user_id)
                ->where("currency", $currency_id)
                ->lockForUpdate()
                ->first();
                
        if ($user_wallet->token_balance > 0) {//有未领取金额就不买矿机
            throw new Exception('有未领取金额就不买矿机');
            return;
        }

        $user=Users::getById($user_id);
        // $parents= UserDAO::getParentsPathDesc($user);
        // $parent_ids=implode(',',$parents);
        // $user_report_list=self::getUserReportList($parent_ids);
        $config= BondConfig::Intnace();
        $runing_count=UserFinancial::where('user_id',$user['id'])->where('is_return',0)->count();
        if($runing_count>0){//有运行中的矿机
            throw new Exception('有运行中的矿机');
            return;
        }
        for ($x=0;$x<$buy_num;$x++){
            //根据配置获取收益率
            $rate=$config->getStaticBonusConfig($num);
            // $rate=$financial_machine->rate;//年化收益率
            $days=1;//天数
            $day_bonus=bcmul($rate,$num,8);
            // $bonus_num=bcmul($day_bonus,$days,8);
            $user_financial=new UserFinancial();
            $user_financial->financial_id=$financial_id;
            $user_financial->user_id=$user_id;
            $user_financial->financial_name='理财';
            $user_financial->num=$num;
            $user_financial->rate=$rate;
            $user_financial->days=$days;
            $user_financial->day_bonus=$day_bonus;
            $user_financial->currency_id=$currency_id;
            $start_date=date('Ymd',time());
            $days=$days;
            $end_date=date('Ymd',strtotime('+'.$days.' day'));
            $user_financial->start_date=$start_date;
            $user_financial->end_date=$end_date;
            // $user_financial->bonus_num=$bonus_num;
            $user_financial->is_sum=0;
            $user_financial->is_return=0;
            $user_financial->status=1;
            $user_financial->create_time=time();
            $user_financial->is_newuser=0;
            $user_financial->currency=$user_wallet->currency;

            // //上级动态收益
            // $parent_bonus_num=0;
            // $guid=self::create_guid();
            // for ($i=0; $i <count($parents); $i++) { 
            //     # code...
            //     $parent_id= $parents[$i];
            //     $team_charge=self::getReportBuyReport($user_report_list,$parent_id);
            //     $parent_financial_count=UserFinancial::where('user_id',$parent_id)->count();
            //     if($parent_financial_count<=0){
            //         continue;
            //     }
            //     // if($team_charge->recharge>0){
               
            //     $total_recharge=bcadd($team_charge['team_total_recharge_kj'],$team_charge['total_recharge_kj'],2);
            //     $rate=$config->getFundDynamicConfig($total_recharge,$i+1);
            //     if($rate>0){
            //         $rate_num= $num*$rate;
            //         $parent_bonus_num+=$rate_num;
            //         $parent_user_wallet = UsersWallet::where("user_id", $parent_id)
            //         ->where("currency", $currency_id)
            //         ->lockForUpdate()
            //         ->first();
            //         $dai=$i+1;
            //         $str='S '.$dai.'D '.$user_wallet->address.' '.$num.' '.$total_recharge.' ';
            //         change_wallet_balance($parent_user_wallet, 1, $rate_num, AccountLog::FINANCIAL_PARENT_BONUS, $str,false,0,0,'',false,false,$guid);
            //     }
            // }
            // $user_financial->parent_bonus_num=$parent_bonus_num;//上级用户总分红
            $user_financial->save();
        }
        $user->can_transfer_num=$num;
        $user->save();
    }
    
    public static function  create_guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12);
        return $uuid;
    }

    public static function getReportBuyReport($report_list,$uid){
        foreach ($report_list as $item) {
            # code...
            if($item['uid']==$uid){
                return $item;
            }
        }
        return null;
    }

    public static function getUserReportList($user_id){
        $date=date('Y-m-d',time());
        $address_url = 'http://127.0.0.1:5566/show/user_report_list?day='.$date.'&uids=' .$user_id ;
        $res = RPC::apihttp($address_url,null,null,30);
        $res = @json_decode($res, true);
        $res['call_date']=$date;
        return $res;
    }

    public static function addFinancial($user_id,$num,$currency_id){
        $old_user_financial=self::where('user_id',$user_id)->first();
        $is_futou=0;
        if(!empty($old_user_financial)){
            $is_futou=1;
        }
        $user_wallet = UsersWallet::where(['user_id'=>$user_id,'currency'=>$currency_id])->first();
        change_wallet_balance($user_wallet, 2, -$num, AccountLog::MINING_BALANCE_OUT, '购买套餐');

        $user_mining=new self();
        $user_mining->user_id=$user_id;
        $user_mining->financial_name=$num;
        $user_mining->num=$num;      
        $user_mining->total_num=$num;
        $user_mining->rate=1;
        $user_mining->days=0;
        $user_mining->day_bonus=0;
        $user_mining->currency_id=18;
        $user_mining->start_date=0;
        $user_mining->end_date=0;
        $user_mining->bonus_num=0;
        $user_mining->is_sum=0;
        $user_mining->is_return=0;
        $user_mining->status=1;
        $user_mining->create_time=time();
        $user_mining->is_futou=$is_futou;
        $user_mining->save();
    }

}