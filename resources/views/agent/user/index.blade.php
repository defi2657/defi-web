@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')
<script type="text/javascript" src="/layuiadmin/lib/extend/clipboard.min.js"></script>

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">开始日期</label>
                        <div class="layui-input-block">
                            <input type="text" name="start" id="datestart" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结束日期</label>
                        <div class="layui-input-block">
                            <input type="text" name="end" id="dateend" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">ID</label>
                        <div class="layui-input-block">
                            <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="account_number" placeholder="请输入" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <!-- <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="currency_id" >
                                <option value="-1" class="ww">全部</option>
                                @foreach ($legal_currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                                @endforeach
                            </select>
                        </div>
                   </div> -->
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="san-user-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                        <!-- <button class="layui-btn layui-btn-normal dao" lay-event="add_user">添加用户</button> -->
                        <!-- <button class="layui-btn layuiadmin-btn-useradmin"  onclick="javascript:window.location.href='/order/users_excel'">导出Excel</button> -->
                        <!-- <button class="layui-btn layui-btn-normal dao" lay-event="excel">导出表格</button> -->
                    </div>
                </div>
            </div>
            <div class="layui-card-body">
                <div style="padding-bottom: 10px;">
                    <!--<button class="layui-btn layuiadmin-btn-useradmin" data-type="batchdel">删除</button>-->
                    <button class="layui-btn layuiadmin-btn-useradmin dao" data-type="add_user">添加用户</button>
                </div>
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <ul class="layui-row layui-col-space10 layui-this">
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('总用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总用户数：</h3>
                                <p><cite style="color:#fff" id="_num">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>代理商用户数</h3>
                                <p><cite style="color:#fff" id="_daili">0</cite></p>
                            </a>
                        </li>
                        <!-- <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总入金</h3>
                                <p><cite style="color:#fff" id="_ru">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总出金</h3>
                                <p><cite style="color:#fff" id="_chu">0</cite></p>
                            </a>
                        </li> -->
                        
                    </ul>
                </div>
            </div>


            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <table id="san-user-manage" lay-filter="san-user-manage" lay-filter="san-user-manage"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/html" id="table-useradmin-webuser">
        <a class="layui-btn layui-btn-normal layui-btn-xs copy" lay-event="copy_link">推广链接</a> 
    </script>


<script>
    layui.use(['index','laydate','form','table'], function () {
            var $ = layui.$
                ,admin = layui.admin
            , table = layui.table
            , layer = layui.layer
            , laydate = layui.laydate
            , form = layui.form;


        //日期
        laydate.render({
            elem: '#datestart'
        });
        laydate.render({
            elem: '#dateend'
        });

        var parent_id = {{ $parent_id }};
        console.log(parent_id);

        admin.req( {
            type : "POST",
            url : '/agent/get_user_num',
            dataType : "json",
            data : {all : 1 , parent_id : parent_id},
            done : function(result) { //返回数据根据结果进行相应的处理
                $("#_num").html(result.data._num);
                $("#_daili").html(result.data._daili);
                $("#_ru").html(result.data._ru);
                $("#_chu").html(result.data._chu);
               
            }
        });

        load(parent_id);

        function load(parent_id) {
            parent_id = parent_id || 0;

            table.render({
                elem: '#san-user-manage'
                , url: '/agent/user/lists?parent_id=' + parent_id //模拟接口
                , cols: [[
                    {type: 'checkbox', fixed: 'left'}
                    , {field: 'id', width: 60, title: 'ID', sort: true}
                    , {field: 'account_number', title: '用户名', minWidth: 150}
                    // , {field: 'my_agent_level', title: '用户身份' , width : 120}
                    // , {field: 'card_id', title: '身份证号' , width : 180}
                    , {field: 'nickname', title: '昵称' ,edit:'text', width : 120,event:'edit_nickname'}
                    , {field: 'parent_agent_name', title: '上级代理商' , width : 120}
                    // , {field: 'phone', title: '手机号', minWidth: 150}
                    // , {field: 'email', title: '邮箱', minWidth: 150}
                    , {field: 'extension_code', title: '邀请码', minWidth: 150}
                    , {field: 'time', title: '加入时间', sort: true, width: 170}
                    , {title: '操作', width: 200, align: 'center', fixed: 'right', toolbar: '#table-useradmin-webuser'}
                ]]
                , page: true
                , limit: 30
                , height: 'full-320'
                , text: '对不起，加载出现异常！'
                // , headers: { //通过 request 头传递
                //     access_token: layui.data('layuiAdmin').access_token
                // }
                // , where: { //通过参数传递
                //     access_token: layui.data('layuiAdmin').access_token
                //     ,parent_id : parent_id
                // }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                    if (res !== 0) {
                        if (res.code === 1001) {
                            //清空本地记录的 token，并跳转到登入页
                            admin.exit();
                        }
                    }
                }
            });

            table.on('edit(san-user-manage)',function(obj){

                if(obj.field=='nickname')
                {
                    admin.req( {
                        type : "POST",
                        url : '/agent/user/update_nickname',
                        dataType : "json",
                        data : {id :obj.data.id , nickname : obj.value},
                        done : function(result) { //返回数据根据结果进行相应的处理
                                layer_msg('修改昵称成功！');
                        }
                    });
                }

                console.log(obj.value); //得到修改后的值
                console.log(obj.field); //当前编辑的字段名
                console.log(obj.data); //所在行的所有相关数据
            });
        }

        function layer_msg(msg)
        {
            layer.msg(msg);
        }
        function copy(addre){
				console.log(addre)
				var content = addre;
				var clipboard = new ClipboardJS('.copy', {
					text: function () {
						return content;
					}
				});
				clipboard.on('success', function (e) {
					layer_msg("复制成功")
				});

				clipboard.on('error', function (e) {
					layer_msg("请重新复制")
				});
			}
        table.on('tool(san-user-manage)', function (obj) {
            var event = obj.event;
            var data = obj.data;

            if (event == 'copy_link') {
                //查看订单
                //location.protocol + '//' + document.domain +'/receive?code=' + invite_code
                var domain="@if(isset($setting['proxy_pop_domain'])){{$setting['proxy_pop_domain']}}@endif";
                var url=  domain +'/?code=' + data.extension_code;
                copy(url);
            }
           

            if (event == 'son') {
                load(data.id);
            }
        });


        form.render(null, 'layadmin-userfront-formlist');

        //监听搜索
        form.on('submit(san-user-search)', function (data) {
            var field = data.field;


            admin.req( {
                type : "POST",
                url : '/agent/get_user_num',
                dataType : "json",
                data : field,
                done : function(result) { //返回数据根据结果进行相应的处理
                    $("#_num").html(result.data._num);
                    $("#_daili").html(result.data._daili);
                    $("#_ru").html(result.data._ru);
                    $("#_chu").html(result.data._chu);
                }
            });

            //执行重载
            table.reload('san-user-manage', {
                where: field
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    if (res.code === 1001) {
                        //清空本地记录的 token，并跳转到登入页
                        admin.exit();
                    }
                    if (res.code === 1) {
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            });
        });

        //导出表格
        $('.dao').click(function () {
            layer.show('添加用户', '/agent/user/add',[]);

        })

    });
</script>

@endsection