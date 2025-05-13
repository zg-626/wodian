<template>
  <div class="divBox">
    <div class="selCard mb14">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="85px">
        <el-form-item label="时间选择：">
          <el-date-picker v-model="timeVal" value-format="yyyy/MM/dd" format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 280px;" :picker-options="pickerOptions" @change="onchangeTime" />
        </el-form-item>
        <el-form-item label="是否支付：" prop="paid">
          <el-select
            v-model="tableFrom.paid"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
        >
            <el-option label="全部" value=""/>
            <el-option label="已支付" value="1"/>
            <el-option label="未支付" value="0"/>
        </el-select>
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input v-model="tableFrom.keyword" @keyup.enter.native="getList(1)" placeholder="微信昵称/姓名/订单号" clearable class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <cards-data v-if="cardLists.length>0" :card-lists="cardLists" :more="true" />
    <el-card>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        class="table"
        highlight-current-row
      >
        <el-table-column
          prop="recharge_id"
          label="ID"
          width="60"
        />
        <el-table-column
          label="头像"
          min-width="80"
        >
          <template slot-scope="scope">
            <div v-if="scope.row.avatar" class="demo-image__preview">
              <el-image
                :src="scope.row.avatar"
                :preview-src-list="[scope.row.avatar]"
              />
            </div>
            <img v-else src="../../../assets/images/f.png" alt="" style="width: 36px; height: 36px; vertical-align: top;">
          </template>
        </el-table-column>
        <el-table-column
          prop="nickname"
          label="用户昵称"
          min-width="130"
        />
        <el-table-column
          prop="order_id"
          label="订单号"
          min-width="180"
        />
        <el-table-column
          sortable
          :sort-method="(a,b)=>{return a.price - b.price}"
          label="支付金额"
          min-width="120"
          prop="price"
        />
        <el-table-column
          sortable
          label="赠送金额"
          :sort-method="(a,b)=>{return a.give_price - b.give_price}"
          min-width="120"
          prop="give_price"
        />
        <el-table-column
          label="是否支付"
          min-width="80"
        >
          <template slot-scope="scope">
            <span class="spBlock">{{ scope.row.paid | payStatusFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="充值类型"
          min-width="80"
          prop="recharge_type"
        >
          <template slot-scope="scope">
            <span class="spBlock">{{ scope.row.recharge_type | rechargeTypeFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="支付时间"
          min-width="150"
        >
          <template slot-scope="scope">
            <span class="spBlock">{{ scope.row.pay_time || '无' }}</span>
          </template>
        </el-table-column>
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
import { rechargeListApi, rechargeTotalApi } from '@/api/accounts'
import cardsData from '@/components/cards/index'
import { fromList } from '@/libs/constants.js'
import timeOptions from '@/utils/timeOptions';
export default {
  name: 'AccountsBill',
  components: { cardsData },
  data() {
    return {
      pickerOptions: timeOptions,
      cardLists: [],
      timeVal: [],
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      tableFrom: {
        paid: '',
        date: '',
        keyword: '',
        page: 1,
        limit: 20
      },
      fromList: fromList
    }
  },
  mounted() {
    this.getList()
    this.getStatistics()
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
      this.tableFrom.page = 1;
      this.getList(1)
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page;
      rechargeListApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch((res) => {
        this.$message.error(res.message)
        this.listLoading = false
      })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList()
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList()
    },
    // 统计
    getStatistics() {
      this.StatisticsLoading = true
      rechargeTotalApi().then(res => {
        const stat = res.data
        this.cardLists = [
          { name: '充值总金额', count: stat.totalPayPrice, className: 'el-icon-s-goods' },
          { name: '充值退款金额', count: stat.totalRefundPrice, className: 'el-icon-s-order' },
          { name: '小程序充值金额', count: stat.totalRoutinePrice, className: 'el-icon-s-cooperation' },
          { name: '公众号充值金额', count: stat.totalWxPrice, className: 'el-icon-s-finance' }
        ]
        this.StatisticsLoading = false
      }).catch((res) => {
        this.$message.error(res.message)
        this.StatisticsLoading = false
      })
    }
  }
}
</script>

<style scoped>
</style>
