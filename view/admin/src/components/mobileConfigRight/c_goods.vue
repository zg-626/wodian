<template>

    <div class="goods-box" v-if="configObj.tabConfig.tabVal == 1">
        <div class="wrapper">
            <draggable
                    class="dragArea list-group"
                    :list="configObj.goodsList.list"
                    group="peoples"
            >
            <div class="item" v-for="(goods,index) in configObj.goodsList.list" :key="index" v-if="configObj.goodsList.list.length">
                <img :src="goods.image" alt="">
                <span class="iconfont iconcha" @click.stop="bindDelete(index)"></span>
            </div>
            <div class="add-item item" @click="getGoods"><span class="iconfont-diy iconaddto"></span></div>
            </draggable>
        </div>
        <el-dialog :visible.sync="modals" title="商品列表" class="paymentFooter" scrollable width="900px" :before-close="cancel">
            <goods-list ref="goodslist" :ischeckbox="true" :isdiy="true"  @getProductId="getProductId" @close="cancel" v-if="modals" :multipleSelectionAll="multipleSelectionAll"></goods-list>
        </el-dialog>
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
import vuedraggable from 'vuedraggable'
import goodsList from '@/components/goodsList'
export default {
    name: 'c_goods',
    props: {
        configObj: {
            type: Object
        }
    },
    components: {
        goodsList,
        draggable: vuedraggable
    },
    watch: {
        configObj: {
            handler (nVal, oVal) {
                this.configObj = nVal
                this.multipleSelectionAll = this.configObj.goodsList.list
            },
            immediate: true,
            deep: true
        },
        defaults: {
            handler (nVal, oVal) {
                this.defaults = nVal
            },
            immediate: true,
            deep: true
        }
    },
    data () {
        return {
            modals: false,
            goodsList: [],
            multipleSelectionAll: [],
            tempGoods: {},
            defaults:{}
        }
    },
    created () {
        // this.defaults = this.configObj
        this.multipleSelectionAll = this.configObj.goodsList.list
    },
    methods: {
        getGoods(){
            this.modals = true
        },
        //对象数组去重；
        unique(arr) {
            const res = new Map();
            return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1))
        },
        getProductId (data) {
            this.modals = false;
            let ids = []
            data.forEach((item,index)=>{
                ids.push(item.product_id)
            })
            this.configObj.goodsList.ids = ids   
            this.$emit('getConfig', this.configObj);
        },
        cancel () {
            this.modals = false;
            // this.tempGoods = {}
        },
        ok () {
            this.configObj.goodsList.list.push(this.tempGoods)
        },
        bindDelete (index) {
            this.configObj.goodsList.list.splice(index, 1)
        }
    }
}
</script>

<style scoped lang="scss">
    .goods-box{
        padding: 16px 0;
        margin-bottom: 16px;
        border-top: 1px solid rgba(0,0,0,0.05);
        .wrapper,.list-group{
            display: flex;
            flex-wrap: wrap;
        }      
        .add-item{
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
            background: #F7F7F7;
            .iconfont-diy{
                font-size: 20px;
                color: #D8D8D8;
            }        
        }
        .item{
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            margin-right: 12px;
            img{
                width: 100%;
                height: 100%;
            }      
            .iconcha{
                position: absolute;
                right: -10px;
                top: -16px;
                color: #999999;
                font-size: 20px;
                cursor: pointer;
            }
                
        }
    }
        
            
</style>
