<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFromIssue" ref="searchForm" :inline="true" size="small" label-width="85px">
        <el-form-item label="使用状态：" prop="status">
          <el-select v-model="tableFromIssue.status" placeholder="请选择状态" class="selWidth" @change="getIssueList(1)">
            <el-option label="全部" value="" />
            <el-option label="已使用" value="1" />
            <el-option label="未使用" value="0" />
            <el-option label="已过期" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="商户名称：" prop="mer_id">
          <el-select v-model="tableFromIssue.mer_id" clearable filterable placeholder="请选择" class="selWidth" @change="getIssueList(1)">
            <el-option v-for="item in merSelect" :key="item.mer_id" :label="item.mer_name" :value="item.mer_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="领取人：" prop="username">
          <el-input v-model="tableFromIssue.username" @keyup.enter.native="getIssueList(1)" placeholder="请输入领取人" class="selWidth" />
        </el-form-item>
        <el-form-item label="优惠劵：" prop="coupon">
          <el-input v-model="tableFromIssue.coupon" @keyup.enter.native="getIssueList(1)" placeholder="请输入优惠券名称" class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getIssueList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table
        v-loading="Loading"
        :data="issueData.data"
        size="small"
      >
        <el-table-column
          prop="coupon_id"
          label="ID"
          min-width="80"
        />
        <el-table-column
          prop="coupon_title"
          label="优惠券名称"
          min-width="150"
        />
        <el-table-column
          label="领取人"
          min-width="150"
        >
          <template slot-scope="scope">
            <span v-if="scope.row.user">{{scope.row.user.nickname | filterEmpty}}</span>
            <span v-else>未知</span>
          </template>
        </el-table-column>
         <el-table-column label="商户名称" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.merchant ? scope.row.merchant.mer_name : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="coupon_price"
          label="面值"
          min-width="80"
        />
        <el-table-column
          prop="use_min_price"
          label="最低消费额"
          min-width="90"
        />
        <el-table-column
          prop="start_time"
          label="开始使用时间"
          min-width="150"
        />
        <el-table-column
          prop="end_time"
          label="结束使用时间"
          min-width="150"
        />
        <el-table-column
          label="获取方式"
          min-width="100"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.type | failFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="is_fail"
          label="是否可用"
          min-width="90"
        >
           <template slot-scope="scope">
            <i
              v-if="scope.row.is_fail === 0 && scope.row.status == 0"
              class="el-icon-check"
              style="font-size: 14px; color: #0092dc"
            />
            <i
              v-else
              class="el-icon-close"
              style="font-size: 14px; color: #ed5565"
            />
          </template>
        </el-table-column>
        <el-table-column
          label="状态"
          min-width="90"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.status | statusFilter }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFromIssue.limit"
          :current-page="tableFromIssue.page"
          layout="total, prev, pager, next, jumper"
          :total="issueData.total"
          @size-change="handleSizeChangeIssue"
          @current-change="pageChangeIssue"
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
import { issueApi } from '@/api/marketing'
import { merSelectApi } from '@/api/product'
import { roterPre } from '@/settings'
export default {
  name: 'CouponUser',
  filters: {
    failFilter(status) {
      const statusMap = {
        'receive': '自己领取',
        'send': '后台发送',
        'give': '满赠',
        'new': '新人',
        'buy': '买赠送'
      }
      return statusMap[status]
    },
    statusFilter(status) {
      const statusMap = {
        0: '未使用',
        1: '已使用',
        2: '已过期'
      }
      return statusMap[status]
    }
  },
  data() {
    return {
      Loading: false,
      roterPre: roterPre,
      tableFromIssue: {
        page: 1,
        limit: 10,
        mer_id: "",
        coupon: '',
        status: '',
        username: ''
      },
      issueData: {
        data: [],
        total: 0
      },
      merSelect: []
    }
  },
  mounted() {
    this.getMerSelect()
    this.getIssueList()
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getIssueList(1)
    },
    // 商户列表；
    getMerSelect() {
      merSelectApi().then(res => {
        this.merSelect = res.data
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 列表
    getIssueList(num) {
      this.tableFromIssue.page = num || this.tableFromIssue.page
      this.Loading = true
      issueApi(this.tableFromIssue).then(res => {
        this.issueData.data = res.data.list
        this.issueData.total = res.data.count
        this.Loading = false
      }).catch(res => {
        this.Loading = false
        this.$message.error(res.message)
      })
    },
    pageChangeIssue(page) {
      this.tableFromIssue.page = page
      this.getIssueList()
    },
    handleSizeChangeIssue(val) {
      this.tableFromIssue.limit = val
      this.getIssueList()
    },
  }
}
</script>

<style scoped lang="scss">

</style>
