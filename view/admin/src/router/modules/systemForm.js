// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import Layout from '@/layout'
import { roterPre } from '@/settings'
const systemFormRouter =
    {
      path: `${roterPre}/systemForm`,
      name: 'system',
      meta: {
        icon: 'dashboard',
        title: '商城设置'
      },
      alwaysShow: true,
      component: Layout,
      children: [
        {
          path: 'Basics/:key?',
          component: () => import('@/views/systemForm/setSystem/index'),
          name: 'Basics',
          meta: { title: '基础配置' }
        },
        {
          path: 'delivery',
          component: () => import('@/views/systemForm/cityDelivery/index'),
          name: 'Delivery',
          meta: { title: '同城配送' }
        },
        {
          path: 'customer_keyword',
          component: () => import('@/views/system/customer_keyword/index'),
          name: 'CustomerKeyword',
          meta: { title: '自动回复' }
        },
        {
          path: 'form_list',
          component: () => import('@/views/systemForm/form/index'),
          name: 'FormList',
          meta: { title: '系统表单' }
        },
        {
          path: 'form_create',
          component: () => import('@/views/systemForm/form/create'),
          name: 'CreateForm',
          meta: { 
            title: '添加系统表单',
            activeMenu: `${roterPre}/systemForm/form_list`
          }
        },
        {
          path: 'form_detail/:id?',
          component: () => import('@/views/systemForm/form/details'),
          name: 'FormDetail',
          meta: { 
            title: '表单详情',
            activeMenu: `${roterPre}/systemForm/form_list`
          }
        }
      ]
    }

export default systemFormRouter
