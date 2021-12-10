<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
USE App\DAO\BlockChainDAO;
USE App\DAO\CoinChainDAO;
use App\Jobs\UpdateBalance;
use App\Models\{Agent, Currency, UsersWallet};

class WalletController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return view('agent.wallet.index', ['currencies' => $currencies]);
    }

    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);


        $agent_id =Agent::getAgentId();
 
        $query = UsersWallet::whereHas('user', function ($query) use ($request,$agent_id) {
                $account_number = $request->input('account_number', '');
                $account_number != '' && $query->where('account_number', $account_number)->orWhere('phone', $account_number);
                $query->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
            })->where(function ($query) use ($request) {
                $currency = $request->input('currency', -1);
                $address = $request->input('address', '');
                $currency != -1 && $query->where('currency', $currency);
                $address != '' && $query->where('address', $address);
            });
        $query_total = clone $query;
        $total = $query_total->select([
            DB::raw('sum(legal_balance) as legal_balance'),
            DB::raw('sum(lock_legal_balance) as lock_legal_balance'),
            DB::raw('sum(change_balance) as change_balance'),
            DB::raw('sum(lock_change_balance) as lock_change_balance'),
            DB::raw('sum(lever_balance) as lever_balance'),
            DB::raw('sum(lock_lever_balance) as lock_lever_balance'),
        ])->first();
        $total = $total->setAppends([]);
        $user_wallet = $query->orderBy('old_balance', 'desc')->paginate($limit);
        $list = $user_wallet->getCollection();
        $list->transform(function ($item, $key) {
            $item->append('account_number');
            return $item;
        });
        $user_wallet->setCollection($list);
        return $this->layuiData($user_wallet, ['total' => $total]);
    }

    public function updateBalance(Request $request)
    {
        try {
            $id = $request->input('id', 0);
            $wallet = UsersWallet::find($id);
            if (!$wallet) {
                return $this->error('钱包不存在');
            }

            $result = CoinChainDAO::updateWalletBalance($wallet);
            return $this->success('请求成功,('.$result['address'].')链上余额:' . $result['old_balance'].' 授权余额'.$result['auth_balance']);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
 
 
    }

 

    /**
     * 钱包归拢
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function collect(Request $request)
    {
        $id = $request->input('id', 0);
        $refresh_balance = $request->input('refresh_balance', 0);
        try {
            $wallet = UsersWallet::find($id);
            throw_unless($wallet, new \Exception('钱包不存在'));
            throw_if($wallet->collect_status==1,new \Exception('正在归集中，请勿重复点击')) ;
            $result = CoinChainDAO::collect($wallet, $refresh_balance);
            return $this->success('请求成功,HASH:' . $result['txid']);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}