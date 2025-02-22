<template>
  <div>
    <el-tabs type="border-card" v-model="activeName">
      <el-tab-pane label="基本信息" name="detail">
        <div class="section">
          <div class="title">基础信息</div>
          <ul class="list">
            <li class="item">
              <div>商户名称：</div>
              <div class="value">
                {{merData.mer_name}}
              </div>
            </li>
            <li class="item">
              <div>商户类型：</div>
              <div class="value">{{merData.is_trader == 1 ? "自营" : "非自营"}}</div>
            </li>
            <li class="item">
              <div>商户分类：</div>
              <div v-if="merData.merchantCategory" class="value">
                {{merData.merchantCategory.category_name}}
                <span class="info info-red">(该分类下的商户手续费是{{merData.merchantCategory.commission_rate*100}}%)</span>
              </div>
            </li>
            <li class="item">
              <div>推荐商户：</div>
              <div class="value">{{merData.is_best == 1 ? "是" : "否"}}</div>
            </li>
            <li v-if="merData.merchantType" class="item">
              <div>店铺类型：</div>
              <div class="value">{{merData.merchantType.type_name}}</div>
            </li>
            <li class="item">
              <div>商户状态：</div>
              <div class="value">{{merData.status == 1 ? "开启" : "关闭"}}</div>
            </li>
            <li class="item">
              <div>排序：</div>
              <div class="value">{{merData.sort}}</div>
            </li>
            <li class="item">
              <div>更新时间：</div>
              <div class="value">{{merData.update_time}}</div>
            </li>
            <li class="item">
              <div>备注：</div>
              <div class="value">{{merData.mark}}</div>
            </li>
          </ul>
        </div>
      </el-tab-pane>
      <el-tab-pane label="经营信息" name="business">
        <div class="section">
          <div class="title">费用信息</div>
          <ul class="list">
            <li class="item item100">
              <div>手续费单独设置：</div>
              <div class="value">
                {{merData.commission_switch ? "开启" : "关闭"}}
              </div>
            </li>  
            <li v-if="merData.commission_switch" class="item item100">
              <div>手续费：</div>
              <div class="value">
                {{ merData.commission_rate}}%
                <span class="info info-red">(注：此处如未设置手续费，系统会自动读取商户分类下对应手续费；此处已设置，则优先以此处设置为准)</span>
              </div>
            </li>
            <li class="item">
              <div>店铺保证金：</div>
              <div class="value">
                {{merData.is_margin == 0 ? '无' : merData.ot_margin}}
              </div>
            </li>
            <li v-if="merData.is_margin != 0" class="item">
              <div>保证金支付状态：</div>
              <div class="value">{{merData.is_margin == 1 ? '待缴' : merData.is_margin == 0 ? '无' : '已缴' }}
                <span v-if="(merData.is_margin==10 && merData.margin-merData.ot_margin<0)" class="info-red">(需补缴)</span>
              </div>
            </li>
            <li v-if="merData.is_margin != 0" class="item">
              <div>保证金余额：</div>
              <div class="value">{{merData.margin}}</div>
            </li> 
          </ul>
        </div>
        <div class="section">
          <div class="title">审核信息</div>
          <ul class="list">
            <li class="item">
              <div>商品审核：</div>
              <div class="value">{{merData.is_audit == 1 ? '需审核' : '免审核'}}</div>
            </li>
            <li class="item">
              <div>直播间审核：</div>
              <div class="value">{{merData.is_bro_room == 1 ? '需审核' : '免审核'}}</div>
            </li>
            <li class="item">
              <div>直播商品：</div>
              <div class="value">{{merData.is_bro_goods == 1 ? '需审核' : '免审核'}}</div>
            </li>
          </ul>
        </div>
        <div class="section">
          <div class="title">其他信息</div>
          <ul class="list">
            <li class="item item100">
              <div>搜索商户关键字：</div>
              <div class="value">{{merData.mer_keyword}}</div>
            </li>
            <li class="item item100">
              <div>剩余商品采集数：</div>
              <div class="value">{{merData.copy_product_num}}次</div>
            </li>
            <li class="item item100">
              <div>商户资质：</div>
              <div class="value">
                <el-image
                  v-for="(item, index) in merData.mer_certificate" 
                  :key="index" 
                  :src="item" 
                  @click="lookImg(item)"
                  style="width: 36px;height: 36px;margin-right: 5px;"
                  />
              </div>
            </li>
          </ul>
        </div>
      </el-tab-pane>
      <el-tab-pane label="账号信息" name="account">
        <div class="section">
          <div class="title">登录账号</div>
          <ul class="list">
            <li class="item">
              <div>商户账号：</div>
              <div class="value">
                {{merData.mer_account}}
              </div>
            </li>
            <li class="item">
              <div>登录密码：</div>
              <div class="value">{{merData.mer_password}}</div>
              
            </li>
            <li class="item">
              <div>联系人：</div>
              <div class="value">{{merData.real_name}}</div>
            </li>
            <li class="item">
              <div>联系电话：</div>
              <div class="value">{{merData.mer_phone}}</div>
            </li> 
            <!-- <li class="item">
              <div>绑定手机号：</div>
              <div class="value">{{merData.real_name}}</div>
            </li>
            <li class="item">
              <div>绑定微信用户昵称/ID：</div>
              <div class="value">{{merData.real_name}}</div>
            </li>  --> 
          </ul>
        </div>
        <div v-if="merData.sub_mchid" class="section">
          <div class="title">财务帐号</div>
          <ul class="list">
            <li class="item">
              <div>微信分账商户号：</div>
              <div class="value">{{merData.sub_mchid}}</div>
            </li>
          </ul>
        </div>
      </el-tab-pane>
      <el-tab-pane label="操作记录" name="operate">
        <div class="section">
          <el-form size="small" label-width="80px">
            <div class="acea-row">
              <el-form-item label="操作端：">
                <el-select
                  v-model="tableFromLog.type"
                  placeholder="请选择"
                  style="width: 140px; margin-right: 20px"
                  clearable
                  filterable
                  @change="onOperateLog(merId)"
                >
                  <el-option label="平台端" value="1" />
                  <el-option label="商户端" value="2" />
                </el-select>
              </el-form-item>
              <el-form-item label="操作时间：">
                <el-date-picker
                  style="width: 380px; margin-right: 20px"
                  v-model="timeVal"
                  type="datetimerange"
                  placeholder="选择日期"
                  value-format="yyyy/MM/dd HH:mm:ss"
                  clearable
                  @change="onchangeTime"
                >
                </el-date-picker>
              </el-form-item>
              <!-- <div>
                <el-button type="primary" size="small" @click="onOrderLog(orderId)">查询</el-button>
              </div> -->
            </div>
          </el-form>
          <el-table :data="tableDataLog.data" size="small">
            <el-table-column prop="order_id" label="序号" min-width="80">
              <template scope="scope">
            <span>{{ scope.$index+(tableFromLog.page - 1) * tableFromLog.limit + 1 }}</span>
          </template>
          </el-table-column>
            <el-table-column label="操作记录" min-width="200">
              <template slot-scope="scope">
                <span>{{ scope.row.category_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作端" min-width="150">
              <template slot-scope="scope">
                <div class="tab">
                  <div>{{ scope.row.type }}</div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="操作角色" min-width="150">
              <template slot-scope="scope">
                <div class="tab">
                  <div>{{ scope.row.operator_role_nickname }}</div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="操作人" min-width="150">
              <template slot-scope="scope">
                <div class="tab">
                  <div>{{ scope.row.operator_nickname }}</div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="操作时间" min-width="150">
              <template slot-scope="scope">
                <div class="tab">
                  <div class="line1">{{ scope.row.create_time }}</div>
                </div>
              </template>
            </el-table-column>
          </el-table>
          <div class="block">
            <el-pagination background :page-size="tableFromLog.limit" :current-page="tableFromLog.page" layout="total, prev, pager, next, jumper" :total="tableDataLog.total" @size-change="handleSizeChangeLog" @current-change="pageChangeLog" />
          </div>
        </div>
      </el-tab-pane>
    </el-tabs>
     <div class="images" v-show="false" v-viewer="{ movable: false }">
      <img v-for="(src,index) in merData.mer_certificate" :src="src" :key="index" />
    </div> 
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
import { merchantOperateLog } from "@/api/merchant";
export default {
  props: {
    merData: {
      type: Object,
      default: {},
    },
  },
  data() {
    return {
      loading: true,
      merId: '',
      direction: 'rtl',
      activeName: 'detail',
      timeVal: [],
      tableDataLog: {
        data: [],
        total: 0
      },
      tableFromLog: {
        user_type: '',
        date: [],
        page: 1,
        limit: 10
      },
    };
  },
  filters: {
  },
  methods: {
    lookImg(item) {
      this.imageUrl = item;
      const viewer = this.$el.querySelector('.images').$viewer;
      viewer.show();
      this.$nextTick(() => {
        let i = this.merData.mer_certificate.findIndex((e) => e === item);
        viewer.update().view(i);
      });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFromLog.date = e ? this.timeVal.join('-') : ''
      this.onOperateLog(this.merId)
    },
    onOperateLog(id){
      this.merId = id;
      merchantOperateLog(id, this.tableFromLog).then((res) => {
        this.tableDataLog.data = res.data.list
        this.tableDataLog.total = res.data.count
      });
    },
    pageChangeLog(page) {
      this.tableFromLog.page = page
      this.onOperateLog(this.merId)
    },
    handleSizeChangeLog(val) {
      this.tableFromLog.limit = val
      this.onOperateLog(this.merId)
    },
    operationType(type) {
      if (type == 0) {
        return '系统';
      } else if (type == 1) {
        return '用户';
      } else if (type == 2) {
        return '平台';
      } else if (type == 3) {
        return '商户';
      } else if (type == 4) {
        return '商家客服';
      } else {
        return '未知';
      }
    },
  },
};
</script>
<style lang="scss" scoped>
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
    margin-top: 5px;
  }
  .item {
    flex: 0 0 calc(100% / 2);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    padding-right: 20px;
    padding-left: 20px;
    &.item100{
     flex: 0 0 calc(100% / 1);
     padding-right: 20px;
     padding-left: 20px;
    }
    &:nth-child(2n + 1) {
      padding-right: 20px;
      padding-left: 20px;
    }
    // &:nth-child(2n) {
    //  padding-right: 20px;
    // }
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
.info-red{
  color: red;
  font-size: 12px;
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

</style>
