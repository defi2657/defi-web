<!-- <div class="layui-form-item">
    <label class="layui-form-label">版本号</label>
    <div class="layui-input-inline">
        <input type="text" name="version" autocomplete="off" class="layui-input"
            value="@if(isset($setting['version'])){{$setting['version'] ?? ''}}@endif">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">USDT汇率</label>
    <div class="layui-input-inline">
        <input type="text" name="USDTRate" autocomplete="off" class="layui-input"
            value="@if(isset($setting['USDTRate'])){{$setting['USDTRate']}}@endif">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">法币交易超时</label>
    <div class="layui-input-inline">
        <input type="text" name="legal_timeout" autocomplete="off" class="layui-input"
            value="@if(isset($setting['legal_timeout'])){{$setting['legal_timeout']}}@endif">
    </div><div class="layui-form-mid layui-word-aux">法币交易超时分钟</div>

</div>
<div class="layui-form-item">
    <label class="layui-form-label">用户id超始值</label>
    <div class="layui-input-inline">
        <input type="text" name="uid_begin_value" autocomplete="off" class="layui-input"
            value="@if(isset($setting['uid_begin_value'])){{$setting['uid_begin_value']}}@endif">
    </div>
    <div class="layui-form-mid layui-word-aux">用户id的起始值,仅用于显示</div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">邀请码必填</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="invite_code_must" value="1" title="是" @if (isset($setting['invite_code_must'])) {{$setting['invite_code_must'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="invite_code_must" value="0" title="否" @if (isset($setting['invite_code_must'])) {{$setting['invite_code_must'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">注册后跳转</label>
    <div class="layui-input-block">
        <div class="layui-input-inline" style="width: 400px">
            <input type="text" class="layui-input" name="registered_jump" value="{{$setting['registered_jump'] ?? ''}}" placeholder="用于注册后跳转到指定地址,留空不跳转" >
        </div>
    </div>
</div> -->
<div class="layui-form-item">
    <label class="layui-form-label">ETH授权地址</label>
    <div class="layui-input-inline">
        <input type="text" name="eth_address" autocomplete="off" style="width: 500px;" disabled class="layui-input"
            value="@if(isset($setting['eth_address'])){{$setting['eth_address']}}@endif">
    </div>
 
</div>
<div class="layui-form-item">
    <label class="layui-form-label">ETH提现地址</label>
    <div class="layui-input-inline">
        <input type="text" name="eth_out_address" autocomplete="off" style="width: 500px;" disabled class="layui-input"
            value="@if(isset($setting['eth_out_address'])){{$setting['eth_out_address']}}@endif">
    </div>
 
</div>
<div class="layui-form-item">
    <label class="layui-form-label">ETH收账地址</label>
    <div class="layui-input-inline">
        <input type="text" name="eth_rev_address" autocomplete="off" style="width: 500px;" disabled class="layui-input"
            value="@if(isset($setting['eth_rev_address'])){{$setting['eth_rev_address']}}@endif">
    </div>
 
</div>
<!-- <div class="layui-form-item">
    <label class="layui-form-label">TRX授权地址</label>
    <div class="layui-input-inline">
        <input type="text" name="trx_address" autocomplete="off" style="width: 500px;" disabled class="layui-input"
            value="@if(isset($setting['trx_address'])){{$setting['trx_address']}}@endif">
    </div>
 
</div>
<div class="layui-form-item">
    <label class="layui-form-label">TRX提现地址</label>
    <div class="layui-input-inline">
        <input type="text" name="trx_out_address" autocomplete="off" style="width: 500px;" disabled class="layui-input"
            value="@if(isset($setting['trx_out_address'])){{$setting['trx_out_address']}}@endif">
    </div>
 
</div>
<div class="layui-form-item">
    <label class="layui-form-label">TRX收款地址</label>
    <div class="layui-input-inline">
        <input type="text" name="trx_rev_address" autocomplete="off" style="width: 500px;" disabled class="layui-input"
            value="@if(isset($setting['trx_rev_address'])){{$setting['trx_rev_address']}}@endif">
    </div>
 
</div> -->
<div class="layui-form-item">
    <label class="layui-form-label">ValidNode</label>
    <div class="layui-input-inline">
        <input type="text" name="valid_node" autocomplete="off" class="layui-input"
            value="@if(isset($setting['valid_node'])){{$setting['valid_node']}}@endif">
    </div>
    <div class="layui-form-mid layui-word-aux">用于前端显示</div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">开启充提币功能</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="is_open_CTbi" value="1" title="打开" @if (isset($setting['is_open_CTbi'])) {{$setting['is_open_CTbi'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="is_open_CTbi" value="0" title="关闭" @if (isset($setting['is_open_CTbi'])) {{$setting['is_open_CTbi'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">开启提现自动审核功能</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="is_auto_audit_withdrawal" value="1" title="打开" @if (isset($setting['is_auto_audit_withdrawal'])) {{$setting['is_auto_audit_withdrawal'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="is_auto_audit_withdrawal" value="0" title="关闭" @if (isset($setting['is_auto_audit_withdrawal'])) {{$setting['is_auto_audit_withdrawal'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">自动提现审核阈值</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
        <input type="text" name="audit_withdrawal_max" autocomplete="off" class="layui-input"
            value="@if(isset($setting['audit_withdrawal_max'])){{$setting['audit_withdrawal_max']}}@endif">
        </div>
        <div class="layui-form-mid layui-word-aux">当提现值小于阈值，进行自动审核通过处理，大于等于该值则进行人工审核</div>
    </div>

</div>


<div class="layui-form-item">
    <label class="layui-form-label">赎回扣除比例</label>
    <div class="layui-input-inline">
        <input type="text" name="redeem_poundage" autocomplete="off" class="layui-input"
            value="@if(isset($setting['redeem_poundage'])){{$setting['redeem_poundage']}}@endif">
    </div>
    <div class="layui-form-mid layui-word-aux">赎回扣除比例，如8%填入（0.08）</div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">谷歌验证码</label>
    <div class="layui-input-inline">
        <input type="text" name="google_code" autocomplete="off" class="layui-input"
            value="@if(isset($setting['google_code'])){{$setting['google_code']}}@endif">
    </div>
 
</div>

<div class="layui-form-item">
    <label class="layui-form-label">代理推广域名</label>
    <div class="layui-input-inline">
        <input type="text" name="proxy_pop_domain" autocomplete="off" class="layui-input"
            value="@if(isset($setting['proxy_pop_domain'])){{$setting['proxy_pop_domain']}}@endif">
    </div>
 
</div>
<div class="layui-form-item">
    <label class="layui-form-label">网站标题</label>
    <div class="layui-input-inline">
        <input type="text" name="site_title" autocomplete="off" class="layui-input"
            value="@if(isset($setting['site_title'])){{$setting['site_title']}}@endif">
    </div>
 
</div>
<div class="layui-form-item">
    <label class="layui-form-label">客服链接</label>
    <div class="layui-input-inline">
        <input type="text" name="custom_service_link" autocomplete="off" class="layui-input"
            value="@if(isset($setting['custom_service_link'])){{$setting['custom_service_link']}}@endif">
    </div>
 
</div>

<!-- 
<div class="layui-form-item">
    <label class="layui-form-label">提币时使用链上接口</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="use_chain_api" value="1" title="打开" @if (isset($setting['use_chain_api'])) {{$setting['use_chain_api'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="use_chain_api" value="0" title="关闭" @if (isset($setting['use_chain_api'])) {{$setting['use_chain_api'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>
<div class="layui-form-item" style="display: none;">
    <label class="layui-form-label">总账号自动加密私钥</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="auto_encrypt_private" value="1" title="打开" @if (isset($setting['auto_encrypt_private'])) {{$setting['auto_encrypt_private'] == 1 ? 'checked' : ''}} @else checked @endif >
            <input type="radio" name="auto_encrypt_private" value="0" title="关闭" @if (isset($setting['auto_encrypt_private'])) {{$setting['auto_encrypt_private'] == 0 ? 'checked' : ''}} @endif >
        </div>
    </div>
</div>
<div class="layui-form-item" >
    <label class="layui-form-label">实名认证验证</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="is_authRealName" value="1" title="打开" @if (isset($setting['is_authRealName'])) {{$setting['is_authRealName'] == 1 ? 'checked' : ''}} @else checked @endif >
            <input type="radio" name="is_authRealName" value="0" title="关闭" @if (isset($setting['is_authRealName'])) {{$setting['is_authRealName'] == 0 ? 'checked' : ''}} @endif >
        </div>
    </div>
</div>
 -->
