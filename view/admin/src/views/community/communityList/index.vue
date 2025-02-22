<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" inline size="small" label-width="85px">
        <el-form-item label="审核状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in statusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="分类名称：" prop="category_id">
          <el-select
            v-model="tableFrom.category_id"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option
              v-for="item in cateSelect"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="话题名称：" prop="topic_id">
          <el-select
            v-model="tableFrom.topic_id"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
            >
            <el-option
              v-for="item in topicSelect"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="是否显示：" prop="is_show">
          <el-select
            v-model="tableFrom.is_show"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option label="显示" value="1"/>
            <el-option label="不显示" value="0"/>
          </el-select>
        </el-form-item>
        <el-form-item label="作者：" prop="username">
          <el-input v-model="tableFrom.username" @keyup.enter.native="getList(1)" placeholder="请输入文章作者" clearable class="selWidth" />
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input v-model="tableFrom.keyword" @keyup.enter.native="getList(1)" placeholder="请输入文章标题" clearable class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <div class="mb5">
        <el-tabs v-if="headeNum.length > 0" v-model="tableFrom.is_type" @tab-click="getList(1)">
          <el-tab-pane v-for="(item,index) in headeNum" :key="index" :name="item.type.toString()" :label="item.title +'('+item.count +')' " />
        </el-tabs>
      </div>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column
          label="ID"
          prop="community_id"
          min-width="100" />       
        <el-table-column
          label="标题"
          prop="title"
          min-width="100" />
        <el-table-column
          label="作者"
          prop="author.nickname"
          min-width="100" />
        <el-table-column :label="tableFrom.is_type == 1 ? '图文封面' : '视频封面图'" min-width="210">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                v-for="(item,index) in scope.row.image"
                :key="index"
                :src="item"
                class="mr5"
                :preview-src-list="[item]"
              />
            </div>
          </template>
        </el-table-column>  
        <el-table-column label="推荐级别"  min-width="150">
          <template slot-scope="scope">
            <el-rate
            disabled
            v-model="scope.row.start"
            :colors="colors">
           </el-rate>
          </template>
        </el-table-column> 
         <el-table-column
          prop="pv"
          :label="tableFrom.is_type == 1 ? '浏览量' : '播放量'"
          min-width="100"
        />   
        <el-table-column
          prop="count_start"
          label="点赞数"
          min-width="100"
        />
        <el-table-column
          prop="count_reply"
          label="评论数"
          min-width="100"
        />
        <el-table-column
          prop="create_time"
          label="发布时间"
          min-width="100"
        />     
        <el-table-column
          prop="status"
          label="是否显示"
          min-width="100"
        >
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.is_show"
              :active-value="1"
              :inactive-value="0"
              active-text="显示"
              inactive-text="隐藏"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column
          label="状态"
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
        
        <el-table-column label="操作" min-width="200" fixed="right">
          <template slot-scope="scope">
             <el-button type="text" size="small" @click="onDetail(scope.row.community_id)">详情</el-button>
            <el-button v-if="scope.row.status == 0 || scope.row.status == -2" type="text" size="small" @click="onAudit(scope.row.community_id)">审核</el-button>
            <el-button type="text" size="small" @click="onEdit(scope.row.community_id)">编辑星级</el-button>
            <el-button v-if="scope.row.status == 1" type="text" size="small" @click="onOff(scope.row.community_id)">强制下架</el-button>
            <el-button type="text" size="small" @click="onReply(scope.row.community_id)">查看评论</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.community_id, scope.$index)">删除</el-button>
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
    <!--审核-->
    <el-dialog v-if="examineVisible" title="审核" :visible.sync="examineVisible" width="600px">
      <div v-loading="loading">
       <el-form ref="ruleForm" :model="ruleForm" :rules="rules" size="small" label-width="90px" class="demo-ruleForm">
        <el-form-item label="审核状态：" prop="status">
          <el-radio-group v-model="ruleForm.status">
            <el-radio :label="1">通过</el-radio>
            <el-radio :label="-1">拒绝</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-if="ruleForm.status===-1" label="原因" prop="refusal">
          <el-input v-model="ruleForm.refusal" type="textarea" placeholder="请输入原因" />
        </el-form-item>
        <el-form-item>
          <div style="text-align: right;">
            <el-button @click="examineVisible=false">取消</el-button>
            <el-button type="primary" @click="onSubmit">提交</el-button>
          </div>
        </el-form-item>
      </el-form>
      </div>
    </el-dialog>
    <!--详情-->
    <el-dialog v-if="dialogVisible" title="详情" :visible.sync="dialogVisible" width="650px" custom-class="dialog-scustom">
      <div v-loading="loading">
         <div class="box-container">
          <div class="basic-info">
            <div class="title">基本信息</div>
            <div class="acea-row">
              <div class="list sp100">
                <label class="name">{{formData.is_type == 1 ? '图文封面：' : '视频封面：'}}</label>
                <div>
                  <span v-for="(item, index) in formData.image" :key="index">
                    <el-image
                      style="width: 60px;height: 60px;margin-right: 10px;"
                      :src="item || ''"
                      :preview-src-list="[item?item:'']"
                    />
                  </span>
                </div>
              </div>
              <div v-if="formData.is_type == 2" class="list sp100">
                <label class="name">短视频：</label>
                <span class="video-img" @click="showVideo=true">
                  <el-image
                    style="width: 60px;height: 60px;"
                    :src="formData.image[0]"
                  />
                  <span>
                    <i class="el-icon-caret-right"></i>
                  </span>
                </span>
              </div>
              <div class="list sp100">
                <label class="name">文章内容：</label>
                <div>{{formData.content}}</div>
              </div>   
            </div>
          </div>
          <div class="title" style="margin-top: 20px;">内容信息：</div>
          <div class="acea-row">   
            <div class="list sp">
              <label class="name">{{formData.type == 1 ? '浏览量：' : '播放量：'}}</label>
              <div>{{ formData.pv }}</div>
            </div>  
            <div class="list sp">
              <label class="name">话题：</label>
              {{ (formData.topic&&formData.topic.topic_name) || '-' }}
            </div>
            <div class="list sp">
              <label class="name">作者：</label>
              {{ (formData.author && formData.author.nickname) || '-' }}
            </div>
            <div class="list sp">
              <label class="name">作者ID：</label>
              {{ (formData.author && formData.author.uid) || '-' }}
            </div>
            <div class="list sp">
              <label class="name">发布时间：</label>
              {{ formData.create_time }}
            </div>
            <div v-if="formData.status==1" class="list sp">
              <label class="name">审核通过时间：</label>
              {{ formData.status_time }}
            </div>
            <div v-if="formData.status==1" class="list sp100">
              <label class="name">关联商品：</label>
              <div v-for="(item, index) in formData.relevance" :key="index" style="display: inline-block;">
                <el-image
                    style="width: 60px; height: 60px；margin-right: 4px;"
                    :src="item.spu.image || ''"
                    :preview-src-list="[item.spu&&item.spu.image]"
                  />
              </div>
            </div>
          </div>
        </div>
      </div>
    </el-dialog>
    <el-dialog v-if="showVideo" title="视频内容" :visible.sync="showVideo" width="680px">
      <div v-if="showVideo" class="video-container">
        <video style="width:600px;max-height: 500px;border-radius:10px;" :src="formData.video_link" autoplay controls="controls">
        您的浏览器不支持 video 标签。
        </video>
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
import {
  communityListApi, communityStatusApi, communityUpdateApi, communityAuditApi, communityDetailApi, communityDeleteApi, communityOffApi,
   communityCateOptionApi, communityTopicOptionApi, communityTitleApi
} from '@/api/community'
export default {
  name: 'communityTopic',
  data() {
    return {
      moren: require("@/assets/images/f.png"),
      colors: ['#99A9BF', '#F7BA2A', '#FF9900'],
      isChecked: false,
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        is_type: '1',
        status: "",
        keyword: "",
        username: "",
        category_id: "",
        topic_id: ""
      },
      statusList: [
        { label: "审核通过", value: 1 },
        { label: "待审核", value: 0 },
        { label: "审核未通过", value: -1 },
        { label: "下架", value: -2 },
      ],
      headeNum: [
        {title: "图文内容", count: 18, type: 1},
        {title: "短视频内容", count: 30, type: 2},
      ],
      cateSelect: [],
      topicSelect: [],
      dialogVisible: false,
      examineVisible: false,
      loading: false,
      isExamine: false,
      showVideo: false,
      community_id: "",
      formData: {},
      rules: {
        status: [
          { required: true, message: '请选择审核状态', trigger: 'change' }
        ],
        refusal: [
          { required: true, message: '请填写拒绝原因', trigger: 'blur' }
        ]
      },
      ruleForm: {
        refusal: '',
        status: 1,
      },
    }
  },
  mounted() {
    this.getList(1)
    this.getCateSelect()
    this.getTopicSelect()
    this.getHeaderData()
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 分类列表；
    getCateSelect() {
      communityCateOptionApi()
        .then((res) => {
          this.cateSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
     // tab栏标题
    getHeaderData() {
      communityTitleApi()
        .then((res) => {
          this.headeNum = res.data;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 话题列表；
    getTopicSelect() {
      communityTopicOptionApi()
        .then((res) => {
          this.topicSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page
      communityListApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    },
    // 编辑
    onEdit(id) {
      this.$modalForm(communityUpdateApi(id)).then(() => this.getList(''))
    },
    // 查看评论
    onReply(id) {
      this.$router.push({
        path: "reply",
        query: {
          community_id: id,
        },
      });
    },
    // 审核
    onAudit(id) {
      this.community_id = id;
      this.examineVisible = true;
    },
    // 详情
    onDetail(id) {
      this.community_id = id;
      this.dialogVisible = true;
      communityDetailApi(id).then((res) => {
        this.formData = res.data
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
    // 强制下架
    onOff(id){
      this.$modalForm(communityOffApi(id)).then(() => this.getList(''))
    },
    onSubmit() {
      communityAuditApi(this.community_id,this.ruleForm).then(res => {
        this.$message.success(res.message)
        this.examineVisible = false
        this.getList('')
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure('确定删除该内容').then(() => {
        communityDeleteApi(id).then(({ message }) => {
          this.$message.success(message)
          this.getList('')
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
    onchangeIsShow(row) {
      communityStatusApi(row.community_id, row.is_show).then(({ message }) => {
        this.$message.success(message)
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    }
  }
}
</script>

<style scoped lang="scss">
.box-container {
  overflow: hidden;
}
.box-container .title{
  color: #17233d;
  font-size: 14px;
  font-weight: bold;
  line-height: 15px;
  padding-left: 5px;
  border-left: 3px solid var(--prev-color-primary);
}
.box-container .list {
  margin-top: 15px;
  font-size: 13px;
}
.box-container .sp {
  flex: 0 0 calc(100% / 2);
  display: flex;
}
.box-container .sp100 {
  flex: 0 0 calc(100% / 1);
  display: flex;
}
.box-container .basic-info{
  padding-bottom: 14px;
  border-bottom: 1px solid #F5F5F5;
}
.box-container .list .name {
  display: inline-block;
  width: 100px;
  text-align: right;
  color: #606266;
}
.box-container .basic-info .name{
  width: 80px;
}
.video-img{
  position: relative;
  width: 60px;
  height: 60px;
  &::before{
    content: "";
    width: 60px;
    height: 60px;
    background: rgba(0,0,0,.1);
    position: absolute;
    top: 0;
    left: 0;
    z-index: 10;
    cursor: pointer;
  }
  span{
    width: 16px;
    height: 16px;
    background: rgba(0,0,0,.5);
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 22px;
    left: 22px;
    z-index: 12;
    .el-icon-caret-right{
      color: #ffffff;
      font-size: 16px;
    }
  }
}
::v-deep .el-form-item__content .el-rate{
  position: relative;
  top: 8px;
}
</style>
