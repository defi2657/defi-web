@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
    {{--    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">--}}
    {{--        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加数据','{{url('admin/market_add')}}')">添加矿机</button>--}}
    {{--    </div>--}}
{{--    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_financial_machine">添加矿机</button>--}}

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
            <div class="layui-inline" style="margin-left: -10px;">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>
        </form>
    </div>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="detail">详情</a>
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
                ,url: '{{url('admin/user_financial/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:60, sort: true}
                    ,{field: 'user_id', title: '用户ID', Width:80}
                    ,{field: 'account_number', title: '用户地址', Width:80}
                    ,{field: 'nick_name', title: '昵称', Width:80}
                    // ,{field: 'financial_name', title: '矿机名字', Width:80}
                    ,{field: 'bonus_num', title: '分红数', minWidth:80}
                    ,{field: 'day_bonus', title: '每日分红', minWidth:80}
                    ,{field: 'days', title: '分红天数', minWidth:80}
                    ,{field: 'rate', title: '分红比率', minWidth:80}
                    ,{field: 'num', title: '数量', minWidth:80}
                    ,{field: 'create_time', title: '创建时间', minWidth:80}
                    // ,{field: 'is_newuser', title:'是否新用户专享', width: 90, templet: '#newusertml'}
                    ,{field: 'status', title: '状态', minWidth:80,templet: '#statustml'}
                    // ,{field: 'is_up', title: '是否上架', minWidth:150, templet: '#typetml'}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}
                ]]
            });
            $('#add_financial_machine').click(function(){
                // layer_show('添加管理员', '/admin/financial_machine/add');
                var index = layer.open({
                    title:'添加套餐'
                    ,type:2
                    ,content: '/admin/financial_machine/add'
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                });
                layer.full(index);
            });
            //监听锁定操作
            form.on('switch(is_up)', function(obj){
                var id = this.value;
                console.log(id);
                $.ajax({
                    url:'{{url('admin/financial_machine/up')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        layer.msg(res.message);

                    }
                });
            });
            form.on('switch(is_newuser)', function(obj){
                var id = this.value;
                console.log(id);
                $.ajax({
                    url:'{{url('admin/financial_machine/newuser')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        layer.msg(res.message);

                    }
                });
            });
            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('admin/financial_machine/del')}}',
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
                } else if(obj.event === 'detail'){
                    layer_show('矿机分红详情','{{url('admin/user_minig/bonusdetail')}}?id='+data.id,1200,500);
                }else if(obj.event==='expiration'){
                    layer.confirm('真的提前到期么？收益会立刻打入用户账户', function(index){
                        $.ajax({
                            url:'{{url('admin/user_financial/acceleration')}}',
                            type:'get',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    layer.msg(res.message);
                                    table.reload('mobileSearch',{
                                        where:{account_number:''},
                                        page: {curr: 1}         //重新从第一页开始
                                    });
                                }
                            }
                        });
                    });
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