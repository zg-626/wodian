<template>
  <div class="divBox">
    <div class="selCard">
      <el-form size="small" label-width="85px">
        <el-form-item label="商户名称：">
          <el-select
            v-model="tableForm.mer_id"
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
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        class="table"
        highlight-current-row
      >
        <el-table-column label="序号" min-width="90">
          <template scope="scope">
            <span>{{ scope.$index+(tableForm.page - 1) * tableForm.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="mer_name"
          label="商户名称"
          min-width="150"
        />
        <el-table-column
          prop="account.sum"
          label="累计交易额"
          min-width="150"
        />
        <el-table-column
          prop="account.charge"
          label="累计应入账金额"
          min-width="150"
        />

        <el-table-column
          prop="mer_money"
          label="商户余额"
          min-width="150"
        />
        <el-table-column
          prop="account.line"
          label="可提现金额"
          min-width="150"
        />
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <router-link :to="{path: roterPre + '/accounts/billDetails/' + scope.row.mer_id}">
              <el-button type="text" size="small" >账单详情</el-button>
            </router-link>
          </template>
        </el-table-column>
      </el-table>
      <div class="block mb20">
        <el-pagination
          background
          :page-size="tableForm.limit"
          :current-page="tableForm.page"
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
import { merchantBillList } from '@/api/accounts'
import { merSelectApi } from "@/api/product";
import { roterPre } from '@/settings'
export default {
  name: 'MerchantBill',
  data() {
    return {
      loading: false,
      roterPre: roterPre,
      timeVal: [],
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableForm: {
        page: 1,
        limit: 10,
        mer_id: ''
      },
      merSelect: [],
      ruleForm: {
        status: '0'
      },
      dialogVisible: false,
      rules: {
        status: [
          { required: true, message: '请选择对账状态', trigger: 'change' }
        ]
      },
      reconciliationId: 0,
      accountDetails: {
        date: '',
        charge: {},
        expend: {},
        income: {}
      }
    }
  },
  computed: {},
  mounted() { 
    this.getMerSelect()
    this.getList('')
  },
  methods: {
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
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableForm.page = num ? num : this.tableForm.page;
      merchantBillList(this.tableForm).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
    pageChange(page) {
      this.tableForm.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableForm.limit = val
      this.chkName = ''
      this.getList('')
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
