<template>
  <div class="divBox">
    <el-card class="box-card">
      <div slot="header" class="clearfix">
        <div class="container">
          <el-form size="small" label-width="90px" :inline="true">
            <el-form-item label="选择时间：" >
              <el-date-picker
                v-model="timeVal"
                type="daterange"
                placeholder="选择日期"
                format="yyyy/MM/dd"
                value-format="yyyy/MM/dd"
                range-separator="-"
                :picker-options="pickerOptions"
                style="width: 280px;"
                clearable
                @change="onchangeTime"
              />
            </el-form-item>
          </el-form>
        </div>
        <el-button size="small" type="primary" @click="onExport">导出</el-button>
      </div>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        style="width: 100%"
        size="small"
        highlight-current-row
        class="switchTable"
      >
        <el-table-column prop="activity.activity_name" label="活动名称" min-width="100" />
        <el-table-column label="用户名称/ID" min-width="100">
          <template slot-scope="{ row }">
            <div>
             {{row.nickname}}/{{row.uid}}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="phone" label="用户手机号" min-width="150" />
        <el-table-column v-for="(item, index) in headers" :key="index" :label="item.label" min-width="100">
          <template slot-scope="{ row }">
            <div v-if="item.type=='uploadPicture'">
              <el-image v-for="(img,i) in row.value[item.key]" :key="i" style="width: 36px; height: 36px" :src="img" :preview-src-list="[img]" />
            </div>
            <div v-else-if="item.type=='dateranges'">
              <div v-if="row.value[item.key]">{{row.value[item.key][0]}}至<br/>{{row.value[item.key][1]}}</div>
            </div>
            <div v-else>
            {{row.value[item.key]}}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
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
import { formDetailList, formDetailExcel } from "@/api/setting";
import { roterPre } from "@/settings";
import createWorkBook from '@/utils/newToExcel.js'
export default {
  name: "FormDetail",
  data() {
    return {
      roterPre: roterPre,
      listLoading: true,
      pickerOptions: {
        shortcuts: [{
          text: '最近一周',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: '最近一个月',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: '最近三个月',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          }
        }]
      },
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        date: "",
        keyword: "",
      },
      drawer: false,
      timeVal: [],
      headers: []
    };
  },
  mounted() {
    this.getList("");
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = this.timeVal ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList("");
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      formDetailList(this.$route.params.id,this.tableFrom)
        .then(res => {
          this.headers = res.data.header;
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList(1);
    }, 
    // 导出
    async onExport() {
      let params = this.tableFrom
      params.id = this.$route.params.id
      let excelData = JSON.parse(JSON.stringify(params)), data = []
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
        formDetailExcel(excelData).then((res) => {
          return resolve(res.data)
        })
      })
    },
  }
};
</script>

<style scoped lang="scss">
</style>
