<template>
  <div class="divBox">
    <div class="selCard mb14">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="85px" inline>
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            clearable
            @change="onchangeTime"
          />
        </el-form-item> 
        <el-form-item label="商户名称：" prop="mer_id">
          <el-select
            v-model="tableFrom.mer_id"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option
              v-for="item in merSelect"
              :key="item.mer_id"
              :label="item.mer_name"
              :value="item.mer_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>      
      </el-form>
    </div>
    <cards-data v-if="cardLists.length>0" :card-lists="cardLists" />
    <el-card class="box-card">
      <div class="deliveryCharge mb20">
        <span style="display:inline-block;margin-right:10px;">{{delivery_type == 2 ? 'UU充值余额' : '达达充值余额'}}：{{balance}}元</span>
        <a
          :href="delivery_type == 2 ? 'https://shangjia.uupt.com/index.html#/home/login' : 'https://newopen.imdada.cn/#/?_k=x9d8mi'">
          <el-button type="primary" size="small" class="mr10">去充值</el-button>
        </a>
      </div>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column label="序号" min-width="50">
          <template scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="merchant.mer_name" label="商户名称" min-width="100" />
        <el-table-column prop="pay_price" label="充值金额" min-width="100" />
        <el-table-column prop="create_time" label="充值时间" min-width="100" /> 
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
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
import { rechargeLst, rechargeCardApi, rechargeBalancei } from '@/api/order'
import { merSelectApi } from '@/api/product'
import { configApi } from '@/api/system'
import timeOptions from '@/utils/timeOptions';
import cardsData from "@/components/cards/index";
export default {
  components: { cardsData },
  data() {
    return {
      pickerOptions: timeOptions,
      tableData: {
        data: [],
        total: 0
      },
      delivery_balance: '',
      listLoading: true,
      loading: true,
      tableFrom: {
        keyword: '',
        date: '',
        mer_id: "",
        page: 1,
        limit: 20
      },
      merSelect: [],
      timeVal: [],
      cardLists: [],
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '昨天', val: 'yesterday' },
          { text: '最近7天', val: 'lately7' },
          { text: '最近30天', val: 'lately30' },
          { text: '本月', val: 'month' },
          { text: '本年', val: 'year' }
        ]
      }, 
      delivery_type: 1,
      balance: 0,
    }
  },
  mounted() {
    this.getList(1)
    this.getMerSelect()
    this.getCardList()
    this.getConfigData()
    this.getBalance()
  },
  methods: {
     /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
      this.getList(1)
    },
     // 商户列表；
    getMerSelect() {
      merSelectApi()
        .then((res) => {
          this.merSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    getCardList(){
      rechargeCardApi(this.tableFrom)
        .then((res) => {
          this.cardLists = res.data;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 获取配置数据
    getConfigData(){
      configApi()
      .then((res) => {
          this.delivery_type = res.data.delivery_type;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 获取余额数据
    getBalance() {
      rechargeBalancei()
      .then((res) => {
          this.balance = res.data.deliverBalance;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      rechargeLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.delivery_balance = res.data.delivery_balance
          this.listLoading = false
        })
        .catch(res => {
          this.$message.error(res.message)
          this.listLoading = false
        })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    }
  }
}
</script>

<style lang="scss" scoped>
  .deliveryCharge{
    span{
      color: var(--prev-color-primary);
    }
  }
  .erweima{
    margin-top: 20px;
     img{
      width: 160px;
      height: 160px;
    
    }
  }
</style>
