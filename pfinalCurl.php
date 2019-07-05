<?php

class pfinalCurl
{
    public static function get(string $url): string
    {
        return (string)self::execute($url, 'get');
    }

    public static function execute(string $url, string $method, array $postData = null, array $options = [], array &$errors = []): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 150); //设置cURL允许执行的最长秒数
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (strtolower($method) === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postData !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        } else if (strtolower($method) === 'get' && $postData !== null) {
            foreach ($postData as $key => $val) {
                if (strpos($url, '?')) {
                    $url .= "&" . $key . "=" . $val;
                } else {
                    $url .= "?" . $key . "=" . $val;
                }
            }
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        if (!($output = curl_exec($ch))) {
            $errors = [
                    'errno' => curl_errno($ch),
                    'error' => curl_error($ch),
                ] + curl_getinfo($ch);
        }
        curl_close($ch);

        return $output;
    }
}