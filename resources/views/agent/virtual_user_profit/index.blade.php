@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
    {{--    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">--}}
    {{--        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加数据','{{url('agent/market_add')}}')">添加矿机</button>--}}
    {{--    </div>--}}
<button class="layui-btn layui-btn-normal layui-btn-radius" id="add_financial_machine">添加虚拟数据</button>

    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <form class="layui-form layui-form-pane layui-inline" action="">

{{--            <div class="layui-inline">--}}
{{--                <label class="layui-form-label">开始日期：</label>--}}
{{--                <div class="layui-input-inline" style="width:120px;">--}}
{{--                    <input type="text" class="layui-input" id="start_time" value="" name="start_time">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="layui-inline">--}}
{{--                <label class="layui-form-label">结束日期：</label>--}}
{{--                <div class="layui-input-inline" style="width:120px;">--}}
{{--                    <input type="text" class="layui-input" id="end_time" value="" name="end_time">--}}
{{--                </div>--}}
{{--            </div>--}}
            <!-- <div class="layui-inline" style="margin-left: -10px;">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div> -->
        </form>
    </div>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
        <!-- @{{d.status==1 ? '
        <a class="layui-btn layui-btn-xs" lay-event="expiration">'+'提前到期'+'</a>' : ''
        }}
{{--        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>--}} -->
    </script>
    <script type="text/html" id="statustml">
        @{{d.status==-1 ? '<span class="layui-badge layui-bg-green">'+'提前到期'+'</span>' : '' }}
        @{{d.status==0 ? '<span class="layui-badge layui-bg-green">'+'已结束并退还'+'</span>' : '' }}
        @{{d.status==1 ? '<span class="layui-badge layui-bg-red">'+'进行中'+'</span>' : '' }}
    </script>
    <script type="text/html" id="newusertml">
        @{{d.is_newuser==0 ? '<span class="layui-badge layui-bg-green">'+'否'+'</span>' : '' }}
        @{{d.is_newuser==1 ? '<span class="layui-badge layui-bg-red">'+'是'+'</span>' : '' }}
    </script>

{{--    <script type="text/html" id="switchTpl">--}}
{{--        <input type="checkbox" name="is_up" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_up" @{{ d.is_up == 1 ? 'checked' : '' }} />--}}
{{--    </script>--}}
{{--    <script type="text/html" id="switchnewuser">--}}
{{--        <input type="checkbox" name="is_up" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_newuser" @{{ d.is_newuser == 1 ? 'checked' : '' }} />--}}
{{--    </script>--}}
@endsection
@section('scripts')
    <script>
        layui.use(['table','form','layer','laydate'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;

            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('agent/virtual_profit/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:60, sort: true}
                    ,{field: 'address', title: '地址', Width:80}
                    ,{field: 'money', title: '分红数量', Width:80}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}
                ]]
            });
            $('#add_financial_machine').click(function(){
                // layer_show('添加管理员', '/agent/financial_machine/add');
                var index = layer.open({
                    title:'添加虚拟数据'
                    ,type:2
                    ,content: '/agent/virtual_profit/add'
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                });
                // layer.full(index);
            });
            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('agent/virtual_profit/del')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });
                    });
                }else if(obj.event === 'edit'){
                    layer_show('编辑','{{url('agent/virtual_profit/add')}}?id='+data.id,1200,500);
                }
            });
            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
        });
    </script>
@endsection