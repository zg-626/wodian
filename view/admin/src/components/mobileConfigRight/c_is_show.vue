<template>
    <div v-if="configData" class="c_row-item">
        <el-col class="c_label" :class="{on:configData.type=='form',on2:configData.type=='ranges'}" :span="configData.type=='form' || configData.type=='ranges' ? 4 : 21">{{configData.title}}</el-col>
        <el-col :span="configData.type=='form' || configData.type=='ranges' ? 19 : 3">
            <el-switch :active-value="true" :inactive-value="false" active-text="是" inactive-text="否" v-model="configData.val"/>
        </el-col>
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
export default {
    name: 'c_is_show',
    props: {
        configObj: {
            type: Object
        },
        configNme: {
            type: String
        }
    },
    data () {
        return {
            defaults: {},
            configData: {}
        }
    },
    created () {
        this.defaults = this.configObj
        this.configData = this.configObj[this.configNme]
    },
    watch: {
        configObj: {
            handler (nVal, oVal) {
                this.defaults = nVal
                this.configData = nVal[this.configNme]
            },
            immediate: true,
            deep: true
        }
    },
    methods:{

    }
}
</script>

<style scoped lang="scss">
    .c_row-item{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .c_label{
        &.on{
          color: #666;
          text-align: right;
        }
        &.on2{
          text-align: left;
          color: #666;
        }
      }
</style>