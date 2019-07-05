<?php
/**
 *   Author: Yanlongli <jobs@yanlongli.com>
 *   Date:   2019/7/5
 *   IDE:    PhpStorm
 *   Desc:   抓取百度搜索结果页的真实URL地址，过滤广告及部分百度内嵌工具。
 */

include_once("pfinalCurl.php");

class baiduSearchDomain
{
    public static function get(string $keyword, int $page): array
    {
        $pn = ($page - 1) * 10;
        $host = "http://www.baidu.com/s?wd=${keyword}&pn=${pn}";
        return self::TResult(pfinalCurl::get($host));
    }

    protected static function TResult(string $result): array
    {
        $patter = '/\<a\s*data\-click\=\"\{[A-Za-z0-9\:\'\,\}\s]*\"\s*href[\s\=\"]*[htps]*\:\/\/www.baidu.com\/link\?url=[a-zA-Z0-9_-]*/';
        preg_match_all($patter, $result, $arr);
        return self::TUrl(array_unique($arr[0]));
    }

    protected static function TUrl(array $result): array
    {
        $result = implode("@", $result);
        $patter = '/[htps]*\:\/\/www.baidu.com\/link\?url=[a-zA-Z0-9_-]*/';
        preg_match_all($patter, $result, $arr);
        $urls = [];
        foreach ($arr[0] as $url) {
            $urls[] = self::decryptBaiduUrl($url);
        }
        return $urls;
    }

    public static function decryptBaiduUrl(string $url): string
    {
        $data = self::get_url($url);
        $url = self::getBaiduRedirectUrl($data);
        return $url;
    }

    protected static function get_url(string $url): string
    {
        $url = parse_url($url);
        $query = $url ['path'];
        if (!empty ($url ['query'])) {
            $query .= "?" . $url ['query'];
        }
        if (!isset($url ['port'])) {
            $url ['port'] = 80;
        }
        $sock = fsockopen($url ['host'], $url ['port']);
        $result = "";
        if (!$sock) {
            return '';
        } else {
            $request = "HEAD $query HTTP/1.1\r\n";
            $request .= "Host: $url[host]\r\n";
            $request .= "Connection: Close\r\n";
            $request .= "\r\n";
            fwrite($sock, $request);
            while (!@feof($sock)) {
                $result .= @fgets($sock, 1024);
            }
            fclose($sock);
            return $result;
        }
    }

    protected static function getBaiduRedirectUrl(string $requestheader): string
    {
        $lines = explode("\r\n", $requestheader);
        foreach ($lines as $line) {
            $pos = strpos($line, "Location: ");
            if ($pos === 0) {
                $url = substr($line, 10, strlen($line));
                return $url;
            }
        }
        return "";
    }

    public static function countDomain(array $urls): array
    {
        $_urls = [];
        foreach ($urls as $url) {
            $host = str_replace(["https://", "http://"], '', $url);
            $index = strpos($host, '/');
            $domain = substr($host, 0, $index);
            $www = 'www.';
            if ($www === substr($domain, 0, 4)) {
                $domain = substr($domain, 4);
            }
            $_urls[] = $domain;
        }
        $result = array_count_values($_urls);
        return $result;
    }
}
