<div class="layui-side layui-side-menu">
    <div class="layui-side-scroll">
        <div class="layui-logo" lay-href="/agent/index">
            <span>代理商后台系统</span>
        </div>

        <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
            <li data-name="home" class="layui-nav-item layui-nav-itemed">
                <a href="javascript:;" lay-tips="主页" lay-direction="2">
                    <i class="layui-icon layui-icon-home"></i>
                    <cite>主页</cite>
                </a>
                <dl class="layui-nav-child">
                    <dd data-name="console" class="layui-this">
                        <a lay-href="/agent/home">控制台</a>
                    </dd>
                </dl>
            </li>
            <li data-name="user" class="layui-nav-item">
                <a href="javascript:;" lay-tips="用户管理" lay-direction="2">
                    <i class="layui-icon layui-icon-user"></i>
                    <cite>用户管理</cite>
                </a>
                <dl class="layui-nav-child">
                   
                    <dd data-name="button">
                        <a lay-href="/agent/user/index">用户管理</a>
                    </dd>
                    <dd data-name="button">
                        <a lay-href="/agent/salesmen/index">代理商管理</a>
                    </dd>
                    
                   
                </dl>
            </li>
         
            <li data-name="template" class="layui-nav-item">
                <a href="javascript:;" lay-tips="财务管理" lay-direction="2">
                    <i class="layui-icon layui-icon-template"></i>
                    <cite>财务管理</cite>
                </a>
                <dl class="layui-nav-child">
                   
                   <dd data-name="button">
                       <a lay-href="/agent/account/account_index">财务流水</a>
                   </dd>
                   <dd data-name="button">
                       <a lay-href="/agent/wallet/index">钱包统计</a>
                   </dd>
                   <dd data-name="button">
                       <a lay-href="/agent/cashb">提币列表</a>
                   </dd>
                   <dd data-name="button">
                       <a lay-href="/agent/report/user">团队报表</a>
                   </dd>
                   <dd data-name="button">
                       <a lay-href="/agent/agent_bonus_task/index">收割列表</a>
                   </dd>
               </dl>
         
            </li>
    

  

            <li data-name="template" class="layui-nav-item">
                <a href="javascript:;" lay-tips="设置" lay-direction="2">
                    <i class="layui-icon layui-icon-set"></i>
                    <cite>设置</cite>
                </a>
                <dl class="layui-nav-child">
                    <dd data-name="button">
                        <a lay-href="/agent/set_password">修改密码</a>
                    </dd>
                </dl>

                <dl class="layui-nav-child">
                    <dd data-name="button">
                        <a lay-href="/agent/set_info">基本资料</a>
                    </dd>
                </dl>
                
            </li>
            

        </ul>
    </div>
</div>
