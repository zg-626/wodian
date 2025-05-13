<template>
  <div class="divBox">
    <el-card>
      <div class="mb20">
        <el-button size="small" type="primary" @click="onAdd">添加表单</el-button>
      </div>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column
          label="ID"
          prop="form_id"
          min-width="80"
        />
        <el-table-column label="表单名称" prop="name" min-width="150" />
        <el-table-column
          prop="create_time"
          label="添加时间"
          min-width="150"
        />
        <el-table-column
          prop="update_time"
          label="更新时间"
          min-width="150"
        />
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onDetails(scope.row.form_id)">查看</el-button>
            <el-button type="text" size="small" @click="onEdit(scope.row.form_id)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.form_id, scope.$index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
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
import {systemFormList, formDeleteApi} from "@/api/setting";
export default {
  name: 'formList',
  data() {
    return {
      roterPre: roterPre,
      isChecked: false,
      listLoading: true,
      total: 0,
      tableData: {
        data: [],
      },
      tableFrom: {
        page: 1,
        limit: 20
      }
    }
  },
  mounted() {
    this.getList()
  },
  methods: {
    // 列表
    getList() {
      this.listLoading = true
      systemFormList(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.total = res.data.count
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
    },
    // 添加
    onAdd() {
      this.$router.push({
        path: `${roterPre}/systemForm/form_create`,
        query: { id: 0 },
      });
    },
    // 查看
    onDetails(id){
      this.$router.push({
        path: `${roterPre}/systemForm/form_detail/${id}`
      });
    },
    // 编辑
    onEdit(id) {
       this.$router.push({
        path: `${roterPre}/systemForm/form_create`,
        query: { id: id },
      });
    },
    // 删除
    handleDelete(id, idx) {
      this.$confirm('删除后不可恢复，请谨慎操作！确定要删除吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        formDeleteApi(id).then(({ message }) => {
          this.$message.success(message)
          this.getList()
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        });          
      });
    },
  }
}
</script>

<style scoped lang="scss">
</style>
