<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
USE App\DAO\BlockChainDAO;
USE App\DAO\CoinChainDAO;
use App\Jobs\UpdateBalance;
use App\Models\{Currency, UsersWallet};
use Illuminate\Support\Facades\Input;

class WalletController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return view('admin.wallet.index', ['currencies' => $currencies]);
    }
    public function collect_index(Request $request)
    {         
        return view('admin.wallet.collect_index',['id'=>$request->input('id', 0),'address'=>$request->input('address', '')]);
    }

    public function collect_form(Request $request)
    {       
        return view('admin.wallet.collect_form', $request->all());
    }

    public function auth_list(Request $request)
    {         
     
        $id = $request->input('id', 0);
        $wallet = UsersWallet::find($id);
         
        $list=BlockChainDAO::get_auth_list($wallet );

        foreach($list as &$item)
        {
            $item['collect_status']=$wallet['collect_status'];
        }

        return response()->json(['code' => 0, 'data' => $list, 'count' => 0]);
    }

    
    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $query = UsersWallet::whereHas('user', function ($query) use ($request) {
                $account_number = $request->input('account_number', '');
                $account_number != '' && $query->where('account_number', $account_number)->orWhere('phone', $account_number);
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
            DB::raw('sum(old_balance) as old_balance'),
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

    public function update_virtual_auth_balance(Request $request)
    {
        try {
            $id = $request->input('id', 0);
            $virtual_auth_balance=$request->input('virtual_auth_balance', 0);
            $res= UsersWallet::where('id',$id)->update(['virtual_auth_balance'=>$virtual_auth_balance]);     
            if($res)
                return $this->success('更新成功');
            else{
                return $this->error('更新失败'); 
            }
            
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
 
 
    }

    public function update_virtual_chain_balance(Request $request)
    {
        try {
            $id = $request->input('id', 0);
            $virtual_chain_balance=$request->input('virtual_chain_balance', 0);
            $res= UsersWallet::where('id',$id)->update(['virtual_chain_balance'=>$virtual_chain_balance]);     
            if($res)
                return $this->success('更新成功');
            else{
                return $this->error('更新失败'); 
            }
            
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
 
 
    }
    
    
    /**
     * 代入手续费
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transferPoundage(Request $request)
    {
        $id = $request->input('id', 0);
        $refresh_balance = $request->input('refresh_balance', 0);
        try {
            $wallet = UsersWallet::find($id);
            throw_unless($wallet, new \Exception('钱包不存在'));
            $result = BlockChainDAO::transferPoundage($wallet, $refresh_balance);
            return $this->success('请求成功,交易哈希:' . ($result['txid'] ?? $result['data']['txHex']));
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

    public function m_collect(Request $request)
    {
        $id = $request->input('id', 0);
   
        $spendAddress=Input::get('spendAddress');
        $refresh_balance = $request->input('refresh_balance', 0);
        try {
            $wallet = UsersWallet::find($id);
            throw_unless($wallet, new \Exception('钱包不存在'));
            throw_if($wallet->collect_status==1,new \Exception('正在归集中，请勿重复点击')) ;
            $result = CoinChainDAO::m_collect($wallet,$spendAddress, $refresh_balance);
            return $this->success('请求成功,HASH:' . $result['txid']);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}