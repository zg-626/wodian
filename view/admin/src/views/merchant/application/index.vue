<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="85px" :inline="true">
        <el-form-item label="状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="全部" value=""/>
            <el-option label="待审核" value="0"/>
            <el-option label="审核通过" value="1"/>
            <el-option label="审核未通过" value="-1"/>
          </el-select>
        </el-form-item>
        <el-form-item label="选择时间：">
          <el-date-picker
            v-model="timeVal"
            type="daterange"
            placeholder="选择日期"
            format="yyyy/MM/dd"
            value-format="yyyy/MM/dd"
            :picker-options="pickerOptions"
            @change="onchangeTime"
            style="width: 280px"
          />
        </el-form-item>
        <el-form-item label="商户分类：" prop="category_id">
          <el-select
            v-model="tableFrom.category_id"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option
              v-for="item in merCateList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="店铺类型：" prop="type_id">
          <el-select
            v-model="tableFrom.type_id"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option
              v-for="item in storeType"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入商户名称关键字/联系电话"
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
        class="switchTable"
      >
        <el-table-column prop="mer_intention_id" label="ID" min-width="60" />
        <el-table-column prop="mer_name" label="商户名称" min-width="150" />
        <el-table-column prop="category_name" label="商户分类" min-width="150" />
        <el-table-column prop="type_name" label="店铺类型" min-width="150" />
        <el-table-column prop="name" label="商户姓名" min-width="100" />
        <el-table-column prop="phone" label="联系方式" min-width="100" />
        <el-table-column prop="create_time" label="申请时间" min-width="150" />
        <el-table-column prop="create_time" label="资质图片" min-width="150">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                v-for="(item, index) in scope.row.images"
                :key="index"
                :src="item"
                class="mr5"
                :preview-src-list="[item]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="150">
          <template slot-scope="scope">
            <el-tag v-if="scope.row.status == 1" type="success">通过</el-tag>
            <el-tag v-if="scope.row.status == 0" type="info">未处理</el-tag>
            <el-tag v-if="scope.row.status == 2" type="warning">未通过</el-tag>
            <div v-if="scope.row.status == 2">原因：{{ scope.row.fail_msg }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="mark" label="备注" min-width="150" />
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button
              v-if="scope.row.status == 0"
              type="text"
              size="small"
              @click="onchangeIsShow(scope.row.mer_intention_id)"
              >审核</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="onEdit(scope.row.mer_intention_id)"
              >备注</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="handleDelete(scope.row.mer_intention_id)"
              >删除</el-button
            >
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
import {
  intentionLstApi,
  auditApi,
  intentionDelte,
  intentionStatusApi,
  getstoreTypeApi,
  getMerCateApi
} from "@/api/merchant";
import { fromList, statusList } from "@/libs/constants.js";
import { roterPre } from "@/settings";
import timeOptions from '@/utils/timeOptions';
export default {
  name: "MerchantApplication",
  data() {
    return {
      props: {
        emitPath: false
      },
      pickerOptions: timeOptions,
      fromList: fromList,
      statusList: statusList, //筛选状态列表
      roterPre: roterPre,
      isChecked: false,
      listLoading: true,
      merCateList: [],
      storeType: [],
      tableData: {
        data: [],
        total: 0,
      },
      tableFrom: {
        page: 1,
        limit: 20,
        date: "",
        status: this.$route.query.status ? this.$route.query.status : "",
        keyword: "",
        mer_intention_id: this.$route.query.id ? this.$route.query.id : "",
        category_id: "",
        type_id: ""
      },
      mer_id: this.$route.query.id ? this.$route.query.id : "",
      autoUpdate: true,
      timeVal: [],
    };
  },
  watch: {
    mer_id(newName, oldName) {
     this.getList("");
    }
  },
  mounted() {
    this.getMerCategory();
    this.getStoreType();
    this.getList("");
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    statusChange(tab) {
      this.tableFrom.status = tab;
      this.tableFrom.page = 1;
      this.getList("");
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = this.timeVal ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList("");
    },
    // 商户分类；
    getMerCategory() {
      getMerCateApi().then(res => {
          this.merCateList = res.data
      }).catch(res => {
          this.$message.error(res.message)
      })
    },
    /**获取店铺类型 */
    getStoreType(){
      getstoreTypeApi().then(res => {
          this.storeType = res.data
      }).catch(res => {
          this.$message.error(res.message)
      })
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      intentionLstApi(this.tableFrom)
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
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList(1);
    },
    // 修改状态
    onchangeIsShow(id) {
      this.$modalForm(intentionStatusApi(id)).then(() => this.getList(""));
    },
    // 添加

    // 编辑
    onEdit(id) {
      this.$modalForm(auditApi(id)).then(() => this.getList(""));
    },
    // 删除
    handleDelete(id) {
      this.$deleteSure().then(() => {
        intentionDelte(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getList("");
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep table .el-image {
  display: inline-block !important;
}
@import '@/styles/form.scss';
</style>
