<template>
  <div>
    <el-drawer
      :with-header="false"
      :size="1000"
      :visible.sync="drawer"
      :direction="direction"
      :before-close="handleClose" 
    >
      <div v-loading="loading">
        <div v-if="!isAdd" class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">
                <span class="bold">{{applyData.activity_name}}</span>
              </div>
              <div>
                <span class="mr20">活动ID：{{applyData.activity_id}}</span>
              </div>
            </div>
            <div>
              <el-button
                v-if="isEdit"
                size="small"
                @click="cancelEdit"
                >取消</el-button
              >
              <el-button 
                v-if="!isEdit"
                type="primary"
                size="small"
                @click="onEdit"
                >编辑</el-button
              >
              <el-button
                v-if="isEdit"
                type="success"
                size="small"
                @click="saveInfo"
                >完成</el-button
              >
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">报名状态</div>
              <div>{{applyData.time_status == 0 ? '未开始' : applyData.status == 1 ? '进行中' : '已结束'}}</div>
            </li>
            <li class="item">
              <div class="title">已报名人数</div>
              <div>{{ applyData.total }}</div>
            </li>
            <li class="item">
              <div class="title">活动人数上限</div>
              <div>{{ applyData.count == 0 ? '不限制' : applyData.count }}</div>
            </li>
            <li class="item">
              <div class="title">创建时间</div>
              <div>{{ applyData.create_time }}</div>
            </li>
          </ul>
        </div>
        <!--详情-->
        <application-info ref="applicationInfo" @editSuccess="editSuccess" :applyData="applyData" :form_name="form_name" :isEdit="isEdit" :formList="formList" :formData="formData"></application-info>
      </div>
      <!-- <div v-if="isAdd" class="footer">
        <el-button size="small" @click="handleClose">取消</el-button>
        <el-button type="primary" size="small" @click="submitInfo">提交</el-button>
      </div>  -->
    </el-drawer>
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
import { activityDetail, sysFormSelect, associatedFormInfo } from "@/api/marketing";
import applicationInfo from './applicationInfo';
export default {
  props: {
    drawer: {
      type: Boolean,
      default: false,
    },
  },
  components: { applicationInfo },
  data() {
    return {
      loading: false,
      applyId: '',
      isEdit: false,
      isAdd: false,
      direction: 'rtl',
      activeName: 'detail',
      form_name: "",
      applyData: {},
      orderImg: require('@/assets/images/store_icon.png'),
      timeVal: [],
      formList: [],
      formData: []
    };
  },
  filters: {
  },
  methods: {
    handleClose() {
      if(this.isEdit || this.isAdd) {
        this.$refs.applicationInfo.resetData()
      }else{
        this.$refs.applicationInfo.activeName = 'detail';
      }
      this.$emit('closeDrawer');
    },
    getInfo(id, isEdit) { 
      this.applyId = id
      this.isAdd = false;
      activityDetail(id)
        .then((res) => {
          this.loading = false;
          this.applyData = res.data;
          this.$nextTick(()=>{
            this.isEdit = isEdit
            this.$refs.applicationInfo.statisticsForm.keyword = "";
            this.$refs.applicationInfo.activeName = 'detail';
            if(!isEdit){
              this.getFormSelect();
              this.$refs.applicationInfo.getStatistics(this.applyId);
              this.$refs.applicationInfo.getForm();
            }else{
              this.$refs.applicationInfo.getFormData();
            }
            
          })
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 获取系统表单下拉数据
    getFormSelect(){
      sysFormSelect().then((res) => {
        this.formList = res.data
        for(var i = 0; i < this.formList.length; i++){
          if(this.applyData.link_id == this.formList[i].form_id){
            this.form_name = this.formList[i].name
            return 
          }
        } 
      })
      .catch((res) => {
        this.$message.error(res.message)
      })
    },
    // 关联的表单信息
    getFormInfo(){
      associatedFormInfo(this.applyData.link_id,{mer_id:0}).then((res) => {
        this.formData = res.data
      })
      .catch((res) => {
        this.$message.error(res.message)
      })
    },
    onEdit(){
      this.isEdit = true; 
      this.$nextTick(()=>{
        this.$refs.applicationInfo.getFormData();
      })
      
    },
    cancelEdit() {
      this.isEdit = false
      this.getInfo(this.applyId);
    },
    // 编辑成功回调
    editSuccess(){
      setTimeout(()=>{
        this.isEdit = false;
        this.$emit('getList')
        this.getInfo(this.applyId);
      },500) 
    },
     //下拉
    handleCommand() {
      this.$emit('onPassword',this.applyId);
    },
    saveInfo(){
      this.$refs.applicationInfo.onSubmit(this.applyId);
    },
    submitInfo(){
      // this.$refs.editForm.handleCreate();
    }
  },
};
</script>
<style lang="scss" scoped>
.head {
  padding: 20px 35px;
  .full {
    display: flex;
    align-items: center;
    .order_icon {
      width: 60px;
      height: 60px;
    }
    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;
      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: #282828;
      }
    }
  }
  .bold{
    font-weight: bold;
  }
  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;
    .item {
      flex: none;
      width: 200px;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);
      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }
    }
  }
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
  }
  .item {
    flex: 0 0 calc(100% / 2);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    &:nth-child(2n + 1) {
      padding-right: 20px;
      padding-left: 20px;
    }
    &:nth-child(2n) {
     padding-right: 20px;
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
.footer{
  width: 100%;
  text-align: center;
  position: absolute;
  bottom: 17px;
  padding-top: 17px;
  border-top: 1px dashed #eeeeee;
}
</style>
