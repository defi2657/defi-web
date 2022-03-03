@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="detail">详情</a>
        {{--        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>--}}
    </script>
    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'已结束并退还'+'</span>' : '' }}
        @{{d.status==0 ? '<span class="layui-badge layui-bg-red">'+'进行中'+'</span>' : '' }}
    </script>
    <script type="text/html" id="newusertml">
        @{{d.status==0 ? '<span class="layui-badge layui-bg-green">'+'是'+'</span>' : '' }}
        @{{d.status==1 ? '<span class="layui-badge layui-bg-red">'+'否'+'</span>' : '' }}
    </script>
    <script type="text/html" id="typetml">
        @{{d.type==1 ? '<span class="layui-badge layui-bg-green">'+'分红'+'</span>' : '' }}
        @{{d.type==2 ? '<span class="layui-badge layui-bg-red">'+'本金'+'</span>' : '' }}
    </script>



    {{--    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">--}}
    {{--        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加数据','{{url('agent/market_add')}}')">添加矿机</button>--}}
    {{--    </div>--}}
    {{--    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_financial_machine">添加矿机</button>--}}
    <div class="layui-inline">
        <label class="layui-form-label">用户名</label>
        <div class="layui-input-inline" >
            <input type="datetime" name="account" id="account" placeholder="请输入用户名" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-inline date_time111" style="margin-left: 50px;">
            <input type="text" name="start_time" id="start_time" placeholder="请输入开始时间" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-inline date_time111" style="margin-left: 50px;">
            <input type="text" name="end_time" id="end_time" placeholder="请输入结束时间" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-inline" style="margin-left: 50px;">
            <label>分红类型&nbsp;&nbsp;</label>
            <div class="layui-input-inline">
                <select name="type" id="type" class="layui-input" style="width: 150px">
                    <option value="">所有类型</option>
                        <option value="1" class="ww">分红</option>
                    <option value="2" class="ww">本金</option>
                </select>
            </div>
        </div>
        <button class="layui-btn btn-search" style="margin-left: 50px;" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon">&#xe615;</i> </button>
    </div>
    <table id="demo" lay-filter="test"></table>

    {{--    <script type="text/html" id="switchTpl">--}}
    {{--        <input type="checkbox" name="is_up" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_up" @{{ d.is_up == 1 ? 'checked' : '' }} />--}}
    {{--    </script>--}}
    {{--    <script type="text/html" id="switchnewuser">--}}
    {{--        <input type="checkbox" name="is_up" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_newuser" @{{ d.is_newuser == 1 ? 'checked' : '' }} />--}}
    {{--    </script>--}}
@endsection
@section('scripts')
    <script>
        layui.use(['table','form','layer','laydate','element'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            var laydate = layui.laydate;

            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('agent/user_financial/bonusList')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:60}
                    ,{field: 'user_id', title: '用户id', Width:200}
                    ,{field: 'account_number', title: '用户名', Width:200}
                    ,{field: 'nick_name', title: '昵称', Width:80}
                    ,{field: 'total', title: '总额', Width:200}
                    ,{field: 'rate', title: '分红比率', Width:100}
                    ,{field: 'num', title: '分红数量', Width:100}
                    ,{field: 'type', title: '分红类型', Width:100}
                    ,{field: 'addtime', title: '时间', Width:200}
                ]]
            });
            {{--form.on('submit(mobile_search)',function(obj){--}}
            {{--    var start_time =  $("#start_time").val();--}}
            {{--    var end_time =  $("#end_time").val();--}}
            {{--    var account =  $("input[name='account']").val();--}}
            {{--    var type = $('#type').val();--}}
            {{--    tbRend("{{url('/agent/user_financial/bonusList')}}?account_number="+account+'&type='+type+'&start_time='+start_time+'&end_time='+end_time);--}}
            {{--    return false;--}}
            {{--});--}}


            $('#add_financial_machine').click(function(){
                // layer_show('添加管理员', '/agent/financial_machine/add');
                var index = layer.open({
                    title:'添加矿机'
                    ,type:2
                    ,content: '/agent/financial_machine/add'
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
                    url:'{{url('agent/financial_machine/up')}}',
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
                    url:'{{url('agent/financial_machine/newuser')}}',
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
                            url:'{{url('agent/financial_machine/del')}}',
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
                } else if(obj.event === 'edit'){
                    layer_show('编辑矿机','{{url('agent/financial_machine/add')}}?id='+data.id);
                }
            });
            //监听提交
            form.on('submit(mobile_search)', function(data){
                // var account_number = data.field.account_number;
                var start_time =  $("#start_time").val();
                var end_time =  $("#end_time").val();
                var account =  $("#account").val();
                var type = $('#type').val();
                table.reload('mobileSearch',{
                    where: {
                        account_name: account,
                        start_time: start_time,
                        end_time: end_time,
                        type: type
                    },
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
        });
    </script>
@endsection