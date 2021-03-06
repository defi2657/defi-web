@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
   <div class="layui-inline">
        <label class="layui-form-label">用户账号</label>
        <div class="layui-input-inline" >
            <input type="datetime" name="account" placeholder="请输入手机号或邮箱" autocomplete="off" class="layui-input" value="">
        </div>
       <div class="layui-input-inline date_time111" style="margin-left: 50px;">
           <input type="text" name="start_time" id="start_time" placeholder="请输入开始时间" autocomplete="off" class="layui-input" value="">
       </div>
       <div class="layui-input-inline date_time111" style="margin-left: 50px;">
           <input type="text" name="end_time" id="end_time" placeholder="请输入结束时间" autocomplete="off" class="layui-input" value="">
       </div>
        <div class="layui-inline" style="margin-left: 50px;">
                <label>日志类型&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="type" id="type" class="layui-input">
                    <option value="">所有类型</option>
                    @foreach ($types as $key=>$type)
                        <option id='type_{{$key}}' value="{{ $key }}" class="ww">{{ $type }}</option>
                    @endforeach
                  </select>
                </div>
        </div>
       <div class="layui-inline" style="margin-left: 50px;">
           <label>货币类型&nbsp;&nbsp;</label>
           <div class="layui-input-inline">
               <select name="currency_type" id="currency_type" class="layui-input">
                   <option value="">所有</option>
                   @foreach ($currency_type as $key=>$type)
                       <option value="{{ $type['id'] }}" class="ww">{{ $type['name'] }}</option>
                   @endforeach
               </select>
           </div>
       </div>
        <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon">&#xe615;</i> </button>
    </div>


    <div class="layui-form">
        <table id="accountlist" lay-filter="accountlist"></table>
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
                            var start_time =  $("#start_time").val()
                            var end_time =  $("#end_time").val()
                            var currency_type =  $("#currency_type").val()
                            var account =  $("input[name='account']").val()
                            var type = $('#type').val()
                            tbRend("{{url('/admin/account/list')}}?account="+account+'&type='+type+'&start_time='+start_time+'&end_time='+end_time+'&currency_type='+currency_type);
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
                                    ,{field:'account',title: '用户账号',width: 350}
                                    ,{field:'account_number',title: '用户交易账号',width: 350}
                                    ,{field:'before',title:'变动前', width:120}
                                    ,{field:'value',title:'变动量', width:120}
                                    ,{field:'after',title:'变动后', width:120}
                                    ,{field:'transaction_info',title:'交易信息', width:90}
                                    ,{field:'currency_name',title:'币种', width:60}
                                    ,{
                                        field:'type',title:'日志类型', width:100,templet:function(data){
                                            var id='#type_'+data.type;
                                            return $(id).text();
                                        }
                                    }
                                    ,{field:'info',title:'记录', minWidth:80}                          
                                    ,{field:'created_time',title:'创建时间', minWidth:140}
//                                    , {fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                            tbRend("{{url('/admin/account/list')}}");
                        //监听工具条
                        table.on('tool(accountlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;

                            if (layEvent === 'viewDetail') { //编辑
                                var index = layer.open({
                                    title: '查看详情'
                                    , type: 2
                                    , content: '{{url('admin/account/viewDetail')}}?id=' + data.id
                                    , maxmin: true
                                });
                                layer.full(index);
                            }
                        });
                    });
                }
            </script>    
@endsection