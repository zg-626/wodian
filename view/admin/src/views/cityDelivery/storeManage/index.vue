<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="95px" inline>
        <el-form-item label="时间选择：" >
          <el-date-picker v-model="timeVal" value-format="yyyy/MM/dd" format="yyyy/MM/dd" size="small" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 280px;" :picker-options="pickerOptions" @change="onchangeTime" />
        </el-form-item>           
        <el-form-item label="开通状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option v-for="item in applyStatus" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="发货点名称：" prop="station_name">  
          <el-input v-model="tableFrom.station_name" placeholder="请输入发货点名称" class="selWidth" size="small" @keyup.enter.native="getList(1)" />
        </el-form-item>  
        <el-form-item label="关键字：" prop="keyword">
          <el-input v-model="tableFrom.keyword" placeholder="请输入联系人姓名或电话" class="selWidth" size="small" @keyup.enter.native="getList(1)" />
        </el-form-item> 
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table v-loading="listLoading" :data="tableData.data" style="width: 100%" size="mini">
        <el-table-column label="序号" min-width="50">
          <template scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="merchant.mer_name" label="商户名称" min-width="100" />
        <el-table-column prop="station_name" label="发货点名称" min-width="100" />
        <el-table-column prop="contact_name" label="联系人姓名" min-width="60" />
        <el-table-column prop="phone" label="联系人电话" min-width="100" />
        <el-table-column prop="create_time" label="创建时间" min-width="100" />
        <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onDetails(scope.row.station_id)">详情</el-button>
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
    <!--详情-->
    <el-dialog
      v-if="dialogVisible"
      title="发货点详情"
      :visible.sync="dialogVisible"
      width="500px"
    >
      <div v-loading="loading">
        <div class="description">
          <div class="acea-row">
            <div class="description-term">商户名称：{{ storeDetail.merchant && storeDetail.merchant.mer_name }}</div>
            <div class="description-term">发货点名称：{{ storeDetail.station_name }}</div>
            <div class="description-term">联系电话：{{ storeDetail.phone }}</div>
            <div class="description-term">配送品类：{{ storeDetail.business && storeDetail.business.label }}</div>

            <div class="description-term">发货点联系人：{{ storeDetail.contact_name }}</div>
            <div class="description-term">发货点联系电话：{{ storeDetail.phone }}</div>
            <div class="description-term">经纬度：{{ storeDetail.lat }},{{ storeDetail.lng }}</div>
            <div class="description-term">详细地址：{{ storeDetail.station_address }}</div>
            <div class="description-term">是否显示：{{ storeDetail.status == 1 ? '显示' : '不显示' }}</div>
            <div class="description-term">备注：{{ storeDetail.mark || '无' }}</div>
          </div>
        </div>
      </div>
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
import { deliveryStoreLst, deliveryStoreDetail } from '@/api/systemForm'
import timeOptions from '@/utils/timeOptions';
export default {
  data() {
    return {
      dialogVisible: false,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      loading: false,
      tableFrom: {
        keyword: '',
        status: '',
        date: '',
        station_name: '',
        page: 1,
        limit: 20
      },
      timeVal: [],
      pickerOptions: timeOptions,
      applyStatus: [
        { value: 1, label: '已开通' },
        { value: 0, label: '未开通' }
      ],
      storeDetail: {}
    }
  },
  mounted() {
    this.getList(1)
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab
      this.timeVal = []
      this.getList(1)
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
      this.getList(1)
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      deliveryStoreLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.listLoading = false
        })
        .catch(res => {
          this.$message.error(res.message)
          this.listLoading = false
        })
    },
    // 详情
    onDetails(id) {
      this.dialogVisible = true
      deliveryStoreDetail(id)
        .then(res => {
          this.storeDetail = res.data
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    },
  }
}
</script>

<style lang="scss" scoped>
.description{
  &-term {
    display: table-cell;
    padding-bottom: 10px;
    line-height: 20px;
    width: 100%;
    font-size: 12px;
  }
}

</style>
