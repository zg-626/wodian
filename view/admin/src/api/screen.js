// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import request from "./request";
import axios from "axios";

export const paramType = `/data_screen`;

export function currentGET(key, data = {}) {
  return request.get(`${paramType}/${key}`, data);
}

export const GETNOBASE = async (url, params) => {
  try {
    const data = await axios.get(url, {
      params: params
    });
    return data;
  } catch (error) {
    return error;
  }
};
