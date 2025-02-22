<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" :inline="true" size="small" label-width="85px">
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="用户名称：" prop="username">
          <el-input
            v-model="tableFrom.username"
            @keyup.enter.native="getList(1)"
            placeholder="请输入用户名称"
            class="selWidth"
          />
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入评论内容"
            class="selWidth"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="reply_id" label="ID" min-width="50" />
        <el-table-column prop="author.nickname" label="用户名/ID" min-width="150">
          <template slot-scope="{row}">
            <span>{{ row.author.nickname +  ' / '  + row.author.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="community.title" label="文章标题" min-width="150" />
        <el-table-column prop="content" label="评论内容" min-width="150" />
        <el-table-column prop="count_reply" label="评论条数" min-width="100" />
        <el-table-column prop="count_start" label="评论点赞数" min-width="100" />
        <el-table-column label="评论时间" min-width="150" prop="create_time" />
        <el-table-column
          label="审核状态"
          min-width="100"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.status | communityStatus }}</span>
            <span
              v-if="scope.row.status == -1"
              style="display: block; font-size: 12px; color: red;"
            >原因 {{ scope.row.refusal }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button
              v-if="scope.row.hasReply !== null"
              type="text"
              size="small"
              @click="getAllReply(scope.row.reply_id)"
            >查看回复</el-button>
            <el-button
              type="text"
              size="small"
              v-if="scope.row.status == 0"
              @click="handleReview(scope.row.reply_id)"
            >审核</el-button>
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
    <!--所有评论-->
    <el-dialog
      v-if="dialogVisible"
      title="所有评论"
      :visible.sync="dialogVisible"
      width="800px"
      :before-close="handleClose"
    >
      <el-table v-loading="replyLoading" :data="replyData.data" size="small">
        <el-table-column prop="reply_id" label="ID" min-width="50" />
        <el-table-column prop="community.title" label="文章标题" min-width="120" />
        <el-table-column prop="content" label="评论内容" min-width="150" />
        <el-table-column prop="count_reply" label="评论条数" min-width="100" />
        <el-table-column prop="count_start" label="评论点赞数" min-width="100" />
        <el-table-column label="评论时间" min-width="150" prop="create_time" />        
      </el-table>
      <div class="block">
        <el-pagination
          :page-size="replyForm.limit"
          :current-page="replyForm.page"
          layout="prev, pager, next, jumper"
          :total="replyData.total"
          @size-change="replySizeChange"
          @current-change="replyPageChange"
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
import { communityReplyListApi, communityReplyDeleteApi, communityReviewApi } from "@/api/community";
import { fromList } from "@/libs/constants.js";
import timeOptions from '@/utils/timeOptions';
export default {
  name: "communityComment",
  data() {
    return {
      pickerOptions: timeOptions,
      fromList: fromList,
      tableData: {
        data: [],
        total: 0,
      },
      replyData: {
        data: [],
        total: 0,
      },
      listLoading: true,
      replyLoading: true,
      tableFrom: {
        page: 1,
        limit: 20,
        community_id: this.$route.query.community_id ? this.$route.query.community_id : "",
        date: "",
        username: "",
        keyword: "",
      },
      replyForm: {
        page: 1,
        limit: 10,
        pid: ''
      },
      timeVal: [],
      props: {},
      tabClickIndex: '',
      dialogVisible: false,
      reply_id: ""
    };
  },
  mounted() {
    this.getList(1);
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
      this.tableFrom.date = this.timeVal ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList(1);
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      communityReplyListApi(this.tableFrom)
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
      this.$modalSure('删除该评论').then(() => {
        communityReplyDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.tableData.data.splice(idx, 1);
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    // 审核
    handleReview(id){
      this.$modalForm(communityReviewApi(id)).then(() => this.getList(''));
    },
    replyPageChange(page) {
      this.replyForm.page = page;
      this.getReplyList()
    },
    replySizeChange(val) {
      this.replyForm.limit = val;
      this.getReplyList()
    },
    //查看所有评论
    getAllReply(id){
      this.dialogVisible = true
      this.replyForm.pid = id
      this.getReplyList()
    },
    getReplyList(){
      this.replyLoading = true;
      communityReplyListApi(this.replyForm)
        .then((res) => {
          this.replyData.data = res.data.list;
          this.replyData.total = res.data.count;
          this.replyLoading = false;
        })
        .catch((res) => {
          this.replyLoading = false;
          this.$message.error(res.message);
        });
    },
    handleClose(){
      this.dialogVisible = false
    }
  },
};
</script>

<style scoped lang="scss">

</style>
