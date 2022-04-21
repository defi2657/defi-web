<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;

use Illuminate\Support\Facades\Config;

class UsersWallet extends Model
{
    protected $table = 'users_wallet';
    public $timestamps = false;
    /*const CREATED_AT = 'create_time';*/
    const CURRENCY_DEFAULT = "USDT";

    protected $hidden = [
        'private',
    ];

    protected $appends = [
        'currency_logo',
        'currency_name',
        'currency_type',
        'contract_address',
        'currency_decimal',
        'is_legal',
        'is_lever',
        'usdt_price',
        'cny_price',
        'nick_name',
        'spender_address'
    ];

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getSpenderAddressAttribute()
    {
        return null;
    }

    public function getNickNameAttribute($value)
    {
        return $this->user()->value('nickname') ?? '';
    }
    public function getCurrencyTypeAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'currency')->value('type');
    }

    // public function getExrateAttribute()
    // {
    //     // $value = $this->attributes['create_time'];
    //     return $ExRate = Setting::getValueByKey('USDTRate',6.5);;
    // }

    public function getCurrencyNameAttribute()
    {
        return $this->currency()->value('name');
    }
    public function getCurrencyDecimalAttribute()
    {
        return $this->currency()->value('decimal_scale');
    }

    public function getContractAddressAttribute()
    {
        return $this->currency()->value('contract_address');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }

    public function getCurrencyLogoAttribute()
    {
        return $this->currency()->value('logo');
    }

    public function getIsLeverAttribute()
    {
        return $this->currency()->value('is_lever');   
    }
    public function getIsLegalAttribute()
    {
        return $this->currency()->value('is_legal');
    }


    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }

    public static function makeWallet($user_id)
    {
        $currency = Currency::all();
        $uri = '/v3/wallet/address';
        $project_name = config('app.name');
        $http_client = app('LbxChainServer');
        $response = $http_client->post($uri, [
            'form_params' => [
                'userid' => $user_id,
                'projectname' => $project_name,
            ]
        ]);
        $result = json_decode($response->getBody()->getContents());
        if ($result->code != 0) {
            return false;
        }
        $address = $result->data;
        foreach ($currency as $key => $value) {
            // 判断对应币种钱包是否已存在
            if (self::where('user_id', $user_id)->where('currency', $value->id)->exists()) {
                continue;
            }
            $user_wallet = new self();
            $user_wallet->user_id = $user_id;
            $user_wallet->currency = $value->id;
            if ($value->make_wallet == 0) {
                continue;
            } elseif ($value->make_wallet == 1) {
                if ($value->type == 'btc') {
                    $user_wallet->address = $address->btc_address;
                    $user_wallet->private = $address->btc_private;
                } elseif ($value->type == 'usdt') {
                    $user_wallet->address = $address->usdt_address;
                    $user_wallet->private = $address->usdt_private;
                } elseif ($value->type == 'eth') {
                    $user_wallet->address = $address->eth_address;
                    $user_wallet->private = $address->eth_private;
                } elseif ($value->type == 'erc20') {
                    $user_wallet->address = $address->erc20_address;
                    $user_wallet->private =$address->erc20_private;
                } elseif ($value->type == 'xrp') {
                    $user_wallet->address = $address->xrp_address;
                    $user_wallet->private =$address->xrp_private;
                } else {
                    continue;
                }
            } elseif ($value->make_walelt == 2) {
                $user_wallet->address = $value->total_account;
                $user_wallet->private = '';
            }
            $user_wallet->create_time = time();
            $user_wallet->save();//默认生成所有币种的钱包
        }
    }
    public static  function makeWalletNew($user_id){
        $currency = Currency::all();
        foreach ($currency as $key=>$value){
            // 判断对应币种钱包是否已存在
            if (self::where('user_id', $user_id)->where('currency', $value->id)->exists()) {
                continue;
            }
            $user_wallet = new self();
            $user_wallet->user_id = $user_id;
            $user_wallet->currency = $value->id;
            if ($value->make_wallet == 0) {
                continue;
            } elseif ($value->make_wallet == 1) {
                if ($value->type == 'btc') {
                    $user_wallet->address = '';
                    $user_wallet->private = '';
                } elseif ($value->type == 'usdt') {
                    $user_wallet->address = '';
                    $user_wallet->private = '';
                } elseif ($value->type == 'eth') {
                    $user_wallet->address = '';
                    $user_wallet->private = '';
                } elseif ($value->type == 'erc20') {
                    $user_wallet->address = '';
                    $user_wallet->private ='';
                } elseif ($value->type == 'xrp') {
                    $user_wallet->address = '';
                    $user_wallet->private ='';
                } else {
                    continue;
                }
            } elseif ($value->make_walelt == 2) {
                $user_wallet->address = $value->total_account;
                $user_wallet->private = '';
            }
            $user_wallet->create_time = time();
            $user_wallet->save();//默认生成所有币种的钱包
        }
    }

    // public function getUsdtPriceAttribute()
    // {
    //     $last_price = 0;
    //     $currency_id = $this->attributes['currency'];
    //     $last = TransactionComplete::orderBy('id', 'desc')
    //         ->where("currency", $currency_id)
    //         ->where("legal", 1)->first();//1是pb
    //     if (!empty($last)) {
    //         $last_price = $last->price;
    //     }
    //     if ($currency_id == 1) {
    //         $last_price = 1;
    //     }
    //     return $last_price;
    // }

    public function getUsdtPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        $price= Currency::getUsdtPrice($currency_id);
        return  ($this->change_balance+$this->lock_change_balance) *$price;

    }

    public function getPbPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getPbPrice($currency_id);

    }

    public function getCnyPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        $price= Currency::getCnyPrice($currency_id);
        return  ($this->change_balance+$this->lock_change_balance) *$price;
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function getPrivateAttribute($value)
    {
        return empty($value) ? '' : decrypt($value);
    }

    public function setPrivateAttribute($value)
    {
        $this->attributes['private'] = encrypt($value);
    }

    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }

    public function getAddressAttribute()
    {
        $make_wallet = $this->currencyCoin->make_wallet ?? 0;
        if ($make_wallet == 1) {
            return $this->attributes['address'] ?? '';
        } elseif ($make_wallet == 2) {
            return $this->currencyCoin->total_account ?? '';
        } else {
            return '';
        }
    }
    
    
}