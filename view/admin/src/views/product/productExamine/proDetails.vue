<template>
  <div>
    <el-drawer
      :with-header="false"
      :visible.sync="drawer"
      size="1100px"
      :direction="direction"
      :before-close="handleClose"
    >
      <div v-loading="loading">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">{{ productData.store_name }}</div>
              <div>
                <span class="mr20">商品ID：{{ productData.product_id }}</span>
              </div>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">商品类型</div>
              <div>{{productData.type == 0 ? '普通商品' : productData.type == 1 ? '虚拟商品' : '卡密商品' }}</div>
            </li>
            <li class="item">
              <div class="title">商品状态</div>
              <div>{{ productData.status == 1 ? '上架显示' : '下架' }}</div>
            </li>
            <li class="item">
              <div class="title">销量</div>
              <div>{{ productData.sales }}</div>
            </li>
            <li class="item">
              <div class="title">库存</div>
              <div>{{ productData.stock }}</div>
            </li>
            <li class="item">
              <div class="title">创建时间</div>
              <div>{{ productData.create_time }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeName" @tab-click="tabClick">
          <el-tab-pane label="基本信息" name="basic">
            <div class="section">
              <!-- <div class="title">用户信息</div> -->
              <ul class="list">
                <li class="item item100">
                  <div class="item-title">封面图：</div>
                  <img :src="productData.image" style="width:40px;height:40px;margin-right:12px;"/>
                </li>
                <li class="item item100">
                  <div class="item-title">轮播图：</div>
                  <img v-for="(pic,idx) in productData.slider_image" :key="idx" :src="pic" style="width:40px;height:40px;margin-right:12px;"/>
                </li>  
              </ul>
              <li class="item item100">
                <div class="item-title">商品简介：</div>
                  <div class="value">{{productData.store_info}}</div>
                </li>
              <ul class="list">
                <li class="item">
                  <div class="item-title">平台分类：</div>
                  <div class="value">{{productData.storeCategory&&productData.storeCategory.cate_name || '-'}}</div>
                </li>
                <li v-if="productData.merCateId&&productData.merCateId.length>0" class="item">
                  <div class="item-title">商户分类：</div>
                  <div class="value">
                    <span v-for="(item,index) in productData.merCateId" :key="index">{{item.category&&item.category.cate_name}}&nbsp;&nbsp;&nbsp;&nbsp;</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">商品标签：</div>
                  <div v-if="(productData.mer_labels&&productData.mer_labels_data.length) || (productData.sys_labels_data&&productData.sys_labels_data.length)" class="value">
                    <template v-if="productData.mer_labels_data&&productData.mer_labels_data.length">
                      <span v-for="(item,index) in productData.mer_labels_data" :key="index" class="value-item"> {{item}} </span> 
                    </template>
                    <template v-if="productData.sys_labels_data&&productData.sys_labels_data.length">
                      <span v-for="(item,index) in productData.sys_labels_data" :key="index" class="value-item"> {{item}} </span>
                    </template>
                  </div>
                  <div v-else class="value"><span>-</span></div>
                </li>
                <li class="item">
                  <div class="item-title">品牌选择：</div>
                  <div class="value">{{productData.brand&&productData.brand.brand_name || '其它'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">单位：</div>
                  <div class="value">{{productData.unit_name}}</div>
                </li>
                <li class="item">
                  <div class="item-title">关键字：</div>
                  <div class="value">{{productData.keyword || '-'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">配送方式：</div>
                  <template v-if="productData.type==0">
                    <div v-if="productData.delivery_way.length==2" class="value">快递/到店自提</div>
                    <div v-else-if="productData.delivery_way.length==1">{{productData.delivery_way[0]==1 ? "到店自提" : "快递"}}</div>
                  </template>
                  <template v-else>
                    <div v-if="productData.type == 1" class="value">虚拟发货</div>
                    <div v-else-if="productData.type == 2" class="value">卡密发货</div>
                  </template>
                </li>
              </ul>
              <ul v-if="productData.video_link" class="list">
                <li class="item item100">
                  <div class="item-title">主图视频：</div>
                  <video style="width:300px;height: 150px;border-radius: 10px;" :src="productData.video_link" controls="controls">
                    您的浏览器不支持 video 标签。
                  </video>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="规格与价格" name="goods">
            <div class="section">
              <ul class="list">
                <li class="item">
                  <div class="item-title">佣金设置：</div>
                  <div class="value">{{productData.extension_type==1?"单独设置":"默认设置"}}</div>
                </li>
                 <li v-if="mer_svip_status" class="item">
                  <div>付费会员价设置：</div>
                  <div class="value">{{productData.svip_price_type==0 ? '不设置会员价' : productData.svip_price_type==1 ? '默认设置会员价' : '自定义设置会员价'}}</div>
                </li>
                 <li class="item">
                  <div class="item-title">规格：</div>
                  <div class="value">{{productData.spec_type == 1 ? "多规格" : "单规格"}}</div>
                </li>
              </ul>
            </div>
            <div class="section" style="margin-top: 50px;">
              <div class="title">规格列表：</div>
              <div class="list">
                <template v-if="productData.spec_type === 0">
                  <el-table :data="OneattrValue" border class="tabNumWidth" size="mini">
                    <el-table-column align="center" label="图片" min-width="80">
                      <template slot-scope="scope">
                        <div class="demo-image__preview">
                          <el-image
                            style="width: 60px; height: 60px"
                            :src="scope.row.image"
                          />
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column v-for="(item,iii) in attrValue" :key="iii" :label="formThead[iii].title" align="center" min-width="120">
                      <template slot-scope="scope">
                        <span class="priceBox" v-text="scope.row[iii]" />
                      </template>
                    </el-table-column>
                    <template v-if="productData.extension_type === 1">
                      <el-table-column align="center" label="一级返佣(元)" min-width="100">
                        <template slot-scope="scope">
                          <span class="priceBox" v-text="scope.row.extension_one" />
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="二级返佣(元)" min-width="100">
                        <template slot-scope="scope">
                          <span class="priceBox" v-text="scope.row.extension_two" />
                        </template>
                      </el-table-column>
                    </template>
                  </el-table>
                </template>
                <template v-if="productData.spec_type === 1">
                  <el-table :data="ManyAttrValue" border class="tabNumWidth" size="mini">
                      <template v-if="manyTabDate">
                        <el-table-column v-for="(item,iii) in manyTabDate" :key="iii" align="center" :label="manyTabTit[iii].title" min-width="100">
                          <template slot-scope="scope">
                            <span class="priceBox" v-text="scope.row[iii]" />
                          </template>
                        </el-table-column>
                      </template>
                    <el-table-column align="center" label="图片" min-width="80">
                      <template slot-scope="scope">
                        <div class="upLoadPicBox">
                          <div class="pictrue tabPic"><img :src="scope.row.image"></div>
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column v-for="(item,iii) in attrValue" :key="iii" :label="formThead[iii].title" align="center" min-width="100">
                      <template slot-scope="scope">
                        <span class="priceBox">{{ scope.row[iii] }}</span>
                      </template>
                    </el-table-column>
                    <template v-if="productData.extension_type === 1">
                      <el-table-column align="center" label="一级返佣(元)" min-width="80">
                        <template slot-scope="scope">
                          <span class="priceBox">{{ scope.row.extension_one }}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="二级返佣(元)" min-width="80">
                        <template slot-scope="scope">
                          <span class="priceBox">{{ scope.row.extension_two }}</span>
                        </template>
                      </el-table-column>
                    </template>
                  </el-table>
                </template>
              </div>
            </div>
          </el-tab-pane>
          <el-tab-pane label="商品详情" name="detail">
            <div class="section">
              <div class="contentPic" v-html="productData.content"/>
            </div>
          </el-tab-pane>
          <el-tab-pane label="营销信息" name="marketing">
            <div class="section">
              <ul class="list">
                <li class="item">
                  <div class="item-title">店铺推荐：</div>
                  <div class="value">{{productData.is_good ? '是' : '否'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">平台推荐：</div>
                  <div v-if="productData.is_benefit||productData.is_new||productData.is_best||productData.is_hot" class="value">
                    <span class="value-item" v-if="productData.is_benefit">促销单品</span>
                    <span class="value-item" v-if="productData.is_new">首发新品</span>
                    <span class="value-item" v-if="productData.is_best">精品推荐</span>
                    <span class="value-item" v-if="productData.is_hot">热门榜单</span>
                  </div>
                  <div v-else class="value-item">无</div>
                </li>
                <li class="item">
                  <div class="item-title">分销礼包：</div>
                  <div class="value">{{productData.is_gift_bag ? '是' : '否'}}</div>
                </li>
                <li v-if="productData.star" class="item">
                  <div class="item-title">平台推荐星级：</div>
                  <div class="value">
                    <el-rate disabled v-model="productData.star" :colors="colors"></el-rate>
                  </div>
                </li>
                <li v-if="productData.merchant&&productData.integral_rate!=0" class="item">
                  <div class="item-title">积分抵扣比例：</div>
                  <div v-if="productData.integral_rate == -1&&productData.merchant" class="value">默认设置{{'('+productData.merchant.mer_integral_rate+'%)'}}</div>
                  <div v-else-if="productData.integral_rate > 0" class="value">单独设置{{'('+productData.integral_rate+'%)'}}</div>
                </li>
                <li v-if="productData.integral_rate!=0" class="item">
                  <div class="item-title">积分抵扣金额：</div>
                  <div class="value">{{productData.integral_price_total}}元</div>
                </li>
                <li v-if="productData.coupon&&productData.coupon.length>0" class="item">
                  <div class="item-title">优惠券(赠送券)：</div>
                  <div class="value">
                    <span v-for="(itm,idx) in productData.coupon" :key="idx" class="value-item"> {{itm.title}} </span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">收藏人数：</div>
                  <div class="value">
                    <span> {{productData.care_count}}人</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">已售数量：</div>
                  <div class="value">
                    <span> {{productData.ficti}} (指手动添加数量)</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">实际销量 ：</div>
                  <div class="value">
                    <span> {{productData.sales-productData.ficti}} (指实际售出数量)</span>
                  </div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="其它信息" name="others">
            <div class="section">
              <ul class="list">
                <li class="item">
                  <div class="item-title">支持退款：</div>
                  <div class="value">{{productData.refund_switch ? '是' : '否'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">最少购买件数：</div>
                  <div class="value">{{productData.once_min_count == 0 ? '不限购' : productData.once_min_count}}</div>
                </li>
                <li v-if="productData.pay_limit!=0" class="item">
                  <div class="item-title">限购类型：</div>
                  <div class="value">{{productData.pay_limit==1?'单次限购':'长期限购'}}{{productData.once_min_count+'('+productData.unit_name+')'}}</div>
                </li>
               <li v-if="productData.guarantee" class="item item100">
                  <div class="item-title">保障服务：</div>
                  <div class="value" style="width: 250px;">
                    <span>{{productData.guarantee.template_name}}</span>
                    <div v-if="productData.guarantee.templateValue && productData.guarantee.templateValue.length>0" style="display: inline;">
                      【<span v-for="(item,i) in productData.guarantee.templateValue" :key="i" class="value-temp">{{item.value&&item.value.guarantee_name}}</span>】
                    </div>
                  </div>
                </li>
                <li v-if="productData.refusal" class="item">
                  <div class="item-title">审核拒绝原因：</div>
                  <div class="value">{{productData.refusal}}</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <ul style="padding: 0;margin-top: 50px;">
                <li class="item item100">
                  <div class="item-title">商户商品参数：</div>
                  <div class="value" style="width: 721px;">
                    <el-table
                      border
                      ref="tableParameter"
                      :data="merParams"
                      row-key="parameter_value_id"
                      size="small"
                      class="ones"
                    >
                      <el-table-column
                        align="center"
                        label="参数名称"
                        width="360"
                      >
                        <template slot-scope="scope">
                          <span>{{scope.row.name}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="参数值" width="360">
                        <template slot-scope="scope">
                          <span>{{scope.row.value}}</span>
                        </template>
                      </el-table-column>
                    </el-table>
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">平台商品参数：</div>
                  <div class="value" style="width: 721px;">
                    <el-table
                      border
                      ref="tableParameter"
                      :data="sysParams"
                      row-key="parameter_value_id"
                      size="small"
                      class="ones"
                    >
                      <el-table-column
                        align="center"
                        label="参数名称"
                        width="360"
                      >
                        <template slot-scope="scope">
                          <span>{{scope.row.name}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="参数值" width="360">
                        <template slot-scope="scope">
                          <span>{{scope.row.value}}</span>
                        </template>
                      </el-table-column>
                    </el-table>
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">关联系统表单：</div>
                  <div v-if="!productData.mer_form_id" class="value">关闭</div>
                  <div v-else-if="productData.mer_form_id && formData.length==0" class="value">表单已被删除</div>
                  <div v-else-if="formData.length>0 && productData.mer_form_id && activeName=='others'" class="value" style="width: 350px;">
                    <iframe class="iframe-box" :src="formUrl" frameborder="0" ref="iframe" style="min-height: 300px;"></iframe>
                    <!-- <el-table
                    border
                    class="specsList"
                    :data="formData"
                    size="small"
                  >
                    <el-table-column prop="label" label="表单标题" width="240" />
                      <el-table-column prop="type" label="表单类型" width="240">
                        <template slot-scope="{row}">
                          <span>{{row.type | formTypeFilter}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column width="240" label="是否必填">
                        <template slot-scope="{row}">
                          <span>{{row.val ? '必填'  : '不必填'}}</span>
                        </template>
                      </el-table-column>
                    </el-table> -->
                  </div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="店铺信息" name="merchant" v-if="productData.merchant">
            <div class="section">
              <ul class="list">
                <li class="item">
                  <div class="item-title">商户名称：</div>
                  <div class="value">{{productData.merchant.mer_name}}</div>
                </li>
                <li class="item">
                  <div class="item-title">商户类别：</div>
                  <div class="value">{{productData.merchant.is_trader == 1 ? '自营' : '非自营'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">店铺类型：</div>
                  <div class="value">{{productData.merchant.type_name}}</div>
                </li>
              </ul>
            </div> 
          </el-tab-pane>
          <el-tab-pane label="商品操作记录" name="records">
            <div class="section">
              <el-form size="small" label-width="90px">
                <div class="acea-row">
                  <el-form-item label="操作端：">
                    <el-select
                      v-model="recordForm.type"
                      placeholder="请选择"
                      class="selWidth mr10"
                      clearable
                      filterable
                      @change="getRecordData(productId)"
                    >
                      <el-option label="平台端" value="1" />
                      <el-option label="商户端" value="2" />
                    </el-select>
                  </el-form-item>
                  <el-form-item label="操作时间：">
                    <el-date-picker
                      class="selWidth"
                      v-model="timeVal"
                      type="datetimerange"
                      placeholder="选择日期"
                      value-format="yyyy/MM/dd HH:mm:ss"
                      clearable
                      @change="onchangeTime"
                    >
                    </el-date-picker>
                  </el-form-item>
                </div>
              </el-form>
              <el-table
                border
                ref="productRecords"
                :data="recordData.data"
                row-key="operate_log_id"
                size="small"
                class="ones"
              >
                <el-table-column
                  align="center"
                  label="序号"
                  min-width="60"
                  prop="operate_log_id"
                />
                <el-table-column align="center" label="操作记录" min-width="120">
                  <template slot-scope="scope">
                    <span>{{scope.row.category_name}}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="操作端" min-width="100">
                  <template slot-scope="scope">
                    <span>{{scope.row.type}}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="操作角色" min-width="100">
                  <template slot-scope="scope">
                    <span>{{scope.row.operator_role_nickname}}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="操作人" min-width="100">
                  <template slot-scope="scope">
                    <span>{{scope.row.operator_nickname}}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  align="center"
                  label="操作时间"
                  min-width="100"
                  prop="create_time"
                />
              </el-table>
              <div class="block">
                <el-pagination :page-size="recordForm.limit" :current-page="recordForm.page" layout="prev, pager, next, jumper" :total="recordData.total" @size-change="handleSizeChange" @current-change="pageChange" />
              </div>
            </div> 
          </el-tab-pane>
        </el-tabs>
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
import { productDetailApi, operateRecordList, associatedMerFormInfo } from '@/api/product';
import { associatedFormInfo } from '@/api/marketing';
import SettingMer from '@/libs/settingMer'
const defaultObj = {
  image: '',
  slider_image: [],
  store_name: '',
  store_info: '',
  keyword: '',
  brand_id: '', // 品牌id
  cate_id: '', // 平台分类id
  mer_cate_id: [], // 商户分类id
  unit_name: '',
  sort: 0,
  is_show: 0,
  is_benefit: 0,
  is_new: 0,
  is_good: 0,
  temp_id: '',
  params: [],
  attrValue: [{
    image: '',
    price: null,
    svip_price: null,
    cost: null,
    ot_price: null,
    stock: null,
    bar_code: '',
    weight: null,
    volume: null
  }],
  attr: [],
  selectRule: '',
  extension_type: 0,
  content: '',
  spec_type: 0
}
const objTitle = {
  price: {
    title: '售价'
  },
  svip_price: {
    title: '付费会员价'
  },
  cost: {
    title: '成本价'
  },
  ot_price: {
    title: '市场价'
  },
  stock: {
    title: '库存'
  },
  bar_code: {
    title: '商品编号'
  },
  weight: {
    title: '重量（KG）'
  },
  volume: {
    title: '体积(m³)'
  }
}
export default {
  components: {},
  props: {
    drawer: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      loading: true,
      productId: '',
      direction: 'rtl',
      activeName: 'basic',
      productData: {},
      formThead: Object.assign({}, objTitle),
      manyTabDate: {},
      manyTabTit: {},
      OneattrValue: [Object.assign({}, defaultObj.attrValue[0])], // 单规格
      ManyAttrValue: [Object.assign({}, defaultObj.attrValue[0])], // 多规格
      svip_type: 0,
      mer_svip_status: 0,
      orderImg: require('@/assets/images/product_icon.png'),
      merParams: [],
      sysParams: [],
      formUrl: "",
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      // baseURL: 'http://localhost:8080',
      formData: [],
      timeVal: [],
      recordData: {
        data: [],
        total: 0
      },//商品操作记录
      recordForm: {
        type: '',
        date: "",
        page: 1,
        limit: 10
      },
      colors: ['#99A9BF', '#F7BA2A', '#FF9900'],
    };
  },
  computed: {
    attrValue() {
      const obj = Object.assign({}, defaultObj.attrValue[0])
      if(this.svip_type == 0 || this.mer_svip_status == 0)delete obj.svip_price
      delete obj.image
      return obj
    },
  },
  filters: {
  },
  methods: {
    handleClose() {
      this.activeName = 'basic';
      this.$emit('closeDrawer');
    },
    getInfo(id) {
      this.loading = true;
      this.productId = id;
      productDetailApi(id).then(res => {
        this.loading = false;
        this.productData = res.data
        this.mer_svip_status = res.data.mer_svip_status
        this.svip_type = res.data.svip_price_type
        if (this.productData.spec_type === 0) {
          this.OneattrValue = res.data.attrValue
        } else {
          this.ManyAttrValue = res.data.attrValue
        }
        const tmp = {}
        const tmpTab = {}
        this.productData.attr.forEach((o, i) => {
          tmp['value' + i] = { title: o.value }
          tmpTab['value' + i] = ''
        })
        this.manyTabDate = tmpTab
        this.manyTabTit = tmp
        this.checkboxGroup = []
        this.formThead = Object.assign({}, this.formThead, tmp)
        this.sysParams = []
        this.merParams = []
        if(res.data.params&&res.data.params.length>0){
          for(var i=0;i<res.data.params.length;i++){
            if(res.data.params[i]['mer_id'] == 0){
              this.sysParams.push(res.data.params[i])
            }else{
              this.merParams.push(res.data.params[i])
            }
          }
        }
        if(res.data.mer_form_id)this.getFormInfo(res.data.mer_form_id,res.data.mer_id)
        this.getRecordData(id)
        this.loading = false
      }).catch(res => {
        this.$message.error(res.message)
        this.loading = false
      })
    },
    // 关联的表单信息
    getFormInfo(id,merId){
      associatedFormInfo(id,{mer_id:merId}).then((res) => {
        this.formData = res.data
        let time = new Date().getTime() * 1000
        let formUrl = `${this.baseURL}/pages/admin/system_form/index?inner_frame=1&form_id=${id}&time=${time}`;
        this.formUrl = formUrl;
      })
      .catch((res) => {
        this.$message.error(res.message)
      })
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.recordForm.date = e ? this.timeVal.join('-') : ''
      this.getRecordData(this.productData.product_id)
    },
    // 商品操作记录
    getRecordData(id){
      operateRecordList(id,this.recordForm)
        .then(res => {
          this.recordData.data = res.data.list
          this.recordData.total = res.data.count
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    pageChange(page) {
      this.recordForm.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.recordForm.limit = val
      this.getList('')
    },
    tabClick(tab) {
   
    },
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
    .iconfont {
      color: var(--prev-color-primary);
      &.sale-after {
        color: #90add5;
      }
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
        font-weight: bold;
        color: #282828;
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
      width: 20%;
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
.tabNumWidth{
  max-height: 350px;
  overflow-y: auto;
  &:before{
    display: none;
  }
}
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.section {
  .title {
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    align-items: center;
    &:nth-child(3n + 1) {
      padding-right: 20px;
    }
    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }
    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
    .item-title{
      width: 100px;
      text-align: right;
    }
  }
  .item100{
    padding-left: 0;
    flex: 0 0 calc(100% / 1);
    padding-left: 0!important;
  }
  .contentPic{
    width: 500px;
    margin: 0 auto;
    max-height: 600px;
    overflow-y: auto;
  }
 
  .value {
    .value-item {
      &::after{
        content: "/";
        display: inline-block;
      }
      &:last-child{
        &::after{
          display: none;
        }
      }
    }
    .value-temp{
       &::after{
        content: "、";
        display: inline-block;
      }
      &:last-child{
        &::after{
          display: none;
        }
      }
    }
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.contentPic ::v-deep img{
  max-width: 100%;
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
::v-deep .ones th{
  background: #F0F5FF;
}

</style>
