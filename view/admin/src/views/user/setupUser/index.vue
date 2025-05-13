<template>
  <div>
    <el-card>
       <el-form :model="basicsForm" label-width="120px">
            <el-row :gutter="24">
              <el-col :span="24" class="mt10">
                <el-form-item label="用户默认头像：">
                  <div
                    style="display: inline-block;"
                    @click="modalPicTap('1')"
                  >
                    <div v-if="authorizedPicture" class="uploadPictrue">
                      <img :src="authorizedPicture">
                    </div>
                    <div v-else class="uploadPictrue">
                      <i class="iconfont iconshangpinshuliang-jia" />
                    </div>
                  </div>
                  <div class="upload-text">建议尺寸：120*120px</div>
                </el-form-item>
              </el-col>
              <el-col :span="24" class="mt10">
                <el-form-item label="用户信息设置：">
                  <!-- 用户表格 -->
                  <el-table
                    :data="listOne"
                    ref="table"
                    class="goods"
                    row-key="id"
                    highlight-current-row
                    :draggable="true"
                  >
                    <el-table-column
                      label="#"
                      min-width="50"
                    >
                    <template>
                      <i class="iconfont-diy icondrag"></i>
                    </template> 
                    </el-table-column>
                    <el-table-column
                      label="信息名称"
                      prop="title"
                      min-width="80"
                    />
                    <el-table-column
                      label="使用"
                      min-width="80"
                    >
                      <template slot-scope="scope">
                        <el-checkbox :true-label="1"  :false-label="0" v-model="scope.row.is_used" />
                      </template> 
                    </el-table-column>
                    <el-table-column
                      label="必填"
                      min-width="80"
                    >
                      <template slot-scope="scope">
                        <el-checkbox :true-label="1"  :false-label="0" v-model="scope.row.is_require" :disabled="scope.row.is_used==0" />
                      </template> 
                    </el-table-column>
                    <el-table-column
                      label="用户端显示"
                      min-width="80"
                    >
                      <template slot-scope="scope">
                        <el-checkbox :true-label="1"  :false-label="0" v-model="scope.row.is_show" :disabled="scope.row.is_used==0" />
                      </template> 
                    </el-table-column>
                    <el-table-column
                      label="信息格式"
                      min-width="150"
                    >
                      <template slot-scope="scope">
                        <span>{{scope.row.type_name}}</span>
                      </template> 
                    </el-table-column>
                    <el-table-column
                      label="提示信息"
                      prop="msg"
                      min-width="150"
                    />
                    <el-table-column label="操作" min-width="60" fixed="right">
                      <template slot-scope="scope"> 
                        <el-button type="text" size="small" v-if="scope.row.is_default!=1" @click="delInfo(scope.row.id)">删除</el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                  <div class="upload-text goods">
                   1.开启使用后，后台添加用户时可填写此信息；开启必填后，后台添加用户时此信息必须填写；开启用户端展示后，在商城用户个人信息中展示
                   <br/>
                   2.自定义添加日期和单选格式的字段，暂不支持用户列表搜索，如业务需要建议进一步开发；其它字段均支持用户列表搜索
                  </div>
                  <el-button type="default" size="small" class="addInfo" @click="addModel = true">新增信息</el-button>
                  <div class="mt20">
                    <el-button type="primary" size="small" @click="handleSubmit()">保存</el-button>
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
    </el-card>
    <!-- 新增信息 -->
    <el-dialog
      :visible.sync="addModel"
      title="新增信息"
      class-name="vertical-center-modal"
      scrollable
      width="630px"
      @close="cancelSubmit"
    >
      <el-form
        ref="formValidate"
        :model="formItem"
        :rules="ruleValidate"
        size="small"
        label-width="90px"
      >
        <el-row>
          <el-col>
            <el-form-item label="字段名：" prop="field">
              <el-input
                v-model="formItem.field"
                placeholder="以英文开头的字母、数字、下划线组合，用于代码中筛选信息名称，在后台前端不展示"
                class="width100"
              />
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="信息名称：" prop="title">
              <el-input
                v-model="formItem.title"
                placeholder="请输入信息名称"
                class="width100"
              />
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="信息格式 ：" prop="type">
              <el-select v-model="formItem.type" class="width100">
                <el-option
                  v-for="item in formatList"
                  :value="item.value"
                  :label="item.label"
                  :key="item.value"
                >
                  {{ item.label }}
                </el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item
              label="单选项 ："
              prop="content"
              v-if="formItem.type === 'radio'"
            >
              <div class="arrbox">
                <el-tag
                  @close="handleClose"
                  :name="item"
                  :closable="true"
                  v-for="(item, index) in formItem.content"
                  :key="index"
                >
                  {{ item }}
                </el-tag>
                <input
                  size="small"
                  class="arrbox_ip width100"
                  v-model="single"
                  placeholder="请输入选项，回车确认"
                  @keyup.enter="addlabel"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="提示文案：" prop="msg">
              <el-input
                v-model="formItem.msg"
                placeholder="请输入提示文案"
                class="width100"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="cancelSubmit">取 消</el-button>
        <el-button size="small" type="primary" @click="addSubmit">确 定</el-button>
      </span>
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
import { settingUser, addSetting, setUser, userSetDelApi } from '@/api/user.js'
import Sortable from 'sortablejs'
export default {
  name: 'setupUser',
  components: {},
  props: {},
  data() {
    const validatorSingle = (rule, value, callback)=>{
      if(value.length<2){
        callback(new Error('单选项最少输入2个'));
      }else{
        callback();
      }
    };
    const validatorActive = (rule, value, callback)=>{
      if(value===""||value == null || value<0){
        callback(new Error('活动价不能为空'));
      }else{
        callback();
      }
    }; 
    const validatorFiled = (rule, value, callback)=>{
      if(value===""||value == null||!value){
        callback(new Error('字段名不能为空'));
      } else if (!/^[a-z][a-z0-9_]*$/.test(value)) {
        callback(new Error('格式不正确!'))
      } else{
        callback();
      }
    };
    return {
      paySwitch: 1,
      phoneSwitch: 1,
      indexCoupon: 0,
      val: '',
      formActive:{
        activeInput: 0
      },
      basicsForm: {},
      selectArr: [],
      value: '',
      formItem: {
        title: '',
        type_name: '',
        msg: '',
        content: [],
      },
      single: '',
      activityShow: false,
      isChoice: '单选',
      modalPic: false,
      loading: false,
      addModel: false,
      authorizedPicture: '', // 图片
      isShow: false,
      formatList: [
        {
          value: 'input',
          label: '文本',
        },
        {
          value: 'int',
          label: '数字',
        },
        {
          value: 'date',
          label: '日期',
        },
        {
          value: 'radio',
          label: '单选项',
        },
        {
          value: 'id_card',
          label: '身份证',
        },
        {
          value: 'email',
          label: '邮件',
        },
        {
          value: 'phone',
          label: '手机号',
        },
        {
          value: 'address',
          label: '地址',
        },
      ],
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      listOne: [],
      tableData: [],
      ruleValidate: {
        field: [
          {
            required: true,
            validator: validatorFiled,
            trigger: 'blur',
          },
        ],
        title: [
          {
            required: true,
            message: '信息名称不能为空',
            trigger: 'blur',
          },
        ],
        type: [
          {
            required: true,
            message: '信息格式不能为空',
            trigger: 'blur',
          },
        ],
        msg: [
          {
            required: true,
            message: '信息文案不能为空',
            trigger: 'blur',
          },
        ],
        content: [
          { required: true, validator:validatorSingle,type: 'array', trigger: 'blur' },
        ],
      },
      ruleActive:{
        activeInput: [
          {
            required: true,
            validator:validatorActive,
            trigger: 'blur'
          },
        ]
      },
      couponType: 0,
      vipCopon: [],
    }
  },
  computed: {},
  created() {

    this.settingUser()
  },
  mounted() {
    this.$nextTick(()=>{
      this.setSort()
    })
  },
  methods: {
    setSort() {
      // 表格中需要实现行拖动，所以选中tr的父级元素
      const table = document.querySelector('.el-table__body-wrapper tbody')
      const self = this
      Sortable.create(table, {
        onEnd({ newIndex, oldIndex }) {
          console.log(newIndex, oldIndex)
          const targetRow = self.listOne.splice(oldIndex, 1)[0]
          self.listOne.splice(newIndex, 0, targetRow)
        }
      })
    },  
    elChangeExForArray(index1, index2, array) {
      const temp = array[index1]
      array[index1] = array[index2]
      array[index2] = temp
      return array
    },
    // 获取用户配置
    settingUser() {
      settingUser().then((res) => {
        this.authorizedPicture = res.data.avatar
        this.listOne = res.data.list
      })
    },
    cancel(name){
      this.activityShow = false
      this.$refs[name].resetFields();
    },
    // 选择图片
    modalPicTap() {
      const _this = this;
        this.$modalUpload(function (img) {  
          _this.authorizedPicture = img[0];   
      },'');   
    },
    // 取消新增信息
    cancelSubmit() {
      this.formItem = {
        title: '',
        type_name: '',
        msg: '',
        content: [],
      }
      this.addModel = false
      this.$refs.formValidate.resetFields()
    },
    // 提交信息
    addSubmit() {
      this.$refs.formValidate.validate((valid) => {
        let obj = {
          ...this.formItem,
          is_required: 0,
          is_used: 0,
          is_show: 0,
        };
        switch (obj.type) {
          case 'input':
            obj.type_name = '文本';
            break;
          case 'int':
            obj.type_name = '数字';
            break;
          case 'date':
            obj.type_name = '日期';
            break;
          case 'radio':
            obj.type_name = '单选项';
            break;
          case 'id_card':
            obj.type_name = '身份证';
            break;
          case 'email':
            obj.type_name = '邮件';
            break;
          case 'phone':
            obj.type_name = '手机号';
            break;
          case 'address':
            obj.type_name = '地址';
            break;
        }
        let labelName = [];
        this.listOne.forEach(item=>{
          labelName.push(item.info)
        });
        if (valid) {
          if(labelName.indexOf(obj.title) == -1){
            // this.listOne.push(obj)
            addSetting(obj).then((res) => {
              this.$message.success(res.message)
              this.settingUser()
            }).catch((err) => {
              this.$message.error(err.message)
            })
            this.cancelSubmit();
          }else{
            this.$message.error('该信息已经添加过')
          }
        }
      })
    },
    // 信息删除
    delInfo(id) {
      this.$modalSure('确定删除该条数据').then(() => {
        userSetDelApi(id)
          .then(res => {
            this.$message.success(res.message)
            this.settingUser();
          })
          .catch(res => {
            this.$message.error(res.message)
          })
      })
    },
    // 输入后回车
    addlabel() {
      if (!this.single) {
        return
      }
      let count = this.formItem.content.indexOf(this.single)
      if (count === -1) {
        this.formItem.content.push(this.single)
      }
      this.single = ''
    },
    // 表单提交
    handleSubmit() {
     let data = {
        avatar: this.authorizedPicture,
        user_extend_info: this.listOne
      }
      setUser(data).then((res) => {
        this.$message.success(res.message)
      })
    },
    handleClose(event, name) {
      const index = this.formItem.content.indexOf(name)
      this.formItem.content.splice(index, 1)
    },

  },
}
</script>
<style scoped lang="scss">
.span-text {
 margin-left:8px;
 font-size: 12px;
}
.goods{
  .icondrag{
    color: #ccc;
  }
}
::v-deep .el-table__cell{
  line-height: 21px;
  font-size: 13px;
}
::v-deep .el-form-item__label {
  font-size: 12px;
  font-weight: 400;
  color: #333333;
}
.basics {
  width: 76px;
  height: 16px;
  text-align: center;
  margin-top: 10px;
  border-left: 2px solid #2D8CF0;
  line-height: 16px;
  font-size: 14px;
  font-weight: 600;
  color: #333333;
}
.arrbox {
  background-color: white;
  font-size: 12px;
  border: 1px solid #dcdee2;
  border-radius: 6px;
  margin-bottom: 0px;
  padding: 0 5px;
  text-align: left;
  box-sizing: border-box;
}
.arrbox_ip {
  font-size: 12px;
  border: none;
  box-shadow: none;
  outline: none;
  background-color: transparent;
  padding: 0;
  margin: 0;
  max-width: inherit;
  min-width: 80px;
  vertical-align: top;
  height: 30px;
  color: #34495e;
  margin: 2px;
  line-height: 30px;
}
.uploadPictrue {
  width: 60px;
  height: 60px;
  background: #F5F5F5;
  border-radius: 4px;
  border: 1px dashed #DDDDDD;
  text-align: center;
  line-height: 60px;
  img {
    width: 100%;
    height: 100%;
  }
}
.upload-text {
  font-size: 12px;
  line-height: 16px;
  font-weight: 400;
  color: #CCCCCC;
  margin-top: 6px;
}
.addInfo {
  width: 78px;
  height: 32px;
  border-radius: 4px;
  border: 1px solid rgba(151, 151, 151, 0.36);
  text-align: center;
  line-height: 32rpx;
  font-size: 12px;
  font-weight: 400;
  color: rgba(0, 0, 0, 0.85);
  margin-top: 20px;
  cursor:pointer;
}
.footer {
  width: 100%;
  height: 65px;
  background: #FFFFFF;
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 10;
  .btn {
    margin-left: 40%;
  }
}
</style>
