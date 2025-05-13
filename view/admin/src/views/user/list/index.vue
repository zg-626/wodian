<template>
<div class="divBox">
    <div class="selCard">
        <el-form :model="userFrom" ref="searchForm" inline size="small" label-width="85px">
            <div v-if="collapse" class="acea-row search-form">
                <div>
                    <el-form-item label="用户搜索：">
                        <el-input placeholder="请输入内容" v-model="keywords" class="input-with-select selWidth" clearable @keyup.enter.native="changeSearch(1)">
                            <el-select v-model="select" slot="prepend" clearable placeholder="请选择">
                                <el-option label="全部" value="">全部</el-option>
                                <el-option label="用户昵称" value="nickname">用户昵称</el-option>
                                <el-option label="手机号" value="phone">手机号</el-option>
                                <el-option label="用户ID" value="uid">用户ID</el-option>
                            </el-select>
                        </el-input>  
                    </el-form-item>
                    <el-form-item label="用户分组：" prop="group_id">
                        <el-select v-model="userFrom.group_id" placeholder="请选择" class="selWidth" clearable filterable @change="changeSearch(1)">
                            <el-option v-for="(item, index) in groupList" :key="index" :value="item.group_id" :label="item.group_name" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="用户标签：" prop="label_id">
                        <el-select v-model="userFrom.label_id" placeholder="请选择" class="selWidth" clearable filterable @change="changeSearch(1)">
                            <el-option v-for="(item, index) in labelLists" :key="index" :value="item.label_id" :label="item.label_name" />
                        </el-select>
                    </el-form-item>
                </div>
                <el-form-item class="search-form-sub">
                   <el-button type="primary" size="small" @click="changeSearch(1)">搜索</el-button>
                    <el-button size="small" @click="searchReset()">重置</el-button> 
                    <a class="ivu-ml-8 font12 ml10" @click="collapse = !collapse">
                        <template v-if="collapse"> 展开 <i class="el-icon-arrow-down" /> </template>
                        <template v-else> 收起 <i class="el-icon-arrow-up" /> </template>
                    </a>  
                </el-form-item>
            </div>
            <div v-else class="acea-row search-form">
                <div class="search-form-box">
                    <el-form-item label="用户搜索：">
                        <el-input placeholder="请输入内容" v-model="keywords" class="input-with-select selWidth" clearable @keyup.enter.native="changeSearch(1)">
                            <el-select v-model="select" slot="prepend" clearable placeholder="请选择">
                                <el-option label="全部" value="">全部</el-option>
                                <el-option label="用户昵称" value="nickname">用户昵称</el-option>
                                <el-option label="手机号" value="phone">手机号</el-option>
                                <el-option label="用户ID" value="uid">用户ID</el-option>
                            </el-select>
                        </el-input>  
                    </el-form-item>
                    <el-form-item label="用户分组：" prop="group_id">
                        <el-select v-model="userFrom.group_id" placeholder="请选择" class="selWidth" clearable filterable @change="changeSearch(1)">
                            <el-option v-for="(item, index) in groupList" :key="index" :value="item.group_id" :label="item.group_name" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="用户标签：" prop="label_id">
                        <el-select v-model="userFrom.label_id" placeholder="请选择" class="selWidth" clearable filterable @change="changeSearch(1)">
                            <el-option v-for="(item, index) in labelLists" :key="index" :value="item.label_id" :label="item.label_name" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="用户类型：" prop="is_svip">
                        <el-select v-model="userFrom.is_svip" placeholder="请选择" class="selWidth" clearable filterable @change="changeSearch(1)">
                            <el-option value="0" label="普通用户">普通用户</el-option>
                            <el-option value="1" label="付费会员">付费会员</el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="用户等级：" prop="member_level">
                        <el-select
                        v-model="userFrom.member_level"
                        placeholder="请选择"
                        class="selWidth" clearable filterable @change="changeSearch(1)"
                        >
                            <el-option
                                v-for="item in memberList"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="消费情况：" prop="pay_count">
                        <el-select v-model="userFrom.pay_count" placeholder="请选择" class="selWidth" clearable @change="changeSearch(1)">
                            <el-option value="-1" label="0次"></el-option>
                            <el-option value="0" label="1次及以上"></el-option>
                            <el-option value="1" label="2次及以上"></el-option>
                            <el-option value="2" label="3次及以上"></el-option>
                            <el-option value="3" label="4次及以上"></el-option>
                            <el-option value="4" label="5次及以上"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="访问时间：">
                        <el-date-picker v-model="timeVal" value-format="yyyy/MM/dd" align="right" unlink-panels format="yyyy/MM/dd" size="small" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 250px;" :picker-options="pickerOptions" @change="onchangeTime" />
                    </el-form-item>
                    
                    <el-form-item label="访问情况：" prop="user_time_type">
                        <el-select v-model="userFrom.user_time_type" placeholder="请选择" class="selWidth" clearable @change="changeSearch(1)">
                            <el-option value="visit" label="最后访问" />
                            <el-option value="add_time" label="首次访问" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="性别：" prop="sex">
                        <el-select v-model="userFrom.sex" placeholder="请选择" class="selWidth" clearable filterable @change="changeSearch(1)">
                            <el-option value="1" label="男">男</el-option>
                            <el-option value="2" label="女">女</el-option>
                            <el-option value="0" label="保密">保密</el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="身份：" prop="is_promoter">
                        <el-select v-model="userFrom.is_promoter" placeholder="请选择" class="selWidth" clearable filterable @change="changeSearch(1)">
                            <el-option value="1" label="推广员">推广员</el-option>
                            <el-option value="0" label="普通会员">普通会员</el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="生日：">
                        <el-date-picker v-model="timeVal2" value-format="yyyy/MM/dd" align="right" unlink-panels format="yyyy/MM/dd" size="small" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 250px;" :picker-options="pickerOptions" @change="onchangeTime2" />
                    </el-form-item>
                    <el-form-item label="信息补充：">
                        <el-input placeholder="请输入内容" v-model="userFrom.fields_value" class="input-with-select selWidth" clearable @keyup.enter.native="changeSearch(1)">
                            <el-select v-model="userFrom.fields_type" slot="prepend" clearable placeholder="请选择">
                                <el-option v-for="(item,index) in selectList" :label="item.label" :value="item.value" :key="index"></el-option>
                            </el-select>
                        </el-input>  
                    </el-form-item>
                </div>
                
                <el-form-item class="search-form-sub">
                    <el-button type="primary" size="small" @click="changeSearch(1)">搜索</el-button>
                    <el-button size="small" @click="searchReset()">重置</el-button> 
                    <a class="ivu-ml-8 font12 ml10" @click="collapse = !collapse">
                        <template v-if="collapse"> 展开 <i class="el-icon-arrow-down" /> </template>
                        <template v-else> 收起 <i class="el-icon-arrow-up" /> </template>
                    </a>  
                </el-form-item>
            </div>
        </el-form>
    </div>
    <el-card class="mt14">
        <div>
            <!-- <el-tabs v-model="user_type" @tab-click="changeSearch(1)" class="mb5">
                <el-tab-pane label="全部用户" name="" />
                <el-tab-pane label="微信用户" name="wechat" />
                <el-tab-pane label="小程序用户" name="routine" />
                <el-tab-pane label="H5用户" name="h5" />
                <el-tab-pane label="APP" name="app" />
                <el-tab-pane label="PC" name="pc" />
            </el-tabs> -->
            <div class="mb20">
                <el-button type="primary" size="small" @click="createUser">创建用户</el-button>             
                <el-button v-show="user_type === 'wechat'" size="small" @click="sendNews">发送图文消息</el-button>
                <el-button :disabled="checkedIds.length == 0" size="small" @click="batchGroup">批量设置分组</el-button>
                <el-button :disabled="checkedIds.length == 0" size="small" @click="batchlabel">批量设置标签</el-button>
                <el-button :disabled="checkedIds.length == 0" size="small" @click="setDistributor">批量设置分销员</el-button>
                <el-button :disabled="checkedIds.length == 0" label="default" size="small" @click="sendCoupon">发送优惠券</el-button>
                <el-button size="small" @click="exportList">导出列表</el-button>
            </div>
            <el-alert v-if="checkedIds.length>0 || allCheck" :title="allCheck ? `已选择  ${tableData.total}  项` : `已选择  ${checkedIds.length}  项`" type="info" show-icon class="mb10" />
        </div>
            <el-table v-loading="listLoading" :data="tableData.data" size="small" highlight-current-row @selection-change="handleSelectionChange">
                <el-table-column type="expand">
                    <template slot-scope="props">
                        <el-form label-position="left" inline class="demo-table-expand">
                            <el-form-item label="首次访问：">
                                <span>{{ props.row.create_time }}</span>
                            </el-form-item>
                            <el-form-item label="近次访问：">
                                <span>{{ props.row.last_time }}</span>
                            </el-form-item>
                            <el-form-item label="身份证号：">
                                <span>{{ props.row.card_id }}</span>
                            </el-form-item>
                            <el-form-item label="手机号：">
                                <span>{{ props.row.phone }}</span>
                            </el-form-item>
                            <el-form-item label="真实姓名：">
                                <span>{{ props.row.real_name }}</span>
                            </el-form-item>
                            <el-form-item label="标签：">
                                <span v-for="(item, index) in props.row.label" :key="index" v-text="item" />
                            </el-form-item>
                            <el-form-item label="生日：">
                                <span>{{ props.row.birthday }}</span>
                            </el-form-item>
                            <el-form-item label="地址：">
                                <span class="minwidth">{{ props.row.addres }}</span>
                            </el-form-item>
                            <el-form-item label="备注：">
                                <span>{{ props.row.mark }}</span>
                            </el-form-item>
                        </el-form>
                    </template>
                </el-table-column>
                <el-table-column  width="50">
                    <template slot="header" slot-scope="scope">
                        <el-popover placement="top-start" width="100" trigger="hover" class="tabPop">
                            <div>
                                <span class="spBlock onHand" :class="{'check': chkName === 'dan'}" @click="onHandle('dan',scope.$index)">选中本页</span>
                                <span class="spBlock onHand" :class="{'check': chkName === 'duo'}" @click="onHandle('duo')">选中全部</span>
                            </div>
                            <el-checkbox slot="reference" :value="(chkName === 'dan' && checkedPage.indexOf(userFrom.page) > -1) || chkName === 'duo'" @change="changeType" />
                        </el-popover>
                    </template>
                    <template slot-scope="scope">
                        <el-checkbox :disabled="scope.row.cancel_time" :value="!scope.row.cancel_time && (checkedIds.indexOf(scope.row.uid) > -1 || (chkName === 'duo' && noChecked.indexOf(scope.row.uid) === -1))" @change="(v)=>changeOne(v,scope.row)" />
                    </template>
            </el-table-column>
            <el-table-column prop="uid" label="ID" min-width="60" />
            <el-table-column label="头像" min-width="50">
                <template slot-scope="scope">
                    <div class="demo-image__preview">
                        <el-image style="width: 36px; height: 36px" :src="scope.row.avatar ? scope.row.avatar : moren" :preview-src-list="[scope.row.avatar || moren]" />
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="昵称" min-width="150">
                <template slot-scope="{row}">
                    <div class="acea-row">
                        <i v-show="row.sex===1" class="el-icon-male mr5" style="font-size: 15px; margin-top: 3px; color:#2db7f5;" />
                        <i v-show="row.sex===2" class="el-icon-female mr5" style="font-size: 15px; margin-top: 3px; color:#ed4014;" />
                        <div v-text="row.nickname" />
                    </div>
                </template>
            </el-table-column>
            <!-- <el-table-column prop="is_svip" label="付费会员" min-width="120">
                    <template slot-scope="{row}">
                    <span>{{row.is_svip > 0 ? "是" : "否"}}</span>
                </template>
            </el-table-column> -->
            <el-table-column prop="phone" label="手机号" min-width="120" />
            <el-table-column label="等级" min-width="100">
                <template slot-scope="{row}">
                    <span>{{ row.member?row.member.brokerage_name:'-' }}</span>
                </template>
            </el-table-column>
            <el-table-column label="分组" min-width="100">
                <template slot-scope="{row}">
                    <span>{{ row.group?row.group.group_name:'无' }}</span>
                </template>
            </el-table-column>
            <el-table-column label="推荐人" min-width="140">
                <template slot-scope="{row}">
                    <span>{{ row.spread ? row.spread.nickname + ' / ' + row.spread.uid : '-'  }}</span>
                </template>
            </el-table-column>
            <el-table-column label="上级" min-width="140">
                <template slot-scope="{row}">
                    <span>{{ row.superior ? row.superior.nickname + ' / ' + row.superior.uid : '-'  }}</span>
                </template>
            </el-table-column>
            <!-- <el-table-column label="用户类型" min-width="100">
                <template slot-scope="{row}">
                <span>{{ row.user_type === 'routine' ? '小程序' : row.user_type === 'wechat' ? '公众号' : row.user_type === 'app' || row.user_type === 'App' ? 'App' : row.user_type === 'pc' ? 'PC' : 'H5' }}</span>
                </template>
            </el-table-column> -->
            <el-table-column prop="now_money" label="余额" sortable min-width="100" :sort-method="(a,b)=>{return a.now_money - b.now_money}"/>
            <el-table-column prop="integral" label="当前可用积分" min-width="100" />
            <el-table-column label="操作" min-width="150" fixed="right">
                <template slot-scope="scope">
                    <el-button v-if="!scope.row.cancel_time" type="text" size="small" @click="onDetails(scope.row.uid)">详情</el-button>
                    <el-button  type="text" size="small" class="mr10" @click="onEdit(scope.row.uid)">编辑</el-button>
                    <!-- <el-button  type="text" size="small" class="mr10" @click="extendInfo(scope.row)">信息补充</el-button> -->
                    <el-dropdown>
                        <span class="el-dropdown-link">
                            更多<i class="el-icon-arrow-down el-icon--right" />
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <!-- <el-dropdown-item @click.native="onEdit(scope.row.uid)">编辑信息</el-dropdown-item>                             -->
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="setMoney(scope.row)">设置余额</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="changeIntegral(scope.row)">设置积分</el-dropdown-item>
                            <el-dropdown-item v-if="scope.row.vip_name && !scope.row.cancel_time">清除等级</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="setGroup(scope.row)">设置分组</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="setLabel(scope.row)">设置标签</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="setSuperior(scope.row)">修改上级</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="setModify(scope.row)">修改推荐人</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="setPassword(scope.row)">修改密码</el-dropdown-item>
                            <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="setMember(scope.row)">编辑会员等级</el-dropdown-item>
                                <el-dropdown-item v-if="!scope.row.cancel_time" @click.native="giveMember(scope.row)">付费会员设置</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </template>
            </el-table-column>
        </el-table>
        <div class="block">
            <el-pagination background :page-size="userFrom.limit" :current-page="userFrom.page" layout="total, prev, pager, next, jumper" :total="tableData.total" @size-change="handleSizeChange" @current-change="pageChange" />
        </div>
    </el-card>
    <el-dialog title="提示" :visible.sync="visible" width="1000px" :before-close="handleClose" class="dia">
        <news-category v-if="visible" :is-show-send="isShowSend" :wechat-ids="wechatIds" :user-ids="ids" :max-cols="maxCols" />
        <!--<span slot="footer" class="dialog-footer" />-->
    </el-dialog>
    <!--创建用户-->
    <user-create
        @closeDrawer="closeDrawer"
        @getList="changeSearch"
        :createDrawer="createDrawer" 
        ref="userCreate" 
        />
    <!--用户详情-->
    <user-detail
        @closeDrawer="closeDrawer"
        @changeDrawer="changeDrawer"
        @onEdit="onEdit"
        @setMoney="setMoney"
        @changeIntegral="changeIntegral"
        @setGroup="setGroup"
        @setLabel="setLabel"
        @setModify="setModify"
        @setSuperior="setSuperior"
        @setPassword="setPassword"
        @setMember="setMember"
        @giveMember="giveMember"
        :drawer="drawer" 
        :isUser="true"
        :labelLists="labelLists"
        :groupList="groupList"
        :memberList="memberList"
        ref="userDetails" 
        :cancel-time="cancel_time" />
    <!-- 选择优惠券 -->
    <el-dialog v-if="visibleCoupon" title="优惠券列表" :visible.sync="visibleCoupon" width="1000px">
      <coupon-List v-if="visibleCoupon" ref="couponList" :couponForm="couponForm" :checkedIds="checkedIds" :allCheck="allCheck" :userFrom="userFrom" @sendSuccess="sendSuccess" />        
    </el-dialog>
</div>
</template>

<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import {
    userLstApi,
    changeGroupApi,
    changelabelApi,
    changeNowMoneyApi,
    changeNowIntegralApi,
    batchChangeGroupApi,
    batchChangelabelApi,
    changePrommoterApi,
    userUpdateApi,
    groupLstApi,
    labelLstApi,
    modifyUserReferrer,
    modifyUserSuperior,
    modifyUserPassword,
    changeMemberApi,
    createUserApi,
    giveMemberApi,
    extendInfo,
    exportUserApi,
    userInfoSelectApi,
    userMemberListApi
} from '@/api/user'
import newsCategory from '@/components/newsCategory/index.vue'
import userDetail from './userDetails'
import userCreate from './userCreate'
import couponList from './couponList'
import createWorkBook from '@/utils/newToExcel.js'
export default {
    name: 'UserList',
    components: {
        newsCategory,
        userDetail,
        userCreate,
        couponList
    },
    data() {
        return {
            moren: require("@/assets/images/f.png"),
            pickerOptions: {
                shortcuts: [{
                        text: '今天',
                        onClick(picker) {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()))
                            picker.$emit('pick', [start, end])
                        }
                    },
                    {
                        text: '昨天',
                        onClick(picker) {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)))
                            end.setTime(end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate())))
                            picker.$emit('pick', [start, end])
                        }
                    },
                    {
                        text: '最近7天',
                        onClick(picker) {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7)
                            picker.$emit('pick', [start, end])
                        }
                    },
                    {
                        text: '最近30天',
                        onClick(picker) {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30)
                            picker.$emit('pick', [start, end])
                        }
                    },
                    {
                        text: '本月',
                        onClick(picker) {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)))
                            picker.$emit('pick', [start, end])
                        }
                    },
                    {
                        text: '本年',
                        onClick(picker) {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)))
                            picker.$emit('pick', [start, end])
                        }
                    }
                ]
            },
            collapse: true, //收起
            select: "",
            keywords: "",
            timeVal: [],
            timeVal2: [],
            selectList: [],
            drawer: false,
            maxCols: 3,
            isShowSend: true,
            visible: false,
            user_type: '',
            tableData: {
                data: [],
                total: 0
            },
            uid: "",
            listLoading: true,
            multipleSelection: [],
            ids: '',
            wechatIds: '',
            uid: '',
            labelPosition: 'right',
            userProps: {
                children: 'children',
                label: 'name',
                value: 'name'
            },
            userFrom: {
                label_id: '',
                user_type: '',
                keyword: '',
                member_level: '',
                sex: '',
                is_promoter: '',
                country: '',
                pay_count: '',
                user_time_type: '',
                user_time: '',
                nickname: '',
                phone: '',
                province: '',
                city: '',
                is_svip: '',
                page: 1,
                limit: 20,
                group_id: '',
                fields_type: '',
                fields_value: ''
            },
            address: [],
            grid: {
                xl: 8,
                lg: 12,
                md: 12,
                sm: 24,
                xs: 24
            },
            grid2: {
                xl: 18,
                lg: 16,
                md: 12,
                sm: 24,
                xs: 24
            },
            grid3: {
                xl: 8,
                lg: 12,
                md: 12,
                sm: 24,
                xs: 24
            },
            addresData: [],
            groupList: [],
            labelLists: [],
            memberList: [],
            chkName: '',
            checkedIds: [], // 订单当前页选中的数据
            noChecked: [], // 订单全选状态下当前页不选中的数据
            checkedPage: [],
            visibleCoupon: false,
            visibleDistributor: false,
            couponForm: {
                用户标签: '',
                用户类型: '',
                性别: '',
                身份: '',
                消费情况: '',
                访问情况: '',
                访问时间: '',
                昵称: ''
            },
            allCheck: false,
            cancel_time: null,
            createDrawer: false,
            is_promoter: 0
        }
    },
    mounted() {
        this.groupLists()
        this.getTagList()
        this.getMemberList()
        this.getSelectList()
        this.getList('')
    },
    methods: {
        /**重置 */
        searchReset(){
            this.timeVal = []
            this.timeVa2 = []
            this.userFrom.user_time = ""
            this.userFrom.birthday = ""
            this.select = ""
            this.keywords = ""
            this.$refs.searchForm.resetFields()
            this.changeSearch(1)
        },
        changeSearch(num){
            this.resetSearchVal()
            switch(this.select){
                case "uid":
                this.userFrom.uid = this.keywords;
                this.userSearchs(num);
                break;
                case "nickname":
                this.userFrom.nickname = this.keywords;
                this.userSearchs(num);
                break;
                case "phone":
                this.userFrom.phone = this.keywords;
                this.userSearchs(num);
                break;
                default:
                this.userFrom.keyword = this.keywords;
                this.userSearchs(num);
                break;
            }
        },
        resetSearchVal(){
            this.userFrom.phone="";
            this.userFrom.nickname="";
            this.userFrom.uid="";
            this.userFrom.keyword="";
        },
        // 用户
        onHandle(name) {
          this.chkName = this.chkName === name ? '' : name
          this.changeType(!(this.chkName === ''))
        },
        changeType(v) {
          if (v) {
            if (!this.chkName) {
              this.chkName = 'dan'
            }
          } else {
            this.chkName = ''
            this.allCheck = false;
          }
          const index = this.checkedPage.indexOf(this.userFrom.page)
          if (this.chkName === 'dan') {
            this.checkedPage.push(this.userFrom.page)
          } else if (index > -1) {
            this.checkedPage.splice(index, 1)
          }
          
          this.syncCheckedId()
        },
        syncCheckedId() {
          const ids = this.tableData.data.map(v => {
              if (!v.cancel_time) {
                return v.uid
              }
          })
          if (this.chkName === 'duo') {
            this.checkedIds = []
            this.allCheck = true;
          } else if (this.chkName === 'dan') {
            this.allCheck = false;
            ids.forEach(id => {
              const index = this.checkedIds.indexOf(id)
              if (index === -1) {
                this.checkedIds.push(id)
              }
            })
          } else {
            ids.forEach(id => {
              const index = this.checkedIds.indexOf(id)
              if (index > -1) {
                this.checkedIds.splice(index, 1)
              }
            })
          }
        },
        // 分开选择
        changeOne(v, user) {
            if (v) {
                if (this.chkName === 'duo') {
                    const index = this.noChecked.indexOf(user.uid)
                    if (index > -1) this.noChecked.splice(index, 1)
                } else {
                    const index = this.checkedIds.indexOf(user.uid)
                    if (index === -1) this.checkedIds.push(user.uid)
                }
            } else {
                if (this.chkName === 'duo') {
                    const index = this.noChecked.indexOf(user.uid)
                    if (index === -1) this.noChecked.push(user.uid)
                } else {
                    const index = this.checkedIds.indexOf(user.uid)
                    if (index > -1) this.checkedIds.splice(index, 1)
                }
            }
        },
         // 发送优惠券
        sendCoupon(){
            if(this.checkedIds.length == 0 && this.allCheck == false){
                this.$message.warning('请选择用户')
            }else{
                this.visibleCoupon = true;
            }
        },
        // 导出
        async exportList() {
            let excelData = JSON.parse(JSON.stringify(this.userFrom)), data = []
            excelData.page = 1
            let pageCount = 1
            let lebData = {};
            for (let i = 0; i < pageCount; i++) {
                lebData = await this.downData(excelData)
                pageCount = Math.ceil(lebData.count/excelData.limit)
                if (lebData.export.length) {
                    data = data.concat(lebData.export)
                    excelData.page++
                }  
            }
            createWorkBook(lebData.header, lebData.title, data, lebData.foot,lebData.filename);
            return
        },
        /**导出用户列表 */
        downData(excelData) {
         return new Promise((resolve, reject) => {
                exportUserApi(excelData).then((res) => {
                    return resolve(res.data)
                })
            })
        },
        sendSuccess(){
            this.visibleCoupon = false;
        },
        getCoupounParmas(){
            let label_id = this.userFrom.label_id == '' ? '' : this.getLabelValue(),
            user_type = this.findKey(this.userFrom.user_type,{'':'','微信用户':'wechat','小程序用户':'routine','H5用户':'h5'}),
            sex = this.findKey(this.userFrom.sex,{'男':'1','女':'2','保密':'0','':''}),
            pay_count = this.findKey(this.userFrom.sex,{'0次':'-1','1次以上':'0','2次以上':'1','3次以上':'2','4次以上':'3','5次以上':'4','': ''}),          
            is_promoter = this.findKey(this.userFrom.is_promoter,{'推广员':'1','普通用户':'0','':''}),
            user_time_type = this.userFrom.user_time_type == 'visit' ? '最后访问' : this.userFrom.user_time_type == 'add_time' ? '首次访问' : ''
            this.couponForm =  {
                用户标签: label_id,
                用户类型: user_type,
                性别: sex,
                消费情况: pay_count,
                身份: is_promoter,
                访问情况: user_time_type,
                访问时间: this.userFrom.user_time,
                昵称: this.userFrom.nickname
            }
        },
        findKey(value,data, compare = (a, b) => a === b) {
            return Object.keys(data).find(k => compare(data[k], value))
        },
        getLabelValue(){
            let labelName = ''
            for(let i = 0; i < this.labelLists.length; i ++) {
                if(this.labelLists[i]['label_id'] === this.userFrom.label_id) {
                    labelName = this.labelLists[i]['label_name']
                    return labelName
                }
            }
        },
        // 具体日期
        onchangeTime(e) {
            this.timeVal = e
            this.userFrom.user_time = e ? this.timeVal.join('-') : ''
            this.changeSearch(1)
        },
        // 具体日期
        onchangeTime2(e) {
            this.timeVal2 = e
            this.userFrom.birthday = e ? this.timeVal2.join('-') : ''
            this.changeSearch(1)
        },
        userSearchs(num) {
          if(this.userFrom.user_time_type && (!this.userFrom.user_time)){
            this.$message.error('请选择访问时间')
          }else if(!this.userFrom.user_time_type && (this.userFrom.user_time)){
            this.$message.error('请选择访问情况')
          }else{
            this.getList(num)
          }
        },
        // 创建用户
        createUser() {
            // this.$modalForm(createUserApi()).then(() => this.getList(''))
            this.createDrawer = true
        },
        // 分组列表
        groupLists() {
            groupLstApi({
                page: 1,
                limit: 9999
            }).then(async res => {
                this.groupList = res.data.list
            }).catch(res => {
                this.$message.error(res.message)
            })
        },
        // 补充信息搜索
        getSelectList(){
            userInfoSelectApi().then(async res => {
                this.selectList = res.data
            }).catch(res => {
                this.$message.error(res.message)
            })
        },
        // 标签列表
        getTagList() {
            labelLstApi({
                page: 1,
                limit: 9999
            }).then(res => {
                this.labelLists = res.data.list
            }).catch(res => {
                this.$message.error(res.message)
            })
        },
        // 用户等级列表
        getMemberList(){
            userMemberListApi().then(res => {
                this.memberList = res.data
            }).catch(res => {
                this.$message.error(res.message)
            })
        },
        // 账户详情
        onDetails(id) {
            this.drawer = true
            this.uid = id
            this.cancel_time = this.tableData.data.find(item => item.uid === id).cancel_time
            this.$refs.userDetails.getData(id,false,true);  
        },
        closeDrawer() {
            this.drawer = false
            this.createDrawer = false;
        },
        changeDrawer(v) {
            this.drawer = v;
        },
        sendNews() {
            if (this.checkedIds.length === 0 && this.allCheck == false) return this.$message.warning('请先选择用户')
            this.visible = true
            this.wechatIds = this.getWechatUsers(this.tableData.data, this.checkedIds)
            
        },
        handleClose() {
            this.visible = false
        },
        /**获取选中的微信用户Id */
        getWechatUsers(arr1, arr2){
            let newArr = [];
            if(this.allCheck){
                for (let i = 0; i < arr1.length; i++) {
                    if(arr1[i]['wechat_user_id']){
                        newArr.push(arr1[i]['wechat_user_id']);
                    }
                } 
            }else{
                for (let i = 0; i < arr1.length; i++) {
                    for (let j = 0; j < arr2.length; j++) {
                        if(arr1[i]['uid'] === arr2[j] && arr1[i]['wechat_user_id']){
                            newArr.push(arr2[j]);
                        }
                    }
                }
            }
             return newArr;
        },
        handleSelectionChange(val) {
            this.multipleSelection = val
            const data = []
            const wechatData = []
            this.multipleSelection.map((item) => {
                data.push(item.uid)
                wechatData.push(item.wechat_user_id)
            })
            this.ids = data.join(',')
            this.wechatIds = wechatData.join(',')
        },
        // 修改分组
        setGroup(row) {
            this.$modalForm(changeGroupApi(row.uid)).then(() => this.changeSearch())
        },
        // 批量分组
        batchGroup() {
            if (this.checkedIds.length === 0) return this.$message.warning('请先选择用户')
            this.$modalForm(batchChangeGroupApi({
                ids: (this.checkedIds).join(",")
            }))
        },
        // 批量标签
        batchlabel() {
            if (this.checkedIds.length === 0) return this.$message.warning('请先选择用户')
            this.$modalForm(batchChangelabelApi({
                ids: (this.checkedIds).join(",")
            }))
        },
        // 修改标签
        setLabel(row) {
            this.$modalForm(changelabelApi(row.uid)).then(() => this.changeSearch())
        },
       // 批量设置分销员
        setDistributor(){
           if (this.checkedIds.length === 0) return this.$message.warning('请先选择用户')
            this.$modalForm(changePrommoterApi({
                ids: (this.checkedIds).join(",")
            }))
        },
        // 编辑会员等级
        setMember(row) {
            this.$modalForm(changeMemberApi(row.uid)).then(() => this.changeSearch())
        },
        // 赠送付费会员
        giveMember(row) {  
            this.$modalForm(giveMemberApi(row.uid)).then(() => this.changeSearch())
        },
        // 修改上级
        setSuperior(row){
            this.$modalForm(modifyUserSuperior(row.uid)).then(({ message }) => {
                this.changeSearch();
            });
        },
        // 修改推荐人
        setModify(row){
            this.$modalForm(modifyUserReferrer(row.uid)).then(({ message }) => {
                this.changeSearch();
            });
        },
        // 修改密码
        setPassword(row){
            this.$modalForm(modifyUserPassword(row.uid)).then(() => this.changeSearch());
        },
        // 修改余额
        setMoney(row) {
            this.$modalForm(changeNowMoneyApi(row.uid)).then(() => this.changeSearch())
        },
        // 修改积分余额
        changeIntegral(row){            
            this.$modalForm(changeNowIntegralApi(row.uid)).then(() => this.changeSearch())
        },
        // 列表
        getList(num) {
            this.listLoading = true
            this.userFrom.page = num ? num : this.userFrom.page;
            this.userFrom.user_type = this.user_type
            this.userFrom.province = this.address[0]
            this.userFrom.city = this.address[1]
            if (this.userFrom.user_type === '0') this.userFrom.user_type = ''
            userLstApi(this.userFrom).then(res => {
                this.tableData.data = res.data.list
                this.tableData.total = res.data.count
                this.listLoading = false
                this.getCoupounParmas();
                // this.checkedIds = [];

            }).catch(res => {
                this.listLoading = false
                this.$message.error(res.message)
            })
        },
        pageChange(page) {
            this.userFrom.page = page
            this.changeSearch()
        },
        handleSizeChange(val) {
            this.userFrom.limit = val
            this.changeSearch()
        },
        // 编辑
        onEdit(id) {
            // this.$modalForm(userUpdateApi(id)).then(() => this.getList(''))
            this.drawer = true
            this.uid = id
            this.$refs.userDetails.getData(id,true,false);
        },
        // 重置
        reset() {
            this.userFrom = {
                label_id: '',
                user_type: '',
                sex: '',
                is_promoter: '',
                country: '',
                pay_count: '',
                user_time_type: '',
                user_time: '',
                nickname: '',
                province: '',
                city: '',
                page: 1,
                limit: 20,
                group_id: ''
            }
            this.timeVal=[]
            this.changeSearch(1)
        },
        // 更新信息
        extendInfo(row){
            this.$modalForm(extendInfo(row.uid)).then(() => this.changeSearch());
        }
    }
}
</script>

<style lang="scss" scoped>
@import '@/styles/form.scss';
.check {
    color: #00a2d4;
}
.dia ::v-deep .el-dialog__body {
    height: 700px !important;  
}
.text-right {
    text-align: right;
}
.minwidth {
    display: inline-block;
    max-width: 200px;
    line-height: 20px;
}
.search-form{
    display: flex;
    justify-content: space-between;
    .search-form-box{
        display: flex;
        flex-wrap: wrap;
        flex: 1;
    }
    a{
        color: var(--prev-color-primary);
    }
}
.selWidth{
    width: 250px!important;
}
.search-form-sub{
    display: flex;
}
.container {
    min-width: 821px;
}
.container ::v-deep .el-form-item {
    width: 100%;
}
.container ::v-deep .el-form-item__content {
    width: 72%;
}
.vipName {
    color: #dab176
}
.el-dropdown-link {
    cursor: pointer;
    color: var(--prev-color-primary);
    font-size: 12px;
}
.el-icon-arrow-down {
    font-size: 12px;
}
.demo-table-expand {
    font-size: 0;
}
.demo-table-expand .el-form-item {
    margin-right: 0;
    margin-bottom: 0;
    width: 33.33%;
}
::v-deep .el-date-editor.el-input{
    width: 100%;
}
::v-deep [type=reset],
[type=submit],
button,
html [type=button] {
    -webkit-appearance: none !important;
}
::v-deep .el-input-group__prepend .el-input{
  width: 100px;
}
</style>
