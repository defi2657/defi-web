@extends('admin._layoutNew')

@section('page-head')
<style>
.layui-form-label {
    width: unset;
}
.layui-form-item .layui-inline , .layui-form-item .layui-input-inline {
    margin-right: 0px;
}
.percent::after {
    content: '%';
}
.layui-table-total [data-field="reward_qty"] div {
    text-align:right;
}
.layui-table-total div {
    font-weight: bolder;
}
.layui-form-label {
    width: unset;
}
.block {
    border:1px solid #fff;
    height: 100px;
    background: #2caac3;
    color: #fff;
    text-align: center;
}
.block .title {
    padding-top: 20px;
    font-size: 20px;
    font-weight: bold;
}
.block .num-value {
    padding-top: 10px;
    font-size: 16px;
}
.block .block-icon {
    float:left;
    width:50%;
}
.block .block-content {
    float:left;
    width:50%;
}
.block .main-icon {
    margin-top: 20px;
}
.block-icon .main-icon .layui-block-icon {
    font-size:60px;
}
</style>
@endsection

@section('page-content')
 
 
<table id="data_table" lay-filter="data_table"></table>
@endsection
@section('scripts')
<script type="text/html" id="toolbar">
<!-- <button class="layui-btn layui-btn-xs" lay-event="update">链上更新</button> -->
 
    <!-- <button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="transfer">打入手续费</button> -->
    @{{d.collect_status==0 ? '<button class="layui-btn layui-btn-xs layui-btn-warm" lay-event="collect">余额归拢</button> ' : '' }}
    @{{d.collect_status==1 ? '<button class="layui-btn layui-btn-xs layui-btn-normal"  >归集中....</button> ' : '' }}
 
</script>
<script>
    layui.use(['table', 'layer', 'form'], function() {
        var table = layui.table
            ,layer = layui.layer
            ,admin = layui.admin
            ,form = layui.form
            ,$ = layui.$
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/wallet/auth_list?id={{$id}}'
            ,height: 'full-200'
            ,page: false
            ,toolbar: false
            ,totalRow: false
            ,cols: [
              [

                {field: 'balance', title: '授权余额'  ,width:200 }
                   
                   // ,{field: 'virtual_auth_balance', title: '虚拟授权余额', width: 170,  edit:'text', event:'edit_virtual_auth_balance', rowspan: 2}
                   ,{field: 'address', title: '授权地址'  }
                   // ,{field: 'gl_time_str', title: '归拢时间',  hide: true, }
                   ,{field: 'operate', fixed: 'right', title: '操作',  toolbar: '#toolbar'}
              ]
      
            ],
            done: function(res, curr, count) {
                var total = res.extra_data.total;
                $('#legal_total p.num-value').text(total.legal_balance);
                $('#change_total p.num-value').text(total.change_balance);
                $('#old_balance_total p.num-value').text(total.old_balance);
                // $('#lever_total p.num-value').text(total.lever_balance);   
            }
        });
        form.on('submit(search)', function (data) {
            data_table.reload({
                where: data.field
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

    

        table.on('tool(data_table)', function (obj) {
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var tr = obj.tr; //获得当前行 tr 的DOM对象
            
            if (layEvent === 'transfer') {

                layer.confirm('确定要打入手续费吗?', function (index) {
                    var loading = layer.load(1, {time: 30 * 1000});
                    layer.close(index);
                    $.ajax({
                        url: '/admin/wallet/transfer_poundage'
                        ,type: 'get'
                        ,data: {id: data.id}
                        ,success: function (res) {
                            if(res.type=='error') {
                                layer.msg(res.message);
                            } else {
                                layer.msg(res.message);
                                parent.layer.close(index);
                            }
                        }
                        ,error: function () {
                            layer.msg('网络错误');
                        }
                        ,complete: function () {
                            layer.close(loading);
                        }
                    });
                });
            }
 
            else if (layEvent === 'collect') {
                var index = layer.open({
                    title:'归集表单'
                    ,type:2
                    ,content: '/admin/wallet/collect_form?id='+data.id+'&from_address={{$address}}&sp_address='+data.address
                    ,area: ['700px', '500px']
                    ,maxmin: true
                    ,anim: 3
                });
                // var postData={id: '{{$id}}',spendAddress:data.address};
                // console.log(postData);
                // layer.confirm('确定要归拢链上ID为{{$id}}的余额吗?', function (index) {
                //     var loading = layer.load(1, {time: 30 * 1000});
                //     layer.close(index);
                //     $.ajax({
                //         url: '/admin/wallet/m_collect'
                //         ,type: 'get'
                //         ,data: postData
                //         ,success: function (res) {
                //             if(res.type=='error') {
                //                 layer.msg(res.message);
                //             } else {
                //                 layer.msg(res.message);
                //                 parent.layer.close(index);
                //             }

                //             data_table.reload({
                //                 where: data.field
                //                 ,page: {
                //                     curr: 1 //重新从第 1 页开始
                //                 }
                //             });
                //         }
                //         ,error: function () {
                //             layer.msg('网络错误');
                //         }
                //         ,complete: function () {
                //             layer.close(loading);
                //         }
                //     });
                // });
            }
        });
    });
</script>
@endsection