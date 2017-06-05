<?php

require_once("config.php");
require_once("simple_html_dom.php");

set_time_limit(0);
ini_set('memory_limit', '512M');
define('COOK_FILE_NAME1', 'cookies1.txt'); // cookies for php-academy.kiev.ua
define('COOK_FILE_NAME2', 'cookies2.txt'); // cookies for devionity.com

/**
 * @return mixed
 */
function logon()
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, URL_LOGIN);
    curl_setopt($curl, CURLOPT_COOKIEJAR, COOK_FILE_NAME1); //сохранить куки в файл
    curl_setopt($curl, CURLOPT_USERAGENT, USERAGENT);
    curl_setopt($curl, CURLOPT_FAILONERROR, 0);
    curl_setopt($curl, CURLOPT_REFERER, URL_LOGIN);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_POST, 1); // устанавливаем метод POST
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'login='.LOGIN.'&password='.PASSWORD);
    curl_setopt($curl, CURLOPT_HEADER, 0);          // включать заголовок в вывод
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);  // не проверять SSL сертификат
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);  // не проверять Host SSL сертификата
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);  // разрешаем редиректы
    curl_setopt($curl, CURLOPT_NOBODY, 0);          // включать тело в вывод
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, 0); // подробный лог

    $result = curl_exec($curl); // выполняем запрос
    curl_close($curl); // заканчиваем работу curl
    return $result;
}

/**
 * login on php academy and save session ID cookie
 */
function logon_php_academy() {
    $html = logon();
    $dom = \SimpleHtmlDom\str_get_html($html);

    $err = $dom->find('form.col-md-6 div.alert-danger');
    if (count($err)!=0) {
        unlink(COOK_FILE_NAME1);
        die('Logon failed. Wrong login or password!'.PHP_EOL);
    }
}

/**
 * @param string $url
 * @param string $referer
 * @return mixed
 */
function curl_get($url, $referer = '')
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_USERAGENT, USERAGENT);
    curl_setopt($curl, CURLOPT_REFERER, $referer);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);  // не проверять SSL сертификат
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);  // не проверять Host SSL сертификата
    curl_setopt($curl, CURLOPT_COOKIEFILE, COOK_FILE_NAME1); //считать куки из файла
    curl_setopt($curl, CURLOPT_VERBOSE, 0); // подробный лог

    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

/**
 * @param string $url
 * @param string $referer
 */
function get_devionity_cook($url, $referer = '')
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_USERAGENT, USERAGENT);
    curl_setopt($curl, CURLOPT_REFERER, $referer);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);  // не проверять SSL сертификат
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);  // не проверять Host SSL сертификата
    curl_setopt($curl, CURLOPT_VERBOSE, 0); // подробный лог
    curl_setopt($curl, CURLOPT_COOKIEJAR, COOK_FILE_NAME2); //сохранить куки в файл
    curl_setopt($curl, CURLOPT_NOBODY, true);

    curl_exec($curl);
    curl_close($curl);
}

/**
 * @param string $url
 * @return mixed
 */
function get_video_frame($url)
{
    $html = curl_get($url);
    $dom = \SimpleHtmlDom\str_get_html($html);
    return $dom->find('#video_frame');
}

/**
 * @param string $url
 * @return string
 */
function get_video_referer($url) {
    $video_frame = get_video_frame($url);
    if (count($video_frame)==0) {
        echo 'Not found video_frame. Maybe cookie is expired. Try to logon...'.PHP_EOL;
        logon_php_academy();
        $video_frame = get_video_frame($url);
        if (count($video_frame)==0) {
            die('Not found video_frame again. Something is wrong. Exit.'.PHP_EOL);
        }
    }

    $Result = $video_frame[0]-> src;
    get_devionity_cook($Result, $url);
    return $Result;
}

/**
 * @param string $url
 * @return string
 */
function get_file_name ($url) {
    return DESTINATION_FOLDER.DIRECTORY_SEPARATOR.substr($url,39).'.mp4';
}

/**
 * @param string $url
 * @param string $fileName
 */
function download_video_file($url, $fileName) {
    $referer = get_video_referer($url);

    $curl = curl_init();

    $header[] = 'Host: devionity.com';
    $header[] = 'Connection: keep-alive';
    $header[] = 'Accept-Encoding: identity;q=1, *;q=0';

    curl_setopt($curl, CURLOPT_URL, 'https://devionity.com/get_video.php');
    curl_setopt($curl, CURLOPT_USERAGENT, USERAGENT);
    curl_setopt($curl, CURLOPT_REFERER, $referer);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 0);
    curl_setopt($curl, CURLOPT_VERBOSE, 0); // подробный лог
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);// не проверять SSL сертификат
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);// не проверять Host SSL сертификата
    curl_setopt($curl, CURLOPT_RANGE, '0-');
    curl_setopt($curl, CURLOPT_COOKIEFILE, COOK_FILE_NAME2); //считать куки из файла

    $file = fopen($fileName, 'w');
    curl_setopt($curl, CURLOPT_FILE, $file);

    echo "Start download file {$fileName} ...".PHP_EOL;
    curl_exec($curl);

    curl_close($curl);
}

/**
 * @param string $video_link
 */
function load_video ($video_link) {
    echo 'Try to load video on page '.$video_link.PHP_EOL;
    $file_name = get_file_name($video_link);
    if(file_exists($file_name)) {
        echo 'Skip file '.$file_name.PHP_EOL;
    } else {
        download_video_file($video_link, $file_name);
    }
}

/* --------------------------------------------------------------------------------------- */
echo 'Try to logon ...'.PHP_EOL;
logon_php_academy();
echo 'Logged on'.PHP_EOL;

$url_array = [];
if (is_array(URL_VIDEO_PAGES)) {
    if (count(URL_VIDEO_PAGES)!=0) {
        $url_array = URL_VIDEO_PAGES;
    }
} else {
    if (is_string(URL_VIDEO_PAGES)) {
        $url_array[] = URL_VIDEO_PAGES;
    }
}

// try get links for your group
if (count($url_array)==0) {
    echo 'Try get links for your group'.PHP_EOL;
    $html = curl_get('https://php-academy.kiev.ua/video');
    $dom = \SimpleHtmlDom\str_get_html($html);
    $a_links = $dom->find('div.col-md-3 form b a');
    if (count($a_links)==0) {
        die('Link to your group video page not found.');
    }
    if (count($a_links)>1) {
        die('Found too many link to your group video page.');
    }

    $group_link = URL_BASE.$a_links[0]->href;
    echo 'Found group url - '.$group_link.PHP_EOL;

    $html = curl_get($group_link);
    $dom = \SimpleHtmlDom\str_get_html($html);
    $a_links = $dom->find('ul.pagination li a');

    if (count($a_links)==0) {
        $url_array[] = $group_link;
    } else {
        foreach ($a_links as  $item) {
            $url_array[] = URL_BASE.'/video/'.$item->href;
        }
    }
}

foreach($url_array as $urlVideoPage) {

    // если это страница с конкретными видео
    if (strpos($urlVideoPage,'video/view')!==false) {
        load_video($urlVideoPage);
    } else {
        if (strpos($urlVideoPage,'/video')!==false) {
            echo 'Load page '.$urlVideoPage.PHP_EOL;
            $html = curl_get($urlVideoPage);
            echo 'Parse page '.$urlVideoPage.PHP_EOL;
            $dom = \SimpleHtmlDom\str_get_html($html);
            $a_links = $dom->find('div.col-md-6 p b a');

            foreach($a_links as $item) {
                $video_link = URL_BASE.$item->href;
                load_video($video_link);
            }
        } else {
            die('Unknown url - '.$urlVideoPage);
        }
    }
}

if(file_exists(COOK_FILE_NAME1)) {
    unlink(COOK_FILE_NAME1);
}
if(file_exists(COOK_FILE_NAME2)) {
    unlink(COOK_FILE_NAME2);
}
