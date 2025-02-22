<template>
  <div class="divBox" style="background: #F0F2F5;">
    <base-info ref="baseInfo" class="mb15" />
    <todo-panel ref="todoPanel" />
    <to-day class="mb15" />
    <!-- <el-row :gutter="20" class="mb15">
      <el-col v-bind="grid">
        <my-ranking :merchant-data="merchantStock" :mer-title="merTitle" @getList="getList" />
      </el-col>
      <el-col v-bind="grid">
        <my-ranking :mer-title="visitTitle" :merchant-data="merchantVisit" @getList="getVisit" />
      </el-col>
      <el-col v-bind="grid">
        <merchant-rate />
      </el-col>
    </el-row> -->
    <user-data class="mb15" />
    <user-from />
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
import { merchantStockApi, merchantVisitApi } from "@/api/home";
import { checkAuthApi, authTypeApi, checkQueueTips } from "@/api/maintain";
import { mapGetters } from "vuex";
import baseInfo from "./components/baseInfo";
import TodoPanel from './components/TodoPanel'
import toDay from "./components/toDay";
import myRanking from "./components/ranking";
import merchantRate from "./components/merchantRate";
import userData from "./components/userData";
import userFrom from "./components/user";
import Cookies from "js-cookie";
export default {
  name: "Dashboard",
  components: { baseInfo, TodoPanel, toDay, myRanking, merchantRate, userData, userFrom },
  data() {
    return {
      merTitle: "商品销量排行",
      visitTitle: "商户访客量排行",
      currentRole: "adminDashboard",
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 24
      },
      merchantStock: [],
      merchantVisit: []
    };
  },
  computed: {
    ...mapGetters(["roles"])
  },
  mounted() {
    this.getAuth();
    this.getList("lately30");
    this.getVisit("lately30");
    this.getCheckTips()
    if(!Cookies.get("auth")) {
      checkAuthApi()
        .then(res => {
          if(res.message !== "success") {
            return this.$notify.warning({
              title: "授权提醒",
              duration: 0,
              dangerouslyUseHTMLString: true,
              message: res.message,
              render: h => {
                return h("div", [
                  h(
                    "a",
                    {
                      attrs: {
                        href:
                          "http://www.crmeb.com/home/grant/applyauthorize.html",
                        target: "_blank"
                      }
                    },
                    res.message
                  )
                ]);
              },
              onClose() {
                Cookies.set("auth", true);
              }
            });
          }
        })
        .catch(res => { });
    }
  },
  methods: {
    getAuth() {
      authTypeApi()
        .then(res => {
          const data = res.data || {};
          if(data.auth_code && data.auth) {
            this.authCode = data.auth_code;
            this.auth = true;
          }
        })
    },
    // 队列和长链接开启提醒
    getCheckTips() {
      if(sessionStorage.getItem("queue") != 1){
        checkQueueTips()
        .then(res => {
            if(res.data.status == 201){
              sessionStorage.setItem("queue", 1);
              let url = res.data.result.url,
              message = res.message+'<br/><a href="'+url+'" style="color: #4073FA;font-size: 14px; "target="_blank">'+res.data.result.msg+'</a>'
              return this.$notify.warning({
                title: "温馨提示",
                duration: 0,
                dangerouslyUseHTMLString: true,
                message: message,
                onClick() {
                  window.open(res.data.result.url)
                },
                onClose() {
                  sessionStorage.setItem("queue", 0);
                }
              });
            }
          })
        .catch(res => {});
      } 
    },
    // 商品销量
    getList(val) {
      merchantStockApi({ date: val })
        .then(res => {
          if(res.status === 200) {
            this.merchantStock = res.data.list;
          }
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 商户访客量
    getVisit(val) {
      merchantVisitApi({ date: val })
        .then(res => {
          if(res.status === 200) {
            this.merchantVisit = res.data.list;
          }
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

  }
};
</script>
