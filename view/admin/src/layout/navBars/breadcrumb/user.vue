<template>
  <div
    class="layout-navbars-breadcrumb-user"
    :style="{ flex: layoutUserFlexNum }"
  >
    <div class="layout-navbars-breadcrumb-user-icon" @click="refresh">
      <i class="el-icon-refresh-right" title="刷新"></i>
    </div>
    <div class="layout-navbars-breadcrumb-user-icon" @click="onSearchClick">
      <i class="el-icon-search" title="菜单搜索"></i>
    </div>

    <div class="layout-navbars-breadcrumb-user-icon">
      <!-- <el-tooltip
        effect="light"
        placement="bottom"
        trigger="click"
        v-model="isShowUserNewsPopover"
        :width="300"
        popper-class="el-tooltip-pupop-user-news"
      >
        <el-badge :is-dot="isDot" @click.stop="openNews">
          <i class="el-icon-bell" title="消息"></i>
        </el-badge>
        <transition name="el-zoom-in-top" slot="content">
          <UserNews
            :vm="this"
            v-show="isShowUserNewsPopover"
            @haveNews="initIsDot"
          ></UserNews>
        </transition>
      </el-tooltip> -->
      <el-dropdown trigger="click" :hide-on-click="true" placement="top" @visible-change="getTodoList">
        <span class="item">
          <i class="el-icon-bell"></i>
          <i v-if="dealtList.length > 0" class="icon-tip"></i>
        </span>
        <el-dropdown-menu slot="dropdown" class="noticedrop">
          <el-dropdown-item class="clearfix">
            <el-tabs>
              <el-card v-if="dealtList.length > 0" class="box-card">
                <div slot="header" class="clearfix">
                  <span>系统通知</span>
                </div>
                <router-link
                  v-for="(item, i ) in dealtList" :key="i" class="text item_content"
                  :to="{path: item.path}"
                >
                  <div class="title">{{ item.title }}</div>
                  <div class="message">{{ item.message }}</div>
                </router-link>
                <div v-if="list.length>3 && list.length!=dealtList.length" class="moreBtn" @click.stop="dealtList=list">展开全部<span class="el-icon-arrow-down"></span></div>
              </el-card>
              <el-card v-else class="box-card">
                <div slot="header" class="clearfix">
                  <span>系统通知</span>
                </div>
                <div class="tab-empty">
                  <img src="@/assets/images/no-message.png" class="empty-img" alt="">
                  <div class="empty-text">暂无系统通知</div>
                </div>
              </el-card>
            </el-tabs>
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>
    <div class="layout-navbars-breadcrumb-user-icon" @click="onScreenfullClick">
      <i
        :title="isScreenfull ? '关全屏' : '开全屏'"
        :class="!isScreenfull ? 'el-icon-full-screen' : 'el-icon-crop'"
      ></i>
    </div>
    <div class="layout-navbars-breadcrumb-user-icon">
      <div class="platformLabel">平台</div>
    </div>
    <el-dropdown
      :show-timeout="70"
      :hide-timeout="50"
      @command="onDropdownCommand"
    >
      <span class="layout-navbars-breadcrumb-user-link">
        {{ adminInfo }}
        <i class="el-icon-arrow-down el-icon--right"></i>
      </span>
      <el-dropdown-menu slot="dropdown">
        <el-dropdown-item command="user">个人中心</el-dropdown-item>
        <el-dropdown-item divided command="password">修改密码</el-dropdown-item>
        <el-dropdown-item divided command="logOut">退出登录</el-dropdown-item>
      </el-dropdown-menu>
    </el-dropdown>
    <div
      class="layout-navbars-breadcrumb-user-icon"
      @click="onLayoutSetingClick"
    >
      <i class="el-icon-setting" title="布局配置"></i>
    </div>
    <Search ref="searchRef" />
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
import screenfull from "screenfull";
// import { AccountLogout } from '@/api/account';
import { Session, Local } from "@/utils/storage.js";
import UserNews from "@/layout/navBars/breadcrumb/userNews.vue";
import Search from "@/layout/navBars/breadcrumb/search.vue";
import settings from "../../../setting";
// import { getBaseInfo } from "@/api/user";
import Cookies from "js-cookie";
import { roterPre } from "@/settings";
import { needDealtList } from '@/api/system'
import { editFormApi, passwordFormApi } from '@/api/user'
export default {
  name: "layoutBreadcrumbUser",
  components: { UserNews, Search },
  data() {
    return {
      roterPre: roterPre,
      isScreenfull: false,
      isShowUserNewsPopover: true,
      disabledI18n: "zh-cn",
      disabledSize: "",
      isDot: false,
      label: {
        mer_name: "admin"
      },
      adminInfo: Cookies.set("AdminName"),
      list: [],
      dealtList: [],
    };
  },
  computed: {
    // 获取用户信息
    getUserInfos() {
      return this.$store.state.userInfo.userInfo;
    },
    // 设置弹性盒子布局 flex
    layoutUserFlexNum() {
      let {
        layout,
        isClassicSplitMenu
      } = this.$store.state.themeConfig.themeConfig;
      let num = "";
      if (
        layout === "defaults" ||
        (layout === "classic" && !isClassicSplitMenu) ||
        layout === "columns"
      )
        num = 1;
      else num = null;
      return num;
    }
  },
  mounted() {
    if (Local.get("themeConfigPrev")) {
      this.initI18n();
      this.initComponentSize();
    }
    this.getTodoList()
    // getBaseInfo()
    //   .then(res => {
    //     this.label = res.data;
    //   })
    //   .catch(({ message }) => {
    //     this.$message.error(message);
    //   });
  },
  methods: {
    initIsDot(status) {
      this.isDot = status;
    },
    openNews() {
      // this.isShowUserNewsPopover = !this.isShowUserNewsPopover;
      this.isDot = false;
    },
    // 搜索点击
    onSearchClick() {
      this.$refs.searchRef.openSearch();
    },
    // 布局配置点击
    onLayoutSetingClick() {
      this.bus.$emit("openSetingsDrawer");
    },
    refresh() {
      this.bus.$emit("onTagsViewRefreshRouterView", this.$route.path);
    },
    // 待办列表
    getTodoList(){
      needDealtList().then(res => {
        this.list = res.data
        this.dealtList = res.data.length>3 ? res.data.slice(0,3) : res.data
      }).catch(res => {
      })
    },
    // 全屏点击
    onScreenfullClick() {
      if (!screenfull.isEnabled) {
        this.$message.warning("暂不不支持全屏");
        return false;
      }
      screenfull.toggle();
      screenfull.on("change", () => {
        if (screenfull.isFullscreen) this.isScreenfull = true;
        else this.isScreenfull = false;
      });
      // 监听菜单 horizontal.vue 滚动条高度更新
      this.bus.$emit("updateElScrollBar");
    },
    // 组件大小改变
    onComponentSizeChange(size) {
      Local.remove("themeConfigPrev");
      this.$store.state.themeConfig.themeConfig.globalComponentSize = size;
      Local.set("themeConfigPrev", this.$store.state.themeConfig.themeConfig);
      this.$ELEMENT.size = size;
      this.initComponentSize();
      window.location.reload();
    },
    // 语言切换
    onLanguageChange(lang) {
      Local.remove("themeConfigPrev");
      this.$store.state.themeConfig.themeConfig.globalI18n = lang;
      Local.set("themeConfigPrev", this.$store.state.themeConfig.themeConfig);
      this.$i18n.locale = lang;
      this.initI18n();
    },
    // 初始化言语国际化
    initI18n() {
      switch (Local.get("themeConfigPrev").globalI18n) {
        case "zh-cn":
          this.disabledI18n = "zh-cn";
          break;
        case "en":
          this.disabledI18n = "en";
          break;
        case "zh-tw":
          this.disabledI18n = "zh-tw";
          break;
      }
    },
    // 初始化全局组件大小
    initComponentSize() {
      switch (Local.get("themeConfigPrev").globalComponentSize) {
        case "":
          this.disabledSize = "";
          break;
        case "medium":
          this.disabledSize = "medium";
          break;
        case "small":
          this.disabledSize = "small";
          break;
        case "mini":
          this.disabledSize = "mini";
          break;
      }
    },
    // `dropdown 下拉菜单` 当前项点击
    onDropdownCommand(path) {
      if (path === "logOut") {
        setTimeout(() => {
          this.$msgbox({
            closeOnClickModal: false,
            closeOnPressEscape: false,
            title: "提示",
            message: "此操作将退出登录, 是否继续?",
            showCancelButton: true,
            confirmButtonText: "确认",
            cancelButtonText: "取消",
            beforeClose: async (action, instance, done) => {
              if (action === "confirm") {
                instance.confirmButtonLoading = true;
                instance.confirmButtonText = "退出中";
                setTimeout(async () => {
                  await this.$store.dispatch("user/logout");
                  this.$router.push(
                    `${roterPre}/login?redirect=${this.$route.fullPath}`
                  );
                  done();
                }, 150);
              } else {
                done();
              }
            }
          })
            .then(() => {
              // 清除缓存/token等
              Session.clear();
              // 使用 reload 时，不需要调用 resetRoute() 重置路由
              window.location.reload();
            })
            .catch(() => {});
        }, 150);
      }else if(path === "password"){
        this.$modalForm(passwordFormApi()).then(()=>{})
      } else if (path === "user") {
        this.$modalForm(editFormApi()).then(()=>{})
      } else {
        this.$router.push(path);
      }
    }
  }
};
</script>

<style scoped lang="scss">
.layout-navbars-breadcrumb-user {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  &-link {
    height: 100%;
    display: flex;
    align-items: center;
    white-space: nowrap;
    &-photo {
      width: 30px;
      height: 30px;
      border-radius: 100%;
    }
  }
  i{
    line-height: 50px;
  }
  &-icon {
    padding: 0 10px;
    cursor: pointer;
    color: var(--prev-bg-topBarColor);
    line-height: 50px;
    display: flex;
    align-items: center;
    &:hover {
      background: var(--prev-color-hover);
      i {
        display: inline-block;
        animation: logoAnimation 0.3s ease-in-out;
      }
    }
    .item{
      position: relative;
    }
    .icon-tip{
      position: absolute;
      background: #F56464;
      width: 6px;
      height: 6px;
      border-radius: 100%;
      top: -1px;
      right: 0px;
    }
    .el-icon-bell{
      font-size: 15px;
      color: var(--prev-bg-topBarColor);
    }
  }
  & ::v-deep .el-dropdown {
    color: var(--prev-bg-topBarColor);
    cursor: pointer;
  }
  & ::v-deep .el-badge {
    height: 40px;
    line-height: 40px;
    display: flex;
    align-items: center;
  }
  & ::v-deep .el-badge__content.is-fixed {
    top: 12px;
  }
}
.platformLabel {
  display: inline-block;
  background: var(--prev-color-primary);
  color: #fff;
  vertical-align: text-bottom;
  font-size: 12px;
  padding: 0 8px;
  height: 26px;
  line-height: 26px;
  border-radius: 10px;
  position: relative;
  width: 40px;
}
.noticedrop{
  padding: 0;
}
.noticedrop .el-dropdown-menu{
  padding: 0;
}
.noticedrop .el-dropdown-menu__item{
  background-color: #ffffff;
  padding: 0;
  border-radius: 6px;
}
.item_content {
  display: inline-block;
  white-space: nowrap;
  width: 100%;
  overflow: hidden;
  text-overflow:ellipsis;
  margin-top: 10px;
  line-height: 20px;
  font-size: 13px;
}
.item_content .title{
  color: #333333;
  font-weight: bold;
}
.item_content .message{
  color: #666666
}
.moreBtn{
  color: #666666;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  cursor: pointer;
}
::v-deep .el-card__body{
  padding: 0 24px 10px;
}
.clearfix:before,
.clearfix:after {
  display: table;
  content: "";
}
.clearfix:after {
  clear: both
}
.box-card {
  width: 240px;
}
::v-deep .el-tabs__header{
  margin: 0;
}
::v-deep .el-card__header{
  padding: 10px 24px 0;
  font-weight: bold;
  border: none;
}
.tab-empty {
  text-align: center;
  margin-top: 15px;
}
.empty-text {
  color: #999999;
  font-size: 12px;
}
.empty-img {
  display: inline-block;
  width: 160px;
  height: 123px;
}
</style>
