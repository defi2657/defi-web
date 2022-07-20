<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
USE App\DAO\BlockChainDAO;
USE App\DAO\CoinChainDAO;
use App\Jobs\UpdateBalance;
use App\Models\{Currency, UsersWallet, WalletApprove};
use Illuminate\Support\Facades\Input;

class WalletApproveController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return view('admin.wallet_approve.index', ['currencies' => $currencies]);
    }
 
 
 
    
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
     
        $result = new WalletApprove();
       
      
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
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