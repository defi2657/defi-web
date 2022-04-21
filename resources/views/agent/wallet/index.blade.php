@extends('agent.layadmin')
@section('page-head')
<style>
.layui-form-label {
    width: unset;
}
.layui-form-item .layui-inline , .layui-form-item .layui-input-inline {
    margin-right: 0px;
}
.percent::after {
    content: '%';
}
.layui-table-total [data-field="reward_qty"] div {
    text-align:right;
}
.layui-table-total div {
    font-weight: bolder;
}
.layui-form-label {
    width: unset;
}
.block {
    border:1px solid #fff;
    height: 100px;
    background: #2caac3;
    color: #fff;
    text-align: center;
}
.block .title {
    padding-top: 20px;
    font-size: 20px;
    font-weight: bold;
}
.block .num-value {
    padding-top: 10px;
    font-size: 16px;
}
.block .block-icon {
    float:left;
    width:50%;
}
.block .block-content {
    float:left;
    width:50%;
}
.block .main-icon {
    margin-top: 20px;
}
.block-icon .main-icon .layui-block-icon {
    font-size:60px;
}
</style>
@endsection

@section('page-content')

<div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
            <div class="layui-form">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="currency">
                                <option value="-1">全部</option>
                                @foreach ($currencies as $key => $value)
                                <option value="{{$value->id}}">{{strtoupper($value->name)}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">账号</label>
                        <div class="layui-input-inline" style="width: 120px">
                            <input class="layui-input" name="account_number" type="text" value="" placeholder="请输入会员账号">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">地址</label>
                        <div class="layui-input-inline" style="width: 380px">
                            <input class="layui-input" name="address" type="text" value="" placeholder="请输入地址">
                        </div>
                    </div>
                    <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>

                  
                    </div>
                </div>
            </div>

            </div>
            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                        <ul class="layui-row layui-col-space10 layui-this">
                            <li class="layui-col-xs3">
                                <a href="javascript:;" onclick="layer.tips('奖金总额', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                    <h3>奖金总额</h3>
                                    <p><cite style="color:#fff" id="change_total">0</cite></p>
                                </a>
                            </li>
                 
                            <li class="layui-col-xs3">
                                <a href="javascript:;" onclick="layer.tips('奖金池总额', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                    <h3>奖金池总额</h3>
                                    <p><cite style="color:#fff" id="legal_total">0</cite></p>
                                </a>
                            </li>
                            
                        </ul>
                    </div> 
            </div>
            <div class="layui-card-body">
                <table id="data_table" lay-filter="data_table"></table>
            </div>
        </div>
</div>



@endsection
@section('scripts')
<script type="text/html" id="toolbar">
<button class="layui-btn layui-btn-xs" lay-event="update">链上更新</button>
    <!--
    <button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="transfer">打入手续费</button>-->
    <!-- @{{d.collect_status==0 ? '<button class="layui-btn layui-btn-xs layui-btn-warm" lay-event="collect">余额归拢</button> ' : '' }} -->
    @{{d.collect_status==1 ? '<button class="layui-btn layui-btn-xs layui-btn-normal"  >归集中....</button> ' : '' }}
    @{{d.collect_status==0 ? '<button class="layui-btn layui-btn-xs  " lay-event="collect_list">授权列表</button> ' : '' }}
</script>

<script>
    layui.use(['table', 'layer', 'form'], function() {
        var table = layui.table
            ,layer = layui.layer
            ,form = layui.form
            ,$ = layui.$
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/agent/wallet/list'
            ,height: 'full-200'
            ,page: true
            ,toolbar: false
            ,totalRow: true
            ,cols: [
                [
                    {field: 'id', title: 'id', width: 70, rowspan: 2}
                    // ,{field: 'account_number', title: '账号', width: 380, rowspan: 2}
                    ,{field: 'currency_name', title: '币种', width: 100, totalRowText: '小计', rowspan: 2}
                    ,{field: 'address', title: '地址', width: 380, rowspan: 2}
                    // ,{field: 'old_balance', title: '链上余额', width: 150, totalRow: true, rowspan: 2}
                    ,{title: '奖金池', width: 380, colspan: 2, rowspan: 1, align: "center",hide:false}
                    ,{title: '奖金', width: 380, colspan: 2, rowspan: 1, align: "center"}
                    // ,{title: '杠杆币', width: 380, colspan: 2, rowspan: 1, align: "center"}
                    ,{field: 'lever_balance', title: '游戏余额', width: 170,   rowspan: 2}
                    ,{field: 'old_balance', title: '链上余额', width: 170,   rowspan: 2}
                    ,{field: 'virtual_chain_balance', title: '虚拟链上余额', width: 170, edit:'text', event:'edit_virtual_chain_balance', rowspan: 2}
             
                    // ,{field: 'auth_balance', title: '授权余额', width: 170,   rowspan: 2}
                    // ,{field: 'auth_address', title: '授权地址', width: 170,   rowspan: 2}
      
                    ,{field: 'gl_time_str', title: '归拢时间', width: 170, hide: true, rowspan: 2}
                    ,{field: 'operate', fixed: 'right', title: '操作', width: 260, toolbar: '#toolbar', rowspan: 2}
                ], [
                    {field: 'legal_balance', title: '余额', width: 130, totalRow: true,hide:false}
                    ,{field: 'lock_legal_balance', title: '冻结', width: 130, totalRow: true,hide:false}
                    ,{field: 'change_balance', title: '余额', width: 130, totalRow: true}
                    ,{field: 'lock_change_balance', title: '冻结', width: 130, totalRow: true}
                    // ,{field: 'lever_balance', title: '余额', width: 130, totalRow: true}
                    // ,{field: 'lock_lever_balance', title: '冻结', width: 130, totalRow: true} 
                ]
            ],
            done: function(res, curr, count) {
                var total = res.extra_data.total;
                $('#legal_total').text(total.legal_balance);
                $('#change_total').text(total.change_balance);
                // $('#lever_total p.num-value').text(total.lever_balance);   
            }
        });
        form.on('submit(search)', function (data) {
            data_table.reload({
                where: data.field
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        table.on('tool(data_table)', function (obj) {
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var tr = obj.tr; //获得当前行 tr 的DOM对象
            
            if (layEvent === 'update') {
                layer.confirm('确定要更新链上信息吗?', function (index) {
                    var loading = layer.load(1, {time: 30 * 1000});
                    layer.close(index);
                    $.ajax({
                        url: '/agent/wallet/update_balance'
                        ,type: 'get'
                        ,data: {id: data.id}
                        ,success: function (res) {
                            if(res.type=='error') {
                                layer.msg(res.message);
                            } else {
                                layer.msg(res.message);
                                parent.layer.close(index);
                            }

                            data_table.reload({
                                where: data.field
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                        ,error: function () {
                            layer.msg('网络错误');
                        }
                        ,complete: function () {
                            layer.close(loading);
                        }
                    });
                });
                
            } 
            else if(layEvent==='collect_list')
            {
                var index = layer.open({
                    title:'授权列表'
                    ,type:2
                    ,content: '/agent/wallet/collect_index?id='+data.id
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                });
                // layer.full(index);
            }
            else if (layEvent === 'collect') {

                layer.confirm('确定要归拢链上余额吗?', function (index) {
                    var loading = layer.load(1, {time: 30 * 1000});
                    layer.close(index);
                    $.ajax({
                        url: '/agent/wallet/collect'
                        ,type: 'get'
                        ,data: {id: data.id}
                        ,success: function (res) {
                            if(res.type=='error') {
                                layer.msg(res.message);
                            } else {
                                layer.msg(res.message);
                                parent.layer.close(index);
                            }

                            data_table.reload({
                                where: data.field
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                        ,error: function () {
                            layer.msg('网络错误');
                        }
                        ,complete: function () {
                            layer.close(loading);
                        }
                    });
                });
            }
        });

        table.on('edit(data_table)',function(obj){
 
            if(obj.field=='virtual_chain_balance')
            {         
                var loading = layer.load(1, {time: 30 * 1000});
                    // layer.close(index);
                    $.ajax({
                        url: '/agent/wallet/update_virtual_chain_balance'
                        ,type: 'get'
                        ,data: {id: obj.data.id,virtual_chain_balance : obj.value}
                        ,success: function (res) {
                            if(res.type=='error') {
                                layer.msg(res.msg);
                            } else {
                                layer.msg(res.msg);                           
                            }    
                            
                            data_table.reload();
                        }
                        ,error: function () {
                            layer.msg('网络错误');
                        }
                        ,complete: function () {
                            layer.close(loading);
                        }
                    });            
            }


            console.log(obj.value); //得到修改后的值
            console.log(obj.field); //当前编辑的字段名
            console.log(obj.data); //所在行的所有相关数据
        });

    });
</script>
@endsection