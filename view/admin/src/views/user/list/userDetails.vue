<template>
  <div>
    <el-drawer
      :with-header="false"
      :size="1000"
      :visible.sync="drawer"
      :direction="direction"
      :before-close="handleClose"  
    >
    <div v-loading="loading" v-if="psInfo">
       <div class="head">
        <div class="full">
          <img class="order_icon" :src="psInfo.avatar ? psInfo.avatar : moren" alt="" />
          <div class="text">
            <div class="title">{{psInfo.nickname}}</div>
            <div class="acea-row">
              <img v-if="psInfo.member_icon" class="svip-grade" :src="psInfo.member_icon" alt="">
              <div v-if="psInfo.is_svip > 0" class="svip">
                <img class="svip-img" src="../../../assets/images/svip.png">
              </div>
            </div>
           
          </div>
          <div v-if="isUser">
            <el-button
              type="primary"
              size="small"
              v-if="isDetail"
              @click="isEdit = true;isDetail = false;activeName='userInfo'"
              >编辑</el-button
            >
            <el-button
              type="default"
              size="small"
              v-if="isEdit&&!isDetail"
              @click="isEdit=false;isDetail=true"
              >取消</el-button
            >
            <el-button
              type="success"
              size="small"
              v-if="isEdit&&!isDetail"
              @click="onEdit"
              >完成</el-button
            >
            <el-dropdown @command="handleCommand" class="ml10">
              <el-button icon="el-icon-more" size="small"></el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="integral">设置积分</el-dropdown-item>
                <el-dropdown-item command="balance">设置余额</el-dropdown-item>
                <el-dropdown-item command="group">设置分组</el-dropdown-item> 
                <el-dropdown-item command="label">设置标签</el-dropdown-item> 
                <el-dropdown-item command="reference">修改推荐人</el-dropdown-item> 
                <el-dropdown-item command="password">修改密码</el-dropdown-item> 
                <el-dropdown-item command="svipLevel">编辑会员等级</el-dropdown-item> 
                <el-dropdown-item command="svipSet">付费会员设置</el-dropdown-item> 
              </el-dropdown-menu>
            </el-dropdown>
          </div>
        </div>
        <div>
          <ul class="list">
            <li class="item">
              <div class="title">余额</div>
              <div>
                <div class="value1">{{psInfo.now_money}}元</div>
              </div>
            </li>
            <li class="item">
              <div class="title">总计订单</div>
              <div>{{psInfo.pay_count}}笔</div>
            </li>
            <li class="item">
              <div class="title">总消费金额</div>
              <div>{{psInfo.pay_price}}元</div>
            </li>
            <li class="item">
              <div class="title">积分</div>
              <div>{{psInfo.integral}}</div>
            </li>
            <li class="item">
              <div class="title">用户成长值</div>
              <div>{{psInfo.member_value}}</div>
            </li>
          </ul>
        </div>
       </div>
        <el-tabs type="border-card" v-model="activeName" @tab-click="tabClick">
          <el-tab-pane v-if="!isEdit&&isDetail" label="用户信息" name="userInfo">
            <template v-if="activeName == 'userInfo'">
              <div class="section">
                <div class="title">基本信息</div>
                <ul class="list">
                  <li class="item">
                    <div>用户ID：</div>
                    <div class="value">{{ psInfo.uid || '-' }}</div>
                  </li>
                  <!-- <li class="item">
                    <div>真实姓名：</div>
                    <div class="value">{{ psInfo.real_name || '-' }}</div>
                  </li> -->
                  <li class="item">
                    <div>手机号码：</div>
                    <div class="value">{{ psInfo.phone || '-' }}</div>
                  </li>
                  <!-- <li class="item">
                    <div>生日：</div>
                    <div class="value">{{ psInfo.birthday || '-' }}</div>
                  </li>
                  <li class="item">
                    <div>性别：</div>
                    <div class="value">{{ psInfo.sex == 1 ? '男' : psInfo.sex == 2 ? '女' : '保密' }}</div>
                  </li> -->
                  <li class="item">
                  <div>用户账号：</div>
                  <div class="value">{{ psInfo.account || '-' }}</div>
                </li>
                </ul>
              </div>
              <div class="section">
                <div class="title">用户概况</div>
                <ul class="list">
                  <li class="item">
                    <div>推广员：</div>
                    <div class="value">{{psInfo.is_promoter == 1 ? '是' : '否'}}</div>
                  </li>
                  <li class="item">
                    <div>用户状态：</div>
                    <div class="value">{{psInfo.status == 1 ? '开启' : '关闭'}}</div>
                  </li>
                  <li class="item">
                    <div>用户等级：</div>
                    <div class="value">{{psInfo.member_level}}</div>
                  </li>
                  <li class="item">
                    <div>用户标签：</div>
                    <div class="value">
                      <span v-for="(item,index) in psInfo.userLabel" :key="index">
                        {{item.label_name}}
                      </span>
                    </div>
                  </li>
                  <li class="item">
                    <div>用户分组：</div>
                    <div class="value">{{psInfo.group && psInfo.group.group_name || '无'}}</div>
                  </li>
                  <li class="item">
                    <div>推广人：</div>
                    <div class="value">{{psInfo.spread && psInfo.spread.nickname || '无'}}</div>
                  </li>
                  <li class="item">
                    <div>注册时间：</div>
                    <div class="value">{{psInfo.create_time}}</div>
                  </li>
                  <li class="item">
                    <div>登录时间：</div>
                    <div class="value">{{psInfo.last_time}}</div>
                  </li>
                  <li v-if="psInfo.is_svip == 1 || psInfo.is_svip == 2" class="item">
                    <div>会员到期时间：</div>
                    <div class="value">{{psInfo.svip_endtime}}</div>
                  </li> 
                </ul>
              </div>
              <div class="section">
                <div class="title">用户备注</div>
                <ul class="list">
                  <li class="item">
                    <div>备注：</div>
                    <div class="value">{{ psInfo.mark ? psInfo.mark : '-' }}</div>
                  </li>
                </ul>
              </div>
              <div class="section">
                <div class="title">补充信息</div>
                <ul class="list">
                  <li class="item" v-for="(item,index) in psInfo.extend_info" :key="index">
                    <div>{{item.title}}：</div>
                    <div v-if="item.type=='radio'" class="value">
                      <span v-for="(itm,idx) in item.content" :key="idx">
                        <span v-if="item.value == idx.toString()">{{itm}}</span>
                      </span>
                    </div>
                    <div v-else class="value">{{ item.value }}</div>
                  </li>
                </ul>
              </div>
            </template>
            
          </el-tab-pane>
          <el-tab-pane v-else-if="isEdit&&!isDetail" label="用户信息" name="userInfo">
            <el-form
              ref="userDataField"
              size="small"
              :rules="ruleValidate"
              :model="psInfo"
              label-width="100px"
              @submit.native.prevent
              v-if="activeName == 'userInfo'"
              >
              <div class="section">
                <div class="title">基本信息</div>
                <el-row :gutter="24" class="mt20">
                  <el-col :span="12">
                    <el-form-item label="用户ID：" prop="uid">
                      <el-input
                        type="text"
                        disabled
                        size="small"
                        v-model="psInfo.uid"
                        class="selWidth"
                      />
                    </el-form-item>
                  </el-col>
                  <!-- <el-col :span="12">
                    <el-form-item label="真实姓名：">
                      <el-input
                        type="text"
                        size="small"
                        v-model="psInfo.real_name"
                        class="selWidth"
                      />
                    </el-form-item>
                  </el-col> -->
                  <el-col :span="12">
                    <el-form-item label="手机号码：" prop="phone">
                      <el-input
                        size="small"
                        v-model="psInfo.phone"
                        class="selWidth"
                      />
                    </el-form-item>
                  </el-col>
                  <!-- <el-col :span="12">
                    <el-form-item label="生日：">
                      <el-date-picker
                        size="small"
                        class="selWidth"
                        value-format="yyyy-MM-dd"
                        format="yyyy-MM-dd"
                        v-model="psInfo.birthday"
                        type="date"
                        style="width:280px;"
                        placeholder="选择日期">
                      </el-date-picker>
                    </el-form-item>
                  </el-col> -->
                  <el-col :span="12">
                    <el-form-item label="身份证号：" prop="card_id">
                      <el-input
                        size="small"
                        v-model="psInfo.card_id"
                        class="selWidth"
                      />
                    </el-form-item>
                  </el-col>
                  <!-- <el-col :span="12">
                    <el-form-item label="用户地址：">
                      <el-input
                        size="small"
                        v-model="psInfo.addres"
                        class="selWidth"
                      />
                    </el-form-item>
                  </el-col> -->
                </el-row>
              </div>
              <div class="section">
                <div class="title">用户概况</div>
                <el-row :gutter="24" class="mt20">
                  <el-col :span="12">
                   <el-form-item label="用户等级：">
                      <el-select
                        size="small"
                        v-model="psInfo.member_level"
                        placeholder="请选择"
                        class="selWidth"
                      >
                        <el-option
                          v-for="item in memberList"
                          :key="item.value"
                          :label="item.label"
                          :value="item.value"
                        />
                      </el-select>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                   <el-form-item label="用户分组：">
                      <el-select
                        size="small"
                        v-model="psInfo.group_id"
                        placeholder="请选择"
                        class="selWidth"
                      >
                        <el-option value="">全部</el-option>
                        <el-option v-for="(item, index) in groupList" :key="index" :value="item.group_id" :label="item.group_name" />
                      </el-select>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                   <el-form-item label="用户标签：">
                      <el-select
                        size="small"
                        v-model="psInfo.label_id"
                        multiple
                        placeholder="请选择"
                        class="selWidth"
                      >
                        <el-option value="">全部</el-option>
                        <el-option v-for="(item, index) in labelLists" :key="index" :value="item.label_id" :label="item.label_name" />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-row :gutter="24">
                  <el-form-item label="推广员：" prop="is_promoter">
                    <el-radio-group
                      v-model="psInfo.is_promoter"
                    >
                      <el-radio :label="1" class="radio">开启</el-radio>
                      <el-radio :label="0">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </el-row>
                <el-row :gutter="24">
                  <el-form-item label="用户状态：" prop="status">
                    <el-radio-group
                      v-model="psInfo.status"
                    >
                      <el-radio :label="1" class="radio">开启</el-radio>
                      <el-radio :label="0">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </el-row>
              </div>
              <div class="section">
                <div class="title">用户备注</div>
                <el-row :gutter="24" class="mt20">
                  <el-form-item label="备注：">
                    <el-input
                      type="textarea"
                      size="small"
                      v-model="psInfo.mark"
                      placeholder="请填写备注"
                      class="selWidth"
                    />
                </el-form-item>
                </el-row>
              </div>
               <div class="section">
                <div class="title">补充信息</div>
                <el-row :gutter="24" class="mt20">
                  <el-col v-for="(item,index) in psInfo.extend_info" :key="index" :span="12">
                    <el-form-item v-if="item.type=='input'" :label="item.title+'：'" :required="item.is_require==1">
                      <el-input
                        type="text"
                        size="small"
                        v-model="item.value"
                        :placeholder="item.msg"
                        class="selWidth"
                      /> 
                    </el-form-item>
                    <el-form-item v-else-if="item.type=='int'" :label="item.title+'：'" :required="item.is_require==1">
                      <el-input
                        type="number"
                        size="small"
                        v-model="item.value"
                        :placeholder="item.msg"
                        class="selWidth"
                      /> 
                    </el-form-item>
                    <el-form-item v-else-if="item.type=='email'" :label="item.title+'：'" :required="item.is_require==1">
                      <el-input
                        size="small"
                        v-model="item.value"
                        :placeholder="item.msg"
                        class="selWidth"
                      /> 
                    </el-form-item>
                    <el-form-item v-else-if="item.type=='date'" :label="item.title+'：'" :required="item.is_require==1">
                       <el-date-picker
                        v-model="item.value"
                        value-format="yyyy-MM-dd"
                        format="yyyy-MM-dd"
                        size="small"
                        type="date"
                        style="width:280px;"
                        placement="bottom-end"
                        :placeholder="item.msg"
                      />
                    </el-form-item>
                    <el-form-item v-else-if="item.type=='id_card'" :label="item.title+'：'" :required="item.is_require==1">
                      <el-input
                        size="small"
                        v-model="item.value"
                        :placeholder="item.msg"
                        class="selWidth"
                      /> 
                    </el-form-item>
                    <el-form-item v-else-if="item.type=='phone'" :label="item.title+'：'" :required="item.is_require==1">
                      <el-input
                        type="number"
                        size="small"
                        v-model="item.value"
                        :placeholder="item.msg"
                        class="selWidth"
                      /> 
                    </el-form-item>
                    <el-form-item v-else-if="item.type=='radio'" :label="item.title+'：'" :required="item.is_require==1">
                       <el-radio-group v-model="item.value">
                        <el-radio :label="key" v-for="(radio,key) in item.content" :key="key">
                          <span>{{radio}}</span>
                        </el-radio>
                      </el-radio-group>
                    </el-form-item>
                    <el-form-item v-else-if="item.type=='address'" :label="item.title+'：'" :required="item.is_require==1">
                      <el-input
                        size="small"
                        v-model="item.value"
                        :placeholder="item.msg"
                        class="selWidth"
                      /> 
                    </el-form-item>
                  </el-col>
                </el-row>
              </div>
            </el-form> 
           
          </el-tab-pane>
          <el-tab-pane label="消费记录" name="record">
            <template v-if="activeName=='record'">
              <el-table :data="tableData.data" size="small">
                <el-table-column
                  v-for="(item, index) in columns"
                  :key="index"
                  :prop="item.key"
                  :label="item.title"
                  width="item.minWidth"
                />
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </template>
          </el-tab-pane>
          <el-tab-pane label="积分明细" name="detailed">
            <template v-if="activeName == 'detailed'">
              <el-table :data="tableData.data" size="small">
                <el-table-column
                  v-for="(item, index) in columns"
                  :key="index"
                  :prop="item.key"
                  :label="item.title"
                  width="item.minWidth"
                >
                  <template slot-scope="scope">
                    <div v-if="item.key == 'number'">
                      <span v-if="scope.row.pm == 1" style="color:rgb(255, 59, 48);">+{{scope.row.number}}</span>
                      <span v-else-if="scope.row.pm == 0" style="color:rgb(130, 228, 147);">-{{scope.row.number}}</span>
                    </div>
                    <div v-else-if="item.key == 'status'">
                      <span v-if="scope.row.status == 1">已解冻</span>
                      <span v-else-if="scope.row.status == 0">冻结中</span>
                    </div>
                    <div v-else>
                      <span>{{scope.row[item.key]}}</span>
                    </div>
                  </template>
                </el-table-column>
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
             </div>
            </template>
          </el-tab-pane>
          <el-tab-pane label="签到记录" name="signRecord">
            <template v-if="activeName == 'signRecord'">
              <el-table :data="tableData.data" size="small">
                <el-table-column
                  v-for="(item, index) in columns"
                  :key="index"
                  :prop="item.key"
                  :label="item.title"
                  width="item.minWidth"
                />
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </template>
          </el-tab-pane>
          <el-tab-pane label="持有优惠券" name="coupon">
            <template v-if="activeName == 'coupon'">
              <el-table :data="tableData.data" size="small">
                <el-table-column
                  v-for="(item, index) in columns"
                  :key="index"
                  :prop="item.key"
                  :label="item.title"
                  width="item.minWidth"
                />
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </template>
          </el-tab-pane>
          <el-tab-pane label="余额变更" name="balance" size="small">
            <template v-if="activeName == 'balance'">
              <el-table :data="tableData.data" size="small">
                <el-table-column
                  v-for="(item, index) in columns"
                  :key="index"
                  :prop="item.key"
                  :label="item.title"
                  width="item.minWidth"
                />
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </template>
          </el-tab-pane>
          <el-tab-pane label="用户成长值" name="growth" size="small">
            <template v-if="activeName == 'growth'">
              <el-table :data="tableData.data" size="small">
                <el-table-column
                  v-for="(item, index) in columns"
                  :key="index"
                  :prop="item.key"
                  :label="item.title"
                  width="item.minWidth"
                >
                  <template slot-scope="scope">
                    <div v-if="item.key == 'number'">
                      <span v-if="scope.row['pm']>0" style="color: #ff0000;">+{{scope.row[item.key]}}</span>
                      <span v-else style="color: #00C050;">-{{scope.row[item.key]}}</span>
                    </div>
                    <span v-else>{{scope.row[item.key]}}</span>
                  </template>
                </el-table-column>
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </template> 
          </el-tab-pane>
          <el-tab-pane label="浏览足迹" name="footsteps">
            <template v-if="activeName == 'footsteps'">
              <el-table :data="tableData.data" size="small">
                <template>
                  <el-table-column
                    v-for="(item, index) in columns"
                    :key="index"
                    :prop="item.key"
                    :label="item.title"
                    width="item.minWidth"
                  >
                  <template slot-scope="scope">
                    <div v-if="item.key == 'image'" class="acea-row" style="align-items: center;">
                        <div class="demo-image__preview">
                          <el-image v-if="scope.row.spu" :src="scope.row.spu&&scope.row.spu.image" :preview-src-list="[scope.row.spu.image]" />
                        </div>
                        <span class="priceBox" style="margin-left: 10px;width: 220px;">{{scope.row.spu&&scope.row.spu.store_name}}</span>
                      </div>
                    <span v-else-if="item.key == 'create_time'">{{scope.row[item.key]}}</span>
                    <span v-else>{{scope.row.spu&&scope.row.spu[item.key]}}</span>
                  </template>
                </el-table-column>
                </template>
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </template>
          </el-tab-pane>
          <el-tab-pane label="推荐人变更记录" name="recommend">
            <template v-if="activeName == 'recommend'">
              <el-table :data="tableData.data" size="small">
                <el-table-column prop="spread.uid" label="上级推荐人ID" min-width="100">
                  <template slot-scope="scope">
                    <span>{{(scope.row.spread && scope.row.spread.uid) || '-'}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="spread.nickname" label="上级推荐人昵称" min-width="100">
                  <template slot-scope="scope">
                    <span>{{(scope.row.spread && scope.row.spread.nickname) || '用户已注销'}}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="create_time" label="绑定时间" min-width="100" />
              </el-table>
              <div class="block">
                <el-pagination
                  :page-size="tableFrom.limit"
                  :current-page="tableFrom.page"
                  layout="prev, pager, next"
                  :total="tableData.total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </template>
          </el-tab-pane>
        </el-tabs>
       </div>
    </el-drawer>
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
import { userOrderApi, userDetailApi, userCouponApi, userBillApi, 
  modifyUserRefLog, userPointsApi, userSignLogApi, userHistoryApi, memberGrowthLog, userEditApi } from '@/api/user'
import { verifyEmail } from '@/utils/toolsValidate';
export default {
  name: 'UserDetails',
  props: {
    drawer: {
      type: Boolean,
      default: false,
    },
    isUser: {
      type: Boolean,
      default: false,
    },
    labelLists: {
      type: Array,
      default: ()=>[],
    },
    groupList: {
      type: Array,
      default: ()=>[],
    },
    memberList: {
      type: Array,
      default: ()=>[],
    }
  },
  data() {
    const validatePhone = (rule, value, callback) => {
      if(!value){
        return
      }else if (!/^1[3456789]\d{9}$/.test(value)) {
        callback(new Error('格式不正确!'))
      } else {
        callback()
      }
    }
    const validateCard = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请输入身份证号'))
      } else if (!/^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[Xx\d]$/.test(value)) {
        callback(new Error('格式不正确!'))
      } else {
        callback()
      }
    }
    return {
      direction: 'rtl',
      moren: require("@/assets/images/f.png"),
      columns: [],
      activeName: 'userInfo',
      Visible: false,
      loading: true,
      uid: "",
      list: [
        { val: '0', label: '消费记录' },
        { val: '3', label: '持有优惠券' },
        { val: '4', label: '余额变动' },
        { val: '2', label: '推荐人修改记录' }
      ],
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 6
      },
      psInfo: null,
      type: '0',
      ruleValidate: {
        phone: [{ validator: validatePhone, trigger: 'blur' }],
        card_id: [{validator: validateCard, trigger: 'blur'}],
        email: [{validator: verifyEmail, trigger: 'blur'}],
        password: [{ required: true, trigger: 'blur' }],
      },
      isEdit: false,
      isDetail: true,
      timeVal: []
    }
  },
  mounted() {
    
  },
  beforeUpdate(){
    this.$nextTick(() =>{
      // this.$refs['tabe'].doLayout();
    })
  },
  methods: {
    changeType(key) {
      this.type = key
      this.tableFrom.page = 1
      this.getInfo(key)
    },
    getData(id, edit, detail){
      this.activeName = 'userInfo'
      if (id) {
        this.uid = id
        this.getHeader()
        // this.getInfo('0')
      }
      this.isEdit = edit
      this.isDetail = detail
    },
    getInfo(key) {
      this.loading = true
      switch (key) {
        case 'record':
          userOrderApi(this.uid, this.tableFrom).then(res => {
            this.tableData.data = res.data.list
            this.tableData.total = res.data.count
            this.columns = [
              {
                title: '订单编号',
                key: 'order_sn',
                minWidth: 250
              },
              {
                title: '收货人',
                key: 'real_name',
                minWidth: 90
              },
              {
                title: '商品数量',
                key: 'total_num',
                minWidth: 80
              },
              {
                title: '商品总价',
                key: 'total_price',
                minWidth: 90
              },
              {
                title: '实付金额',
                key: 'pay_price',
                minWidth: 90
              },
              {
                title: '交易完成时间',
                key: 'pay_time',
                minWidth: 160
              }
            ]
            this.loading = false
          }).catch(() => {
            this.loading = false
          })
        break
      case 'recommend':
          modifyUserRefLog(this.uid, this.tableFrom).then(res => {
            this.tableData.data = res.data.list
            this.tableData.total = res.data.count
            this.columns = [
              {
                title: '上级推荐人ID',
                key: 'spread.uid',
                minWidth: 120
              },
              {
                title: '上级推荐人昵称',
                key: 'spread.nickname',
                minWidth: 120
              },
              {
                title: '绑定时间',
                key: 'create_time',
                minWidth: 120
              }
            ]
            this.loading = false
          }).catch(() => {
            this.loading = false
          })
          break
      case 'signRecord':
        userSignLogApi(this.uid, this.tableFrom).then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.columns = [
            {
              title: '获得积分',
              key: 'number',
              minWidth: 120
            },
            {
              title: '签到时间',
              key: 'create_time',
              minWidth: 120
            },
            {
              title: '备注',
              key: 'title',
              minWidth: 120
            },
            
          ]
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
        break
      case 'coupon':
        userCouponApi(this.uid, this.tableFrom).then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.columns = [
            {
              title: '优惠券名称',
              key: 'coupon_title',
              minWidth: 120
            },
            {
              title: '面值',
              key: 'coupon_price',
              minWidth: 120
            },
            {
              title: '最低消费额',
              key: 'use_min_price',
              minWidth: 120
            },
            {
              title: '兑换时间',
              key: 'use_time',
              minWidth: 120
            }
          ]
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
        break
      case 'balance':
        userBillApi(this.uid, this.tableFrom).then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.columns = [
            {
              title: '变动金额',
              key: 'number',
              minWidth: 90
            },
            {
              title: '变动后',
              key: 'balance',
              minWidth: 90
            },
            {
              title: '类型',
              key: 'title',
              minWidth: 100
            },
            {
              title: '创建时间',
              key: 'create_time',
              minWidth: 150
            },
            {
              title: '备注',
              key: 'mark',
              minWidth: 200
            }
          ]
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
        break
      case 'growth':
        this.tableFrom.uid = this.uid
        memberGrowthLog(this.tableFrom).then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.columns = [
            {
              title: '成长值来源',
              key: 'title',
              minWidth: 90
            },
            {
              title: '成长值变化',
              key: 'number',
              minWidth: 90
            },
            {
              title: '变化后成长值',
              key: 'balance',
              minWidth: 100
            },
            {
              title: '日期',
              key: 'create_time',
              minWidth: 150
            },
            {
              title: '备注',
              key: 'mark',
              minWidth: 200
            }
          ]
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
        break
      case 'detailed':
        userPointsApi(this.uid, this.tableFrom).then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.columns = [
            {
              title: '来源/用途',
              key: 'title',
              minWidth: 90
            },
            {
              title: '积分变化',
              key: 'number',
              minWidth: 90
            },
            {
              title: '状态',
              key: 'status',
              minWidth: 90
            },
            {
              title: '当前有效积分',
              key: 'balance',
              minWidth: 100
            },
            {
              title: '日期',
              key: 'create_time',
              minWidth: 150
            },
            {
              title: '备注',
              key: 'mark',
              minWidth: 200
            }
          ]
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
        break 
      case 'footsteps':
        userHistoryApi(this.uid, this.tableFrom).then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.columns = [
            {
              title: '商品信息',
              key: 'image',
              minWidth: 200
            },
            {
              title: '价格',
              key: 'price',
              minWidth: 50
            },
            {
              title: '浏览时间',
              key: 'create_time',
              minWidth: 50
            }
          ]
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
        break
      default:
        userBillApi(this.uid, this.tableFrom).then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.columns = [
            {
              title: '变动金额',
              key: 'number',
              minWidth: 90
            },
            {
              title: '变动后',
              key: 'balance',
              minWidth: 90
            },
            {
              title: '类型',
              key: 'title',
              minWidth: 100
            },
            {
              title: '创建时间',
              key: 'create_time',
              minWidth: 150
            },
            {
              title: '备注',
              key: 'mark',
              minWidth: 200
            }
          ]
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
      }
    },
    handleClose() {
      this.$emit('closeDrawer');
    },
    tabClick(tab) {
      this.tableFrom.page = 1;
      this.getInfo(tab.name)
    },
    onEdit(){
      let that = this;
      for (var i = 0; i < that.psInfo.extend_info.length; i++) {
        let data = that.psInfo.extend_info[i]
        if (data.is_require || data.value) {
          if (data.type === 'date' || data.type === 'address' || data.type === 'int') {
            if (!data.value) {
              return this.$message.warning(data.msg)
            }
          }
          if(data.type === 'input'){
            if (!data.value.trim()) {
              return this.$message.warning(data.msg)
            }
          }
          // if (data.type === 'int') {
          //   if (data.value <= 0) {
          //     this.$message.warning(data.msg)
          //   }
          // }
          if (data.type === 'email') {
            if (data.is_require) {
              if (!data.value) {
                return this.$message.warning(data.msg)
              }
            }
            if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(data.value)) {
              return this.$message.warning('请填写正确的邮箱')
            }
          }
          if (data.type === 'phone') {
            if (data.is_require) {
              if (!data.value) {
                return this.$message.warning(data.msg)
              }
            }
            if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(data.value)) {
              return this.$message.warning(data.msg)
            }
          }
          if (data.type === 'id_card') {
            if (data.is_require) {
              if (!data.value) {
                return this.$message.warning(data.msg)
              }
            }
            if (!/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i.test(data.value)) {
              return this.$message.warning('请填写正确的身份证号码')
            }
          }
        }
      }
      that.loading = true;
      userEditApi(this.uid,this.psInfo).then(res => {
        this.isEdit = false;
        this.isDetail = true;
        this.loading = false;
         this.$message.success(res.message)
      }).catch((res) => {
        this.loading = false;
        this.$message.error(res.message)
      })
    },
   //下拉
    handleCommand(command) {
       if (command == 'integral') {
        this.$emit('changeIntegral',{uid: this.uid});
      }else if (command == 'balance'){
        this.$emit('setMoney',{uid: this.uid});
      } else if (command == 'group') {
        this.$emit('setGroup',{uid: this.uid});
      } else if (command == 'label') {
        this.$emit('setLabel',{uid: this.uid});
      } else if (command == 'reference') {
        this.$emit('setModify',{uid: this.uid});
      } else if (command == 'password') {
        this.$emit('setPassword',{uid: this.uid});
      } else if (command == 'svipLevel') {
        this.$emit('setMember',{uid: this.uid});
      } else if (command == 'svipSet') {
        this.$emit('giveMember',{uid: this.uid});
      } 
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getInfo(this.activeName)
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getInfo(this.activeName)
    },
    getHeader() {
      this.loading = true
      userDetailApi(this.uid).then(res => {
        this.loading = false
        this.psInfo = res.data
      }).catch(() => {
        this.loading = false
      })
    }
  }
}
</script>

<style scoped lang="scss">
  .avatar{
    width: 60px;
    height: 60px;
    margin-left: 18px;
    img{
      width: 100%;
      height: 100%;
    }
  }
  .dashboard-workplace {
    &-header {
      &-avatar {
        margin-right: 16px;
        font-weight: 600;
      }
      &-tip {
        width: 82%;
        display: inline-block;
        vertical-align: middle;
        margin-top: -12px;
        &-title {
          font-size: 13px;
          color: #000000;
          margin-bottom: 12px;
        }
        &-desc {
          &-sp {
            width: 32%;
            color: #17233D;
            font-size: 13px;
            display: inline-block;
          }
        }
      }
      &-extra {
        .ivu-col {
          p {
            text-align: right;
          }
          p:first-child {
            span:first-child {
              margin-right: 4px;
            }
            span:last-child {
              color: #808695;
            }
          }
          p:last-child {
            font-size: 22px;
          }
        }
      }
    }
  }
  .head {
    padding: 20px 35px;
    .full {
      display: flex;
      align-items: center;
      .order_icon {
        width: 60px;
        height: 60px;
        border-radius: 100%;
      }
      .iconfont {
        color: var(--prev-color-primary);
        &.sale-after {
          color: #90add5;
        }
      }
      .text {
        align-self: center;
        flex: 1;
        min-width: 0;
        padding-left: 12px;
        font-size: 13px;
        color: #606266;
        .title {
          margin-bottom: 10px;
          font-weight: 500;
          font-size: 16px;
          line-height: 16px;
          color: rgba(0, 0, 0, 0.85);
        }
        .order-num {
          padding-top: 10px;
          white-space: nowrap;
        }
      }
    .svip-grade{
      width: 15px;
      height: 15px;
      margin-right: 7px;
    }
    .svip{
      
      .svip-img{
        width: 40px;
        height: 15px;
      }
    }
  }
  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;
    .item {
      flex: none;
      width: 200px;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);
      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }
      .value1 {
        color: #f56022;
      }

      .value2 {
        color: #1bbe6b;
      }

      .value3 {
        color: #437FFD;
      }

      .value4 {
        color: #6a7b9d;
      }

      .value5 {
        color: #f5222d;
      }
    }
  }
}
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.section {
  padding: 20px 0 8px;
  border-bottom: 1px dashed #eeeeee;
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    line-height: 18px;
    align-items: center;
    &:nth-child(3n + 1) {
      padding-right: 20px;
    }

    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }

    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
  }
  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
::v-deep .el-drawer__body {
  overflow: auto;
}
.gary {
  color: #aaa;
}
.logistics{
  align-items: center;
  padding: 10px 0px;
  .logistics_img{
    width: 45px;
    height: 45px;
    margin-right: 12px;
    img{
      width: 100%;
      height: 100%;
    }
  }
  .logistics_cent{
    span{
      display: block;
      font-size: 12px;
    }
  }
}
.tabBox_tit {
  width: 53%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
.el-date-editor.el-input{
  width: 300px;
}
</style>
