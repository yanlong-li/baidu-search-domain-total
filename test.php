<?php
include_once("baiduSearchDomain.php");
$result = [];
for ($i = 0; $i < 3; $i++) {
    $urls = baiduSearchDomain::get("10分钟邮箱", $i + 1);
    $_domainCount = baiduSearchDomain::countDomain($urls);

    foreach ($_domainCount as $domain => $count) {
        if (isset($result[$domain])) {
            $result[$domain] += $count;
        } else {
            $result[$domain] = $count;
        }
    }
}


var_dump($result);
