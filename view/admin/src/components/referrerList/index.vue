<template>
  <div class="divBox">
    <div class="selCard">
      <el-form inline size="small">
        <el-form-item label="用户名称：">
          <el-input v-model="tableFrom.nickname" @keyup.enter.native="getList(1)" placeholder="请输入用户名称" clearable class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">查询</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-table
      v-loading="listLoading"
      :data="tableData.data"
      size="small"
    >
      <el-table-column
        width="55"
      >
        <template slot-scope="scope">
          <el-radio v-model="templateRadio" :label="scope.row.uid" @change.native="getTemplateRow(scope.row)">&nbsp</el-radio>
        </template>
      </el-table-column>
      <el-table-column prop="uid" label="ID" min-width="50" />
      <el-table-column label="昵称" min-width="90">
          <template slot-scope="{row}">
            <div class="acea-row">
                <i v-show="row.sex===1" class="el-icon-male mr5" style="font-size: 15px; margin-top: 3px; color:#2db7f5; " />
                <i v-show="row.sex===2" class="el-icon-female mr5" style="font-size: 15px; margin-top: 3px; color:#ed4014; " />
                <div v-text="row.nickname" />
            </div>
            <div v-show="row.vip_name" class="vipName">{{ row.vip_name }}</div>
          </template>
      </el-table-column>
      <el-table-column label="头像" min-width="80">
         <template slot-scope="scope">
                <div class="demo-image__preview">
                    <el-image style="width: 36px; height: 36px" :src="scope.row.avatar ? scope.row.avatar : moren" :preview-src-list="[scope.row.avatar || moren]" />
                </div>
         </template>
      </el-table-column>
       <el-table-column label="用户类型" min-width="100">
            <template slot-scope="{row}">
                <span>{{ row.user_type === 'routine' ? '小程序' : row.user_type === 'wechat' ? '公众号' : 'H5' }}</span>
            </template>
        </el-table-column>
    </el-table>
    <div class="block mb20">
      <el-pagination
        :page-size="tableFrom.limit"
        :current-page="tableFrom.page"
        layout="prev, pager, next, jumper"
        :total="tableData.total"
        @size-change="handleSizeChange"
        @current-change="pageChange"
      />
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
import { userLstApi } from '@/api/user'
import { roterPre } from '@/settings'
export default {
  name: 'GoodList',
  data() {
    return {
      moren: require("@/assets/images/f.png"),
      props: {
        emitPath: false
      },
      templateRadio: 0,
      merCateList: [],
      merSelect: [],
      roterPre: roterPre,
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        nickname: ''
      },
      multipleSelection: {},
      checked: []
    }
  },
  mounted() {
    this.getList()
    window.addEventListener('unload', e => this.unloadHandler(e))
  },
  methods: {
    unloadHandler() {
      if (this.multipleSelection) {
        if (this.$route.query.field) {
          /* eslint-disable */
          if(this.multipleSelection.id){
            //form_create_helper.onOk(()=>{
              form_create_helper.set(this.$route.query.field, this.multipleSelection)
              form_create_helper.close(this.$route.query.field)
           // })
          }
        }
      } else {
        this.$message.warning('请先选择商品')
      }
    },
    getTemplateRow(row){
      this.multipleSelection = {src:row.avatar,id: row.uid}
    },

    // 列表
    getList() {
      this.listLoading = true
      userLstApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.checked = window.form_create_helper && window.form_create_helper.get(this.$route.query.field)||[]
        this.tableData.data.forEach(item => {
          this.checked.forEach(element => {
            if (Number(item.uid) === Number(element.id)) {
              this.$nextTick(() => {
                this.$refs.multipleTable.toggleRowSelection(item, true)
              })
            }
          })
        })
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
    }
  }
}
</script>

<style scoped lang="scss">
  .seachTiele{
    line-height: 35px;
  }
  .fr{
    float: right;
  }
</style>
