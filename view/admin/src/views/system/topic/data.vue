<template>
  <div class="divBox">
    <el-card>
      <div class="mb20">
        <el-button size="small" type="primary" @click="onAdd">添加专场</el-button>
      </div>
      <el-table
        v-loading="loading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column
          v-for="(item,index) in columns"
          :key="index"
          :prop="item.key"
          :label="item.title"
          :min-width="item.minWidth"
        >
          <template slot-scope="scope">
            <div v-if="['img','image','pic'].indexOf(item.key) > -1 || item.key.indexOf('pic') > -1 || item.key.indexOf('img') > -1 || item.key.indexOf('image') > -1 || item.key.indexOf('banner') > -1" class="demo-image__preview">
              <div v-if="Array.isArray(scope.row[item.key])">
                <span v-for="(item,index) in scope.row[item.key]" :key="index">
                    <el-image 
                    style="width: 36px; height: 36px; margin-right: 5px;"
                    :src="item"
                    />
                </span>
              </div>
              <div v-else>        
                  <el-image 
                    style="width: 36px; height: 36px"
                    :src="scope.row[item.key]"
                />
              </div>  
            </div>
            <span v-else-if="item.key=='type'">
              {{scope.row[item.key] == 1 ? '小图' : (scope.row[item.key] == 2 ? '中图' : '大图')}}
            </span>
            <span v-else>{{ scope.row[item.key] }}</span>
            
          </template>
        </el-table-column>
        <el-table-column
          prop="status"
          label="是否显示"
          min-width="100"
        >
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="显示"
              inactive-text="隐藏"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onEdit(scope.row.group_data_id)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.group_data_id, scope.$index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableData.limit"
          :current-page="tableData.page"
          layout="total, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
    <!--添加专场-->
     <el-dialog :title="titles" :visible.sync="dialogVisible" width="650px" :before-close="handleClose" :append-to-body='true'>
      <el-form ref="formValidate" label-width="120px" v-loading="fullscreenLoading" class="formValidate mt20" :rules="ruleValidate" :model="formValidate" size="small" @submit.native.prevent>
        <el-form-item label="关联标签：" prop="label_id">
           <el-select v-model="formValidate.label_id" clearable filterable size="small" placeholder="请选择" class="width100">
            <el-option
              v-for="item in labelList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="活动名称：" prop="name">
          <el-input v-model="formValidate.name" size="small" placeholder="请输入活动名称" />
        </el-form-item>
        
        <el-form-item label="活动列表图：" prop="pic">
          <div class="acea-row row-middle">
            <div
              class="upLoadPicBox mr15"
              title="710*340px"
              @click="modalPicTap('1',0)"
            >
              <div v-if="formValidate.pic" class="pictrue">
                <img :src="formValidate.pic">
              </div>
              <div v-else class="upLoad">
                <i class="iconfont iconjiahao" />
              </div>
            </div>
            <div style="color: #ccc;font-size: 12px;">
              建议尺寸710*340px
              <el-popover
                placement="bottom"
                title=""
                min-width="200"
                trigger="hover"
                >
                <img :src="`${baseURL}/statics/system/topic94_01.jpg`" style="height:270px;" alt="">
                <el-button type="text" slot="reference">查看示例</el-button>
              </el-popover>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="活动轮播图：">
          <div class="acea-row row-middle">
            <div class="acea-row">
              <div
                v-for="(item,index) in formValidate.banner"
                :key="index"
                class="pictrue" 
              >
                <img :src="item">
                <i class="el-icon-error btndel" @click="handleRemove(index)" />
              </div>
              <div v-if="formValidate.banner.length < 5" class="upLoadPicBox mr15" @click="modalPicTap('2')">
                <div class="upLoad">
                  <i class="iconfont iconjiahao" />
                </div>
              </div>
            </div>
            <div style="color: #ccc;font-size: 12px;">
              建议尺寸750*750px
               <el-popover
                placement="bottom"
                title=""
                min-width="200"
                trigger="hover"
                >
                <img :src="`${baseURL}/statics/system/topic94_02.jpg`" style="height:270px;" alt="">
                <el-button type="text" slot="reference">查看示例</el-button>
              </el-popover>
            </div>
          </div>
        </el-form-item>
         <el-form-item label="活动主题：" prop="image">
          <div class="acea-row row-middle">
            <div
              class="upLoadPicBox mr15"
              title="710*340px"
              @click="modalPicTap('1',1)"
            >
              <div v-if="formValidate.image" class="pictrue">
                <img :src="formValidate.image">
              </div>
              <div v-else class="upLoad">
                <i class="iconfont iconjiahao" />
              </div>
            </div>
            <div style="color: #ccc;font-size: 12px;">
              建议尺寸710*340px
              <el-popover
                placement="bottom"
                title=""
                min-width="200"
                trigger="hover"
                >
                <img :src="`${baseURL}/statics/system/topic94_03.jpg`" style="height:270px;" alt="">
                <el-button type="text" slot="reference">查看示例</el-button>
              </el-popover>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="活动背景色：">
          <div class="acea-row row-middle">
            <el-color-picker v-model="formValidate.color"></el-color-picker>
          </div>
          <div style="color: #ccc;font-size: 12px;">
            若未设置背景色，则为默认色
            <el-popover
              placement="bottom"
              title=""
              min-width="200"
              trigger="hover"
              >
              <img :src="`${baseURL}/statics/system/topic94_04.jpg`" style="height:270px;" alt="">
              <el-button type="text" slot="reference">查看示例</el-button>
            </el-popover>
          </div>
        </el-form-item>  
        <el-form-item label="商品布局：">
          <el-radio-group v-model="formValidate.type">
            <el-radio :label="1">小图</el-radio>
            <el-radio :label="2">中图</el-radio>
            <el-radio :label="3">大图</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="是否显示：">
          <el-switch
            :width="60"
            v-model="formValidate.status"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
          />
        </el-form-item>
        <el-form-item label="排序：">
          <el-input-number
            v-model="formValidate.sort"
            placeholder="请输入排序序号"
            controls-position="right"
            style="width: 200px;"
          />
        </el-form-item>
        
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="dialogVisible=false">取 消</el-button>
        <el-button type="primary" size="small" @click="handleSubmit('formValidate')">确 定</el-button>
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
import {
  groupDataLst,
  deleteGroupData,
  groupDetail,
  statusGroupData,
  topicCreate,
  topicUpdate,
  topicDetail
} from '@/api/system'
import { getProductLabelApi } from '@/api/product'
import { roterPre } from '@/settings'
import SettingMer from '@/libs/settingMer'
export default {
  name: 'Data',
  data() {
    return {
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      roterPre: roterPre,
      tableData: {
        page: 1,
        limit: 20,
        data: [],
        total: 0
      },
      groupId: null,
      topicId: "",
      loading: false,
      groupDetail: null,
      titles: '',
      dialogVisible: false,
      fullscreenLoading: false,
      ruleValidate: {
        activity_name: [{ required: true,message: "请输入活动名称", trigger: "blur"}],
        pic: [{ required: true, message: '请上传封面图', trigger: 'change' }],
        label_id: [{ required: true,message: "请选择关联标签", trigger: "change"}],
      },
      formValidate: {
        pic: "",
        image: "",
        banner: [],
        type: 1,
        status: 1
      },
      labelList: []
    }
  },
  computed: {
    columns() {
      if (!this.groupDetail) return []
      const colums = [
        {
          title: 'ID',
          key: 'group_data_id',
          minWidth: 60
        }]
      if(this.groupDetail.fields)
        this.groupDetail.fields.forEach((val) => {
        colums.push({
          title: val.name,
          key:
          val.field,
          minWidth:
            80
        })
      })
      colums.push(
        {
          title: '添加时间',
          key: 'create_time',
          minWidth: 200
        },
        // {
        //   title: '状态',
        //   key: 'status',
        //   minWidth: 80
        // },
        // {
        //   title: '操作',
        //   slot: 'action',
        //   minWidth: 120,
        //   fixed: 'right'
        // }
      )

      return colums
    },
  },
  watch: {
    '$route.params.id': function(n) {
      this.groupId = n
    },
    groupId(n) {
      this.getGroupDetail(n)
    }
  },
  mounted() {
    this.groupId = this.$route.params.id
    this.getLabelLst()
  },
  created() {
    // this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    getGroupDetail(id) {
      groupDetail(id).then(res => {
        this.groupDetail = res.data
        this.tableData.page = 1
        this.getList()
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 获取标签项
    getLabelLst() {
      getProductLabelApi().then(res => {
        this.labelList = res.data
      })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    // 点击商品图
    modalPicTap(tit, num, i) {
      const _this = this;
      this.$modalUpload(function(img) {
        if (tit === "1") {
          if(num==0){
             _this.formValidate.pic = img[0];
          }else{
             _this.formValidate.image = img[0];
          }
         
        }
        if (tit === '2') {
          img.map((item) => {
            _this.formValidate.banner.push(item)
            if (_this.formValidate.banner.length > 5) {
              _this.formValidate.banner.length = 5
            }
          })
        }
      }, tit);
    },
    handleRemove(i) {
      this.formValidate.banner.splice(i, 1)
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.fullscreenLoading = true
          this.topicId ?
          topicUpdate(this.groupId,this.topicId,this.formValidate)
            .then(async(res) => {
              this.fullscreenLoading = false
              this.$message.success(res.message)
              this.dialogVisible = false
              this.getList()
            })
            .catch((res) => {
              this.fullscreenLoading = false
              this.$message.error(res.message)
            }) :
           topicCreate(this.groupId,this.formValidate)
            .then(async(res) => {
              this.fullscreenLoading = false
              this.$message.success(res.message)
              this.dialogVisible = false
              this.getList()
            })
            .catch((res) => {
              this.fullscreenLoading = false
              this.$message.error(res.message)
            })
        } else {
          
        }
      })
    },
     handleClose() {
      this.dialogVisible = false
    },
    // 列表
    getList() {
      this.loading = false
      groupDataLst(this.$route.params.id, this.tableData.page, this.tableData.limit).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.loading = false
      }).catch(({ message }) => {
        this.loading = false
        this.$message.error(message)
      })
    },
    pageChange(page) {
      this.tableData.page = page
      this.getList()
    },
    handleSizeChange(val) {
      this.tableData.limit = val
      this.getList()
    },
    // 添加
    onAdd() {
      // this.$modalForm(createGroupData(this.$route.params.id)).then(() => this.getList())
      this.titles = "添加专场"
      this.topicId = ""
      this.dialogVisible = true
      this.formValidate = {
        status: 1,
        type: 1,
        banner: [],
        image: "",
        pic: "",
        label_id: "",
        name: "",
        color: "",
        sort: ""
      }
    },
    // 编辑
    onEdit(id) {
      // this.$modalForm(updateGroupData(this.$route.params.id, id)).then(() => this.getList())
      topicDetail(id)
        .then(async(res) => {
          this.topicId = id
          this.titles = "编辑专场"
          this.dialogVisible = true
          let data = res.data.value 
          this.formValidate = {
            sort: res.data.sort,
            status: res.data.status,
            type: Number(res.data.value.type),
            banner: data.banner,
            image: data.image,
            pic: data.pic,
            label_id: data.label_id,
            name: data.name,
            color: data.color,
          }
        })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    onchangeIsShow(row) {
      statusGroupData(row.group_data_id, { status: row.status }).then(({ message }) => {
        this.$message.success(message)
        this.getList()
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure('确定删除该专题').then(() => {
        deleteGroupData(id).then(({ message }) => {
          this.$message.success(message)
          this.tableData.data.splice(idx, 1)
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/form.scss';
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
