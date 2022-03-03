@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
    <script type="text/html" id="typetml">
        @{{d.type==1 ? '<span class="layui-badge layui-bg-green">'+'分红'+'</span>' : '' }}
        @{{d.type==2 ? '<span class="layui-badge layui-bg-red">'+'本金'+'</span>' : '' }}
    </script>
    <input type="hidden" id="id_value" value="{{ $id }}"/>
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
            var id=$("#id_value").val();
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('agent/user_financial/userBonusList')}}?id='+id //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:60}
                    ,{field: 'user_id', title: '用户id', Width:200}
                    ,{field: 'account_number', title: '用户名', Width:200}
                    ,{field: 'total', title: '总额', Width:200}
                    ,{field: 'rate', title: '分红比率', Width:100}
                    ,{field: 'num', title: '分红数量', Width:100}
                    ,{field: 'type', title: '分红类型', Width:100}
                    ,{field: 'up_down', title: '社区类型', Width:100}
                    // ,{field: 'type', title: '分红类型', Width:100}
                    ,{field: 'addtime', title: '时间', Width:200}
                ]]
            });
        });
    </script>
@endsection