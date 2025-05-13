// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import SettingMer from '@/libs/settingMer'
import Vue from 'vue'
import ElementUI from 'element-ui'
import router from '../router'
import { roterPre } from '@/settings'
function bindEvent(vm) {
  vm.$on('notice', function(data) {
    this.$notify.info({
      title: data.title || '消息',
      message: data.message,
      duration: 5000,
      onClick() {
        console.log('click')
      }
    })
  })
}
function createWebScoket(token) {
  return new WebSocket(`${SettingMer.wsSocketUrl}?type=admin&token=${token}`)
}
function notice(token) {
  const ws = createWebScoket(token)
  const vm = new Vue()
  let ping
  function send(type, data) {
    ws.send(JSON.stringify({ type, data }))
  }
  ws.onopen = function() {
    vm.$emit('open')
    ping = setInterval(function() {
      send('ping')
    }, 10000),
    data_status = ping = setInterval(function() {
      send('data_status')
    }, 10000)
  }
  ws.onmessage = function(res) {
    vm.$emit('message', res)
    const data = JSON.parse(res.data)
    if (data.status === 200) {
      vm.$emit(data.data.status, data.data.result)
    }
    console.log('notice.js',data)
  }
  ws.onclose = function(e) {
    vm.$emit('close', e)
    console.log('on close')
    clearInterval(ping)
  }

  bindEvent(vm)

  return function() {
    ws.close()
  }
}

export default notice
