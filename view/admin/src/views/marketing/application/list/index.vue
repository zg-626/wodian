<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="85px" :inline="true">
        <el-form-item label="活动搜索：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            placeholder="请输入活动名称/关键字"
            class="selWidth"
            clearable
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item label="报名状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option label="未开始" :value="0" />
            <el-option label="进行中" :value="1" />
            <el-option label="已结束" :value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item label="关联表单：" prop="form_id">
          <el-select
            v-model="tableFrom.form_id"
            clearable
            class="selWidth"
            placeholder="请选择"
            @change="getList(1)"
          >
            <el-option
              v-for="item in formList"
              :key="item.form_id"
              :label="item.name"
              :value="item.form_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <!-- <div class="mb20"><el-button size="small" type="primary" @click="onAdd">创建报名活动</el-button></div>  -->
      <router-link  :to="{ path: `${roterPre}` + '/marketing/application/create' }">
        <el-button size="small" type="primary" class="mb20">创建报名活动</el-button>
      </router-link>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column
          label="ID"
          prop="activity_id"
          min-width="50"
        />
        <el-table-column label="活动名称" prop="activity_name" min-width="120" />
        <el-table-column min-width="120" label="封面图">
          <template slot-scope="scope">
            <el-image style="width: 36px; height: 36px" :src="scope.row.pic" />
          </template>
        </el-table-column>
        <el-table-column min-width="120" label="分享海报图">
          <template slot-scope="scope">
            <el-image v-for="(item,index) in scope.row.images" :key="index" style="width: 36px; height: 36px; margin-right: 5px;" :src="item" />
          </template>
        </el-table-column>
        <el-table-column label="报名状态" min-width="80">
          <template slot-scope="{ row }">
            <el-tag type="danger" v-if="row.time_status == 0">未开始</el-tag>
            <el-tag v-if="row.time_status == 1">进行中</el-tag>
            <el-tag type="info" v-if="row.time_status == -1">已结束</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="已报人数/总人数"  align="center" min-width="120">
          <template slot-scope="{ row }">
            <span>{{row.total}}</span>/<span>{{ row.count || '不限制'}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="form_name"
          label="关联表单"
          min-width="100"
        />
        <el-table-column
          prop="create_time"
          label="创建时间"
          min-width="120"
        />
        <el-table-column label="是否显示" min-width="90">
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
        <el-table-column label="操作" min-width="120" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onDetails(scope.row.activity_id, false)">查看</el-button>
            <el-button type="text" size="small" @click="onDetails(scope.row.activity_id,true)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.activity_id, scope.$index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
    <!--创建活动-->
    <create-application ref="createApplication" :formList="formList" @getList="getList"></create-application>
    <!--活动详情-->
    <application-details 
      ref="applicationDetails" 
      :applyId="applyId"
      :drawer="drawer"
      @getList="getList"
      @closeDrawer="closeDrawer"/>
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
import { roterPre } from '@/settings'
import { activityList,activityStatusApi,sysFormSelect,activityDeleteApi } from "@/api/marketing";
import createApplication from '../handle/create.vue';
import applicationDetails from '../handle/applicationDetails.vue';
export default {
  name: 'application',
  components: {
    createApplication,
    applicationDetails
  },
  data() {
    return {
      roterPre: roterPre,
      isChecked: false,
      listLoading: true,
      applyId: "",
      drawer: false,
      total: 0,
      formList: [],
      tableData: {
        data: [],
      },
      tableFrom: {
        page: 1,
        limit: 20
      },
      
    }
  },
  mounted() {
    this.getFormSelect()
    this.getList()
  },
  methods: {
    // 获取系统表单下拉数据
    getFormSelect(){
      sysFormSelect().then((res) => {
        this.formList = res.data
      })
      .catch((res) => {
        this.$message.error(res.message)
      })
    },
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      activityList(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
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
    closeDrawer() {
      this.drawer = false;
    },
    // 添加
    onAdd() {
      this.$refs.createApplication.dialogVisible = true;
      // this.$refs.createApplication.resetData();
    },
    // 查看
    onDetails(id, isEdit){
      this.drawer = true;
      this.applyId = id;
      this.$refs.applicationDetails.getInfo(id,isEdit)
     
    },
    // 编辑
    onEdit(id) {
      this.drawer = true;
      this.applyId = id;
      this.$refs.applicationDetails.getInfo(id, true)
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure('删除后不可恢复，确定删除该活动').then(() => {
        activityDeleteApi(id).then(({ message }) => {
          this.$message.success(message)
          this.getList()
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
    // 修改状态
    onchangeIsShow(row) {
      activityStatusApi(row.activity_id, row.is_show)
        .then(({ message }) => {
          this.$message.success(message);
          this.getList('');
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
  }
}
</script>

<style scoped lang="scss">
</style>
