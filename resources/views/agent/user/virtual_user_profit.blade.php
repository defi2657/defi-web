@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <input type="hidden" name="id" value="{{$user->id}}">
        <div class="layui-form-item">
            <label class="layui-form-label">交易账号</label>
            <div class="layui-input-block">
                <input type="text" readonly disabled=disabled name="account_number" autocomplete="off" placeholder="" class="layui-input" value="{{$user->account_number ?? ''}}" >
            </div>
        </div>
 
        <div class="layui-form-item">
            <label class="layui-form-label">虚拟收益</label>
            <div class="layui-input-block">
                <input type="number" name="virtual_user_profit" autocomplete="off" placeholder="请填写偏移量" class="layui-input" value="{{$user->virtual_user_profit ?? ''}}">
            </div>
        </div>
  
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="form">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    layui.use(['element', 'form', 'layer'], function () {
        var element = layui.element
            ,form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
        form.on('submit(form)', function (data) {
            $.ajax({
                url: ''
                ,type: 'POST'
                ,data: data.field
                ,success: function (res) {
                    layer.msg(res.msg, {
                        time: 2000
                        ,end: function () {
                            if (res.code == 0) {
                                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                parent.layer.close(index); //再执行关闭 
                                parent.layui.table.reload('san-user-manage');       
                            }
                        }
                    });
                }
                ,error: function (res) {
                    layer.msg('网络错误');
                }
            });
            return false;
        });
    });
</script>
@endsection