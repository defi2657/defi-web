@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
    <div class="layui-form-item">
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
 
        <!-- <div class="layui-form-item">
            <label class="layui-form-label">邀请码</label>
            <div class="layui-input-block">
                <input type="text" name="extension_code" autocomplete="off" placeholder="" class="layui-input" value="">
            </div>
        </div> -->

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
       

        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var loading = layer.load(1, {time: 30 * 1000});
                var data = data.field;
                $.ajax({
                    url:'{{url('agent/user/add_save')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
     
                        if(res.code==1){
                            layer.msg(res.msg);
                        }else{
                            layer.msg(res.msg);
                            // parent.layer.close(index);
                            // parent.window.location.reload();
                        }

                        layer.close(loading);
                    }
                });
                return false;
            });
        });
    </script>

@endsection