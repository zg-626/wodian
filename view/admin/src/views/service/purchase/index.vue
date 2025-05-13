<template>
  <div class="divBox">
    <div v-if="tableFrom.sys == 0" class="selCard mb14">
      <el-form :model="tableFrom" ref="searchForm" inline size="small" label-width="85px">    
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px"
            :picker-options="pickerOptions"
            @change="onchangeTime"
          />
        </el-form-item>                  
        <el-form-item label="购买类型：" prop="type">
          <el-select
            v-model="tableFrom.type"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option
              v-for="item in purchaseList"
              :key="item.type"
              :label="item.name"
              :value="item.type"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="商户名称：" prop="mer_id">
          <el-select
            v-model="tableFrom.mer_id"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1),getCardList()"
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
    <el-card>
      <!-- <div>
        <el-tabs v-model="tableFrom.sys" @tab-click="getList(1)">
          <el-tab-pane v-for="(item,index) in headeNum" :key="index" :name="item.type.toString()" :label="item.title" />
        </el-tabs>
      </div> -->
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column label="序号" min-width="100">
          <template scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column
          v-if="tableFrom.sys == 0"
          key="1"
          prop="merchant.mer_name"
          label="商户名称"
          min-width="150"
        />
        <el-table-column
          label="购买记录"
          min-width="150"
        > 
        <template scope="scope">
            <span v-if="scope.row.order_info && tableFrom.sys == 0">{{ scope.row.order_info.price }}元 / {{ scope.row.order_info.num }}次</span>
            <span v-if="tableFrom.sys == 1">{{scope.row.mark}}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="购买类型"
          min-width="150"
        > 
        <template slot-scope="scope">
            <span v-if="tableFrom.sys == 1">{{scope.row.type | purchaseType}}</span>
            <span v-else-if="tableFrom.sys == 0">{{scope.row.type == 1 ? '商品采集' : '电子面单'}}</span>
          </template>
        </el-table-column>             
        <el-table-column
        v-if="tableFrom.sys == 1"
        key="4"
          prop="add_time"
          label="购买时间"
          min-width="180"
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
import { purchaseRecordLst, purchaseMerLst } from '@/api/setting'
import { merSelectApi } from "@/api/product";
import { roterPre } from '@/settings'
import timeOptions from '@/utils/timeOptions';
export default {
  name: 'serviceSetting',
  data() {
    return {
      pickerOptions: timeOptions,
      smsAccount: 'tangkai',
      imgUrl: require('@/assets/images/ren.png'),
      headeNum: [
        {type: 1,title: "平台购买记录"},
        {type: 0,title: "商户购买记录"}
      ],
      purchaseList: [
        {name: "商品采集",type: 1},
        {name: "电子面单",type: 2}
        
      ],
      merSelect: [],
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      timeVal: [],
      fullscreenLoading: false,
      tableFrom: {
        page: 1,
        limit: 20,
        sys: '0',
        type: '',
        mer_id: '',
        date: ''
      }

    }
  },
  mounted() {
    this.getList('');
    this.getMerSelect();
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
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.getList(1);
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page;
      this.tableFrom.sys == '1' ? 
      purchaseRecordLst(this.tableFrom).then(res => {
        this.tableData.data = res.data.data
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
        this.$router.push(roterPre + '/setting/sms/sms_config/index')
      }) :
       purchaseMerLst(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
        this.$router.push(roterPre + '/setting/sms/sms_config/index')
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
  }
}
</script>

<style scoped lang="scss">
  .content{
    justify-content: space-between;
  }
  .dashboard-workplace-header-tip {
    display: inline-block;
    vertical-align: middle;
  }
  .dashboard-workplace-header-tip-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 12px;
  }
  .dashboard-workplace-header-avatar{
      margin-right: 16px;
  }
.filter-container .filter-item{
  margin-bottom: 0;
}
::v-deep .el-input--medium .el-input__inner{
  line-height: 32px;
  height: 32px;
}
.demo-table-expand .el-form-item{
  width: 100%;
}
.dialog-footer{
  text-align: center;
}
</style>
