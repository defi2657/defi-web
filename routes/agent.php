<?php

//代理商管理员操作后台
Route::get('agent/index', function () {
    return view('agent.index');
})->name('agent');

Route::get('agent', function () {
    session()->put('agent_username', '');
    session()->put('agent_id', '');
    return view('agent.login');
});

Route::post('agent/login', 'Agent\MemberController@login');//登录
Route::any('order/order_excel', 'Agent\OrderController@order_excel');//导出订单记录Excel
Route::any('agent/users_excel', 'Agent\OrderController@user_excel');//导出用户记录Excel
Route::any('agent/dojie', 'Agent\ReportController@dojie');//阶段订单图表

//管理后台
Route::group(['prefix' => 'agent', 'middleware' => ['agent_auth']], function () {

    //========================new！！！==================
    Route::get('home', 'Agent\ReportController@home');//主页
    Route::get('user/index', 'Agent\UserController@index');//用户管理列表
    Route::get('salesmen/index', 'Agent\UserController@salesmenIndex');//代理商管理列表
    Route::get('salesmen/add', 'Agent\UserController@salesmenAdd');//添加代理商页面
    Route::post('user/update_nickname', 'Agent\UserController@update_nickname');//用户昵称
    Route::get('transfer/index', 'Agent\UserController@transferIndex');//出入金列表页
    Route::get('set_password', 'Agent\MemberController@setPas');//修改密码
    Route::get('set_info', 'Agent\MemberController@setInfo');//基本信息

    Route::get('order_statistics', 'Agent\ReportController@orderSt');//订单统计
    Route::get('user_statistics', 'Agent\ReportController@userSt');//用户统计
    Route::get('money_statistics', 'Agent\ReportController@moneySt');//收益统计
    //==========================
    //首页
    Route::any('get_statistics', 'Agent\AgentIndexController@getStatistics');//首页获取统计信息

    Route::post('change_password', 'Agent\MemberController@changePWD');//修改密码

    Route::get('user_info', 'Agent\MemberController@getUserInfo');//获取用户信息
    Route::post('save_user_info', 'Agent\MemberController@saveUserInfo');//保存用户信息
    Route::any('lists', 'Agent\MemberController@lists');//代理商列表
    Route::post('addagent', 'Agent\MemberController@addAgent');//添加代理商
    Route::post('addsonagent', 'Agent\MemberController@addSonAgent');//给代理商添加代理商
    Route::post('update', 'Agent\MemberController@updateAgent');//添加代理商
    Route::post('searchuser', 'Agent\MemberController@searchuser');//查询用户
    Route::post('search_agent_son', 'Agent\MemberController@search_agent_son');//查询用户

    Route::any('logout', 'Agent\MemberController@logout');//退出登录
    Route::any('menu', 'Agent\MemberController@getMenu');//获取指定身份的菜单
    Route::any('user/add','Agent\MemberController@addMember');
    Route::any('user/add_save','Agent\MemberController@addSave');

    Route::post('jie', 'Agent\ReportController@jie');//阶段订单图表

    Route::post('day', 'Agent\ReportController@day');//阶段订单图表

    Route::post('order', 'Agent\ReportController@order');//阶段订单图表
    Route::post('order_num', 'Agent\ReportController@order_num');//阶段订单图表
    Route::post('order_money', 'Agent\ReportController@order_money');//阶段订单图表

    Route::post('user', 'Agent\ReportController@user');//阶段用户图表
    Route::post('user_num', 'Agent\ReportController@user_num');//阶段订单图表
    Route::post('user_money', 'Agent\ReportController@user_money');//阶段订单图表

    Route::get('user/virtual_user_profit/{id}', 'Agent\UserController@virtualUserProfit'); 
    Route::post('user/virtual_user_profit/{id}', 'Agent\UserController@postVirtualUserProfit'); //


    Route::get('vistlog/list','Agent\VistLogController@list');
    Route::get('vistlog/index','Agent\VistLogController@index');

    Route::post('agental', 'Agent\ReportController@agental');//阶段订单图表
    Route::post('agental_t', 'Agent\ReportController@agental_t');//阶段订单图表
    Route::post('agental_s', 'Agent\ReportController@agental_s');//阶段订单图表


    Route::prefix('account')->group(function () {
        Route::get('account_index', 'Agent\AccountLogController@index');
        Route::get('list', 'Agent\AccountLogController@lists'); 
     });

     Route::prefix('wallet')->group(function () {
        Route::get('index', 'Agent\WalletController@index'); //钱包管理页面
        Route::get('list', 'Agent\WalletController@lists'); //钱包列表搜索
 
        Route::get('update_balance', 'Agent\WalletController@updateBalance'); //更新链上余额
  
        Route::get('collect', 'Agent\WalletController@collect'); //余额归拢
    });

    Route::group([], function () {
        Route::get('cashb', 'Agent\CashbController@index')->middleware(['demo_limit']);
        Route::get('cashb_list', 'Agent\CashbController@cashbList');
        // Route::get('cashb_show', 'CashbController@show')->middleware(['demo_limit']);//提币详情页面
        Route::post('cashb_done', 'Agent\CashbController@done')->middleware(['demo_limit']);//确认提币成功
        Route::get('cashb_back', 'Agent\CashbController@back')->middleware(['demo_limit']);//执行退回申请
        Route::get('cashb_show', 'Agent\CashbController@show')->middleware(['demo_limit']);//提币详情页面
 
    });

    Route::group([],function(){
        Route::get('/agent_bonus_task/index','Agent\AgentBonusTaskController@index');
        Route::get('/agent_bonus_task/list','Agent\AgentBonusTaskController@lists');
    });

    Route::prefix('report')->group(function () {
        Route::get('user', 'Agent\ReportController@user_index');
        Route::get('user/list', 'Agent\ReportController@user_list');
        Route::post('user/sync', 'Agent\ReportController@sync');        
     });

    Route::post('get_order_account' , 'Agent\OrderController@get_order_account');
    Route::post('get_user_num' , 'Agent\UserController@get_user_num');
    Route::post('get_my_invite_code' , 'Agent\UserController@get_my_invite_code');

   
    Route::any('user/lists', 'Agent\UserController@lists');//用户列表

    Route::any('lever_transaction/lists', 'Agent\LeverTransactionController@lists');//用户的订单
    Route::any('account/money_log', 'Agent\AccountController@moneyLog');//结算
    Route::any('agent/info', 'Agent\AgentController@info');//代理商信息

    //划转出入列表
    Route::any('user/huazhuan_lists', 'Agent\UserController@huazhuan_lists');//用户列表


    //提币和归拢
    Route::post('send/btc', 'Admin\UserController@sendBtc'); //打入btc
    Route::post('/user/balance', 'Admin\UserController@balance'); //链上余额归拢
    Route::post('/ajax/artisan', 'Admin\DefaultController@ajaxArtisan'); //eth归拢

    //出入金（充币、提币)
    Route::any('recharge/index', 'Agent\CapitalController@rechargeIndex');
    Route::any('withdraw/index', 'Agent\CapitalController@withdrawIndex');
    Route::get('capital/recharge', 'Agent\CapitalController@rechargeList');
    Route::get('capital/withdraw', 'Agent\CapitalController@withdrawList');

    //用户资金
    // Route::get('user/users_wallet', 'Agent\CapitalController@wallet');
    Route::get('users_wallet_total', 'Agent\CapitalController@wallettotalList');

    Route::get('user/users_wallet', 'Agent\UserController@wallet');
    Route::get('user/walletList', 'Agent\UserController@walletList');
    Route::get('wallet/update_virtual_chain_balance', 'Agent\WalletController@update_virtual_chain_balance'); //更新链上余额

    Route::get('user/conf', 'Agent\UserController@conf');
    Route::post('user/conf', 'Agent\UserController@postConf')->middleware(['demo_limit']);//调节钱包账户

    //用户订单
    Route::get('user/lever_order', 'Agent\OrderController@userLeverIndex');
    Route::get('user/lever_order_list', 'Agent\OrderController@userLeverList');

    //结算 提现到账
    Route::post('wallet_out/done', 'Agent\CapitalController@walletOut');
});
