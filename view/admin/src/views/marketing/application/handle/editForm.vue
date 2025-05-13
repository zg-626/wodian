<template>
  <el-form
    ref="applyDataField"
    size="small"
    :rules="ruleValidate"
    :model="applyData"
    label-width="100px"
    @submit.native.prevent
    >
    <el-row :gutter="24" class="mt20">
      <el-col :span="12">
        <el-form-item label="活动名称：" prop="activity_name">
          <el-input 
           v-model="applyData.activity_name" 
           maxlength="50" 
           placeholder="请输入活动名称" 
           show-word-limit />
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item label="起止时间：" required>
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy-MM-dd HH:mm:ss"
            format="yyyy-MM-dd HH:mm:ss"
            type="datetimerange"
            placement="bottom-end"
            placeholder="请选择活动时间"
            @change="onchangeTime"
          />
        </el-form-item>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="12">
        <el-form-item label="活动简介：">
          <el-input type="textarea" maxlength="500" v-model="applyData.info" :autosize="{ minRows: 4, maxRows: 8}" show-word-limit placeholder="请输入活动简介" />
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item label="背景色：">
          <el-color-picker v-model="applyData.color"></el-color-picker>
        </el-form-item>
      </el-col>
    </el-row>
    <el-row>
      <el-form-item label="封面图：" prop="pic">
        <div
        class="upLoadPicBox"
        title="710*340px"
        @click="modalPicTap('1')"
      >
        <div v-if="applyData.pic" class="pictrue">
          <img :src="applyData.pic">
        </div>
        <div v-else class="upLoad">
          <i class="el-icon-camera" />
        </div>
      </div>
      <div style="color: #ccc;font-size: 12px;">
        建议尺寸750*350px
      </div>
      </el-form-item>
    </el-row>
    <el-row>
      <el-form-item label="分享海报：" prop="images">
        <div class="acea-row">
          <div
            v-for="(item,index) in applyData.images"
            :key="index"
            class="pictrue" 
          >
            <img :src="item" @click="lookImg(item)">
          <i class="el-icon-error btndel" @click="handleRemove(index)" />
          </div>
          <div v-if="applyData.images.length < 5" class="upLoadPicBox" @click="modalPicTap('2')">
            <div class="upLoad">
              <i class="el-icon-camera" />
            </div>
          </div>
        </div>
        <div style="color: #ccc;font-size: 12px;">
        建议尺寸750*1250px
      </div>
      </el-form-item>
    </el-row>
    <el-row>
      <el-col :span="12">
        <el-form-item label="排序：">
          <el-input-number
            v-model="applyData.sort"
            controls-position="right"
            placeholder="请输入排序"
          />
        </el-form-item>
      </el-col>
      <el-col :span="12">
       <el-form-item label="活动人数：">
          <el-input-number
            v-model="applyData.count"
            controls-position="right"
            placeholder="请输入本次活动总人数"
          />
          <span>0为不限制人数</span>
        </el-form-item>
      </el-col>
    </el-row>
    <el-row>
      <el-form-item label="是否显示：">
        <el-switch
          v-model="applyData.is_show"
          :active-value="1"
          :inactive-value="0"
          :width="55"
          active-text="开启"
          inactive-text="关闭"
        />
      </el-form-item>
    </el-row>
    <el-row>
      <el-col :span="12">
        <el-form-item label="关联表单：" prop="link_id">
          <el-select
            v-model="applyData.link_id"
            @change="getFormInfo"
            placeholder="请选择"
            clearable
          >
            <el-option
              v-for="item in formList"
              :key="item.form_id"
              :label="item.name"
              :value="item.form_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item v-if="applyData.link_id">
          <div style="width: 350px;">
            <iframe id="iframe" class="iframe-box" :src="formUrl" frameborder="0" ref="iframe" style="min-height: 300px;"></iframe>
          </div>
          <!-- <el-table
            v-loading="listLoading"
            :data="formData"
            class="specsList"
            size="small"
            highlight-current-row
          >
            <el-table-column prop="label" label="表单标题" min-width="100" />
            <el-table-column prop="type" label="表单类型" min-width="100">
              <template slot-scope="{row}">
                <span>{{row.type | formTypeFilter}}</span>
              </template>
            </el-table-column>
            <el-table-column min-width="100" label="是否必填">
              <template slot-scope="{row}">
                <span>{{row.val ? '必填'  : '不必填'}}</span>
              </template>
            </el-table-column>
          </el-table> -->
        </el-form-item>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="12">
        <el-form-item label="更新时间：">
          {{applyData.update_time}}
        </el-form-item>
      </el-col>
    </el-row>
    <div class="images" v-show="false" v-viewer="{ movable: false }">
      <img v-for="(src,index) in applyData.images" :src="src" :key="index" />
    </div> 
</el-form> 
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
import { activityUpdateApi, sysFormSelect, associatedFormInfo } from "@/api/marketing";
import SettingMer from '@/libs/settingMer'
export default {
  props: {
    applyData: {
      type: Object,
      default: {},
    },
    isAdd: {
      type: Boolean,
      default: false,
    },

  }, 
  data() {
    return {
      loading: false,
      listLoading: false,
      applyId: "",
      direction: 'rtl',
      activeName: 'detail',
      formUrl: "",
      // BaseURL: 'http://localhost:8080',
      BaseURL: SettingMer.httpUrl || 'http://localhost:8080',
      timeVal: [this.applyData.start_time,this.applyData.end_time],
      ruleValidate: {
        activity_name: [{ required: true,message: "请输入活动名称", trigger: "blur"}],
        link_id: [{ required: true, message: '请选择关联表单', trigger: 'change' }],
        pic: [{ required: true, message: '请上传封面图', trigger: 'change' }],
        images: [{ required: true, message: '请上传海报图', trigger: 'change' }],
        
      },
      formList: [],
      formData: []
    };
  },
  watch: {
    applyData: function(n) {
      this.timeVal = [n.start_time,n.end_time]
    },
  },
  filters: {
  },
  mounted() {  
    let time = new Date().getTime() * 1000
    let formUrl = `${this.BaseURL}/pages/admin/system_form/index?inner_frame=1&time=${time}&form_id=${this.applyData.link_id}`;
    this.formUrl = formUrl;
    // window.parent.document.getElementById("iframe").height = document.body.scrollHeight;
  },
  methods: {
    // 活动起止日期
    onchangeTime(e) {
      this.timeVal = e
      this.applyData.start_time = e ? e[0] : ''
      this.applyData.end_time = e ? e[1] : ''
    },
     // 点击商品图
    modalPicTap(tit, num, i) {
      const _this = this;
      this.$modalUpload(function(img) {
        if (tit === "1") {
          _this.applyData.pic = img[0];
        }
        if (tit === '2') {
          img.map((item) => {
            _this.applyData.images.push(item)
            if (_this.applyData.images.length > 5) {
              _this.applyData.images.length = 5
            }
          })
        }
      }, tit);
    },
    handleRemove(i) {
      this.applyData.images.splice(i, 1)
    },
    lookImg(src) {
      const viewer = this.$el.querySelector('.images').$viewer;
      viewer.show();
      this.$nextTick(() => {
        let i = this.applyData.images.findIndex((e) => e === src);
        viewer.update().view(i);
      });
    },
    /**重置表单数据 */
    resetData(){
      this.$refs.applyDataField.resetFields();
    },
     // 获取系统表单下拉数据
    getFormSelect(){
      sysFormSelect().then((res) => {
        this.formList = res.data
      })
      .catch((res) => {
        this.$message.error(res.message)
      })
    },
    // 关联的表单信息
    getFormInfo(){
      if(this.applyData.link_id){
        let time = new Date().getTime() * 1000
        let formUrl = `${this.BaseURL}/pages/admin/system_form/index?inner_frame=1&form_id=${this.applyData.link_id}&time=${time}`;
        this.formUrl = formUrl;
        // associatedFormInfo(this.applyData.link_id,{mer_id:0}).then((res) => {
        //   this.formData = res.data
        // })
        // .catch((res) => {
        //   this.applyData.link_id = ""
        //   this.formData = []
        // })
      }
    },
    /*提交信息*/
    onSubmit(id){
      if(!this.timeVal){
        this.$message.warning('请选择活动时间！');
      }else{
        this.$refs['applyDataField'].validate(valid => {
          if (valid) {
            this.loading = true;
            this.applyData.form_id = this.applyData.link_id
            activityUpdateApi(id,this.applyData)
            .then(async res => {
              this.$message.success(res.message);
              this.loading = false;
              this.$emit('success');
            })
            .catch(res => {
              this.loading = false;
              this.$message.error(res.message);
            });
          } 
       });
      }
    },
    /**创建商户 */
    handleCreate(){
      this.$refs['applyDataField'].validate((valid) => {
        if (valid) {
          merchantCreate(this.applyData)
            .then(async (res) => {
              this.$message.success(res.message);
              this.$emit('success');
            })
            .catch((res) => {
              this.$message.error(res.message);
            });
        } else {
          if(!this.applyData.mer_name)return this.$message.error('请填写基本信息-商户名称');

        }
      });
    }
  },
};
</script>
<style lang="scss" scoped>
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
.info-red{
  color: red;
  font-size: 12px;
}
::v-deep .el-input-number.is-controls-right .el-input__inner{
 padding: 0 40px 0 10px;
}
::v-deep .el-form-item__label{
  font-weight: normal;
  color: #282828;
}
::v-deep .specsList th {
  line-height: 20px!important;
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
.pictrue{
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0,0,0,0.1);
  margin-right: 10px;
  position: relative;
  cursor: pointer;
  img{
    width: 100%;
    height: 100%;
  }
  .btndel {
    position: absolute;
    z-index: 1;
    width: 20px !important;
    height: 20px !important;
    left: 46px;
    top: -4px;
  }
}
::v-deep .el-input .el-input__count .el-input__count-inner{
  background: #ffffff;
}
</style>
 