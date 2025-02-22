<template> 
  <div class="divBox"> 
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="90px">
        <el-form-item label="套餐类型：" prop="type">
          <el-select
            class="selWidth"
            v-model="tableFrom.type"
            placeholder="请选择套餐类型"
            clearable
            @change="getList('')"
          >
            <el-option value="0" label="固定套餐">固定套餐</el-option>
            <el-option value="1" label="搭配套餐">搭配套餐</el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="套餐状态：" prop="status">
          <el-select 
            class="selWidth"
            placeholder="请选择"
            v-model="tableFrom.status"
            clearable
            @change="getList(1)"
          >
            <el-option value="" label="全部">全部</el-option>
            <el-option value="1" label="上架">上架</el-option>
            <el-option value="0" label="下架">下架</el-option>
          </el-select>
        </el-form-item> 
        <el-form-item label="套餐搜索：" prop="title">
          <el-input
            class="selWidth"
            placeholder="请输入套餐名称"
            v-model="tableFrom.title"
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>  
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button> 
        </el-form-item>         
      </el-form>
    </div>
    <el-card class="mt14"> 
      <el-table v-loading="loading" :data="tableData.data" size="small">
       <el-table-column label="ID" prop="discount_id" min-width="80"/>
       <el-table-column label="套餐名称" prop="title" min-width="150">
       </el-table-column>
       <el-table-column label="套餐类型" min-width="100">
          <template slot-scope="scope">
          {{ scope.row.type == 0 ? "固定套餐" : "搭配套餐" }}
          </template>
       </el-table-column>
       <el-table-column label="显示状态" min-width="90">
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
        <el-table-column label="限时" min-width="150">
          <template slot-scope="scope">
            <div v-if="scope.row.start_time == 0">不限时</div>
            <div v-else>
              <div>起：{{ scope.row.start_time || "--" }}</div>
              <div>止：{{ scope.row.stop_time || "--" }}</div>
            </div>
          </template>
        </el-table-column>
         <el-table-column label="创建时间" prop="create_time" min-width="120" />
         <el-table-column label="剩余数量" min-width="100">
          <template slot-scope="scope">
            {{scope.row.is_limit?scope.row.limit_num:'不限量'}}
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="60">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="handleDetail(scope.row.discount_id)">查看</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
    <!--详情-->
    <el-drawer :with-header="false" :visible.sync="dialogVisible" size="1000px" v-if="dialogVisible">
      <div v-loading="dialogLoading">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">{{ formValidate.title }}</div> 
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">套餐数量</div>
              <div>{{ formValidate.is_limit ? formValidate.limit_num: "不限量" }}</div>
            </li>
            <li class="item">
              <div class="title">显示状态</div>
              <div>{{ formValidate.status === 1 ? "显示" : "不显示" }}</div>
            </li>
            <li class="item">
              <div class="title">活动开始时间</div>
              <div>{{ formValidate.is_time ? formValidate.time[0] : '不限时' }}</div>
            </li>
            <li class="item">
              <div class="title">活动结束时间</div>
              <div>{{ formValidate.is_time ? formValidate.time[1] : '不限时' }}</div>
            </li>
            <li class="item">
              <div class="title">创建时间</div>
              <div>{{ formValidate.create_time }}</div>
            </li>
          </ul>
        </div>
        <div class="box-container"> 
          <div> 
            <div class="list mt10" v-if="formValidate.type == 1">
              <div class="title">套餐主商品</div>    
              <div>
                <el-table :data="specsMainData" class="mt20" size="small">
                  <el-table-column
                    prop="store_name"
                    label="商品名称"
                    min-width="200">
                      <template slot-scope="scope">
                        <div class="product-data">
                          <img v-if="scope.row.product" class="image" :src="scope.row.product.image" />
                          <div>{{  scope.row.product && scope.row.product.store_name }}</div>
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column label="参与规格" min-width="100">
                        <template slot-scope="scope">
                          <div v-for="(item, index) in scope.row.attr" :key="index">
                            {{ item.sku }} | {{ item.price }}
                          </div>
                      </template>
                    </el-table-column> 
                </el-table>
              </div>   
            </div>
            <!--搭配套餐-->
            <div class="list mt10">
              <label class="title">套餐搭配商品</label>
              <div class="labeltop">
                <el-table :data="specsData" class="mt20" size="small">
                  <el-table-column
                    prop="store_name"
                    label="商品名称"
                    min-width="200">
                      <template slot-scope="scope">
                        <div class="product-data">
                          <img v-if="scope.row.product" class="image" :src="scope.row.product.image" />
                          <div>{{  scope.row.product && scope.row.product.store_name }}</div>
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column label="参与规格" min-width="100">
                        <template slot-scope="scope">
                          <div v-for="(item, index) in scope.row.attr" :key="index">
                            {{ item.sku }} | {{ item.price }}
                          </div>
                      </template>
                    </el-table-column>                
                </el-table>
              </div>
            </div>
          </div>  
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
import { discountsList, discountsChangeStatus, discountsGetDetails } from "@/api/marketing";
import { formatDate } from "@/utils/validate";
import { roterPre } from '@/settings'
export default {
  name: "Discounts",
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, "yyyy-MM-dd hh:mm");
      }
    },
  },
  data() {
    return {
      loading: false,
      dialogLoading: false,
      roterPre: roterPre,
      dialogVisible: false,
      orderImg: require('@/assets/images/goods_icon.png'),
      tableData: {
        data: [],
        total: 0,
      },
      tableFrom: {
        status: "",
        title: "",
        page: 1,
        type: "",
        limit: 15,
      },
      specsMainData: [],
      specsData: [],
      formValidate: {
        title: "", //套餐名称
        type: 0, //套餐类型
        image: "", //套餐主图
        is_time: 0, //是否限时
        is_limit: 0, //限量1/不限量0
        limit_num: 0, //限量
        link_ids: [], //参与用户标签
        time: [], //套餐时间
        sort: 0, //排序
        free_shipping: 1, //是否包邮
        status: 1,
        products: [],
      },
    };
  },
  computed: {
  },
  created() {
    this.getList('');
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 查看
    handleDetail(id) {
      this.dialogVisible = true;
      this.dialogLoading = true;
      this.specsMainData = [];
      this.specsData = [];
      discountsGetDetails(id).then((res) => {
        this.formValidate = res.data;
        this.formValidate.time = res.data.time || [];   
        this.dialogLoading = false;  
        for (let i = 0; i < res.data.discountsProduct.length; i++) {
          const element = res.data.discountsProduct[i];
          element.attr= [];
          const attrArr = element['product'] && element['product']['attrValue'] || [];
            for (let j = 0; j < attrArr.length; j++) {
              const attr = attrArr[j];
              if (attr.productSku) {
                element.attr.push(attr) 
              } 
            }
          if (element.type == 1) {
            this.specsMainData.push(element);
          } else {
            this.specsData.push(element);
          }
        }
      });
    },
   
    // 列表
    getList(num) {
      this.loading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      discountsList(this.tableFrom)
        .then(async (res) => {
           this.tableData.data = res.data.list;
           this.tableData.total = res.data.count;
           this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList('');
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList('');
    },
    // 修改是否显示
    onchangeIsShow(row) {
      discountsChangeStatus(row.discount_id,row.status)
        .then(async (res) => {
          this.$message.success(res.message);
          this.getList('');
        })
        .catch((res) => {
          this.$message.error(res.message);
          this.getList('');
        });
    },
  },
};
</script>

<style scoped lang="scss">
.head {
  padding: 30px 35px;
  border-bottom: 1px solid #EEEEEE;
  .full {
    display: flex;
    align-items: center;
    .order_icon {
      width: 60px;
      height: 60px;
    }
    
    .text {  
      margin-left: 12px;
      .title {
        margin-bottom: 10px;
        font-size: 16px;
        font-weight: bold;
      }
      .order-num {
        padding-top: 10px;
        white-space: nowrap;
      }
    }
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
.box-container {
  padding: 20px 35px;
}

.labeltop{
  max-height: 280px;
  min-height: 120px;
  overflow-y: auto;
}
.box-container .title{
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary);
  font-size: 15px;
  line-height: 15px;
  color: #303133;
  font-weight: bold;
}
.product-data {
  display: flex;
  align-items: center;
  .image {
    width: 50px !important;
    height: 50px !important;
    margin-right: 10px;
  }
}
</style>
