<?php

/**
 * Paytm uses checksum signature to ensure that API requests and responses shared between your 
 * application and Paytm over network have not been tampered with. We use SHA256 hashing and 
 * AES128 encryption algorithm to ensure the safety of transaction data.
 *
 * @author     Lalit Kumar
 * @version    2.0
 * @link       https://developer.paytm.com/docs/checksum/#php
 */

class Paytm
{

    private static $iv = "@@@@&&&&####$$$$";

    static public function get_credentials()
    {
        $settings = get_settings('payment_method', true);
        $data['paytm_payment_mode'] = (isset($settings['paytm_payment_mode'])) ? $settings['paytm_payment_mode'] : "sandbox";
        $data['paytm_merchant_key'] = $settings['paytm_merchant_key'];
        $data['paytm_merchant_id'] = $settings['paytm_merchant_id'];
        $data['url'] = ($settings['paytm_payment_mode'] == "production") ? "https://securegw.paytm.in/" : "https://securegw-stage.paytm.in/";
        $data['paytm_website'] = ($settings['paytm_payment_mode'] == "production") ? $settings['paytm_website'] : "WEBSTAGING";
        $data['paytm_industry_type_id'] = ($settings['paytm_payment_mode'] == "production") ? $settings['paytm_industry_type_id'] : "Retail";
        return $data;
    }

    static public function encrypt($input, $key)
    {
        $key = html_entity_decode($key);

        if (function_exists('openssl_encrypt')) {
            $data = openssl_encrypt($input, "AES-128-CBC", $key, 0, self::$iv);
        } else {
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
            $input = self::pkcs5Pad($input, $size);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
            mcrypt_generic_init($td, $key, self::$iv);
            $data = mcrypt_generic($td, $input);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = base64_encode($data);
        }
        return $data;
    }

    static public function decrypt($encrypted, $key)
    {
        $key = html_entity_decode($key);

        if (function_exists('openssl_decrypt')) {
            $data = openssl_decrypt($encrypted, "AES-128-CBC", $key, 0, self::$iv);
        } else {
            $encrypted = base64_decode($encrypted);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
            mcrypt_generic_init($td, $key, self::$iv);
            $data = mdecrypt_generic($td, $encrypted);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = self::pkcs5Unpad($data);
            $data = rtrim($data);
        }
        return $data;
    }

    static public function initiate_transaction($params)
    {
        $CI = &get_instance();
        $credentials = self::get_credentials();
        $params["body"]["mid"] = $credentials['paytm_merchant_id'];
        $params["body"]["websiteName"] = $credentials['paytm_website'];
        $checksum = self::generateSignature(json_encode($params["body"], JSON_UNESCAPED_SLASHES), $credentials['paytm_merchant_key']);
        $params["head"] = array(
            "signature"    => $checksum,
            "channelId" => "WEB"
        );
        $post_data = json_encode($params, JSON_UNESCAPED_SLASHES);

        /* for Staging */
        $url = $credentials['url'] . "/theia/api/v1/initiateTransaction?mid=" . $credentials['paytm_merchant_id'] . "&orderId=" . $params['body']['orderId'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    static public function process_payment($params, $txnToken)
    {
        $CI = &get_instance();
        $credentials = self::get_credentials();
        $params["body"]["mid"] = $credentials['paytm_merchant_id'];
        $params["body"]["websiteName"] = $credentials['paytm_website'];
        $params["head"] = array(
            "txnToken"    => $txnToken,
            "channelId"=>"WEB"
        );
        $post_data = json_encode($params, JSON_UNESCAPED_SLASHES);

        /* for Staging */
        $url = $credentials['url'] . "/theia/api/v1/processTransaction?mid=" . $credentials['paytm_merchant_id'] . "&orderId=" . $params['body']['orderId'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($ch);

        return json_decode($response, true);
    }
    static public function transaction_status($order_id)
    {

        $credentials = get_settings('payment_method', true);
        $credentials['url'] = ($credentials['paytm_payment_mode'] == "production") ? "https://securegw.paytm.in/" : "https://securegw-stage.paytm.in/";

        $paytmParams = array();
        $paytmParams["body"] = array(
            "mid" => $credentials['paytm_merchant_id'],
            "orderId" => $order_id,
        );

        $checksum = self::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $credentials['paytm_merchant_key']);

        $paytmParams["head"] = array(
            "signature"    => $checksum
        );

        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

        $url = $credentials['url'] . "v3/order/status";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($ch);
        return $response;
    }
    static public function generateSignature($params, $key)
    {
        if (!is_array($params) && !is_string($params)) {
            throw new Exception("string or array expected, " . gettype($params) . " given");
        }
        if (is_array($params)) {
            $params = self::getStringByParams($params);
        }
        return self::generateSignatureByString($params, $key);
    }

    static public function verifySignature($params, $key, $checksum)
    {
        if (!is_array($params) && !is_string($params)) {
            throw new Exception("string or array expected, " . gettype($params) . " given");
        }
        if (isset($params['CHECKSUMHASH'])) {
            unset($params['CHECKSUMHASH']);
        }
        if (is_array($params)) {
            $params = self::getStringByParams($params);
        }
        return self::verifySignatureByString($params, $key, $checksum);
    }

    static private function generateSignatureByString($params, $key)
    {
        $salt = self::generateRandomString(4);
        return self::calculateChecksum($params, $key, $salt);
    }

    static private function verifySignatureByString($params, $key, $checksum)
    {
        $paytm_hash = self::decrypt($checksum, $key);
        $salt = substr($paytm_hash, -4);
        return $paytm_hash == self::calculateHash($params, $salt) ? true : false;
    }

    static private function generateRandomString($length)
    {
        $random = "";
        srand((float) microtime() * 1000000);

        $data = "9876543210ZYXWVUTSRQPONMLKJIHGFEDCBAabcdefghijklmnopqrstuvwxyz!@#$&_";

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }

        return $random;
    }

    static private function getStringByParams($params)
    {
        ksort($params);
        $params = array_map(function ($value) {
            return ($value !== null && strtolower($value) !== "null") ? $value : "";
        }, $params);
        return implode("|", $params);
    }

    static private function calculateHash($params, $salt)
    {
        $finalString = $params . "|" . $salt;
        $hash = hash("sha256", $finalString);
        return $hash . $salt;
    }

    static private function calculateChecksum($params, $key, $salt)
    {
        $hashString = self::calculateHash($params, $salt);
        return self::encrypt($hashString, $key);
    }

    static private function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    static private function pkcs5Unpad($text)
    {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text))
            return false;
        return substr($text, 0, -1 * $pad);
    }
}
