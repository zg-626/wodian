<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="85px">
        <el-form-item label="时间选择：">
          <el-date-picker v-model="timeVal" value-format="yyyy/MM/dd" format="yyyy/MM/dd" type="daterange" placement="bottom-end" clearable placeholder="自定义时间" style="width: 280px;" :picker-options="pickerOptions" @change="onchangeTime" />
        </el-form-item>
        <el-form-item label="明细类型：" prop="type">
          <el-select v-model="tableFrom.type" class="selWidth" filterable clearable placeholder="请选择" @change="getList(1)">
            <el-option
              v-for="(item, index) in options"
              :key="index"
              :label="item.title"
              :value="item.type"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input v-model="tableFrom.keyword" @keyup.enter.native="getList(1)" placeholder="微信昵称/姓名/支付宝账号/银行卡号" clearable class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <div class="mb20">
        <el-button size="small" type="primary" @click="exports">导出列表</el-button>
      </div>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        class="table"
        highlight-current-row
      >
        <el-table-column
          prop="uid"
          label="会员ID"
          width="100"
        />
        <el-table-column
          prop="nickname"
          label="昵称"
          min-width="150"
        />
        <el-table-column
          prop="number"
          label="金额"
          min-width="120"
        />
        <el-table-column
          label="明细类型"
          min-width="180"
          prop="title"
        />
        <el-table-column
          prop="mark"
          label="备注"
          min-width="250"
        />
        <el-table-column
          prop="create_time"
          label="创建时间"
          min-width="150"
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
    <!--导出订单列表-->
    <file-list ref="exportList" />
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
import { billListApi, billTypeApi, fundingRecordsExportApi } from '@/api/accounts'
import { fromList } from '@/libs/constants.js'
import createWorkBook from '@/utils/newToExcel.js'
import fileList from '@/components/exportFile/fileList'
import timeOptions from '@/utils/timeOptions';
export default {
  components: { fileList },
  name: 'AccountsCapital',
  data() {
    return {
      timeVal: [],
      pickerOptions: timeOptions,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      tableFrom: {
        type: '',
        date: '',
        keyword: '',
        page: 1,
        limit: 20
      },
      fromList: fromList,
      options: []
    }
  },
  mounted() {
    this.getTypes()
    this.getList()
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
    async exports() {
      let excelData = JSON.parse(JSON.stringify(this.tableFrom)), data = []
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
    /**资金记录 */
    downData(excelData) {
      return new Promise((resolve, reject) => {
        fundingRecordsExportApi(excelData).then((res) => {
          return resolve(res.data)
        })
      })
    },
    // 导出
    exportRecord() {
      fundingRecordsExportApi(this.tableFrom)
        .then((res) => {
          const h = this.$createElement;
          this.$msgbox({
            title: '提示',
            message: h('p', null, [
              h('span', null, '文件正在生成中，请稍后点击"'),
              h('span', { style: 'color: teal' }, '导出记录'),
              h('span', null, '"查看~ '),
            ]),
            confirmButtonText: '我知道了',
          }).then(action => {

          });
        })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    // 导出列表
    getExportFileList() {
      this.$refs.exportList.exportFileList()
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page;
      billListApi(this.tableFrom).then(res => {
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
    getTypes() {
      billTypeApi().then(res => {
        this.options = res.data
      }).catch((res) => {
        this.$message.error(res.message)
      })
    }
  }
}
</script>

<style scoped>
</style>
