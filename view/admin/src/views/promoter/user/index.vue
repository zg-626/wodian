<template>
  <div class="divBox">
    <div class="selCard mb14">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="85px" inline>
        <el-form-item label="时间选择：">
          <el-date-picker v-model="timeVal" value-format="yyyy/MM/dd" format="yyyy/MM/dd" size="small" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 280px;" :picker-options="pickerOptions" @change="onchangeTime" />
        </el-form-item>
        <el-form-item label="等级名称：" prop="brokerage_level">
          <el-select v-model="tableFrom.brokerage_level" clearable filterable placeholder="请选择" class="selWidth" @change="getList(1),getStatistics()">
            <el-option
              v-for="item in levelList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input v-model="tableFrom.keyword" @keyup.enter.native="getList" placeholder="请输入姓名、电话、UID" class="selWidth" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <cards-data v-if="cardLists.length>0" :card-lists="cardLists" />
    <el-card class="box-card">
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        style="width: 100%"
        size="mini"
        class="table"
        highlight-current-row
      >
        <el-table-column
          prop="uid"
          label="ID"
          width="60"
        />
        <el-table-column
          label="头像"
          min-width="80"
        >
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                :src="scope.row.avatar || moren"
                :preview-src-list="[scope.row.avatar || moren]"
              />
            </div>
          </template>
        </el-table-column>
         <el-table-column
          label="用户信息"
          min-width="150"
        >
          <template slot-scope="scope">
            <div>昵称：{{ scope.row.nickname }}</div>
            <div>电话：{{ scope.row.phone }}</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="spread_count"
          label="推广用户数量"
          min-width="100"
        />
         <el-table-column
          label="等级名称"
          min-width="90"
         >
         <template slot-scope="scope">
           <span>{{scope.row.brokerage ? scope.row.brokerage.brokerage_name : ''}}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="推广订单数量"
          min-width="120"
          prop="spread_pay_count"
        />
        <el-table-column
          label="推广订单金额"
          min-width="120"
          prop="spread_pay_price"
        />
        <el-table-column
          label="佣金金额"
          min-width="120"
          sortable
          :sort-method="(a,b)=>{return a.total_brokerage_price - b.total_brokerage_price}"
          prop="total_brokerage_price"
        />
        <el-table-column
          label="已提现金额"
          min-width="120"
          sortable
          :sort-method="(a,b)=>{return a.total_extract_price - b.total_extract_price}"
          prop="total_extract_price"
        />
        <el-table-column
          prop="total_extract_num"
          label="提现次数"
          min-width="90"
        />
        <el-table-column
          label="未提现金额"
          min-width="120"
          sortable
          :sort-method="(a,b)=>{return a.brokerage_price - b.brokerage_price}"
          prop="brokerage_price"
        />
        <el-table-column
          prop="spread.nickname"
          label="上级推广人"
          min-width="120"
        />
        <el-table-column label="操作" min-width="120" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" class="mr10" @click="onSpread(scope.row.uid, 'man')">推广人</el-button>
            <el-dropdown>
              <span class="el-dropdown-link">
                更多<i class="el-icon-arrow-down el-icon--right" />
              </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="onSpreadOrder(scope.row.uid, 'order')">推广订单</el-dropdown-item>
                <!--<el-dropdown-item @click.native="onSpreadType(scope.row.uid)">推广方式</el-dropdown-item>-->
                <el-dropdown-item @click.native="clearSpread(scope.row)">清除上级推广人</el-dropdown-item>
                <el-dropdown-item @click.native="setDistriLevel(scope.row)">编辑分销员等级</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
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

    <!--推广人-->
    <el-dialog
      :title="showDistributor ? '推广订单' : '推广人'"
      :visible.sync="dialogVisible"
      width="900px"
      :before-close="handleClose"
    >
      <div class="container">
        <el-form size="small" label-width="85px" inline>
          <el-form-item label="时间选择：">
            <el-date-picker v-model="timeValSpread" value-format="yyyy/MM/dd" format="yyyy/MM/dd" size="small" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 280px;" :picker-options="pickerOptions" @change="onchangeTimeSpread" />
          </el-form-item>
          <el-form-item label="用户类型：">
            <el-select
              v-model="spreadFrom.level"
              clearable
              placeholder="请选择"
              class="selWidth"
              @change="onChanges"
            >
              <el-option label="全部" value="" />
              <el-option label="一级推广人" value="1" />
              <el-option label="二级推广人" value="2" />
              <el-option v-if="showDistributor" label="分销员自购" value="-1" />
            </el-select>
          </el-form-item>
          <el-form-item label="关键字：">
            <el-input v-model="spreadFrom.keyword" @keyup.enter.native="onChanges" :placeholder="showDistributor ? '请输入订单号' : '请输入请输入姓名、电话、UID'" class="selWidth" clearable size="small" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" size="small" @click="onChanges">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-table
        v-if="onName === 'man'"
        key="men"
        v-loading="spreadLoading"
        :data="spreadData.data"
        size="small"
        class="table"
        highlight-current-row
      >
        <el-table-column
          prop="uid"
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
            <span v-else>
              <img style="width: 36px; height: 36px"
                    src="../../../assets/images/f.png" alt="">
            </span>
          </template>
        </el-table-column>
        <el-table-column
          label="用户信息"
          min-width="90"
        >
          <template slot-scope="scope">
            <div>昵称：{{ scope.row.nickname }}</div>
            <div>电话：{{ scope.row.phone }}</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="is_promoter"
          label="是否推广员"
          min-width="100"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.is_promoter | filterYesOrNo }}</span>
          </template>
        </el-table-column>
        <el-table-column
          sortable
          :sort-method="(a,b)=>{return a.spread_count - b.spread_count}"
          label="推广人数"
          min-width="100"
          prop="spread_count"
        />
        <el-table-column
          sortable
          label="订单数"
          min-width="90"
          prop="pay_count"
        />
        <el-table-column
          sortable
          label="绑定时间"
          min-width="100"
          prop="spread_time"
        />
        <el-table-column
          sortable
          label="解绑时间"
          min-width="100"
        >
        <template slot-scope="scope">
            <span>{{ scope.row.spread_limit ? scope.row.spread_limit : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="create_time"
          label="关注时间"
          min-width="100"
        />
      </el-table>
      <el-table
        v-if="onName === 'order'"
        key="order"
        v-loading="spreadLoading"
        :data="spreadData.data"
        size="small"
        class="table"
        highlight-current-row
      >
        <el-table-column
          prop="order_sn"
          label="订单ID"
          min-width="100"
        />
        <el-table-column
          prop="create_time"
          label="时间"
          min-width="100"
        />
        <el-table-column
          sortable
          :sort-method="(a,b)=>{return a.brokerage - b.brokerage}"
          label="返佣金额"
          min-width="100"
          prop="brokerage"
        />
      </el-table>
      <div class="block">
        <el-pagination
          :page-size="spreadFrom.limit"
          :current-page="spreadFrom.page"
          layout="prev, pager, next, jumper"
          :total="spreadData.total"
          @size-change="handleSizeChangeSpread"
          @current-change="pageChangeSpread"
        />
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
import { promoterListApi, spreadListApi, spreadOrderListApi, spreadClearApi, getDistributionLevel, distributionStatistics,distributionLevelUpdate } from '@/api/promoter'
import { fromList } from '@/libs/constants.js'
import cardsData from "@/components/cards/index";
import timeOptions from '@/utils/timeOptions';
export default {
  name: 'AccountsUser',
  components: { cardsData },
  data() {
    return {
      pickerOptions: timeOptions,
      moren: require("@/assets/images/f.png"),
      timeVal: [],
      levelList: [],
      cardLists: [],
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      tableFrom: {
        paid: '',
        date: '',
        keyword: '',
        brokerage_level: '',
        page: 1,
        limit: 20
      },
      fromList: fromList,
      dialogVisible: false,
      spreadData: {
        data: [],
        total: 0
      },
      spreadFrom: {
        page: 1,
        limit: 10,
        date: '',
        level: '',
        keyword: ''
      },
      timeValSpread: [],
      spreadLoading: false,
      uid: '',
      onName: '',
      showDistributor: false
    }
  },
  mounted() {
    this.getList('')
    this.getLevelList();
    this.getStatistics();
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
      this.getStatistics()
    },
    // 清除
    clearSpread(row) {
      this.$modalSure('解除【' + row.nickname + '】的上级推广人吗').then(() => {
        spreadClearApi(row.uid).then(({ message }) => {
          this.$message.success(message)
          this.getList('')
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
    onSpread(uid, n) {
      this.onName = n
      this.uid = uid
      this.showDistributor = false
      this.dialogVisible = true
      this.spreadFrom = {
        page: 1,
        limit: 10,
        date: '',
        level: '',
        keyword: ''
      }
      this.getListSpread(uid,'')
    },
    handleClose() {
      this.dialogVisible = false
    },
    // 获取分销等级   
    getLevelList(){
        getDistributionLevel().then(res => {
            this.levelList = res.data;
        }).catch((res) => {
            this.$message.error(res.message)
        })
    },
    // 获取统计数据
    getStatistics(){
        distributionStatistics(this.tableFrom).then(res => {
            this.cardLists = res.data;
        }).catch((res) => {
            this.$message.error(res.message)
        })
    },
    // 编辑分销员等级
    setDistriLevel(row){
      this.$modalForm(distributionLevelUpdate(row.uid))
    },
    // 选择时间
    selectChangeSpread(tab) {
      this.timeValSpread = []
      this.spreadFrom.date = tab
      this.onName === 'man' ? this.getListSpread(this.uid,1) : this.getSpreadOrderList(this.uid,1)
    },
    // 具体日期
    onchangeTimeSpread(e) {
      this.timeValSpread = e
      this.spreadFrom.date = e ? this.timeValSpread.join('-') : ''
      this.onName === 'man' ? this.getListSpread(this.uid,'') : this.getSpreadOrderList(this.uid,'')
    },
    onChanges() {
      this.onName === 'man' ? this.getListSpread(this.uid,1) : this.getSpreadOrderList(this.uid,1)
    },
    // 推广人列表
    getListSpread(uid,num) {
      this.spreadLoading = true
      this.spreadFrom.page = num ? num : this.spreadFrom.page
      spreadListApi(uid, this.spreadFrom).then(res => {
        this.spreadData.data = res.data.list
        this.spreadData.total = res.data.count
        this.spreadLoading = false
      }).catch((res) => {
        this.$message.error(res.message)
        this.spreadLoading = false
      })
    },
    pageChangeSpread(page) {
      this.spreadFrom.page = page
      this.onName === 'man' ? this.getListSpread(this.uid,'') : this.getSpreadOrderList(this.uid,'')
    },
    handleSizeChangeSpread(val) {
      this.spreadFrom.limit = val
      this.onName === 'man' ? this.getListSpread(this.uid,'') : this.getSpreadOrderList(this.uid,'')
    },
    // 推广订单
    onSpreadOrder(uid, n) {
      this.uid = uid
      this.onName = n
      this.showDistributor = true
      this.dialogVisible = true
      this.spreadFrom = {
        page: 1,
        limit: 10,
        date: '',
        level: '',
        keyword: ''
      }
      this.getSpreadOrderList(uid,1)
    },
    getSpreadOrderList(uid, num) {
      this.spreadLoading = true
      this.spreadFrom.page = num ? num : this.spreadFrom.page
      spreadOrderListApi(uid, this.spreadFrom).then(res => {
        this.spreadData.data = res.data.list
        this.spreadData.total = res.data.count
        this.spreadLoading = false
      }).catch((res) => {
        this.$message.error(res.message)
        this.spreadLoading = false
      })
    },
    selectChange(tab) {
      this.tableFrom.date = tab
      this.timeVal = []
      this.tableFrom.page = 1;
      this.getList('')
      this.getStatistics()
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
      this.tableFrom.page = 1;
      this.getList('')
      this.getStatistics()
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page
      promoterListApi(this.tableFrom).then(res => {
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
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    }
  }
}
</script>

<style scoped>
  .el-dropdown-link {
    cursor: pointer;
    color: var(--prev-color-primary);
    font-size: 12px;
  }
  .el-icon-arrow-down {
    font-size: 12px;
  }
</style>
