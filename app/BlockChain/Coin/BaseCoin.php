<?php

namespace App\BlockChain\Coin;

use App\Models\ChargeHash;
use App\Models\ChainHash;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;

abstract class BaseCoin implements CoinInterface
{
    protected $projectName = ''; //项目名称

    protected $apiBaseUrl = '';

    protected $coinCode = ''; //币种标识

    protected $decimalScale = 0; //小数位数

    protected $contractToken = ''; //代币名称或合约地址

    protected $generateUri = '/v3/wallet/address'; //生成钱包地址

    protected $balanceUri = ''; //查询余额地址

    protected $transferUri = ''; //转账地址

    protected $transactionUri = ''; //交易记录地址

    protected $billsUri = ''; //查询账单地址

    const TYPE_COLLECT = 1; //1.归拢
    const TYPE_SEND_FEE = 2; //2.打入手续费
    const TYPE_WITHDRAW = 3; //3.提币

    public function __construct($decimal_scale = 0, $contract_token = '', $project_name = '', $api_base_url = '')
    {
        $this->projectName = $project_name != '' ? $project_name : config('app.name');
        $this->apiBaseUrl = $api_base_url != '' ? $api_base_url : config('lbxchain.wallet_api');
        $decimal_scale >= 0 && $this->decimalScale = $decimal_scale;
        $this->contractToken = $contract_token;
        App::singleton($this->coinCode . 'ChainClient', function () {
            return new Client(['base_uri' => $this->apiBaseUrl]);
        });
    }

    /**
     * 查账单
     *
     * @param string $address 钱包地址
     * @return array
     */
    public function getBills($address)
    {
        return [];
    }

    /**
     * 生成钱包
     *
     * @param integer $user_id 用户id
     * @return array
     */
    public function makeWallet($user_id)
    {
        $uri = $this->generateUri;
        $chain_client = app($this->coinCode . 'ChainClient');
        $response = $chain_client->request('post', $uri, [
            'form_params' => [
                'userid' => $user_id,
                'projectname' => $this->projectName,
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 查询余额
     *
     * @param string $address 钱包地址
     * @return array
     */
    public function getBalance($address)
    {             
        $uri = $this->balanceUri;
   
        $params = [
            'query' => [
                'address' => $address,
                'tokenaddress' => $this->contractToken,
            ],
        ];
        $chain_client = app($this->coinCode . 'ChainClient');
 
		$uri=$uri.'?address='.$address.'&tokenaddress='. $this->contractToken;
        $response = $chain_client->request('get', $uri, $params);
                
   
 
        $result = json_decode($response->getBody()->getContents(), true);
           
        if (!isset($result['code']) || $result['code'] != 0) {
        
            throw new \Exception('请求链上接口发生异常');
        }

        $balance = $result['data']['balance'];
 
        return $balance;
    }

    /**
     * 转账
     *
     * @param integer $scene 场景
     * @param float $amount 转出金额
     * @param string $receiver 接收者(转入)
     * @param string $sender 发送者(转出)
     * @param string $sender_private_key 发送者私钥(转出方私钥)
     * @param float $fee 矿工手续费
     * @param string $captcha 验证码
     * @return array
     */
    public function transfer($scene, $amount, $receiver, $sender, $sender_private_key, $fee = 0, $captcha = '')
    {        
        if (!in_array($scene, [self::TYPE_COLLECT, self::TYPE_SEND_FEE, self::TYPE_WITHDRAW])) {
            throw new \Exception('场景参数错误');
        }
        if (bc_comp($amount, '0') <= 0) {
            throw new \Exception('转账金额必须大于0');
        }
        if ($receiver == '') {
            throw new \Exception('接收地址不能为空');
        }
        if ($sender == '') {
            throw new \Exception('发送地址不能为空');
        }
        if ($sender_private_key == '') {
            throw new \Exception('私钥不能为空');
        }
        // $fee = $this->convertEnlarge($fee);
        // $amount = $this->convertEnlarge($amount);
        $uri = $this->transferUri;
        $params = [
        	// 'debug'=>true,
			'headers' => [
               'Accept'=> 'application/json',
               'Content-Type'=>'application/json'
            ],
            'json' => [
                'type'=> $scene,
                'fromaddress' => $sender,
                'privkey' => $sender_private_key,
                'toaddress' => $receiver,
                'amount' => $amount,
                'tokenaddress' => $this->contractToken,
                'fee' => $fee,
                'verificationcode' => $captcha,
            ],
        ];
         //return json_encode($params);
        $chain_client = app($this->coinCode . 'ChainClient');
        $response = $chain_client->request('post', $uri, $params);
        $result = json_decode($response->getBody()->getContents(), true);
        return $result;
    }

    /**
     * 查交易
     *
     * @param string $hash
     * @return  array
     */
    public function getTransaction($hash)
    {
        $uri = $this->transactionUri;
        $params = [
            'query' => [
                'hash' => $hash,
            ]
        ];
        $chain_client = app($this->coinCode . 'ChainClient');
        $response = $chain_client->request('get', $uri, $params);
        $result = json_decode($response->getBody()->getContents(), true);
        return $result;
    }

    /**
     * 转换为链上操作数字(放大)
     *
     * @param string $amount 转换的金额,最好为字符串型以避免精度丢失
     * @return string
     */
    public function convertEnlarge($amount) : string
    {
        $ratio = bc_pow(10, $this->decimalScale);
        return bc_mul($amount, $ratio, 0);
    }

    /**
     * 转换为正常数字(缩小)
     *
     * @param string $amount 转换的金额,最好为字符串型以避免精度丢失
     * @return string
     */
    public function convertNarrow($amount) : string
    {
        $ratio = bc_pow(10, $this->decimalScale);
        return bc_div($amount, $ratio, $this->decimalScale);
    }


    //充币监听 插入hash记录
    public function insertHash($data)
    {
        if (method_exists($this, 'parseBlockData')) {
            $da = $this->parseBlockData($data);
            ChargeHash::create($da);
        }

    }



}