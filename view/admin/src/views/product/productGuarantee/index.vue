<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :inline="true" size="small" :model="tableFrom" ref="searchForm" label-width="85px">
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            :picker-options="pickerOptions"
            style="width: 280px;"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="服务名称：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入服务名称搜索"
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
      <el-button size="small" type="primary" class="mb20" @click="add">添加保障服务</el-button>
      <el-table v-loading="listLoading" :data="tableData.data" size="small " @rowclick.stop="closeEdit" :row-class-name="tableRowClassName">
        <el-table-column label="序号" min-width="50">
          <template scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="guarantee_name" label="服务条款" min-width="150" />
        <el-table-column label="服务条款图标" min-width="120">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                style="width: 36px; height: 36px"
                :src="scope.row.image"
                :preview-src-list="[scope.row.image]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="guarantee_info" label="服务内容描述" min-width="200" />
        <el-table-column prop="sort" align="center" label="排序" min-width="80">
            <template slot-scope="scope">
                <span v-if="scope.row.index === tabClickIndex">
                    <el-input v-model.number="scope.row['sort']" type="number" maxlength="300" size="mini" @blur="inputBlur(scope)" autofocus/>
                </span>
                <span v-else @dblclick.stop="tabClick(scope.row)">{{ scope.row['sort'] }}</span>
            </template>
        </el-table-column>
        <el-table-column prop="nickname" label="是否显示" min-width="90">
            <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="显示"
              inactive-text="不显示"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="mer_count" label="使用的商户数" min-width="100" />
        <el-table-column prop="product_cout" label="使用商品数" min-width="100" />
        <el-table-column label="创建时间" min-width="150" prop="create_time" />
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="handleEdit(scope.row.guarantee_id)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.guarantee_id, scope.$index)">删除</el-button>
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
import { guaranteeLstApi, guaranteeAddApi, guaranteeUpdateApi, guaranteeDeleteApi, guaranteeSortApi, guaranteeStatusApi } from "@/api/product";
import { fromList } from "@/libs/constants.js";
import timeOptions from '@/utils/timeOptions';
export default {
  name: "ProductGuarantee",
  data() {
    return {
      pickerOptions: timeOptions,
      fromList: fromList,
      tableData: {
        data: [],
        total: 0,
      },
      listLoading: true,
      tableFrom: {
        page: 1,
        limit: 20,
        date: "",
        keyword: "",
      },
      timeVal: [],
      props: {},
      tabClickIndex: '',
    };
  },
  mounted() {
    this.getList(1);
  },
  methods: {
    // 把每一行的索引放进row
    tableRowClassName({ row, rowIndex }) {
      row.index = rowIndex;
    },
    // 添加明细原因 row 当前行 column 当前列
    tabClick(row) {
      this.tabClickIndex = row.index;
    },
    // 失去焦点初始化
    inputBlur(scope) {
        if(!scope.row.sort || scope.row.sort<0)scope.row.sort=0
        guaranteeSortApi(scope.row.guarantee_id,{sort:scope.row.sort})
        .then((res) => {
          this.closeEdit();
        })
        .catch((res) => {
        });
    },
    closeEdit(){
      this.tabClickIndex = null;
    },
    add() {
      this.$modalForm(guaranteeAddApi("")).then(() => this.getList(1));
    },
    /**重置 */
    searchReset(){
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    //编辑
    handleEdit(id){
      this.$modalForm(guaranteeUpdateApi(id)).then(() => this.getList(1));
    },
    // 修改显示状态
    onchangeIsShow(row) {
      guaranteeStatusApi(row.guarantee_id, { status: row.status })
        .then(({ message }) => {
          this.$message.success(message);
          this.getList('');
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab;
      this.timeVal = [];
      this.tableFrom.page = 1;
      this.getList(1);
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = this.timeVal ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList(1);
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      guaranteeLstApi(this.tableFrom)
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
      this.tableFrom.page = page;
      this.getList('');
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList('');
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure().then(() => {
        guaranteeDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.tableData.data.splice(idx, 1);
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
  },
};
</script>

<style scoped lang="scss">
@import '@/styles/form.scss';
</style>
