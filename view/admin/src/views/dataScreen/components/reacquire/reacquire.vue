<template>
  <div
    class="reacquire flex justify-center  blocks  cursor-pointer"
    :style="{ lineHeight: lineHeight }"
    @click="getData"
  >
    <span>
      重新获取
    </span>
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
  components: {},
  props: {
    lineHeight: {
      type: String,
      default: "200px"
    }
  },
  data() {
    return {};
  },
  computed: {},
  methods: {
    init() {},
    getData(e) {
      this.$emit("onclick", e);
    }
  },
  created() {},
  mounted() {},
  beforeDestroy() {}
};
</script>

<style lang="scss" scoped>
.reacquire {
  user-select: none;
  color: rgb(168, 168, 168);
  span:hover {
    color: $primary-color;
    text-decoration: underline;
  }
}

.blocks {
  width: 100%;
  height: 100%;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
