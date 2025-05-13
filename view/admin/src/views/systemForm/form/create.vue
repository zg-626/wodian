<template>
  <!-- 系统表单 -->
  <div class="diy-page">
    <div class="product_tabs">
      <div slot="title"> 
        <div style="float: left;">
          <el-button icon="el-icon-arrow-left" type="text" class="back" @click="goBack">返回</el-button>
          <span class="form-name">{{storeName}}</span>
        </div>
        <div>
          <el-button type="primary" size="mini" @click="saveConfig" :loading="loading">保存</el-button>
          <el-button size="mini" @click="reast">重置</el-button>
        </div>
      </div>
    </div>
    <el-card>
      <div class="diy-wrapper" :style="'height:'+ clientHeight + 'px;'">
        <!-- 左侧 -->
        <div class="left">
          <div class="title">
            <div class="tips">表单名称</div>
            <el-input
              placeholder="请输入表单名称"
              class="input-add"
              size="small"
              v-model="storeName"
            />
          </div>
          <div class="wrapper" :style="'height:'+ (clientHeight-96) + 'px;'" v-if="tabCur == 0">
            <div v-for="(item, index) in leftMenu" :key="index">
              <div class="tips" @click="item.isOpen = !item.isOpen">
                {{ item.title }}
                <i class="el-icon-arrow-right" size="16" v-if="!item.isOpen"></i>
                <i class="el-icon-arrow-down" size="16" v-else></i>
              </div>
              <!-- 拖拽组件 -->
              <draggable
                  class="dragArea list-group"
                  :list="item.list"
                  :group="{ name: 'people', pull: 'clone', put: false }"
                  :clone="cloneDog"
                  dragClass="dragClass"
                  filter=".search , .navbar , .homeComb , .service"
              >
                <!--filter=".search , .navbar"-->
                <!--:class="{ search: element.cname == '搜索框' , navbar: element.cname == '商品分类' }"-->
                <div
                    class="list-group-item"
                    :class="{ search: element.cname == '搜索框' , navbar: element.cname == '商品分类' , homeComb: element.cname == '组合组件' , service: element.cname == '在线客服'}"
                    v-for="(element, index) in item.list"
                    :key="element.id"
                    @click="addDom(element, 1)"
                    v-show="item.isOpen"
                >
                  <div>
                    <div class="position" style="display: none">释放鼠标将组建添加到此处</div>
                    <span class="conter iconfont-diy" :class="element.icon"></span>
                    <p class="conter">{{ element.cname }}</p>
                  </div>
                </div>
              </draggable>
            </div>
          </div>
          
        </div>
        <!-- 中间自定义配置移动端页面 -->
        <div
            class="wrapper-con"
            style="
            flex: 1;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            padding-top: 20px;
            height: 100%;
          "
        >
          <div class="content">
            <div
                class="contxt"
                style="
                display: flex;
                flex-direction: column;
                overflow: hidden;
                height: 100%;
              "
            >
              <div class="overflowy">
                <div class="picture"><img src="@/assets/images/electric.png"></div>
                <div
                    class="page-title"
                >
                  {{storeName}}
                </div>
              </div>
              <div class="scrollCon">
                <div style="width: 460px;margin: 0 auto">
                  <div class="scroll-box" :class="picTxt&&tabValTxt==2?'fullsize noRepeat':picTxt&&tabValTxt==1?'repeat ysize':'noRepeat ysize'" :style="'background-color:'+(colorTxt?colorPickerTxt:'')+';background-image: url('+(picTxt?picUrlTxt:'')+');height:'+ rollHeight + 'px;'" ref="imgContainer">
                    <draggable
                      class="dragArea list-group"
                      :list="mConfig"
                      group="people"
                      @change="log"
                      filter=".top"
                      :move="onMove"
                      animation="300"
                    >
                      <div
                        class="mConfig-item"
                        :class="{
                        on: activeIndex == key,
                        top: item.name == 'search_box' || item.name == 'nav_bar',
                    }"
                      v-for="(item, key) in mConfig"
                      :key="key"
                      @click.stop="bindconfig(item, key)"
                      :style="colorTxt?'background-color:'+colorPickerTxt+';':'background-color:#fff;'"
                      >
                        <component
                          :is="item.name"
                          ref="getComponentData"
                          :configData="propsObj"
                          :index="key"
                          :num="item.num"
                        ></component>
                        <div class="delete-box">
                          <div class="handleType">
                            <div class="iconfont iconshanchu2" @click.stop="bindDelete(item, key)"></div>
                            <div class="iconfont iconfuzhi" @click.stop="bindAddDom(item, 0, key)"></div>
                            <div class="iconfont iconshangyi" :class="key===0?'on':''" @click.stop="movePage(item, key, 1)"></div>
                            <div class="iconfont iconxiayi" :class="key===mConfig.length-1?'on':''" @click.stop="movePage(item, key, 0)"></div>
                          </div>
                        </div>
                        <div class="handle"></div>
                      </div>
                    </draggable>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- 右侧页面设置 -->
        <div class="right-box">
          <div class="mConfig-item" style="background-color:#fff;" v-for="(item, key) in rConfig" :key="key">
            <div class="title-bar">{{ item.cname }}</div>
            <component
                :is="item.configName"
                @config="config"
                :activeIndex="activeIndex"
                :num="item.num"
                :index="key"
            ></component>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script crossorigin='anonymous'>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import {systemForm, systemFormInfo, systemFormUpdate} from "@/api/setting";
import vuedraggable from "vuedraggable";
import mPage from "@/components/mobileFormPage/index.js";
import mConfig from "@/components/mobileFormConfig/index.js";
import {mapState} from "vuex";
import html2canvas from 'html2canvas';
import { roterPre } from "@/settings";
let idGlobal = 0;
export default {
  inject: ['reload'],
  name: "index.vue",
  components: {
    html2canvas,
    draggable: vuedraggable,
    ...mPage,
    ...mConfig,
  },
  filters: {
    filterTxt(val) {
      if (val) {
        return (val = val.substr(0, val.length - 1));
      }
    },
  },
  computed: {
    ...mapState({
      nameTxt: (state) => state.mobildConfig.pageName || "模板",
      showTxt: (state) => state.mobildConfig.pageShow,
      colorTxt: (state) => state.mobildConfig.pageColor,
      picTxt: (state) => state.mobildConfig.pagePic,
      colorPickerTxt: (state) => state.mobildConfig.pageColorPicker,
      tabValTxt: (state) => state.mobildConfig.pageTabVal,
      picUrlTxt: (state) => state.mobildConfig.pagePicUrl,
    }),
  },
  data() {
    return {
      roterPre: roterPre,
      clientHeight:'',//页面动态高度
      rollHeight:'',
      leftMenu: [], // 左侧菜单
      lConfig: [], // 左侧组件
      mConfig: [], // 中间组件渲染
      rConfig: [], // 右侧组件配置
      activeConfigName: "",
      propsObj: {}, // 组件传递的数据,
      activeIndex: 0, // 选中的下标
      number: 0,
      pageId: "",
      pageName: "",
      pageType: "",
      tabCur: 0,
      loading: false,
      relLoading: false,
      isSearch: false,
      isTab: false,
      isFllow: false,
      isComb: false,
      isService: false,
      storeName: "系统表单"
    };
  },
  created() {
    this.pageId = this.$route.query.id;
    this.pageName = this.$route.query.name;
    this.pageType = this.$route.query.type;
    this.lConfig = this.objToArr(mPage);
  },
  mounted() {
    this.$nextTick(() => {
      this.arraySort();
      if (this.pageId != 0) {
        this.getDefaultConfig();
      }
      this.clientHeight = `${document.documentElement.clientHeight}`-65.81;//获取浏览器可视区域高度
      let H = `${document.documentElement.clientHeight}`-180;
      this.rollHeight = H>650?650:H;
      let that = this;
      window.onresize = function(){
        that.clientHeight =  `${document.documentElement.clientHeight}`-65.81;
        let H = `${document.documentElement.clientHeight}`-180;
        that.rollHeight = H>650?650:H;
      }
    });
  },
  methods: {
    goBack(){
     this.$router.push({ path: `${roterPre}/systemForm/form_list` });
    },
    onMove(e) {
      if (e.relatedContext.element.name == "search_box") return false;
      if (e.relatedContext.element.name == "nav_bar") return false;
      if (e.relatedContext.element.name == "home_comb") return false;
      return true;
    },
    onCopy() {
      this.$message.success("复制成功");
    },
    onError() {
      this.$message.error("复制失败");
    },
    // 左侧tab
    bindTab(index) {
      this.tabCur = index;
    },
    // 对象转数组
    objToArr(data) {
      let obj = Object.keys(data);
      let m = obj.map((key) => data[key]);
      return m;
    },
    log(evt) {
      // 中间拖拽排序
      if (evt.moved) {
        evt.moved.oldNum = this.mConfig[evt.moved.oldIndex].num;
        evt.moved.newNum = this.mConfig[evt.moved.newIndex].num;
        evt.moved.status = evt.moved.oldIndex > evt.moved.newIndex;
        this.mConfig.forEach((el, index) => {
          el.num = new Date().getTime() * 1000 + index;
        });
        evt.moved.list = this.mConfig;
        this.rConfig = [];
        let item = evt.moved.element;
        let tempItem = JSON.parse(JSON.stringify(item));
        this.rConfig.push(tempItem);
        this.activeIndex = evt.moved.newIndex;
        this.$store.commit("mobildConfig/SETCONFIGNAME", item.name);
        this.$store.commit("mobildConfig/defaultArraySort", evt.moved);
      }
      // 从左向右拖拽排序
      if (evt.added) {
        let data = evt.added.element;
        let obj = {};
        let timestamp = new Date().getTime() * 1000;
        data.num = timestamp;
        this.activeConfigName = data.name;
        let tempItem = JSON.parse(JSON.stringify(data));
        tempItem.id = "id" + tempItem.num;
        this.mConfig[evt.added.newIndex] = tempItem;
        this.rConfig = [];
        this.rConfig.push(tempItem);
        this.mConfig.forEach((el, index) => {
          el.num = new Date().getTime() * 1000 + index;
        });
        evt.added.list = this.mConfig;
        this.activeIndex = evt.added.newIndex;
        // 保存组件名称
        this.$store.commit("mobildConfig/SETCONFIGNAME", data.name);
        this.$store.commit("mobildConfig/defaultArraySort", evt.added);
      }
    },
    cloneDog(data) {
      // this.mConfig.push(tempItem)
      return {
        ...data,
      };
    },
    //数组元素互换位置
    swapArray(arr, index1, index2) {
      arr[index1] = arr.splice(index2, 1, arr[index1])[0];
      return arr;
    },
    //点击上下移动；
    movePage(item,index,type){
      if(type){
        if(index == 0){
          return
        }
      }else {
        if(index == this.mConfig.length-1){
          return
        }
      }
      if(type){
        this.swapArray(this.mConfig, index-1, index);
      }else {
        this.swapArray(this.mConfig, index, index+1);
      }
      let obj = {};
      this.rConfig = [];
      obj.oldIndex = index;
      if(type){
        obj.newIndex = index-1;
      }else {
        obj.newIndex = index+1;
      }
      this.mConfig.forEach((el, index) => {
        el.num = new Date().getTime() * 1000 + index;
      });
      let tempItem = JSON.parse(JSON.stringify(item));
      this.rConfig.push(tempItem);
      obj.element = item;
      obj.list = this.mConfig;
      if(type){
        this.activeIndex = index-1;
      }else {
        this.activeIndex = index+1;
      }
      this.$store.commit("mobildConfig/SETCONFIGNAME", item.name);
      this.$store.commit("mobildConfig/defaultArraySort", obj);
    },
    // 组件添加
    addDomCon(item,type,index){  
      idGlobal += 1;
      let obj = {};
      let timestamp = new Date().getTime() * 1000;
      item.num = `${timestamp}`;
      item.id = `id${timestamp}`;
      this.activeConfigName = item.name;
      let tempItem = JSON.parse(JSON.stringify(item));
      if(type){
        this.rConfig = [];
        this.mConfig.push(tempItem);
        this.activeIndex = this.mConfig.length - 1;
        this.rConfig.push(tempItem);
      }else {
        this.mConfig.splice(index+1, 0, tempItem);
        this.activeIndex = index;
      }
      this.mConfig.forEach((el, index) => {
        el.num = new Date().getTime() * 1000 + index;
      });
      // 保存组件名称
      obj.element = item;
      obj.list = this.mConfig;
      this.$store.commit("mobildConfig/SETCONFIGNAME", item.name);
      this.$store.commit("mobildConfig/defaultArraySort", obj);
    },
    //中间页点击添加模块；
    bindAddDom(item, type, index) {
      let i = item;
      this.lConfig.forEach(j=>{
        if(item.name==j.name){
          i = j
        }
      });
      this.addDomCon(i,type,index);
    },
    //左边配置模块点击添加；
    addDom(item, type) {
      this.addDomCon(item,type);
    },
    // 点击显示相应的配置
    bindconfig(item, index) {
      this.rConfig = [];
      let tempItem = JSON.parse(JSON.stringify(item));
      this.rConfig.push(tempItem);
      this.activeIndex = index;
      this.$store.commit("mobildConfig/SETCONFIGNAME", item.name);
    },
    // 组件删除
    bindDelete(item, key) {
      if (item.name == "search_box") {
        this.isSearch = false;
      }
      if (item.name == "nav_bar") {
        this.isTab = false;
      }
      if (item.name == "home_comb") {
        this.isComb = false;
      }
      if (item.name == "home_service") {
        this.isService = false;
      }
      this.mConfig.splice(key, 1);
      this.rConfig.splice(0, 1);
      if(this.mConfig.length != key){
        this.rConfig.push(this.mConfig[key]);
      }else {
        if(this.mConfig.length){
          this.activeIndex = key-1;
          this.rConfig.push(this.mConfig[key-1]);
        }
      }
      // 删除第几个配置
      this.$store.commit("mobildConfig/DELETEARRAY", item);
    },
    // 组件返回
    config(data) {
      let propsObj = this.propsObj;
      propsObj.data = data;
      propsObj.name = this.activeConfigName;
    },
    addSort(arr, index1, index2) {
      arr[index1] = arr.splice(index2, 1, arr[index1])[0];
      return arr;
    },
    // 数组排序
    arraySort() {
      let tempArr = [];
      let basis = {
        title: "组件",
        list: [],
        isOpen: true,
      };
      this.lConfig.map((el, index) => {
        if (el.type == 0) {
          basis.list.push(el);
        }
      });
      tempArr.push(basis);
      this.leftMenu = tempArr;
    },
    diySaveDate(val){
      this.pageId ?
      systemFormUpdate(this.pageId,{
        value: val,
        name: this.storeName,
      })
      .then((res) => {
        this.$message.success(res.message);
        let that = this;
        this.loading = false;
        this.relLoading = false;
        setTimeout(function (){
          that.$router.push(that.roterPre + '/systemForm/form_list');
        },500)
      })
      .catch((res) => {
        this.loading = false;
        this.$message.error(res.message);
      }) : 
      systemForm({
        value: val,
        name: this.storeName,
      })
      .then((res) => {
        // this.pageId = res.data.id;
        this.$message.success(res.message);
        let that = this;
        this.loading = false;
        setTimeout(function (){
          that.$router.push(that.roterPre + '/systemForm/form_list');
        },500)
      })
      .catch((res) => {
        this.loading = false;
        this.relLoading = false;
        this.$message.error(res.message);
      });
    },
    // 保存配置
    saveConfig() {
      if (this.mConfig.length == 0) {
        return this.$message.error("暂未添加任何组件，保存失败！");
      }
      this.loading = true;
      let val = this.$store.state.mobildConfig.defaultArray;
      this.$nextTick(function () {
        this.diySaveDate(val);
      })
    },
    // 获取默认配置
    getDefaultConfig() {
      systemFormInfo(this.pageId).then(({data}) => {
        this.storeName = data.name;
        let obj = {};
        let tempARR = [];
        let newArr = this.objToArr(data.value);
        function sortNumber(a, b) {
          return a.timestamp - b.timestamp;
        }
        newArr.sort(sortNumber);
        newArr.map((el, index) => {
          el.id = "id" + el.timestamp;
          this.lConfig.map((item, j) => {
            if (el.name == item.defaultName) {
              item.num = el.timestamp;
              item.id = "id" + el.timestamp;
              let tempItem = JSON.parse(JSON.stringify(item));
              tempARR.push(tempItem);
              obj[el.timestamp] = el;
              this.mConfig.push(tempItem);
              // 保存默认组件配置
              this.$store.commit("mobildConfig/ADDARRAY", {
                num: el.timestamp,
                val: el,
              });
            }
          });
        });
        this.rConfig = [];
        this.activeIndex = 0;
        this.rConfig.push(this.mConfig[0]);
      });
    },
    // 重置
    reast() {
      if (this.pageId == 0) {
        this.$message.warning("新增页面，无法重置");
      } else {
         this.$modalSure("是否重置当前页面数据?").then(() => {
            this.mConfig = [];
            this.rConfig = [];
            this.activeIndex = -99;
            this.getDefaultConfig();
        })
      }
    },
  },
  beforeDestroy() {
    this.$store.commit("mobildConfig/titleUpdata", "");
    this.$store.commit("mobildConfig/nameUpdata", "");
    this.$store.commit("mobildConfig/showUpdata", 1);
    this.$store.commit("mobildConfig/colorUpdata", 0);
    this.$store.commit("mobildConfig/picUpdata", 0);
    this.$store.commit("mobildConfig/pickerUpdata", "#f5f5f5");
    this.$store.commit("mobildConfig/radioUpdata", 0);
    this.$store.commit("mobildConfig/picurlUpdata", "");
    this.$store.commit("mobildConfig/SETEMPTY");
  },
  destroyed() {
    this.$store.commit("mobildConfig/titleUpdata", "");
    this.$store.commit("mobildConfig/nameUpdata", "");
    this.$store.commit("mobildConfig/showUpdata", 1);
    this.$store.commit("mobildConfig/colorUpdata", 0);
    this.$store.commit("mobildConfig/picUpdata", 0);
    this.$store.commit("mobildConfig/pickerUpdata", "#f5f5f5");
    this.$store.commit("mobildConfig/radioUpdata", 0);
    this.$store.commit("mobildConfig/picurlUpdata", "");
    this.$store.commit("mobildConfig/SETEMPTY");
  },
};
</script>

<style scoped lang="scss">
.product_tabs{
  padding: 15px 20px;
  background: #fff;
  border-bottom: 1px solid #e8eaec;
  text-align: right;
  .back{
    color: #303133;
  }
  .form-name{
    font-size: 14px;
    font-weight: bold;
    margin-left: 20px;
    color: #303133;
    &::before{
      content: "";
      display: inline-block;
      width: 1.1px;
      height: 14px;
      background: #303133;
      position: relative;
      top: 2px;
      left: -10px;
    }
  }
}

.ysize {
  background-size: 100%;
}
.fullsize {
  background-size: 100% 100%;
}
.repeat {
  background-repeat: repeat;
}
.noRepeat {
  background-repeat: no-repeat;
}
.overflowy{
  overflow-y: scroll;
  .picture{
    width: 379px;
    height: 20px;
    margin: 0 auto;
    background-color: #fff;
  }
}
.bnt{
  width: 80px!important;
  &:hover{
    border-color: rgba(255,255,255,0.8);
    color: rgba(255,255,255,0.8);
  }
}
/*定义滑块 内阴影+圆角*/
::-webkit-scrollbar-thumb{
  -webkit-box-shadow: inset 0 0 6px #fff;
  display: none;
}

.left:hover::-webkit-scrollbar-thumb,.right-box:hover::-webkit-scrollbar-thumb{
  display: block;
}

.contxt:hover ::-webkit-scrollbar-thumb{
  display: block;
}

::-webkit-scrollbar {
  width: 4px!important; /*对垂直流动条有效*/
}

.scrollCon{
  overflow-y: scroll;
  overflow-x: hidden;
}

.scroll-box .position{
  display: block!important;
  height: 40px;
  text-align: center;
  line-height: 40px;
  border: 1px dashed var(--prev-color-primary);
  color: var(--prev-color-primary);
  background-color: #edf4fb;
}

.scroll-box .conter{
  display: none!important;
}

.dragClass {
  background-color: #fff;
}

.ivu-mt {
  display: flex;
  justify-content: space-between;
}

.iconfont-diy {
  font-size: 24px;
  color: var(--prev-color-primary);
}

.diy-wrapper {
  max-width: 100%;
  min-width: 1100px;
  display: flex;
  justify-content: space-between;
  .left {
    min-width: 300px;
    max-width: 300px;
    border-radius: 4px;
    height: 100%;
    .title{
      padding: 15px;
      .tips{
        font-size: 13px;
        color: #000;
      }
    }
    .input-add{
      margin-top: 10px;
    }
    .wrapper {
      padding: 15px;
      overflow-y: scroll;
      -webkit-overflow-scrolling: touch;
      .tips {
        display: flex;
        justify-content: space-between;
        padding-bottom: 15px;
        font-size: 13px;
        color: #000;
        cursor: pointer;
        .ivu-icon {
          color: #000;
        }
      }
    }
    .link-item {
      padding: 10px;
      border-bottom: 1px solid #F5F5F5;
      font-size: 12px;
      color: #323232;
      .name {
        font-size: 14px;
        color: var(--prev-color-primary);
      }
      .link-txt {
        margin-top: 2px;
        word-break: break-all;
      }
      .params {
        margin-top: 5px;
        color: #1CBE6B;
        word-break: break-all;
        .txt {
          color: #323232;
        }
        span {
          &:last-child i {
            display: none;
            color: red;
          }
        }
      }
      .lable {
        display: flex;
        margin-top: 5px;
        color: #999;
        align-items: center;
        p {
          flex: 1;
          word-break: break-all;
        }
        button {
          margin-left: 30px;
        }
      }
    }
    .dragArea.list-group {
      display: flex;
      flex-wrap: wrap;
      .list-group-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 74px;
        height: 66px;
        margin-right: 17px;
        margin-bottom: 10px;
        font-size: 12px;
        color: #666;
        cursor: pointer;
        border-radius: 5px;
        text-align: center;
        &:hover {
          box-shadow: 0 0 5px 0 rgba(24, 144, 255, 0.3);
          border-right: 5px;
        }
        &:nth-child(3n) {
          margin-right: 0;
        }
      }
    }
}
  .content {
    position: relative;
    height: 100%;
    width: 100%;
    .page-foot {
      position: relative;
      width: 379px;
      margin: 0 auto 20px auto;
      .delete-box {
        display: none;
        position: absolute;
        left: -2px;
        top: 0;
        width: 383px;
        height: 100%;
        border: 2px dashed var(--prev-color-primary);
        padding: 10px 0;
      }
      &:hover, &.on {
        /*cursor: move;*/
        .delete-box {
          /*display: block;*/
        }
      }
      &.on {
        cursor: move;
        .delete-box {
          display: block;
          border: 2px solid var(--prev-color-primary);
          box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
        }
      }
    }
    .page-title {
      position: relative;
      height: 35px;
      line-height: 35px;
      background: #fff;
      font-size: 15px;
      color: #333333;
      text-align: center;
      width: 379px;
      margin: 0 auto;
      .delete-box {
        display: none;
        position: absolute;
        left: -2px;
        top: 0;
        width: 383px;
        height: 100%;
        border: 2px dashed var(--prev-color-primary);
        padding: 10px 0;
        span {
          position: absolute;
          right: 0;
          bottom: 0;
          width: 32px;
          height: 16px;
          line-height: 16px;
          display: inline-block;
          text-align: center;
          font-size: 10px;
          color: #fff;
          background: rgba(0, 0, 0, 0.4);
          margin-left: 2px;
          cursor: pointer;
          z-index: 11;
        }
      }
      &:hover, &.on {
        /*cursor: move;*/
        .delete-box {
          /*display: block;*/
        }
      }
      &.on {
        cursor: move;
        .delete-box {
          display: block;
          border: 2px solid var(--prev-color-primary);
          box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
        }
      }
    }
    .scroll-box {
      flex: 1;
      background-color: #fff;
      width: 379px;
      margin: 0 auto;
      padding-top: 1px;
    }
    .dragArea.list-group {
      width: 100%;
      height: 100%;
      .mConfig-item {
        position: relative;
        cursor: move;
        .delete-box {
          display: none;
          position: absolute;
          left: -2px;
          top: 0;
          width: 383px;
          height: 100%;
          border: 2px dashed var(--prev-color-primary);
          /*padding: 10px 0;*/
          .handleType{
            position: absolute;
            right: -43px;
            top: 0;
            width: 36px;
            height: 143px;
            border-radius: 4px;
            background-color: var(--prev-color-primary);
            cursor: pointer;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;
            .iconfont {
              padding: 5px 0;
              color: #fff;
              &.on{
                opacity: 0.4
              }
            }
          }
        }
        &.on {
          cursor: move;
          .delete-box {
            display: block;
            border: 2px solid var(--prev-color-primary);
            box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
          }
        }
      }
    }
  }
   .right-box {
      max-width: 400px;
      min-width: 400px;
      height: 100%;
      border-radius: 4px;
      overflow: scroll;
      -webkit-overflow-scrolling: touch;
      .title-bar {
        width: 100%;
        height: 45px;
        line-height: 45px;
        padding-left: 24px;
        color: #000;
        border-radius: 4px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
      }
  }
  ::-webkit-scrollbar {
    width: 6px;
    background-color: transparent;
  }
  ::-webkit-scrollbar-track {
    border-radius: 10px;
  }
  ::-webkit-scrollbar-thumb {
    background-color: #bfc1c4;
  }
}
.foot-box {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 80px;
  background: #fff;
  box-shadow: 0px -2px 4px 0px rgba(0, 0, 0, 0.03);
  button {
    width: 100px;
    height: 32px;
    font-size: 13px;
    &:first-child {
      margin-right: 20px;
    }
  }
}
::v-deep .ivu-scroll-loader {
  display: none;
}
::v-deep .el-card {
  border: none;
  box-shadow: none;
}
::v-deep .el-card__body{
  padding: 0;
}
</style>