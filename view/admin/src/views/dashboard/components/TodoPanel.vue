<template>
  <div>
    <el-row :gutter="14" class="mb15">
      <!-- 待办事项 -->
      <el-col
        :xs="{span: 24}"
        :sm="{span: 24}"
        :md="{span: 12}"
        :lg="{span: 12}"
        :xl="{span: 12}"
      >
        <el-row class="todo-count" >
          <el-col>
            <el-row :span="24">
              <div class="title">待办事项</div>
            </el-row>
            <el-row :span="24" class="panel-count">
              <el-col v-for="(item, index) in todoList" :key="index" class="item" :span="8">
                <el-dropdown placement="bottom">
                  <router-link :to="{path: item.path}" class="item-link">
                    <div class="icon" :class="'icon'+index">
                      <span class="iconfont" :class="item.icon"></span>
                    </div>
                    <div class="desc">
                      <div class="text" :title="item.title">{{item.title}}</div>
                      <div class="count">{{item.count}}</div>
                    </div>
                  </router-link>
                  <el-dropdown-menu v-show="item.children.length>0" :class="item.children.length>0 ? 'dropdown': 'nodrop'">
                    <el-dropdown-item v-for="(itm, idx) in item.children" :key="idx">
                      <router-link :to="{path: itm.path}">
                        {{itm.message}}
                      </router-link>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
      </el-col>
      <!--商户销量金额排行-->
      <el-col
        :xs="{span: 24}"
        :sm="{span: 24}"
        :md="{span: 12}"
        :lg="{span: 12}"
        :xl="{span: 12}"
      >
      <el-row class="sales-amount">
        <el-col>
         <div class="panel-title">
            <div class="acea-row row-between-wrapper">
              <span>商户销售情况排行TOP 10</span>
               <div class="header-time">                  
                  <el-radio-group v-model="name" size="mini" @change="getSalesRank">
                      <el-radio-button v-for="(item,i) in fromList.fromTxt" :key="i" :label="item.val">{{item.text}}</el-radio-button>
                  </el-radio-group>
              </div>
            </div>
          </div>
          <div class="grid-title-count">
            <el-row class="grid-title">
              <el-col :span="3">
                <div class="title">排名</div>
              </el-col>
              <el-col :span="3">
                <div class="title">商户头像</div>
              </el-col>
              <el-col :span="10">
                <div class="title">商户名称</div>
              </el-col>
              <el-col :span="4">
                <div class="grid-sort el-table">
                  <span>销量</span>
                  <div class="caret-wrapper" @click="getSort('sales')">
                    <i class="sort-caret ascending" :class="{'active' : (sort=='asc'&&type=='sales')}"></i>
                    <i class="sort-caret descending" :class="{'active' : (sort=='desc'&&type=='sales')}"></i>
                  </div>
                </div>
              </el-col>
              <el-col :span="4"> 
                <div class="grid-sort el-table">
                  <span>销售金额</span>
                  <div class="caret-wrapper" @click="getSort('price')">
                    <i class="sort-caret ascending" :class="{'active' : (sort=='asc'&&type=='price')}"></i>
                    <i class="sort-caret descending" :class="{'active' : (sort=='desc'&&type=='price')}"></i>
                  </div>
                </div>
              </el-col>
            </el-row>
          </div>
          <div class="grid-list-content">
            <el-row v-for="(list, index) in salesRankingList" :key="index" class="grid-count">
              <el-col :span="3" class="grid-list">
                <span :class="'gray'+index" class="navy-blue">{{ index+1 }}</span>
              </el-col>
               <el-col class="grid-list" :span="3">
                <img :src="list.mer_avatar" alt>
              </el-col>
              <el-col class="grid-list" :span="10">
                <span>{{ list.mer_name }}</span>
              </el-col>
              <el-col class="grid-list" :span="4">{{ list.sales }}</el-col>
              <el-col class="grid-list" :span="4">{{ list.price }}</el-col>
            </el-row>
          </div>
        </el-col>
      </el-row>
      </el-col> 
    </el-row>
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
  toDoDataApi, getSalesRankApi
} from '@/api/home'
export default {
  name: 'ToDoPanel',
  components: {
   
  },
  data() {
    return {  
      todoList: [],
      salesRankingList: [],
      salesAmountList: [],
      sort: "desc",
      type: "sales",
      name: 'month',
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
            {
                text: '近七天',
                val: 'lately7'
            },
            {
                text: '近30天',
                val: 'lately30'
            },
            {
                text: '本月',
                val: 'month'
            },
            {
                text: '本年',
                val: 'year'
            }
        ]
      }
    }
  },
  activated() {
    
  },
  mounted() {
    this.getTodoData()
    this.getSalesRank('')
  },
  methods: {

    getTodoData() {
      toDoDataApi()
        .then(res => {
          this.todoList = res.data
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },  
    getSalesRank() {

      getSalesRankApi({date: this.name,type: this.type, sort: this.sort})
        .then(res => {
          this.salesRankingList = res.data.list
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },  
    /**排序参数 */
    getSort(type){
      this.type=type;
      this.sort = this.sort == 'asc' ? 'desc' : 'asc';
      this.getSalesRank()
    }
  }
}
</script>
<style lang="scss" scoped>
  .todo-count{
    background: #ffffff;
    border-radius: 4px;
    height: 360px;
    .title{
      padding: 25px 30px;
      color: #000000;
      font-size: 16px;
      font-weight: bold;
    }
  }
  .panel-count{
    padding: 0 47px 40px;
    .item{ 
      margin-top: 50px;
      &:nth-child(-n+3){
        margin-top: 0;
      }
      .item-link{
        display: flex;
        align-items: center;
      }
      .icon{
        width: 50px;
        height: 50px;
        margin-right: 16px;
        text-align: center;
        line-height: 50px;
        .iconfont{
          font-size: 28px;
        }
        &.icon0{
          background: #EFF7FF;
          .iconfont{
            color:#3491FA;
          }
        }
        &.icon1{
          background: #F9F5FE;
          .iconfont{
            color:#B27FEB;
          }
        }
        &.icon2{
          background: #FFF5EB;
          .iconfont{
            color:#FF7D00;
          }
        }
        &.icon3{
          background: #FFF5EB;
          .iconfont{
            color:#FF7D00;
          }
        }
        &.icon4{
          background: #FFFAED;
          .iconfont{
            color:#F7BA1E;
          }
        }
        &.icon5{
          background: #F0F4FF;
          .iconfont{
            color:#4073FA;
          }
        }
        &.icon6{
          background: #ECFBFB;
          .iconfont{
            color:#0FC6C2;
          }
        }
        &.icon7{
          background: #F8FDED;
          .iconfont{
            color:#9FDB1D;
          }
        }
        &.icon8{
          background: #F9F5FE;
          .iconfont{
            color:#B27FEB;
          }
        }
        &.icon9{
          background: #FFFAED;
          .iconfont{
            color: #F7BA1E;
          }
        }
      }
      .desc{
        .text{
          color: #606266;
          font-size: 14px;
          text-overflow: ellipsis;
          max-width: 100px;
          overflow: hidden;
          white-space: nowrap;
        }
        .count{
          margin-top: 8px;
          color: #333333;
          font-size: 18px;
          font-weight: bold;
        }
      }
    }
  }
.dropdown{
  border: none;
  padding: 10px 5px;
  box-shadow: 0px 0px 14px 0px rgba(0,53,132,0.16);
  border-radius: 6px;
  .el-dropdown-menu__item{
    font-size: 13px;
    color: #626266;
    line-height: 26px;
    background: #ffffff;
    margin-top: 10px;
    &:hover{
      color: #4073FA;
    }
  }
}
.dropdown ::v-deep .popper__arrow{
  box-shadow: none;
  filter: none;
}
.nodrop{
  padding: 0;
  box-shadow: none;
  opacity: 0;
}
.sales-amount{
  background: #ffffff;
  border-radius: 4px;
  padding-bottom: 10px;
}
.panel-title {
  padding: 18px 30px 0;
  color: #000;
  overflow: hidden;
  font-weight: bold;
  font-size: 16px;
  span{
    font-weight: bold;
  }
}
.grid-title {
  padding: 10px 0 0;
  margin: 0 30px;
  font-size: 12px;
  border-bottom: 1px solid #EBEEF5;
  display: flex;
  align-items: center;
  .title{
    font-weight: bold;
  }
}
.caret-wrapper{
  .ascending.active{
    border-bottom-color: var(--prev-color-primary);
  }
  .descending.active{
    border-top-color: var(--prev-color-primary);
  }
}
.grid-sort {
  display: flex;
  align-items: center;
  color: #303133;
  font-size: 12px;
  &::before{
    display: none;
  }
}
/*定义滑块 内阴影+圆角*/
::-webkit-scrollbar-thumb{
  -webkit-box-shadow: inset 0 0 6px transparent;;
}
::-webkit-scrollbar {
  width: 4px!important; /*对垂直流动条有效*/
}
.grid-list-content {
  padding: 0 30px;
  height: 260px;
  overflow-x: hidden;
  overflow-y: scroll;
  .el-row {
    border-bottom: 1px solid #EBEEF5;
    &:last-child {
      margin-bottom: 0;
      border-bottom: none;
    }
  } 
}
.grid-list{
  height: 51px;
  display: flex;
  align-items: center;
  &:nth-child(2){
    span {
      display: inline-block;
      white-space: nowrap;
      width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 13px;
      margin-left: 10px;
      max-width: 100px;
    }
    img {
      width: 30px;
      height: 30px;
      border-radius: 2px;
    }
  } 
}
.navy-blue{
  display: block;
  width: 18px;
  height: 18px;
  line-height: 18px;
  text-align: center;
  color: #fff;
  border-radius: 100%;
  -webkit-border-radius: 100%;
  font-size: 12px;
  background: #D0D0D0;
  &.navy-blue {
    background: #D0D0D0;
  }
  &.gray0 {
    background: #EBCA80;
  }
  &.gray1 {
    background: #ABB4C7;
  }
  &.gray2 {
    background: #CCB3A0;
  }
}
</style>
