<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\LegalDeal;
use App\Models\LegalDealSend;
use App\Models\Seller;
use App\Models\UserReal;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    public function index()
    {
        return view('admin.seller.index');
    }


    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account_number = $request->get('account_number', '');
        $result = new Seller();
        if (!empty($account_number)) {
            $users = Users::where('account_number', 'like', '%' . $account_number . '%')->get()->pluck('id');
            $result = $result->whereIn('user_id', $users);
        }
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $acceptor = new Seller();
            $acceptor->create_time = time();
        } else {
            $acceptor = Seller::find($id);
        }
        $banks = Bank::all();
        $currencies = Currency::where('is_legal', 1)->orderBy('id', 'desc')->get();
        return view('admin.seller.add')->with(['result' => $acceptor, 'banks' => $banks, 'currencies' => $currencies]);
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $account_number = $request->get('account_number', '');
        $name = $request->get('name', '');
        $mobile = $request->get('mobile', '');
        $currency_id = $request->get('currency_id', '');
        $seller_balance = $request->get('seller_balance', 0);
        $wechat_nickname = $request->get('wechat_nickname', '') ?? '';
        $wechat_account = $request->get('wechat_account', '') ?? '';
        $wechat_collect = $request->get('wechat_collect', '') ?? '';
        $ali_nickname = $request->get('ali_nickname', '') ?? '';
        $ali_account = $request->get('ali_account', '') ?? '';
        $alipay_collect = $request->get('alipay_collect', '') ?? '';
        $bank_id = $request->get('bank_id', 0);
        $bank_account = $request->get('bank_account', '') ?? '';
        $bank_address = $request->get('bank_address', '') ?? '';

        //???????????????????????????
        $messages  = [
            'required'       => ':attribute ???????????????',
        ];

        $validator = Validator::make($request->all(), [
            'account_number' => 'required',
            'name' => 'required',
            'mobile' => 'required',
            'currency_id' => 'required',
            'seller_balance' => 'required',
            'wechat_nickname' => 'required',
            'wechat_account' => 'required',
            'wechat_collect' => ['sometimes', 'nullable', 'string', 'min:10', 'regex:/\.(|bmp|jpg|jpeg|png|gif|svg)$/'],
            'ali_nickname' => 'required',
            'ali_account' => 'required',
            'alipay_collect' => ['sometimes', 'nullable', 'string', 'min:10', 'regex:/\.(|bmp|jpg|jpeg|png|gif|svg)$/'],
            'bank_id' => 'required',
            'bank_account' => 'required',
            'bank_address' => 'required',
        ], $messages);

        //?????????????????????
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $self = Users::where('account_number', $account_number)->first();
        if (empty($self)) {
            return $this->error('?????????????????????????????????');
        }
        $real = UserReal::where('user_id', $self->id)->where('review_status', 2)->first();
        if (empty($real)) return $this->error('?????????????????????????????????');
        $currency = Currency::find($currency_id);
        if (empty($currency)) {
            return $this->error('???????????????');
        }
        if (empty($currency->is_legal)) {
            return $this->error('??????????????????');
        }
        $has = Seller::where('name', $name)->where('user_id', '!=', $self->id)->where('currency_id', $currency_id)->first();
        if (empty($id) && !empty($has)) {
            return $this->error('????????? ' . $name . ' ?????????????????????');
        }
        $has_user = Seller::where('user_id', $self->id)->where('currency_id', $currency_id)->first();
        if (!empty($has_user) && empty($id)) {
            return $this->error('??????????????????????????????');
        }

        if (empty($id)) {
            $acceptor = new Seller();
            $acceptor->create_time = time();
        } else {
            $acceptor = Seller::find($id);
        }
        $acceptor->user_id = $self->id;
        $acceptor->name = $name;
        $acceptor->mobile = $mobile;
        $acceptor->currency_id = $currency_id;
        $acceptor->seller_balance = floatval($seller_balance);
        $acceptor->wechat_nickname = $wechat_nickname;
        $acceptor->wechat_account = $wechat_account;
        $acceptor->wechat_collect = $wechat_collect;
        $acceptor->ali_nickname = $ali_nickname;
        $acceptor->alipay_collect = $alipay_collect;
        $acceptor->ali_account = $ali_account;
        $acceptor->bank_id = intval($bank_id);
        $acceptor->bank_account = $bank_account;
        $acceptor->bank_address = $bank_address;
        try {
            $acceptor->save();
            return $this->success('????????????');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }


    public function delete(Request $request)
    {
        $id = $request->get('id', 0);
        $acceptor = Seller::find($id);
        if (empty($acceptor)) {
            return $this->error('????????????');
        }
        try {
            $acceptor->delete();
            return $this->success('????????????');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }


    public function sendBack(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('????????????');
        }
        $send = LegalDealSend::find($id);
        if (empty($send)) {
            return $this->error('????????????');
        }
        DB::beginTransaction();
        try {
            LegalDealSend::sendBack($id);
            DB::commit();
            return $this->success('??????????????????,?????????????????????????????????');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    public function sendDel(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('????????????');
        }

        $is = LegalDealSend::isHasIncompleteness($id);
        if ($is) {
            return $this->error('???????????????????????????????????????????????????????????????????????????');
        }

        DB::beginTransaction();
        try {
            $send = LegalDealSend::lockForUpdate()->find($id);
            if (empty($send)) {
                return $this->error('????????????');
            }
            $deals = LegalDeal::where('legal_deal_send_id', $id)->get();
            if (!empty($deals)) {
                foreach ($deals as $deal) {
                    $deal->delete();
                }
            }
            if ($send->type == 'sell') {
                $seller = Seller::lockForUpdate()->find($send->seller_id);
                if (!empty($seller)) {
                    $seller->increment('seller_balance', $send->surplus_number);
                    $seller->decrement('lock_seller_balance', $send->surplus_number);
                }
            }

            $send->delete();
            DB::commit();
            return $this->success('????????????');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }



    public function is_shelves(Request $request)
    {
        $id = $request->get('id', 0);
        $is_shelves = $request->get('is_shelves', 1);
        if (empty($id)) {
            return $this->error('????????????');
        }
        $send = LegalDealSend::find($id);
        if (empty($send)) {
            return $this->error('????????????');
        }
        if (empty($send->is_shelves)) {
            $send->is_shelves = 1;
            $send->save();
        }
        DB::beginTransaction();
        try {
            if ($send->is_shelves == 1) {
                $send->is_shelves = 2;
            } elseif ($send->is_shelves == 2) {
                $send->is_shelves = 1;
            }
            $send->save();
            DB::commit();
            return $this->success('????????????');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    //??????????????????
    public function applyStatus(Request $request){

        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error('????????????');
        }
        $seller=Seller::find($id);

        try {
            if ($seller->status == 1) {
                $seller->status = 0;
            } elseif ($seller->status == 0) {
                $seller->status = 1;
            }

            $seller->save();

            return $this->success('????????????');
        } catch (\Exception $exception) {

            return $this->error($exception->getMessage());
        }

    }

}