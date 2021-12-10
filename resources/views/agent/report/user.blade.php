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
                                <input type="datetime" name="account" placeholder="请输入用户地址" autocomplete="off" class="layui-input" value="">
                            </div>
                            <div class="layui-input-inline date_time111" style="margin-left: 50px;">
                                <input type="text" name="start_time" id="start_time" placeholder="请输入开始时间" autocomplete="off" class="layui-input" value="">
                            </div>
                            <div class="layui-input-inline date_time111" style="margin-left: 50px;">
                                <input type="text" name="end_time" id="end_time" placeholder="请输入结束时间" autocomplete="off" class="layui-input" value="">
                            </div>
                    
                 
                    </div>
                    <div class="layui-inline">

                        <button id="mobile_search" class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="mobile_search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                        <button class="layui-btn btn-search" id="sync" lay-submit lay-filter="sync"> 重新统计 </button>
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
                            var start_time =  $("#start_time").val()
                            var end_time =  $("#end_time").val()
                            var currency_type =  $("#currency_type").val()
                            var account =  $("input[name='account']").val()
                            var type = $('#type').val()
                            tbRend("{{url('/agent/report/user/list')}}?account="+account+'&type='+type+'&start_time='+start_time+'&end_time='+end_time+'&currency_type='+currency_type);
                            return false;
                        });

                        form.on('submit(sync)',function(obj){
                            var start_time =  $("#start_time").val()
                            var end_time =  $("#end_time").val()
                            var url = "{{url('/agent/report/user/sync')}}";
                            layer.confirm('确定进行'+start_time+'至'+end_time+'的数据同步吗？会耽误您一定的时间，请耐心等待', function(index){

                                $.post(url,{st:start_time,et:end_time}, function(data) {
                                    if(data.type == 'ok') {
                                        layer.close(index);
                                        layer.alert(data.message,{},function(){
                                            setTimeout(() => {
                                                window.location.reload();
                                            }, 1000);

                                        });
                                
                                    } else {
                                        layer.msg('同步失败');
                                    }
                                });
                                


                            });
                    
 

                           
                            return false;
                        });
                        function tbRend(url) {
                            table.render({
                                elem: '#accountlist'
                                , url: url
                                , page: true
                                ,autoSort:false
                                ,limit: 15
                                , cols: [[
                                    {field: 'id', title: 'ID',  width: 60,rowspan: 2,fixed: 'left'}
                                    ,{field:'account_number',title: '账号',width: 300,rowspan: 2,fixed: 'left',sort: true}
                                    ,{field:'day',title: '日期',width: 120,rowspan: 2,fixed: 'left',sort: true}

                                    ,{title: '个人/天', width: 380, colspan: 4, rowspan: 1, align: "center"}
                                    ,{title: '个人/全部', width: 380, colspan: 4, rowspan: 1, align: "center"}
                                    ,{title: '团队/天', width: 380, colspan: 4, rowspan: 1, align: "center"}
                                    ,{title: '团队/全部', width: 380, colspan: 4, rowspan: 1, align: "center"}

                                    ,{title:'统计时间', minWidth:150,rowspan: 2,templet:function(data){
                                        var arr=[];
                                        if(data.create_time!=0)
                                        {
                                            arr.push('创建时间:'+data.create_time);
                                        }
                                        if (data.update_time!=0)
                                        {
                                            arr.push('更新时间:'+data.update_time);
                                        }
                                        var html=arr.join('<br>');
                                        console.log(html);
                                        return html;
                                    }}
                                 
//                                    , {fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barDemo'}
                                ],
                                
                                [                     
                                    {field:'invite_count',title:'直推', width:80,sort: true}
                                    ,{field:'recharge',title: '总业绩',width: 120,sort: true}                                
                                    ,{field:'withdrawals',title:'总提现', width:120,sort: true}           
                                    ,{field:'collect',title:'总归集', width:120,sort: true}      

                                    ,{field:'total_invite_count',title:'直推', width:80,sort: true}
                                    ,{field:'total_recharge',title: '总业绩',width: 120,sort: true}                                    
                                    ,{field:'total_withdrawals',title:'总提现', width:120,sort: true}
                                    ,{field:'total_collect',title:'总归集', width:120,sort: true}

                                    ,{field:'team_invite_count',title:'直推', width:80,sort: true}
                                    ,{field:'team_recharge',title: '总业绩',width: 120,sort: true}
                                    ,{field:'team_withdrawals',title:'总提现', width:120,sort: true}
                                    ,{field:'team_collect',title:'总归集', width:120,sort: true}

                                    ,{field:'team_total_invite_count',title:'直推', width:80,sort: true}         
                                    ,{field:'team_total_recharge',title:'总业绩', width:120,sort: true}                                   
                                    ,{field:'team_total_withdrawals',title:'总提现', width:120,sort: true}
                                    ,{field:'team_total_collect',title:'总归集', width:120,sort: true}                
                                                
                                ]
                         
                                ]
                            });
                        }
                            tbRend("{{url('/agent/report/user/list')}}");

                        table.on('sort(accountlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                     
                            table.reload('accountlist', {
                                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。
                                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                                field: obj.field //排序字段
                                ,order: obj.type //排序方式
                                }
                            });
                        });
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