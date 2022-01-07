@extends('admin._layoutNew')
@section('page-head')
    <link rel="stylesheet" type="text/css" href="{{URL("layui/css/layui.css")}}" media="all">
{{--    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/bootstrap/css/bootstrap.css")}}" media="all">--}}
{{--    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/global.css")}}" media="all">--}}
{{--    <link rel="stylesheet" type="text/css" href="{{URL("admin/css/personal.css")}}" media="all">--}}
@endsection
@section('page-content')
    <form class="layui-form">
        <input type="hidden" name="id" value="@if (isset($financial['id'])){{ $financial['id'] }}@endif">
        {{ csrf_field() }}
        <div class="layui-form-item">
            <label class="layui-form-label">虚拟地址</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="address" lay-verify="required" placeholder="请输入虚拟地址" type="text" value="@if (isset($financial['address'])){{$financial['address']}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">虚拟金额</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="money" lay-verify="required" placeholder="请输入虚拟金额" type="number" value="@if (isset($financial['money'])){{$financial['money']}}@endif">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
{{--    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/ueditor.config.js') }}"></script>--}}
{{--    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/ueditor.all.js') }}"> </script>--}}
{{--    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/lang/zh-cn/zh-cn.js') }}"></script>--}}
    <script>
        layui.use(['element', 'form', 'layer', 'jquery', 'layedit', 'laydate','upload'], function(){
            var upload = layui.upload;
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;

            form.on('submit(submit)', function(data){
                var data = data.field;

                $.ajax({
                    type: 'POST'
                    ,url: '/admin/virtual_profit/add'
                    ,data: data
                    ,success: function(data) {
                        console.log(data.type);
                        if(data.type == 'ok') {
                            layer.msg(data.message, {
                                icon: 1,
                                time: 1000,
                                end: function() {
                                    console.log('进来了');
                                    console.log(window.name);
                                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    parent.layer.close(index);
                                    parent.window.location.reload();
                                }
                            });
                        } else {
                            layer.msg(data.message, {icon:2});
                        }
                    }
                    ,error: function(data) {
                        console.log(data);
                        //重新遍历获取JSON的KEY
                        var str = '服务器验证失败,错误信息:' + '<br>';
                        for(var o in data.responseJSON.errors) {
                            str += data.responseJSON.errors[o] + '<br>';
                        }
                        layer.msg(str, {icon:2});
                    }
                });
                parent.layui.layer.close();
                return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
            });
            //执行实例
            var uploadInst = upload.render({
                elem: '#financial_image_btn' //绑定元素
                ,url: '{{URL("api/upload")}}' //上传接口
                ,done: function(res){
                    console.log(res);
                    //上传完毕回调
                    if (res.code == 200){
                        $("#financial_image").val(res.msg)
                        $("#img_financial_image").show()
                        $("#img_financial_image").attr("src",res.msg)
                    } else{
                        alert(res.msg)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });

            //执行实例
            var uploadInst1 = upload.render({
                elem: '#financial_image2_btn' //绑定元素
                ,url: '{{URL("api/upload")}}' //上传接口
                ,done: function(res) {
                    console.log(res);
                    //上传完毕回调
                    if (res.code == 200){
                        $("#financial_image2").val(res.msg)
                        $("#img_financial_image2").show()
                        $("#img_financial_image2").attr("src",res.msg)
                    } else{
                        alert(res.msg)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });
        });
    </script>
@endsection