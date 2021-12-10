@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')
<style>
    .cashb{
        margin-right: 0px !important;
    }
    .cashb .layui-form-label
    {
        width: 60px !important;
        padding-left: 2px;
        padding-right: 15px;
    }
</style>
<div class="layui-fluid">
        <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
        <form action="">

        <div class="layui-form-item">
            <div class="layui-inline cashb" >
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline"  >
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline cashb"  >
                <label class="layui-form-label"  >审核状态</label>
                <div class="layui-input-inline"  style="width: 100px;">
                    <select name="status" id="status">
                        <option value="-1">所有</option>
                        <option value="1">待审核</option>
                        <option value="2">已通过</option>
                        <option value="3">已驳回</option>
                 
                    </select>
                </div>
            </div>
			<div class="layui-inline cashb"  >
                <label class="layui-form-label"  >链上状态</label>
                <div class="layui-input-inline" style="width: 100px;" >
                    <select name="txid_status" id="txid_status">
                        <option value="-1">所有</option>
                        <option value="0">无</option>
                        <option value="1">确认中</option>
                        <option value="2">已确认</option>
                 
                    </select>
                </div>
            </div>
            <div class="layui-inline cashb"   >
                <label class="layui-form-label" >币种</label>
                <div class="layui-input-inline" style="width: 100px;" >
                    <select name="currency" id="currencies">
                        <option value="-1">所有</option>
                        @foreach ($currencies as $key => $currency)
                        <option value="{{$currency->id}}">{{$currency->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
       
            <div class="layui-inline cashb">
                <label class="layui-form-label">日期范围</label>
                <div class="layui-input-inline" style="width: 100px;" >
                    <input type="text" class="layui-input" id="start_time" placeholder="开始日期" value="">
                </div>
                <div class="layui-form-mid">-</div>
                <div class="layui-input-inline"  style="width: 100px;">
                    <input type="text" class="layui-input" id="end_time" placeholder="结束日期" value="">
                </div>
            </div>
        
 
            <div class="layui-inline ">
                <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="mobile_search">
                    <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                </button>
                <!-- <button class="layui-btn layuiadmin-btn-useradmin"  onclick="javascript:window.location.href='/order/users_excel'">导出Excel</button> -->
                <button class="layui-btn layui-btn-normal" onclick="javascrtpt:window.location.href='{{url('/admin/cashb/csv')}}'">导出提币记录</button>
            </div>
        </div>
        </form>
        </div>

            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <ul class="layui-row layui-col-space10 layui-this">
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('提币合计', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>提币合计</h3>
                                <p><cite style="color:#fff" id="sum">0</cite></p>
                            </a>
                        </li>
                  
                        
                    </ul>
                </div>
            </div>
            <div class="layui-card-body">
                
            </div>
        </div>
</div>

 

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
    
    <a class="layui-btn layui-btn-xs" lay-event="show">查看</a>
    
    </script>
    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-black">'+'申请提币'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge layui-bg-green">'+'审核通过'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge layui-bg-red">'+'申请失败'+'</span>' : '' }}
 
    </script>
	<script type="text/html" id="txid_statustml">
	    @{{d.txid_status==0 ? '<span class="layui-badge layui-bg-gray">'+'无'+'</span>' : '' }}
        @{{d.txid_status==-2 ? '<span class="layui-badge layui-bg-black">'+'异常'+'</span>' : '' }}
        @{{d.txid_status==1 ? '<span class="layui-badge layui-bg-blue">'+'确认中'+'</span>' : '' }}
        @{{d.txid_status==2 ? '<span class="layui-badge layui-bg-green">'+'已确认'+'</span>' : '' }}
 
    </script>
    
	<script type="text/html" id="type_text">
	    @{{d.type==0 ? '<span class="layui-badge layui-bg-gray">'+'无'+'</span>' : '' }}
        @{{d.type==2 ? '<span class="layui-badge layui-bg-green">'+d.type_text+'</span>' : '' }}
        @{{d.type==1 ? '<span class="layui-badge layui-bg-blue">'+d.type_text+'</span>' : '' }}
 
    </script>
@endsection

@section('scripts')
    <script>

        layui.use(['table','form','laydate'], function(){
            var table = layui.table
                ,$ = layui.jquery
                ,form = layui.form
                ,laydate = layui.laydate
                ,layer = layui.layer
            //第一个实例
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: "{{url('agent/cashb_list')}}" //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:60, sort: true,fixed: 'left'}
                    ,{field: 'account_number', title: '用户名', width:320,fixed: 'left'}
                    ,{field: 'type_text', title: '所属板块', width:90,fixed: 'left',templet: '#type_text'}
                    // ,{field: 'nationality', title: '国籍', width:100}
                    ,{field: 'currency_name', title: '虚拟币', width:80}
                    ,{field: 'number', title: '提币数量', width: 120}
                    ,{field: 'rate', title: '手续费率', width: 90}
                    ,{field: 'real_number', title: '实际提币', width:120}
                    // ,{field: 'memo', title: '备注(MEMO)', width:120}
                    ,{field: 'status', title: '审核状态', width: 100, templet: '#statustml'}
                    ,{field: 'address', title: '提币地址', width:320}
                    ,{field: 'txid', title: '交易哈希', width:100}
                	,{field: 'txid_status', title: '链上状态', width: 120, templet: '#txid_statustml'}
                		,{field: 'errmsg', title: '异常信息', width: 120}
                    ,{field: 'create_time', title: '提币时间', width:180}
                    // ,{title:'操作', width:120, toolbar: '#barDemo',fixed: 'right'}

                ]] , done: function(res){
                    $("#sum").text(res.extra_data);
                }
            });

            table.on('tool(test)', function(obj){
                var data = obj.data;
         
            });

            //监听提交
            form.on('submit(mobile_search)', function(data) {

                table.reload('mobileSearch',{
                    where: data.field,
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection