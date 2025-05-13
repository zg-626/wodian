<template>
  <div class="divBox">
    <div class="selCard">
      <el-form size="small" :model="tableFrom" ref="searchForm" label-width="85px" inline>
        <el-form-item label="时间选择：" prop="date">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            :picker-options="pickerOptions"
            style="width: 280px;"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="用户名称：" prop="nickname">
          <el-input
            v-model="tableFrom.nickname"
            @keyup.enter.native="getList(1)"
            placeholder="请输入用户名称"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入商品ID或者商品信息"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item label="评价状态：" prop="is_reply">
          <el-select
            v-model="tableFrom.is_reply"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="已回复" value="1" />
            <el-option label="未回复" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="add">添加自评</el-button>
      <el-table v-loading="listLoading" :data="tableData.data" size="small" @rowclick.stop="closeEdit" :row-class-name="tableRowClassName">
        <el-table-column prop="reply_id" label="ID" min-width="50" />
        <el-table-column label="商品图" min-width="80">
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
        <el-table-column prop="store_name" label="商品名称" min-width="200" />
        <el-table-column prop="nickname" label="用户名称" min-width="100" />
        <el-table-column prop="product_score" label="产品评分" sortable min-width="100" />
        <el-table-column prop="sort" label="排序" min-width="80">
          <template slot-scope="scope">
            <span v-if="scope.row.index === tabClickIndex">
                <el-input v-model.number="scope.row['sort']" type="number" maxlength="300" size="mini" @blur="inputBlur(scope)" autofocus/>
            </span>
            <span v-else @dblclick.stop="tabClick(scope.row)">{{ scope.row['sort'] }}</span>
          </template>
        </el-table-column>
        <el-table-column label="评价内容" min-width="200">
          <template slot-scope="scope">
            <div class="mb5 content_font">{{ scope.row.comment }}</div>
            <div class="demo-image__preview">
              <el-image
                v-for="(item,index) in scope.row.pics"
                :key="index"
                :src="item"
                class="mr5"
                :preview-src-list="[item]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="merchant_reply_content" label="回复内容" min-width="200" />
        <el-table-column label="评价时间" min-width="150" prop="create_time" />
        <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              @click="handleDelete(scope.row.reply_id, scope.$index)"
            >删除</el-button>
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
import { replyListApi, replyCreateApi, replyDeleteApi, productSort } from "@/api/product";
import { fromList } from "@/libs/constants.js";
import timeOptions from '@/utils/timeOptions';
export default {
  name: "ProductComment",
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
        is_reply: "",
        product_id: this.$route.query.product_id ? this.$route.query.product_id : '',
        date: "",
        nickname: "",
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
        //   this.tabClickLabel = column.label;
    },
    // 失去焦点初始化
    inputBlur(scope) {
      if(!scope.row.sort || scope.row.sort<0)scope.row.sort=0
        productSort(scope.row.reply_id,{sort:scope.row.sort})
        .then((res) => {
        this.closeEdit();
        //   this.$message.success(res.message);
        }).catch((res) => {
        //  this.$message.error(res.message);
      });
    },
    closeEdit(){
      this.tabClickIndex = null;
    },
    /**重置 */
    searchReset(){
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    add() {
      this.$modalForm(replyCreateApi()).then(() => this.getList(1));
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab;
      this.timeVal = [];
      // this.tableFrom.page = 1;
      this.getList(1);
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      // this.tableFrom.date = this.timeVal ? this.timeVal.join("-") : "";
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
      // this.tableFrom.page = 1;
      this.getList(1);
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      replyListApi(this.tableFrom)
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
      this.$modalSure('确定删除该评论').then(() => {
        replyDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getList('')
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
::v-deep .el-rate{
  margin-top: 9px;
}
</style>
