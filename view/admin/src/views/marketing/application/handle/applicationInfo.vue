<template>
  <div>
    <el-tabs type="border-card" v-model="activeName">
      <el-tab-pane label="活动信息" name="detail">
        <div class="section">
          <div class="title">活动信息</div>
          <ul v-if="!isEdit" class="list">
            <li class="item">
              <div class="item-title">活动名称：</div>
              <div class="value">
                {{applyData.activity_name}}
              </div>
            </li>
            <li class="item">
              <div class="item-title">活动简介：</div>
              <div class="value">{{applyData.info}}</div>
            </li>
            <li class="item">
              <div class="item-title">开始时间：</div>
              <div class="value">{{applyData.start_time}}</div>
            </li>
            <li class="item">
              <div class="item-title">结束时间：</div>
              <div class="value">{{applyData.end_time}}</div>
            </li>
            <li class="item">
              <div class="item-title">排序：</div>
              <div class="value">{{applyData.sort}}</div>
            </li>
            <li class="item">
              <div class="item-title">背景色：</div>
              <div class="value"> <el-color-picker v-model="applyData.color" disabled></el-color-picker></div>
            </li>
            <li class="item item100">
              <div class="acea-row row-middle">
                <div class="item-title">封面图：</div>
                <div class="value">
                  <img :src="applyData.pic" style="width:40px;height:40px;margin-right:12px;"/>
                </div>
              </div>
            </li>
            <li v-if="applyData.images&&applyData.images.length>0" class="item item100">
              <div class="acea-row row-middle">
                <div class="item-title">分享海报：</div>
                <div class="value">
                  <img v-for="(pic,idx) in applyData.images" :key="idx" :src="pic" @click="lookImg(pic)" style="width:40px;height:40px;margin-right:12px;"/>
                </div>
              </div>
            </li>
            <li class="item item100">
              <div class="item-title">关联表单：</div>
              <div class="value">{{form_name}}</div>
            </li>
            <li class="item item100">
              <div class="item-title"></div>
              <div class="value" style="width: 350px;">
                <iframe id="iframe" class="iframe-box" :src="formUrl" frameborder="0" ref="iframe"></iframe>
              </div>
            </li>
            <li class="item item100">
              <div class="item-title">是否显示：</div>
              <div class="value">{{applyData.is_show == 1 ? '显示' : '不显示'}}</div>
            </li>
            <li class="item">
              <div class="item-title">更新时间：</div>
              <div class="value">{{applyData.update_time}}</div>
            </li>
          </ul>
          <div v-else>
            <edit-form ref="editForm" :applyData="applyData" @success="success"></edit-form>
          </div>
        </div>
      </el-tab-pane>
      <el-tab-pane v-if="!isEdit" label="活动统计" name="statistics">
        <el-form size="small" label-width="80px" inline class="mt10">
          <el-form-item label="用户搜索：">
            <el-input
              v-model="statisticsForm.keyword"
              placeholder="请输入用户昵称/ID/手机号搜索"
              class="selWidth"
              size="small"
              clearable
              @keyup.enter.native="getStatistics(applyId,1)"
            />
          </el-form-item>
          <el-button size="small" type="primary" @click="getStatistics(applyId,1)">查询</el-button>
          <el-button size="small" type="primary" icon="el-icon-top" @click="exportStatics">导出</el-button>
        </el-form>
        <el-table :data="tableData.data" size="small">
          <el-table-column prop="order_id" label="序号" min-width="60">
            <template scope="scope">
            <span>{{ scope.$index+(statisticsForm.page - 1) * statisticsForm.limit + 1 }}</span>
          </template>
          </el-table-column>
          <el-table-column label="用户名称/ID" min-width="120">
            <template slot-scope="scope">
              <span>{{ scope.row.nickname+'/'+scope.row.uid }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="phone" label="手机号" min-width="100" />
          <el-table-column v-for="(item, index) in headers" :key="index" :label="item.label" min-width="100">
            <template slot-scope="{ row }">
              <div v-if="item.type=='uploadPicture'">
                <el-image v-for="(img,i) in row.value[item.key]" :key="i" style="width: 36px; height: 36px" :src="img" :preview-src-list="[img]" />
              </div>
              <div v-else-if="item.type=='dateranges'">
                <div v-if="row.value[item.key]">{{row.value[item.key][0]}}至<br/>{{row.value[item.key][1]}}</div>
              </div>
              <div v-else>
              {{row.value[item.key]}}
              </div>
            </template>
          </el-table-column>
          <el-table-column label="提交时间" prop="create_time" min-width="150" />  
        </el-table>
        <div class="block">
          <el-pagination :page-size="statisticsForm.limit" :current-page="statisticsForm.page" layout="prev, pager, next, jumper" :total="tableData.total" @size-change="handleSizeChange" @current-change="pageChange" />
        </div>
      </el-tab-pane>
    </el-tabs>
    <div class="images" v-show="false" v-viewer="{ movable: false }">
      <img v-for="(src,index) in applyData.images" :src="src" :key="index" />
    </div> 
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
import { activityUserStatics, activityStaticsExport } from "@/api/marketing";
import editForm from './editForm';
import createWorkBook from '@/utils/newToExcel.js';
import SettingMer from '@/libs/settingMer'
export default {
  props: {
    applyData: {
      type: Object,
      default: {},
    },
    isEdit:{
      type: Boolean,
      default: false,
    },
    form_name: {
      type: String,
      default: "",
    },
    formData: {
      type: Array,
      default: ()=>[]
    },
    formList: {
      type: Array,
      default: ()=>[]
    },
  },
  components: { editForm },
  data() {
    return {
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      // baseURL: 'http://localhost:8080',
      formUrl: "",
      loading: true,
      applyId: '',
      direction: 'rtl',
      activeName: 'detail',
      headers: [],
      tableData: {
        total: 0,
        data: []
      },
      statisticsForm: {
        page: 1,
        limit: 10,
        keyword: ""
      }
    };
  },
  mounted() {
    // window.parent.document.getElementById("iframe").height = document.body.scrollHeight;
  },
  methods: {
    getStatistics(id,num){
      this.applyId = id
      this.loading = true
      this.statisticsForm.page = num || this.statisticsForm.page
      activityUserStatics(id,this.statisticsForm).then(res => {
        this.headers = res.data.list.length && res.data.list[0]['keys'] || [];
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.loading = false
      }).catch(res => {
        this.loading = false
        this.$message.error(res.message)
      })
    },
    getForm(){
      let time = new Date().getTime() * 1000
      let formUrl = `${this.baseURL}/pages/admin/system_form/index?inner_frame=1&form_id=${this.applyData.link_id}&time=${time}`;
      this.formUrl = formUrl;  
    },
    getFormData(){
      this.$nextTick(()=>{
        this.activeName = 'detail'
        this.$refs.editForm.getFormSelect();
        this.$refs.editForm.getFormInfo();
      }) 
     
    },
     lookImg(src) {
      const viewer = this.$el.querySelector('.images').$viewer;
      viewer.show();
      this.$nextTick(() => {
        let i = this.applyData.images.findIndex((e) => e === src);
        viewer.update().view(i);
      });
    },
    // 编辑成功回调
    success(){
      this.$emit('editSuccess')
    },
    pageChange(page) {
      this.statisticsForm.page = page
      this.getStatistics(this.applyId)
    },
    handleSizeChange(val) {
      this.statisticsForm.limit = val
      this.getStatistics(this.applyId)
    },
    resetData(){
      this.$refs.editForm.resetData();
    },
    onSubmit(id){
      this.$refs.editForm.onSubmit(id);
    },
    //导出
    async exportStatics() {
      let excelData = JSON.parse(JSON.stringify(this.statisticsForm)), data = []
      excelData.page = 1
      let pageCount = 1
      let lebData = {};
      for (let i = 0; i < pageCount; i++) {
        lebData = await this.downData(excelData)
        pageCount = Math.ceil(lebData.count/excelData.limit)
        if (lebData.export.length) {
          data = data.concat(lebData.export)
          excelData.page++
        }  
      }
      createWorkBook(lebData.header, lebData.title, data, lebData.foot,lebData.filename);
      return
    },
    /**资金流水 */
    downData(excelData) {
      return new Promise((resolve, reject) => {
        activityStaticsExport(this.applyId, excelData).then((res) => {
          return resolve(res.data)
        })
      })
    }
  },
};
</script>
<style lang="scss" scoped>
.iframe-box{
  min-height: 300px;
}
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.section {
  padding: 20px 0 8px;
  border-bottom: 1px dashed #eeeeee;
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin-top: 5px;
  }
  .item {
    flex: 0 0 calc(100% / 2);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    padding-right: 20px;
    padding-left: 20px;
    align-items: center;
    .item-title {
      width: 80px;
      text-align: right;
    }
    &.item100{
     flex: 0 0 calc(100% / 1);
     padding-right: 20px;
     padding-left: 20px;
    }
    &:nth-child(2n + 1) {
      padding-right: 20px;
      padding-left: 20px;
    }
  }
  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.info-red{
  color: red;
  font-size: 12px;
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
::v-deep .el-drawer__body {
  overflow: auto;
}
.gary {
  color: #aaa;
}

</style>
