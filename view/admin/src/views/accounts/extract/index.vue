<template>
<div class="divBox">
    <div class="selCard">
        <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="85px">
            <el-form-item label="时间选择：">
                <el-date-picker v-model="timeVal" value-format="yyyy/MM/dd" format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 280px;" :picker-options="pickerOptions" @change="onchangeTime" />
            </el-form-item>
            <el-form-item label="提现状态：" prop="status">
                <el-select
                    v-model="tableFrom.status"
                    clearable
                    filterable
                    placeholder="请选择"
                    class="selWidth"
                    @change="getList(1)"
                >
                    <el-option label="全部" value=""/>
                    <el-option label="审核中" value="0"/>
                    <el-option label="已提现" value="1"/>
                    <el-option label="已拒绝" value="-1"/>
                </el-select>
            </el-form-item>
            <el-form-item label="提现方式：" prop="extract_type">
                <el-select
                    v-model="tableFrom.extract_type"
                    clearable
                    filterable
                    placeholder="请选择"
                    class="selWidth"
                    @change="getList(1)"
                >
                    <el-option label="全部" value=""/>
                    <el-option label="银行卡" value="0"/>
                    <el-option label="支付宝" value="2"/>
                    <el-option label="微信" value="1"/>
                    <el-option label="微信零钱" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item label="关键字：" prop="keyword">
                <el-input v-model="tableFrom.keyword" @keyup.enter.native="getList(1)" placeholder="姓名/支付宝账号/银行卡号" clearable class="selWidth" />   
            </el-form-item>
            <el-form-item>
                <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
                <el-button size="small" @click="searchReset()">重置</el-button> 
            </el-form-item>
        </el-form>
    </div>
    <el-card class="mt14">
        <div class="mb20">
            <el-button size="small" type="primary" @click="exports">导出列表</el-button>
        </div>
        <el-table v-loading="listLoading" :data="tableData.data" size="small" class="table" highlight-current-row>
            <el-table-column prop="extract_id" label="序号" width="60" />
            <el-table-column label="二维码" min-width="80">
                <template slot-scope="scope">
                    <div class="demo-image__preview">
                        <el-image v-if="scope.row.extract_pic" :src="scope.row.extract_pic" :preview-src-list="[scope.row.extract_pic]" />
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="user.nickname" label="用户信息" min-width="100" />
            <el-table-column prop="uid" label="用户UID" min-width="80" />
             <el-table-column prop="real_name" label="户名" min-width="100" />
            <el-table-column prop="extract_price" label="提现金额" min-width="90" />
            <el-table-column label="提现方式" min-width="100">
                <template slot-scope="scope">
                    <span>{{ scope.row.extract_type | extractTypeFilter }}</span>
                </template>
            </el-table-column>
            <el-table-column label="银行名称" min-width="100">
                <template slot-scope="scope">
                    <span v-if="scope.row.extract_type === 0">{{ (scope.row.bank_name&&scope.row.bank_address) ? scope.row.bank_name+scope.row.bank_address : scope.row.bank_address }}</span>
                    <span v-else>-</span>
                </template>
            </el-table-column>
            <el-table-column label="账号" min-width="100">
                <template slot-scope="scope">
                    <span v-if="scope.row.extract_type==0">{{scope.row.bank_code }}</span>
                    <span v-else-if="scope.row.extract_type==2">{{scope.row.alipay_code }}</span>
                    <span v-else-if="scope.row.extract_type==1">{{scope.row.wechat }}</span>
                    <span v-else></span>
                </template>
            </el-table-column>
            <el-table-column label="审核状态" min-width="90">
                <template slot-scope="scope">
                    <span class="spBlock">{{ scope.row.status | extractStatusFilter }}</span>
                    <!-- <template v-if="scope.row.status === 0">
                        <el-button type="danger" icon="el-icon-close" size="mini" @click="onExamine(scope.row.extract_id)">未通过</el-button>
                        <el-button type="primary" icon="el-icon-check" size="mini" @click="ok(scope.row.extract_id)">通过</el-button>
                    </template> -->
                </template>
            </el-table-column>
            <el-table-column label="拒绝原因" min-width="120">
                <template slot-scope="scope">
                    <span class="spBlock">{{ scope.row.fail_msg ? scope.row.fail_msg : '-' }}</span>
                </template>
            </el-table-column>
            <el-table-column prop="create_time" label="添加时间" min-width="150" />
            <el-table-column label="操作" min-width="80" fixed="right">
                <template slot-scope="scope">
                    <el-button v-if="scope.row.status === 0" type="text" size="small" @click="onAudit(scope.row.extract_id)">审核</el-button>
                    <el-button v-else type="text" size="small" @click="onDetails(scope.row.extract_id)">详情</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div class="block">
            <el-pagination background :page-size="tableFrom.limit" :current-page="tableFrom.page" layout="total, prev, pager, next, jumper" :total="tableData.total" @size-change="handleSizeChange" @current-change="pageChange" />
        </div>
    </el-card>
    <el-dialog v-if="dialogVisible" title="提现详情" center :visible.sync="dialogVisible" width="660px">
      <div v-loading="loading" style="margin-top: 5px;">
        <div class="box-container">
          <div class="list-count">
            <div class="title">用户信息</div>
            <div class="acea-row">
                <div v-if="extractDetail.user" class="list"><label class="name">用户昵称：</label>{{ extractDetail.user.nickname }}</div>
                <div v-if="extractDetail.user" class="list"><label class="name">用户ID：</label><span>{{ extractDetail.user.uid }}</span></div>
                <div class="list"><label class="name">提现金额：</label>{{ extractDetail.extract_price }}</div>
                <div class="list"><label class="name">申请时间：</label><span>{{ extractDetail.create_time }}</span></div>
                <div v-if="extractDetail.extract_type==0" class="list"><label class="name">开户人：</label>{{ extractDetail.real_name }}</div>
                <div v-if="extractDetail.extract_type==0" class="list"><label class="name">银行卡号：</label>{{ extractDetail.bank_code }}</div>
                <div v-if="extractDetail.extract_type==0" class="list"><label class="name">开户行：</label>{{ extractDetail.bank_name }}</div>
                <div v-if="extractDetail.extract_pic" class="list sp100 image">
                <label class="name">二维码：</label>
                <img
                    style="max-width: 150px; height: 80px;"
                    :src="extractDetail.extract_pic"
                >
                </div>
            </div>
          </div>
          <div class="list-count">
            <div class="title">提现方式</div>
            <div class="acea-row">
                <div class="list"><label class="name">审核状态：</label>{{ extractDetail.status | extractStatusFilter }}</div>
                <div class="list"><label class="name">审核时间：</label>{{ extractDetail.status_time }}</div>
                <div v-if="extractDetail.status == -1" class="list sp100"><label class="name">拒绝原因：</label>{{ extractDetail.fail_msg }}</div>
            </div>
          </div>
          
        </div>
      </div>
    </el-dialog>
    <!--导出订单列表-->
    <file-list ref="exportList" />
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
    extractListApi, 
    extractStatusApi, 
    extractManageExportApi, 
    extractManageAudit,
    extractManageDetail
} from '@/api/accounts'
import createWorkBook from '@/utils/newToExcel.js'
import { fromList } from '@/libs/constants.js'
import fileList from '@/components/exportFile/fileList'
import timeOptions from '@/utils/timeOptions';
export default {
    components: { fileList },
    name: 'AccountsExtract',
    data() {
        return {
            pickerOptions: timeOptions,
            timeVal: [],
            tableData: {
                data: [],
                total: 0
            },
            listLoading: true,
            loading: true,
            tableFrom: {
                extract_type: '',
                status: '',
                date: '',
                keyword: '',
                page: 1,
                limit: 20
            },
            fromList: fromList,
            extractDetail: {},
            dialogVisible: false,
        }
    },
    mounted() {
        this.getList()
    },
    methods: {
        /**重置 */
        searchReset(){
            this.timeVal = []
            this.tableFrom.date = ""
            this.$refs.searchForm.resetFields()
            this.getList(1)
        },
        onExamine(id) {
            this.$prompt('未通过', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                inputErrorMessage: '请输入原因',
                inputType: 'textarea',
                inputValue: '输入信息不完整或有误!',
                inputPlaceholder: '请输入原因',
                inputValidator: (value) => {
                    if (!value) {
                        return '请输入原因'
                    }
                }
            }).then(({
                value
            }) => {
                extractStatusApi(id, {
                    status: -1,
                    fail_msg: value
                }).then(res => {
                    this.$message({
                        type: 'success',
                        message: '提交成功'
                    })
                    this.getList()
                }).catch((res) => {
                    this.$message.error(res.message)
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '取消输入'
                })
            })
        },
        ok(id) {
            this.$modalSure('审核通过吗').then(() => {
                extractStatusApi(id, {
                    status: 1
                }).then(({
                    message
                }) => {
                    this.$message.success(message)
                    this.getList()
                }).catch(({
                    message
                }) => {
                    this.$message.error(message)
                })
            })
        },
        /**审核 */
        onAudit(id){
            this.$modalForm(extractManageAudit(id)).then(() => this.getList(1));
        },
        onDetails(id){
            this.dialogVisible = true
            extractManageDetail(id).then(res => {
                this.loading = false
                this.extractDetail = res.data
            }).catch((res) => {
                this.$message.error(res.message)
                this.loading = false
            })
        },
        // 选择时间
        selectChange(tab) {
            this.timeVal = []
            this.tableFrom.date = tab
            this.tableFrom.page = 1;
            this.getList()
        },
        // 具体日期
        onchangeTime(e) {
            this.timeVal = e
            this.tableFrom.date = e ? this.timeVal.join('-') : ''
            this.tableFrom.page = 1;
            this.getList()
        },
        async exports() {
            let excelData = JSON.parse(JSON.stringify(this.tableFrom)), data = []
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
        /**体现管理 */
        downData(excelData) {
            return new Promise((resolve, reject) => {
                extractManageExportApi(excelData).then((res) => {
                    return resolve(res.data)
                })
            })
        },
        // 导出
        exportRecord() {
            extractManageExportApi(this.tableFrom)
                .then((res) => {
                const h = this.$createElement;
                this.$msgbox({
                    title: '提示',
                    message: h('p', null, [
                    h('span', null, '文件正在生成中，请稍后点击"'),
                    h('span', { style: 'color: teal' }, '导出记录'),
                    h('span', null, '"查看~ '),
                    ]),
                    confirmButtonText: '我知道了',
                }).then(action => {

                });
                })
                .catch((res) => {
                this.$message.error(res.message)
                })
            },
        // 导出列表
        getExportFileList() {
        this.$refs.exportList.exportFileList()
        },
        // 列表
        getList(num) {
            this.listLoading = true
            this.tableFrom.page = num ? num : this.tableFrom.page;
            extractListApi(this.tableFrom).then(res => {
                this.tableData.data = res.data.list
                this.tableData.total = res.data.count
                this.listLoading = false
            }).catch((res) => {
                this.$message.error(res.message)
                this.listLoading = false
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
.box-container {
  overflow: hidden;
  
}
.list-count{
    padding:30px 0;
    &:first-child{
        padding-top: 0;
        border-bottom: 1px dashed #f5f5f5;
    }
}
.box-container .list {
  margin-top: 15px;
  font-size: 13px;
  width: 50%;
  color: var(--prev-color-text-primary);
}
.box-container .sp100 {
  width: 100%;
}
.box-container .list .name {
  display: inline-block;
  color: var(--prev-color-text-secondary);

}
.box-container .list .blue {
  color: var(--prev-color-primary);
}
.box-container .list.image {
    display: flex;
  align-items: center;
}

.labeltop{
  max-height: 280px;
  overflow-y: auto;
}
.title{
  color: #17233d;
  font-size: 14px;
  font-weight: bold;
  line-height: 15px;
  padding-left: 5px;
  border-left: 3px solid var(--prev-color-primary);
}
</style>
