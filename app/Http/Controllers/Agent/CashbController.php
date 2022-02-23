<?php

/**
 * 提币控制器
 */

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\WithdrawAuditEvent;
use PHPGangsta_GoogleAuthenticator;
use App\Models\{UsersWalletOut, UsersWallet, AccountLog, Agent, Currency, Setting, Users};

class CashbController extends Controller
{
    public function index()
    {
        $currencies = Currency::CurrencyData();
        return view('agent.cashb.index')->with('currencies', $currencies);
    }

    public function cashbList(Request $request)
    {
        $limit = $request->get('limit', 20);
        $account_number = $request->input('account_number', '');
        $start_time = $request->input('start_time', '');
        $end_time = $request->input('end_time', '');
        $status = $request->input('status', -1);
        $txid_status = $request->input('txid_status', -1);
        $currency = $request->input('currency', -1);
        $userWalletOut = new UsersWalletOut();
        $agent_id =Agent::getAgentId();

        $userWalletOutList = $userWalletOut->where(function ($query) use ($account_number) {
            if (!empty($account_number)) {
                $user = Users::where('phone', $account_number)
                    ->orWhere('account_number', $account_number)
                    ->orWhere('email', $account_number)
                    ->first();
                if (!empty($user)) {
                    $query->where('user_id', $user->id);
                }
            }
        })
        ->whereHas('user',function($query) use ($agent_id){
            $query->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
        })
        ->where(function ($query) use ($start_time, $end_time) {
            if (!empty($start_time)) {
                $start_time = strtotime($start_time);
                $query->where('create_time', '>=', $start_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $query->where('create_time', '<=', $end_time);
            }
        })->when($status > -1, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($txid_status > -1, function ($query) use ($txid_status) {
            $query->where('txid_status', $txid_status);
        })->when($currency > -1, function ($query) use ($currency) {
            $query->where('currency', $currency);
        })->orderBy('id', 'desc')->paginate($limit);


        foreach ($userWalletOutList as &$item) {
            $item['type_text'] = AccountLog::$trade_scene[$item['type']];
        }

        $sum = $userWalletOutList->sum('number');
        return $this->layuiData($userWalletOutList, $sum);
    }

    public function show(Request $request)
    {


        $id = $request->get('id', '');
        if (!$id) {
            return $this->error('参数小错误');
        }
        $walletout = UsersWalletOut::find($id);
        $in = AccountLog::where('type', AccountLog::ETH_EXCHANGE)
            ->where('user_id', $walletout->user_id)
            ->where('currency', $walletout->currency)
            ->sum('value');

        $builder = DB::table('account_log')
            ->join('wallet_log', 'account_log.id', '=', 'wallet_log.account_log_id')
            ->select('account_log.value', 'account_log.type', 'account_log.user_id', 'account_log.currency', 'wallet_log.balance_type')
            ->where('account_log.type', '=', AccountLog::ETH_EXCHANGE)
            ->where('account_log.user_id', '=', $walletout['user_id'])
            ->where('account_log.currency', '=', $walletout['currency']);

        $bindings = $builder->getBindings();
        $sql = str_replace('?', '%s', $builder->toSql());
        $query_sql = sprintf($sql, ...$bindings);


        $in_kj = DB::table(DB::raw("($query_sql) as res"))->where('balance_type', 1)->sum('value'); //$query->where('wallet_log.balance_type',1)->sum('account_log.value');         
        $in_zb = DB::table(DB::raw("($query_sql) as res"))->where('balance_type', 2)->sum('value');

        $out = UsersWalletOut::where('currency', $walletout->currency)
            ->where('user_id', $walletout->user_id)
            ->where('status', 2)
            ->sum('real_number');

        $out_kj = UsersWalletOut::where('currency', $walletout->currency)
            ->where('user_id', $walletout->user_id)
            ->where('status', 2)
            ->where('type', 1)
            ->sum('real_number');

        $out_zb = UsersWalletOut::where('currency', $walletout->currency)
            ->where('user_id', $walletout->user_id)
            ->where('status', 2)
            ->where('type', 2)
            ->sum('real_number');

        $use_chain_api = Setting::getValueByKey('use_chain_api', 0);
        $type_text = AccountLog::$trade_scene[$walletout->type];
        return view('agent.cashb.edit', [
            'wallet_out' => $walletout,
            'out' => $out,
            'in' => $in,
            'in_kj' => $in_kj,
            'in_zb' => $in_zb,
            'out_kj' => $out_kj,
            'out_zb' => $out_zb,
            'type_text' => $type_text,
            'use_chain_api' => $use_chain_api,
        ]);
    }

    public function done(Request $request)
    {
        set_time_limit(0);
        $id = $request->get('id', 0);
        $method = $request->get('method', '');
         $txid =  $request->get('txid', '');
        $notes = $request->get('notes', '');
        // $verificationcode = $request->input('verificationcode', '') ?? '';

        // $balance_type = [2, 'change', '币币'];
        // // $field_name = $balance_type[1] . '_balance';
        // $type = $balance_type[0];

        try {
            UsersWalletOut::AuditWithdraw($id, $method, $notes,$txid);
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            return $this->error($ex->getFile() . $ex->getLine() . $ex->getMessage());
        }
    }

    //导出用户列表至excel
    public function csv()
    {
        $data = USersWalletOut::all()->toArray();
        return Excel::create('提币记录', function ($excel) use ($data) {
            $excel->sheet('提币记录', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('账户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('虚拟币');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('提币数量');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('手续费');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('实际提币');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('提币地址');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('反馈信息');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('状态');
                });
                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('提币时间');
                });
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        $i = $key + 2;
                        if ($value['status'] == 1) {
                            $value['status'] = '申请提币';
                        } else if ($value['status'] == 2) {
                            $value['status'] = '提币成功';
                        } else {
                            $value['status'] = '提币失败';
                        }
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account_number']);
                        $sheet->cell('C' . $i, $value['currency_name']);
                        $sheet->cell('D' . $i, $value['number']);
                        $sheet->cell('E' . $i, $value['rate']);
                        $sheet->cell('F' . $i, $value['real_number']);
                        $sheet->cell('G' . $i, $value['address']);
                        $sheet->cell('H' . $i, $value['notes']);
                        $sheet->cell('I' . $i, $value['status']);
                        $sheet->cell('I' . $i, $value['create_time']);
                    }
                }
            });
        })->download('xlsx');
    }
}
