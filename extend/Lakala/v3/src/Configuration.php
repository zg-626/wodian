<?php
/**
 * Configuration
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3;

class Configuration
{
    private $config;

    public function __construct($configfile = '.lklopensdk.env')
    {
        if (is_string($configfile)) {
            $dir = dirname(__FILE__);
            $this->config = $this->readenv($dir . '/' . $configfile);
            $this->config['app_debug'] = strtolower($this->config['app_debug']);
            $this->config['merchant_private_key_path'] = realpath($dir . '/' . $this->config['merchant_private_key_path']);
            $this->config['lkl_certificate_path'] = realpath($dir . '/' . $this->config['lkl_certificate_path']);
        }
        else if (is_array($configfile)) {
            $this->config = $configfile;
        }
    }

    public function getAppId() {
        return $this->getConfig('app_id');
    }

    public function getSerialNo() {
        return $this->getConfig('serial_no');
    }

    public function getSm4Key() {
        return $this->getConfig('sm4_key');
    }

    public function getMerchantPrivateKeyPath() {
        return $this->getConfig('merchant_private_key_path');
    }

    public function getLklCertificatePath() {
        return $this->getConfig('lkl_certificate_path');
    }

    private function getConfig($key) {
        return $this->config[$key];
    }

    public function getHost() {
        if ($this->config['app_debug'] === 'true' || $this->config['app_debug'] === true) {
            return $this->getConfig('host_test');
        }
        return $this->getConfig('host_pro');
    }

    public function getDefaultHeaders() {
        return [
            'Lkl-Op-Appid: ' . $this->getAppId(),
            'Lkl-Op-Sdk: lkl-php-sdk-3.0.0',
            'Lkl-Op-Flowgroup: NORMAL',
        ];
    }

    public function readenv($env) {
        $envData = file_get_contents($env);
        $lines = explode("\n", $envData);
    
        $config = array();
        foreach ($lines as $line) {
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = strtolower(trim($parts[0]));
                $value = trim($parts[1]);
                $config[$key] = $value;
            }
        }
        return $config;
    }
    // Getters and setters...
}