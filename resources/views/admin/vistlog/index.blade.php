@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
 
 
<div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">关键字</label>
                            <div class="layui-input-inline" >
                                <input type="datetime" name="account" placeholder="请输入用户地址或者IP" autocomplete="off" class="layui-input" value="">
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
                       
                    </div>
                </div>
            </div>
            <div class="layui-card-body">
            <table id="demo" lay-filter="test"></table>
            </div>

        </div>
</div>

 
 
@endsection
@section('scripts')
    <script>
        layui.use(['table','form','layer','laydate'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            var laydate = layui.laydate;
            laydate.render({
                elem: '#start_time',
                type:'datetime'
            });
            laydate.render({
                elem: '#end_time',
                type:'datetime'
            });
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('admin/vistlog/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:60}
                    ,{field: 'address', title: '地址', width:350}
                    ,{field: 'ip', title: 'IP', width:150}
   
                    ,{field: 'time', title: '时间', minWidth:80}
          
                    ,{field: 'type', title: '类型', minWidth:80}
                    ,{field: 'code', title: '邀请码', minWidth:80}
                    ,{field: 'code_name', title: '邀请人', minWidth:80}
                ]]
            });
  
  
            //监听提交
            form.on('submit(mobile_search)', function(data){

                var start_time =  $("#start_time").val()
                var end_time =  $("#end_time").val()
                var currency_type =  $("#currency_type").val()
                var account =  $("input[name='account']").val()
                var type = $('#type').val()

                table.reload('mobileSearch',{
                    where:{start_time:start_time,end_time:end_time,account:account},
                    page: {curr: 1}         //重新从第一页开始
                });
    
                return false;
            });
        });
    </script>
@endsection