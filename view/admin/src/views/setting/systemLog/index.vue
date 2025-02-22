<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="85px" @submit.native.prevent>
        <el-form-item label="管理员ID：" prop="admin_id">
          <el-input v-model="tableFrom.admin_id" placeholder="请输入" class="selWidth" @keyup.enter.native="getList(1)" />
        </el-form-item>
        <el-form-item label="请求方式：" prop="method">
          <el-select v-model="tableFrom.method" placeholder="请选择" clearable class="selWidth">
            <el-option v-for="item in importanceOptions" :key="item" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item label="操作时间：">
         <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            clearable
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column
          prop="admin_id"
          label="管理员ID"
          min-width="60"
        />
        <el-table-column
          prop="admin_name"
          label="管理员名称"
          min-width="100"
        />
        <el-table-column
          prop="menu.menu_name"
          label="权限名称"
          min-width="100"
        />
        <el-table-column
          prop="route"
          label="请求"
          min-width="180"
        />
        <el-table-column
          prop="method"
          label="请求方式"
          min-width="80"
        />
        <el-table-column
          prop="url"
          label="链接"
          min-width="250"
        />
        <el-table-column
          prop="ip"
          label="IP"
          min-width="120"
        />
        <el-table-column
          prop="create_time"
          label="操作时间"
          min-width="120"
        />
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
import { adminLogApi } from '@/api/setting'
export default {
  name: 'SystemLog',
  data() {
    return {
      isChecked: false,
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      timeVal: [],
      section_time: [],
      importanceOptions: ['POST', 'DELETE'],
      tableFrom: {
        page: 1,
        limit: 20,
        admin_id: '',
        method: '',
        date: ''
      }
    }
  },
  mounted() {
    this.getList('')
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page;
      adminLogApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList(1)
    },
    onChange(e) {
      if (e == null) {
        this.tableFrom.section_startTime = ''
        this.tableFrom.section_endTime = ''
      } else {
        this.tableFrom.section_startTime = e[0]
        this.tableFrom.section_endTime = e[1]
      }
      this.getList('')
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList('');
    },
  }
}
</script>

<style scoped lang="scss">
.filter-container .filter-item{
  margin-bottom: 0;
}
::v-deep .el-input--medium .el-input__inner{
  line-height: 32px;
  height: 32px;
}
</style>
