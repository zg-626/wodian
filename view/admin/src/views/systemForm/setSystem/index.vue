<template>
  <div class="divBox">
    <el-card class="box-card FromData">
      <div
        v-if="types == 'app' || types == 'wechat' || types == 'routine'"
        class="guide"
      >
        <el-button
          slot="extra"
          type="text"
          size="small"
          icon="el-icon-guide"
          @click="guideShow = true"
          >配置引导</el-button
        >
      </div>
      <el-row>
        <el-col>
          <form-create
            v-if="FromData"
            :option="option"
            :rule="FromData.rule"
            @submit="onSubmit"
          />
        </el-col>
      </el-row>
    </el-card>
    <el-drawer :visible.sync="guideShow" :title="`${title}引导`" size="800px">
      <component :is="types"></component>
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
import guide from "@/components/settingGuide/index";
import { mapGetters } from "vuex";
import formCreate from "@form-create/element-ui";
import { configApi, uploadApi } from "@/api/systemForm";
import request from "@/api/request";
import { roterPre } from "@/settings";
export default {
  name: "Basics",
  mixins: [guide],
  provide() {
    return {
      type: this.types
    };
  },
  components: { formCreate: formCreate.$form() },
  data() {
    return {
      guideShow: false,
      routeKey: "",
      title: "",
      types: "",
      roterPre: roterPre,
      option: {
        form: {
          labelWidth: "200px"
        },
        global: {
          upload: {
            props: {
              onSuccess(rep, file) {
                if (rep.status === 200) {
                  file.url = rep.data.src;
                }
              }
            }
          }
        }
      },
      FromData: null,
      titles: ""
    };
  },
  watch:{
    '$route.path': {   
      handler: function() {
        this.setTagsViewTitle();
        this.getFrom();
      },
      immediate: false,
      deep: true
    },
  },
  mounted() {
    this.setTagsViewTitle();
    this.getFrom();
  },
  computed: {
    ...mapGetters(["menuList"])
  },
  created() {
    console.log(this.$route);
    let routerArr = this.$route.path.split("/");
    let routeKey = routerArr[routerArr.length - 1];
    this.getType(routeKey);
    this.tempRoute = Object.assign({}, this.$route); //'wechat_open_app'开放平台
  },
  methods: {
    getFrom() {
      this.$route.params.key === "upload"
        ? uploadApi()
            .then(async res => {
              this.FromData = res.data;
            })
            .catch(res => {
              this.$message.error(res.message);
            })
        : configApi(this.$route.params.key)
            .then(async res => {
              this.FromData = res.data;
            })
            .catch(res => {
              this.$message.error(res.message);
            });
    },
    onSubmit(formData) {
      request[this.FromData.method.toLowerCase()](this.FromData.api, formData)
        .then(res => {
          this.$message.success(res.message || "提交成功");
        })
        .catch(err => {
          this.$message.error(err.message || "提交失败");
        });
    },
    setTagsViewTitle() {
      this.deepTraversal(this.menuList, "children");
      const route = Object.assign({}, this.tempRoute, { title: this.titles });
      this.$store.dispatch("tagsView/updateVisitedView", route);
    },
    deepTraversal(arr, child) {
      const that = this;
      function traversal(a) {
        a.forEach(o => {
          if (
            o.path &&
            o.path.indexOf("Basics") !== -1 &&
            o.path === that.$route.path
          ) {
            that.titles = o.title;
            return;
          }
          if (o[child] && o[child].length) {
            traversal(o[child]);
          }
        });
      }
      traversal(arr);
    },
    getType(key) {
      switch (key) {
        case "wechat_open_app":
          this.types = "app";
          this.title = "微信开放平台配置";
          break;
        case "wechat":
          this.types = "wechat";
          this.title = "公众号配置";
          break;
        case "smallapp":
          this.types = "routine";
          this.title = "小程序配置";
          break;
        default:
          this.types = "";
          this.title = "";
      }
    }
  }
};
</script>

<style scoped lang="scss">
.form-create .el-form-item__label {
  font-size: 12px !important;
}
.FromData ::v-deep .el-textarea__inner {
  min-height: 100px !important;  
}
.guide {
  text-align: right;
  margin: 0 10px 20px 0;
}
::v-deep .el-drawer__header {
  margin-bottom: 0;
}
::v-deep .guide-tab .el-tabs__nav-wrap::after {
  display: none;
}
::v-deep .guide-tab .el-tabs__active-bar {
  display: none;
}
.guide-tab {
  padding: 16px;
}
::v-deep .el-timeline {
  padding: 0;
}
::v-deep .el-tabs__nav {
  display: flex;
  width: 100%;
}
::v-deep .guide-tab .el-tabs__item {
  flex: 1;
  text-align: center;
  background-color: #f5f5f5;
}
::v-deep .guide-tab .el-tabs__item::after {
  content: "";
  position: absolute;
  top: 6px;
  width: 29px;
  height: 29px;
  border: inherit;
  border-left-color: transparent;
  border-bottom-color: transparent;
  transform: rotate(45deg);
  right: -14px;
  background: inherit;
  z-index: 3;
}
::v-deep .guide-tab .el-tabs__item.is-active {
  background-color: #2d8bef;
  color: #fff;
}
::v-deep .el-timeline-item__dot {
  text-align: center;
}
::v-deep .el-timeline-item__dot .dot {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background-color: var(--prev-color-primary);
  line-height: 20px;
  color: #fff;
}
::v-deep .el-timeline-item__tail {
  left: 10px;
  top: 10px;
  border-left: 1px solid #dfe4ed;
}
::v-deep .el-drawer__header {
  border-bottom: 1px solid #e8eaec;
  padding: 14px 16px;
}
</style>
