<template>
  <div>
    <el-dialog title="创建报名活动" :visible.sync="dialogVisible" width="650px" :before-close="handleClose" :append-to-body='true'>
      <el-form ref="formValidate" label-width="120px" v-loading="fullscreenLoading" class="formValidate mt20" :rules="ruleValidate" :model="formValidate" size="small" @submit.native.prevent>
        <el-form-item label="活动名称：" prop="activity_name">
          <el-input v-model="formValidate.activity_name" maxlength="50" size="small" placeholder="请输入活动名称" show-word-limit />
        </el-form-item>
        <el-form-item label="活动简介：" prop="info">
          <el-input type="textarea" maxlength="500" v-model="formValidate.info" size="small" :autosize="{ minRows: 4, maxRows: 8}" show-word-limit placeholder="请输入活动简介" />
        </el-form-item>
        <el-form-item label="封面图：" prop="pic">
          <div class="acea-row row-middle">
            <div
              class="upLoadPicBox mr15"
              title="710*340px"
              @click="modalPicTap('1')"
            >
              <div v-if="formValidate.pic" class="pictrue">
                <img :src="formValidate.pic">
              </div>
              <div v-else class="upLoad">
                <i class="iconfont iconjiahao" />
              </div>
            </div>
            <div style="color: #ccc;font-size: 12px;">
              建议尺寸750*350px
              <el-popover
                placement="bottom"
                title=""
                min-width="200"
                trigger="hover"
                >
                <img :src="`${baseURL}/statics/system/application01.jpg`" style="height:270px;" alt="">
                <el-button type="text" slot="reference">查看示例</el-button>
              </el-popover>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="活动分享海报：" prop="images">
          <div class="acea-row row-middle">
            <div class="acea-row">
              <div
                v-for="(item,index) in formValidate.images"
                :key="index"
                class="pictrue" 
              >
                <img :src="item" @click="lookImg(item)">
                <i class="el-icon-error btndel" @click="handleRemove(index)" />
              </div>
              <div v-if="formValidate.images.length < 5" class="upLoadPicBox mr15" @click="modalPicTap('2')">
                <div class="upLoad">
                  <i class="iconfont iconjiahao" />
                </div>
              </div>
            </div>
            <div style="color: #ccc;font-size: 12px;">
              建议尺寸750*1250px
               <el-popover
                placement="bottom"
                title=""
                min-width="200"
                trigger="hover"
                >
                <img :src="`${baseURL}/statics/system/application02.jpg`" style="height:270px;" alt="">
                <el-button type="text" slot="reference">查看示例</el-button>
              </el-popover>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="活动背景色：" prop="color">
          <div class="acea-row">
            <el-color-picker v-model="formValidate.color" class="mr15"></el-color-picker>
            <div style="color: #ccc;font-size: 12px;">
              若未设置颜色，则为默认色
                <el-popover
                  placement="bottom"
                  title=""
                  min-width="200"
                  trigger="hover"
                  >
                  <img :src="`${baseURL}/statics/system/application03.jpg`" style="height:270px;" alt="">
                  <el-button type="text" slot="reference">查看示例</el-button>
                </el-popover>
              </div>
          </div>
        </el-form-item>
        <el-form-item label="活动起止日期：" required>
         <el-date-picker
            v-model="timeVal"
            value-format="yyyy-MM-dd HH:mm:ss"
            format="yyyy-MM-dd HH:mm:ss"
            size="small"
            type="datetimerange"
            placement="bottom-end"
            placeholder="请选择活动时间"
            @change="onchangeTime"
            style="width: 100%;"
          />
        </el-form-item>
        <el-form-item label="活动人数上限：" prop="count">
          <el-input-number
            v-model="formValidate.count"
            placeholder="请输入本次活动总人数"
            controls-position="right"
            style="width: 200px;"
          />
          <span>0为不限制人数</span>
        </el-form-item>
        <el-form-item label="关联系统表单：" prop="form_id">
          <el-select
            v-model="formValidate.form_id"
            @change="getFormInfo"
            class="width100"
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
        <el-form-item v-if="formValidate.form_id">
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
        <el-form-item label="排序：" prop="sort">
          <el-input-number
            v-model="formValidate.sort"
            placeholder="请输入排序序号"
            controls-position="right"
            style="width: 200px;"
          />
        </el-form-item>
        <el-form-item label="是否开启：" prop="is_show">
          <el-switch
            :width="60"
            v-model="formValidate.is_show"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
          />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="handleClose">取 消</el-button>
        <el-button type="primary" size="small" @click="handleSubmit('formValidate')">确 定</el-button>
      </span>
    </el-dialog>
    <div class="images" v-show="false" v-viewer="{ movable: false }">
      <img v-for="(src,index) in formValidate.images" :src="src" :key="index" />
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
import { roterPre } from '@/settings'
import { activityCreate, associatedFormInfo} from "@/api/marketing";
import SettingMer from '@/libs/settingMer'
export default {
  name: 'createActivity',
  props: {
    formList: {
      type: Array,
      default: ()=>[],
    }
  },
  data() {
    return {
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      // baseURL: 'http://localhost:8080',
      roterPre: roterPre,
      dialogVisible: false,
      fullscreenLoading: false,
      listLoading: false,
      formData: [],
      formUrl: "",
      ruleValidate: {
        activity_name: [{ required: true,message: "请输入活动名称", trigger: "blur"}],
        pic: [{ required: true, message: '请上传封面图', trigger: 'change' }],
        images: [{ required: true, message: '请上传海报图', trigger: 'change' }],
        form_id: [{ required: true,message: "请选择关联系统表单", trigger: "change"}],
      },
      formValidate: {
        pic: "",
        images: [],
        
      },
      timeVal: '',
      
    }
  },
  mounted() {

    
  },
  methods: {
    handleClose() {
      this.resetData()
      this.dialogVisible = false
    },
    resetData(){
      this.$refs['formValidate'].resetFields()
      this.timeVal = '';
      this.formValidate.start_time = "";
      this.formValidate.end_time = "";
      this.formData = []
    },
    // 点击商品图
    modalPicTap(tit, num, i) {
      const _this = this;
      this.$modalUpload(function(img) {
        if (tit === "1") {
          _this.formValidate.pic = img[0];
        }
        if (tit === '2') {
          img.map((item) => {
            _this.formValidate.images.push(item)
            if (_this.formValidate.images.length > 5) {
              _this.formValidate.images.length = 5
            }
          })
        }
      }, tit);
    },
    handleRemove(i) {
      this.formValidate.images.splice(i, 1)
    },
    lookImg(src) {
      const viewer = this.$el.querySelector('.images').$viewer;
      viewer.show();
      this.$nextTick(() => {
        let i = this.formValidate.images.findIndex((e) => e === src);
        viewer.update().view(i);
      });
    },
    // 活动起止日期
    onchangeTime(e) {
      this.timeVal = e
      this.formValidate.start_time = e ? e[0] : ''
      this.formValidate.end_time = e ? e[1] : ''
    },

    // 关联的表单信息
    getFormInfo(){
      let time = new Date().getTime() * 1000
      let formUrl = `${this.baseURL}/pages/admin/system_form/index?inner_frame=1&form_id=${this.formValidate.form_id}&time=${time}`;
      this.formUrl = formUrl;
     
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.fullscreenLoading = true
          activityCreate(this.formValidate)
            .then(async(res) => {
              this.fullscreenLoading = false
              this.$message.success(res.message)
              this.dialogVisible = false
              this.$emit('getList')
              this.$refs[name].resetFields()
            })
            .catch((res) => {
              this.fullscreenLoading = false
              this.$message.error(res.message)
            })
        } else {
          
        }
      })
    },
  }
}
</script>

<style scoped lang="scss">
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

</style>
