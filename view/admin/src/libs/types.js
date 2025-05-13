// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
export function hasOwn(obj, key) {
    return hasOwnProperty.call(obj, key);
  };
  export function isVNode(node) {
    return node !== null && typeof node === 'object' && hasOwn(node, 'componentOptions');
  };

// 是否字符串
export function isString2(str) {
    return (typeof str == 'string') && str.constructor == String;
}
export function isString(obj) {
    return Object.prototype.toString.call(obj) === '[object String]';
}
export function isObject(obj) {
    return Object.prototype.toString.call(obj) === '[object Object]';
}
export function isNumber(obj) {
    return Object.prototype.toString.call(obj) === '[object Number]';
}
// 是否完整的
export function isDef(val) {
    return val !== undefined && val !== null;
}
//
export function isKorean(text) {
    const reg = /([(\uAC00-\uD7AF)|(\u3130-\u318F)])+/gi;
    return reg.test(text);
}

export function isHtmlElement(node) {
    return node && node.nodeType === Node.ELEMENT_NODE;
}
export const isUndefined = (val) => {
    return val === void 0;
};


