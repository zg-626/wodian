<?php
/**
 * V2ObjectSerializer
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V2
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V2;

class V2ObjectSerializer
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
}