@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')
<div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">用户地址</label>
                        <div class="layui-input-inline" >
                            <input type="datetime" name="address" id="address" placeholder="请输入地址" autocomplete="off" class="layui-input" value="">
                        </div>       

                    </div>
                    <div class="layui-inline">

                        <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> 
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                
                    </div>
                    </div>
            </div>
            <div class="layui-card-body">
                <table id="accountlist" lay-filter="accountlist"></table>
            </div>

        </div>
</div>
 
        <script type="text/html" id="barDemo">
            <a class="layui-btn layui-btn-xs" lay-event="viewDetail">查看详情</a>
        </script>

@endsection

        @section('scripts')
            <script>

                window.onload = function() {
                    document.onkeydown=function(event){
                        var e = event || window.event || arguments.callee.caller.arguments[0];
                        if(e && e.keyCode==13){ // enter 键
                            $('#mobile_search').click();
                        }
                    };
                    layui.use(['element', 'form', 'layer', 'table','laydate'], function () {
                        var element = layui.element;
                        var layer = layui.layer;
                        var table = layui.table;
                        var $ = layui.$;
                        var form = layui.form;
                        var laydate = layui.laydate;

                        laydate.render({
                            elem: '#start_time'
                        });
                        laydate.render({
                            elem: '#end_time'
                        });

                        form.on('submit(mobile_search)',function(obj){
                            var address =  $("#address").val()
                           
                            tbRend("{{url('/agent/agent_bonus_task/list')}}?address="+address);
                            return false;
                        });
                        function tbRend(url) {
                            table.render({
                                elem: '#accountlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    {field: 'id', title: 'ID',  width: 50}
                                    ,{field:'tx_id',title: 'tx_id',width: 350}
                                    ,{field:'address',title: '地址',width: 350}
                                    ,{field:'finish_time',title:'结束时间', width:200}
                                    ,{field:'create_time',title:'创建时间', width:200}
                                    ,{
                                        field:'status',title:'状态', width:100,templet:function(data){
                                            if(data.status==1){
                                                return '处理完成'
                                            }else{
                                                return '处理中'
                                            }
                                        }
                                    }
                                    ,{field:'amount',title:'金额', minWidth:80}
                                ]]
                            });
                        }
                            tbRend("{{url('/agent/agent_bonus_task/list')}}");
                        //监听工具条
                        table.on('tool(accountlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;

                            if (layEvent === 'viewDetail') { //编辑
                                var index = layer.open({
                                    title: '查看详情'
                                    , type: 2
                                    , content: '{{url('agent/account/viewDetail')}}?id=' + data.id
                                    , maxmin: true
                                });
                                layer.full(index);
                            }
                        });
                    });
                }
            </script>    
@endsection