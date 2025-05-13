<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableForm" ref="searchForm" size="small" label-width="80px" inline>
        <el-form-item label="状态：" prop="status_tag">
          <el-select
            v-model="tableForm.status_tag"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="全部" value="" />
            <el-option label="待审核" value="0" />
            <el-option label="已审核" value="1" />
            <el-option label="审核失败" value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item label="商户类别：" prop="is_trader">
          <el-select
            v-model="tableForm.is_trader"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="自营" value="1" />
            <el-option label="非自营" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input
            v-model="tableForm.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入直播商品名称/ID"
            class="selWidth"
            clearable
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
        highlight-current-row
      >
        <el-table-column label="序号" min-width="50">
          <template scope="scope">
            <span>{{ scope.$index+(tableForm.page - 1) * tableForm.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商户名称" min-width="120">
          <template scope="scope">
            <span>{{ scope.row.merchant ? scope.row.merchant.mer_name : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="mer_name" label="商户类别" min-width="80">
          <template slot-scope="scope">
            <span v-if="scope.row.merchant" class="spBlock">{{ scope.row.merchant.is_trader ? '自营' : '非自营' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="goods_id" label="商品ID" min-width="60" />

        <el-table-column prop="name" label="商品名称" min-width="150" />
        <el-table-column label="原价" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.product ? scope.row.product.price : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="直播价" min-width="80" />
        <el-table-column label="库存" min-width="60">
          <template slot-scope="scope">
            <span>{{ scope.row.product ? scope.row.product.stock : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="sort" min-width="60" label="排序" />
        <el-table-column v-if="tableForm.status_tag != 1" key="3" label="审核状态" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.status | liveReviewStatusFilter }}</span>
            <span
              v-if="scope.row.status == -1"
              style="display: block; font-size: 12px;"
            >原因 {{ scope.row.error_msg }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开启/关闭" min-width="100">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.is_show"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="160" />
        <el-table-column label="操作" min-width="180" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" v-if="scope.row.status == 0" @click="toExamine(scope.row.broadcast_goods_id)">审核</el-button>
            <el-button type="text" size="small" @click="onProDetails(scope.row.broadcast_goods_id)">详情</el-button>
            <el-button type="text" size="small" @click="handleEdit(scope.row.broadcast_goods_id)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.broadcast_goods_id, scope.$index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
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
    <!--详情-->
    <details-from ref="ProDetail" @getList="getList" />
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
import {
  broadcastProListApi,
  changeProDisplayApi,
  applyBroadcastProApi,
  broadcastProDeleteApi
} from "@/api/marketing";
import { roterPre } from "@/settings";
import detailsFrom from "./proDetail";
export default {
  name: "BroadcastProList",
  components: { detailsFrom },
  data() {
    return {
      Loading: false,
      roterPre: roterPre,
      listLoading: true,
      product_id: "",
      dialogVisible: false,
      tableData: {
        data: [],
        total: 0,
      },
      tableForm: {
        page: 1,
        limit: 20,
        status_tag: this.$route.query.status ? this.$route.query.status : "",
        keyword: "",
        is_trader: '',
        broadcast_goods_id: this.$route.query.id ? this.$route.query.id : "",
      },
      liveRoomStatus: "",
    };
  },
  mounted() {
    this.getList();
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 编辑
    handleEdit(id) {
      this.product_id = id;
      this.$refs.ProDetail.dialogVisible = true;
      this.$refs.ProDetail.isEdit = true;
      this.$refs.ProDetail.getData(id);
    },
    // 详情
    onProDetails(id) {
      this.product_id = id;
      this.$refs.ProDetail.dialogVisible = true;
      this.$refs.ProDetail.isEdit = false;
      this.$refs.ProDetail.getData(id);
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSureDelete().then(
        () => {
          broadcastProDeleteApi(id)
            .then(({ message }) => {
              this.$message.success(message);
              this.tableData.data.splice(idx, 1)
              this.getList()
            })
            .catch(({ message }) => {
              this.$message.error(message);
            });
        }
      );
    },
    handleSizeChangeIssue(val) {
      this.tableFormIssue.limit = val;
      this.getIssueList();
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableForm.page = num ? num : this.tableForm.page;
      console.log(this.tableForm);
      broadcastProListApi(this.tableForm)
        .then((res) => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch((res) => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableForm.page = page;
      this.getList();
    },
    handleSizeChange(val) {
      this.tableForm.limit = val;
      this.getList();
    },
    // 修改状态
    onchangeIsShow(row) {
      changeProDisplayApi(row.broadcast_goods_id, { is_show: row.is_show })
        .then(({ message }) => {
          this.$message.success(message);
          this.getList();
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
    // 审核
    // 修改状态
    toExamine(id) {
      this.$modalForm(applyBroadcastProApi(id)).then(() => this.getList());
    },
  },
};
</script>

<style scoped lang="scss">
@import '@/styles/form.scss';
</style>
