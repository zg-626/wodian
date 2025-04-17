<?php
/**
 * ObjectSerializer
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3;

class ObjectSerializer
{
    public static function sanitizeForSerialization($data)
    {
        if (is_array($data)) {
            foreach ($data as $property => $value) {
                $data[$property] = self::sanitizeForSerialization($value);
            }
            return $data;
        } elseif (is_object($data)) {
            $values = [];
            foreach (get_object_vars($data) as $property => $value) {
                $values[$property] = self::sanitizeForSerialization($value);
            }
            return $values;
        } else {
            return $data;
        }
    }

    public static function deserialize($data, $class, $headers = null)
    {
        if ($class === '\DateTime') {
            return new \DateTime($data);
        } elseif (in_array($class, ['bool', 'boolean', 'int', 'integer', 'float', 'double', 'string', 'array'])) {
            settype($data, $class);
            return $data;
        } else {
            $instance = new $class();
            foreach ($data as $property => $value) {
                $camelProp = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
                $setter = 'set' . $camelProp;
                if (method_exists($instance, $setter)) {
                    $instance->$setter($value);
                }
            }
            if ($headers && method_exists($instance, 'setHeaders')) {
                $instance->setHeaders($headers);
            }
            return $instance;
        }
    }

    # 此方法仅针对请求Model参数的对象json编码处理
    public static function jsonencode($value)
    {
        if ($value === null || is_string($value)) return $value;
        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
        if (method_exists($value, 'jsonSerialize')) {
            return json_encode($value->jsonSerialize(), JSON_UNESCAPED_UNICODE);
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}