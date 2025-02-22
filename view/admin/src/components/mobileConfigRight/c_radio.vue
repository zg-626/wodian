<template>
    <div class="c_radio mb15" v-if="configData">
        <div class="c_row-item">
            <el-col class="c_label" :class="configData.type=='form'?'on':''" :span="4">
                {{configData.title}}
            </el-col>
            <el-col class="color-box" :span="19">
                <el-radio-group v-model="configData.tabVal" @change="radioChange($event)">
                    <el-radio :label="key" v-for="(radio,key) in configData.tabList" :key="key">
                        <span>{{radio.name}}</span>
                    </el-radio>
                </el-radio-group>
            </el-col>
        </div>
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
        name: 'c_radio',
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
        methods: {
            radioChange (e) {
                this.$emit('getConfig', e)
            }
        }
    }
</script>

<style scoped lang="scss">
    .c_radio{
        .c_row-item{
           align-items: unset;
        }
        .c_label{
            color: #000;
            margin-right: 15px;
            margin-top: 4px;
            &.on{
              text-align: right;
              color: #666;
            }
        }
    }
    ::v-deep .c_radio .el-radio{
        margin: 5px 25px 5px 0;
    }
</style>
