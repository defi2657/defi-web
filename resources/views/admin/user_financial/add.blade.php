@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
   
        <div class="layui-form-item">
            <label class="layui-form-label">用户ID</label>
            <div class="layui-input-block">
                <input type="text" name="user_id" disabled autocomplete="off" placeholder="" class="layui-input" value="{{$user_id}}">
            </div>
        </div>
<!--     
        <div class="layui-form-item">
            <label class="layui-form-label">理财金额</label>
            <div class="layui-input-block">
                <input type="text" disabled  id="balance" name="balance" autocomplete="off" placeholder="" class="layui-input" value="">
            </div>
        </div> -->
        <div class="layui-form-item">
            <label class="layui-form-label">货币类型</label>
            <div class="layui-input-block">
            <select name="currency_id"   >
    
                   @foreach ($currency_type as $type)
                       <option value="{{ $type['id'] }}"   class="ww">{{ $type['name'] }}(余额：{{$type['balance']}}) </option>
                   @endforeach
               </select>
            </div>
        </div>
   
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <!-- <button type="reset" class="layui-btn layui-btn-primary">重置</button> -->
            </div>
        </div>
    </form>

@endsection
 
@section('scripts')
    <script>
        var currency_type=[];

        @foreach ($currency_type as $type)

        currency_type["{{ $type['id'] }}"]={{ $type['balance'] }};
        @endforeach

        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                data['balance']=currency_type[data.currency_id];
                $.ajax({
                    url:'{{url('admin/user_financial/post_add')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            // parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

@endsection