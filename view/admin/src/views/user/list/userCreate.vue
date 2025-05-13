<template>
  <div>
    <el-drawer
      :with-header="false"
      :size="800"
      :visible.sync="createDrawer"
      direction="rtl"
      :before-close="handleClose"  
    >
      <div>
        <div class="head">
          <div class="title">用户信息填写</div>
        </div>
        <div class="demo-drawer__content">
          <el-row class="mt20">
            <el-col :span="18">
              <el-form
                ref="userField"
                size="small"
                :rules="ruleValidate"
                :model="userInfo"
                label-width="130px"
                @submit.native.prevent
                >
                <el-form-item label="手机号(账号)：" prop="account">
                  <el-input
                    type="text"
                    size="small"
                    placeholder="请输入手机号(账号)"
                    v-model="userInfo.account"
                  />
                </el-form-item>
                <el-form-item label="登录密码：" prop="pwd">
                  <el-input
                    type="password"
                    size="small"
                    minlength="6"
                    placeholder="请输入密码"
                    v-model="userInfo.pwd"
                  />
                </el-form-item>
                <el-form-item label="确认密码：" prop="repwd">
                  <el-input
                    type="password"
                    size="small"
                    minlength="6"
                    placeholder="请再次输入密码"
                    v-model="userInfo.repwd"
                  />
                </el-form-item>
                <el-form-item label="用户昵称：" prop="nickname">
                  <el-input
                    type="text"
                    size="small"
                    placeholder="请输入用户昵称"
                    v-model="userInfo.nickname"
                  />
                </el-form-item>
                <el-form-item label="头像：">
                  <div
                    class="upLoadPicBox"
                  >
                    <div v-if="userInfo.avatar" class="pictrue">
                      <img :src="userInfo.avatar">
                      <i
                      class="el-icon-error btndel"
                      @click="userInfo.avatar=''"
                  />
                    </div>
                    <div v-else class="upLoad" @click="modalPicTap('1')">
                      <i class="el-icon-camera cameraIconfont" />
                    </div>
                  </div>
                </el-form-item>
                <el-form-item label="真实姓名：">
                  <el-input
                    type="text"
                    size="small"
                    placeholder="请输入真实姓名"
                    v-model="userInfo.real_name"
                  />
                </el-form-item>
                <el-form-item label="手机号：">
                  <el-input
                    type="text"
                    placeholder="请输入手机号"
                    size="small"
                    v-model="userInfo.phone"
                  />
                </el-form-item>
                <el-form-item label="身份证：" prop="card_id">
                  <el-input
                    type="text"
                    placeholder="请输入身份证号码"
                    size="small"
                    v-model="userInfo.card_id"
                  />
                </el-form-item>
                <!-- <el-form-item label="生日：" prop="birthday">
                  <el-date-picker
                    size="small"
                    v-model="userInfo.birthday"
                    type="date"
                    placeholder="选择日期">
                  </el-date-picker>
                </el-form-item> -->
                <el-form-item label="性别：">
                  <el-radio-group v-model="userInfo.sex">
                    <el-radio :label="0">保密</el-radio>
                    <el-radio :label="1">男</el-radio>
                    <el-radio :label="2">女</el-radio>
                  </el-radio-group>
                </el-form-item>
                <el-form-item label="状态：" required>
                  <el-radio-group v-model="userInfo.status">
                    <el-radio :label="0">禁用</el-radio>
                    <el-radio :label="1">正常</el-radio>
                  </el-radio-group>
                </el-form-item>
                <el-form-item label="推广员：" required>
                  <el-radio-group v-model="userInfo.is_promoter">
                    <el-radio :label="0">关闭</el-radio>
                    <el-radio :label="1">开启</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-form>   
            </el-col>
            <el-col :span="23">
              <div class="demo-drawer__footer">
                <el-button @click="handleClose" size="small">取 消</el-button>
                <el-button type="primary" @click="submitForm('userField')" :loading="loading" size="small">{{ loading ? '提交中 ...' : '确 定' }}</el-button>
              </div>
            </el-col>
          </el-row>
        </div>
       </div>  
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

import { addUserApi } from '@/api/user'
export default {
  name: 'UserCreate',
  props: {
    createDrawer: {
      type: Boolean,
      default: false,
    }, 
  },
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请输入手机号'))
      } else if (!/^1[3456789]\d{9}$/.test(value)) {
        callback(new Error('格式不正确!'))
      } else {
        callback()
      }
    }
    const validateCard = (rule, value, callback) => {
      if (value && !/^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[Xx\d]$/.test(value)) {
        callback(new Error('格式不正确!'))
      } else {
        callback()
      }
    }
    let validatePass2 = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("请再次输入密码"));
      } else if (value !== this.userInfo.pwd) {
        callback(new Error("两次输入密码不一致!"));
      } else {
        callback();
      }
    };
    return {
      loading: false,
      userInfo: {
        avatar: "",
        sex: 0,
        status: 1,
        is_promoter: 1
      },
      ruleValidate: {
        account: [{ required: true, validator: validatePhone, trigger: 'blur' }],
        phone: [{ validator: validatePhone, trigger: 'blur' }],
        card_id: [{validator: validateCard, trigger: 'blur'}],
        pwd: [{ required: true, message: '请输入密码', trigger: 'blur' }],
        repwd: [{ required: true, validator: validatePass2, trigger: 'blur' }],
      },
    }
  },
  mounted() {},
  methods: {
    handleClose() {
      this.resetData()
      this.$emit('closeDrawer');
    },
    resetData(){
      this.$refs.userField.resetFields()
      this.userInfo.avatar=""
      this.userInfo.phone=""
      this.userInfo.real_name=""
      this.userInfo.card_id=""
      this.userInfo.birthday=""
      this.userInfo.sex = 0
    },
    // 点击商品图
    modalPicTap(tit, num, i) {
      const _this = this;
      this.$modalUpload(function(img) {
        _this.userInfo.avatar = img[0];
      }, tit);
    },
    submitForm(name){
      this.loading = true;
      this.$refs[name].validate(valid => {
        if (valid) {
          addUserApi(this.userInfo).then(async res => {
            this.loading = false;
            this.$message.success(res.message);
            this.$emit('closeDrawer');
            this.$emit('getList');
          })
          .catch(res => {
            this.loading = false;
            this.$message.error(res.message);
          })
        }else{
          this.loading = false;
        }
      });
    }
  }
}
</script>

<style scoped lang="scss">
  .head {
    padding: 15px 20px;
    border-bottom: 1px solid #DCDFE6;
    .title {
      font-weight: bold;
      font-size: 14px;
      line-height: 16px;
      color: rgba(0, 0, 0, 0.85);
    }
  }
  .demo-drawer__footer{
    text-align: right;
    padding: 20px 0;
  }
</style>
