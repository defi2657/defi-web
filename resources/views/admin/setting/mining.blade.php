<div class="layui-tab-item layui-show">
    <fieldset class="layui-elem-field">
        <legend>
            <i class="layui-icon layui-icon-password"></i>
            <span>基础设置</span>

        </legend>
        <div class="layui-field-box">


            <div class="layui-form-item">
                <label class="layui-form-label">邀请分红比率</label>
                <div class="layui-input-inline">
                    <input type="text" name="mining_invite_rate" autocomplete="off" class="layui-input" value="@if(isset($setting['mining_invite_rate'])){{$setting['mining_invite_rate'] ?? ''}}@endif">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">用户套餐分红条件(持有矿机TRX数量)</label>
                <div class="layui-input-inline">
                    <input type="text" name="user_mining_num" autocomplete="off" class="layui-input" value="@if(isset($setting['user_mining_num'])){{$setting['user_mining_num'] ?? ''}}@endif">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">用户矿机分红条件(钱包TRX余额)</label>
                <div class="layui-input-inline">
                    <input type="text" name="user_wallet_num" autocomplete="off" class="layui-input" value="@if(isset($setting['user_wallet_num'])){{$setting['user_wallet_num'] ?? ''}}@endif">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">TRX汇率</label>
                <div class="layui-input-inline">
                    <input type="text" name="mining_usdt_price" autocomplete="off" class="layui-input" value="@if(isset($setting['mining_usdt_price'])){{$setting['mining_usdt_price'] ?? ''}}@endif">

                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">最小提币金额</label>
                <div class="layui-input-inline">
                    <input type="text" name="withdraw_min_amount" autocomplete="off" class="layui-input" value="@if(isset($setting['withdraw_min_amount'])){{$setting['withdraw_min_amount'] ?? ''}}@endif">

                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">最大提币金额</label>
                <div class="layui-input-inline">
                    <input type="text" name="withdraw_max_amount" autocomplete="off" class="layui-input" value="@if(isset($setting['withdraw_max_amount'])){{$setting['withdraw_max_amount'] ?? ''}}@endif">

                </div>
            </div>
            <!-- <div class="layui-form-item">
                <label class="layui-form-label">最小提币金额(理财)</label>
                <div class="layui-input-inline">
                    <input type="text" name="withdraw_min_amount_bk2" autocomplete="off" class="layui-input" value="@if(isset($setting['withdraw_min_amount_bk2'])){{$setting['withdraw_min_amount_bk2'] ?? ''}}@endif">

                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">最大提币金额(理财)</label>
                <div class="layui-input-inline">
                    <input type="text" name="withdraw_max_amount_bk2" autocomplete="off" class="layui-input" value="@if(isset($setting['withdraw_max_amount_bk2'])){{$setting['withdraw_max_amount_bk2'] ?? ''}}@endif">

                </div>
            </div> -->
        </div>
    </fieldset>

    <!-- <fieldset class="layui-elem-field">
    <input type="hidden" id="withdrawalConfig" name="withdrawalConfig" autocomplete="off" v-model="withdrawalConfigJson" class="layui-input" value="@if(isset($setting['withdrawalConfig'])){{$setting['withdrawalConfig'] ?? ''}}@endif">

        <legend><i class="layui-icon layui-icon-component"></i> <span>市场配置</span>
            <span class=" layui-word-aux">提现复投</span>  </span>
        </legend>

        <div class="layui-field-box">
            <table class="layui-table">

                <tr>
                    <th>
                        序号
                    </th>
                    <th>
                        投资额(TRX)
                    </th>
                    <th>
                        直推数
                    </th>
                    <th>
                        提现比例
                    </th>
                    <th>
                        复投比例
                    </th>
                </tr>
                <tr v-for="(item,index) in withdrawalConfig">
                    <td>@{{index+1}}</td>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" autocomplete="off" v-model="item.amount_range.st" class="layui-input">
                            </div>
                            -
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" value="1000" v-model="item.amount_range.et" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </td>
                    <td>                    
                        <table>
                            <tr>
                                <td style="border-width:0px;width:80px">
 
                                <select  lay-ignore v-model="item.push_count.op"  lay-filter='select_push_count_op'>
                                                    <option value="==">==</option>
                                                    <option value="<"><</option>
                                                    <option value="<="><=</option>
                                                    <option value=">">></option>
                                                    <option value=">=">>=</option>
                                                    <option value="<>"><></option>
                                                </select>
                                </td>
                                <td style="border-width:0px;">

                                <input type="text" placeholder="直推" v-model="item.push_count.op_val" autocomplete="off" class="layui-input">
                                        
                                </td>
                            </tr>
                        </table>
             
                    </td>
                    <td>
                        <input type="text" placeholder="提现" v-model="item.withdrawal_scale" autocomplete="off" class="layui-input">
                    </td>
                    <td>
                        <input type="text" placeholder="复投" v-model="item.ft_scale" autocomplete="off" class="layui-input">
                    </td>
                </tr>

            </table>


        </div>
 
    </fieldset> -->

    <fieldset class="layui-elem-field">
    <input type="hidden" id="staticBonusConfig" name="staticBonusConfig" autocomplete="off" v-model="staticBonusConfigJson" class="layui-input" value="@if(isset($setting['staticBonusConfig'])){{$setting['staticBonusConfig'] ?? ''}}@endif">

        <legend><i class="layui-icon layui-icon-component"></i> <span>市场配置</span>
            <span class=" layui-word-aux">静态奖励</span>  </span>
        </legend>

        <div class="layui-field-box">
            <table class="layui-table">

                <tr>
                    <th>
                        序号
                    </th>
                    <th>
                        投资额(TRX)
                    </th>
                    <th>
                        奖励比例
                    </th>
                </tr>
                <tr v-for="(item,index) in staticBonusConfig">
                    <td>@{{index+1}}</td>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" autocomplete="off" v-model="item.amount_range.st" class="layui-input">
                            </div>
                            -
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" value="1000" v-model="item.amount_range.et" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </td>                    
                    <td>
                        <input type="text" placeholder="比例" v-model="item.bonus_scale" autocomplete="off" class="layui-input">
                    </td>
                </tr>

            </table>


        </div>
 
    </fieldset>

    <!-- <fieldset class="layui-elem-field">
    <input type="hidden" id="bondZanZhuConfig" name="bondZanZhuConfig" autocomplete="off" v-model="bondZanZhuConfigJson" class="layui-input" value="@if(isset($setting['bondZanZhuConfig'])){{$setting['bondZanZhuConfig'] ?? ''}}@endif">

        <legend><i class="layui-icon layui-icon-component"></i> <span>市场配置</span>
            <span class=" layui-word-aux">赞助社区50%（血缘七代）</span>
        </legend>

        <div class="layui-field-box">
            <table class="layui-table">

                <tr>
                    <th>
                        序号
                    </th>
                    <th>
                        投资额(TRX)
                    </th>
                    <th>
                        直推数
                    </th>
                    <th>
                        拿几代
                    </th>
                    <th>
                        比例(@{{sumZanZhuScale}})
                    </th>
                </tr>
                <tr v-for="(item,index) in bondZanZhuConfig">
                    <td>@{{item.seq}}</td>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" autocomplete="off" v-model="item.amount_range.st" class="layui-input">
                            </div>
                            -
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" value="1000" v-model="item.amount_range.et" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </td>
                    <td>
                    <table>
                            <tr>
                                <td style="border-width:0px; width:80px" >
                                <select  lay-ignore v-model="item.push_count.op" lay-verify="required">
                                                    <option value="==">==</option>
                                                    <option value="<"><</option>
                                                    <option value="<="><=</option>
                                                    <option value=">">></option>
                                                    <option value=">=">>=</option>
                                                    <option value="<>"><></option>
                                                </select>
                                </td>
                                <td style="border-width:0px;">

                                <input type="text" placeholder="直推" v-model="item.push_count.op_val" autocomplete="off" class="layui-input">
                                        
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <input type="text" placeholder="代" v-model="item.obtain_count" autocomplete="off" class="layui-input">
                    </td>
                    <td>
                        <input type="text" placeholder="比例" v-model="item.obtain_scale" autocomplete="off" class="layui-input">
                    </td>
                </tr>

            </table>


        </div>
    </fieldset> -->


    <!-- <fieldset class="layui-elem-field">
    <input type="hidden" id="bondSheQuConfig" name="bondSheQuConfig" autocomplete="off" v-model="bondSheQuConfigJson" class="layui-input" value="@if(isset($setting['bondSheQuConfig'])){{$setting['bondSheQuConfig'] ?? ''}}@endif">

        <legend><i class="layui-icon layui-icon-component"></i> <span>市场配置</span>
            <span class=" layui-word-aux">社区（50%）<span style="color: red;">红色:上社区</span> <span style="color:green">绿色:下社区</span>  </span>
        </legend>

        <div class="layui-field-box">
            <table class="layui-table">

                <tr>
                    <th>
                        序号
                    </th>
                
<th v-for="(item,index) in bondSheQuStruct">
@{{item.st}}-@{{item.et}} (@{{sumScale[index]}}) 
</th>
                </tr>
                <tr v-for="(item,index) in bondSheQuConfig">
                    <td v-if="item.seq>0" style="color:green">@{{item.seq}}</td>
                    <td v-if="item.seq<0" style="color:red">@{{item.seq}}</td>
                    <td v-for="amountObjs in item.amount_range">
                        <input  type="number" placeholder="TRX" autocomplete="off" v-model="amountObjs.obtain_scale" class="layui-input">
                    </td>
                </tr>
            </table>
        </div>
    </fieldset> -->

    <fieldset class="layui-elem-field">
    <input type="hidden" id="fundDynamicConfig" name="fundDynamicConfig" autocomplete="off" v-model="fundDynamicConfigJson" class="layui-input" value="@if(isset($setting['fundDynamicConfig'])){{$setting['fundDynamicConfig'] ?? ''}}@endif">

        <legend><i class="layui-icon layui-icon-component"></i> <span>财富计划</span>
            <span class=" layui-word-aux"> 动态收益 <a href="javascript:void(0)" v-on:click="add_fundDynamicConfig()">添加</a>   </span>
        </legend>

        <div class="layui-field-box">
            <table class="layui-table">

                <tr>
                    <th>
                        序号
                    </th>
                    <th>
                        用户等级(LEVEL)
                    </th>
                    <th>
                        投资额（TRX）
                    </th>
                    <th>
                        代数比例
                    </th>
                    <th>
                        操作
                    </th>
              
                </tr>
                <tr v-for="(item,index) in fundDynamicConfig">
                    <td>@{{index+1}}</td>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="LEVEL" autocomplete="off" v-model="item.user_level"  >
                            </div>
                             
                        </div>
                    </td>
                    <td>                    
                    <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" autocomplete="off" v-model="item.amount_range.st" >
                            </div>
                            -
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" placeholder="TRX" value="1000" v-model="item.amount_range.et" autocomplete="off"  >
                            </div>
                        </div>
             
                    </td>
                    <td>
                        <div>
                            
                        </div>
                         <table  >
                             <tr v-for="(item_sub,index_sub) in item.obtain">
                                <td style="border-width:0px;">
                                    <select  lay-ignore v-model="item_sub.obtain_count"  lay-filter='select_obtain_count'>
                                        <option v-for="index of 20" :key="index" :value="index">@{{index}}代</option>                                        
                                        <option  value="-1">其代</option>  
                                    </select>                                                                      
                                </td>
                                <td style="border-width:0px;">
                                    <input type="text" placeholder="" v-model="item_sub.obtain_scale" autocomplete="off"  >      
                                </td>
                                <td style="border-width:0px;">
                                   <a href="javascript:void(0)" v-on:click="add_fundDynamicConfig_sub(index)">添加</a> 
                                   <a href="javascript:void(0)" v-on:click="del_fundDynamicConfig_sub(index,index_sub)">删除</a> 
                                </td>
                             </tr>
                         </table>
                    </td>
                    <td>
                                    <a href="javascript:void(0)" v-on:click="add_fundDynamicConfig()">添加</a> 
                                   <a href="javascript:void(0)" v-on:click="del_fundDynamicConfig(index)">删除</a> 
                    </td>
                </tr>

            </table>


        </div>
    </fieldset>
</div>