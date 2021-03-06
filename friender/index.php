<?php
date_default_timezone_set('Europe/Ulyanovsk');
require_once('config.php');
require_once('db.php');
require('phpQuery/phpQuery/phpQuery.php');
$time = date('H:i:s');
logs($fd,"\t[$time] \x1b[33mЗапущен бот автоответчик. Ожидание сообщений... \x1b[0m \n\n");

$headers = array(
 'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
 'content-type' => 'application/x-www-form-urlencoded',
 'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36'
);
 
// получаем главную страницу
$get_main_page = post('https://vk.com', array(
 'headers' => array(
  'accept: '.$headers['accept'],
  'content-type: '.$headers['content-type'],
  'user-agent: '.$headers['user-agent']
 )
));
 
// парсим с главной страницы параметры ip_h и lg_h
preg_match('/name=\"ip_h\" value=\"(.*?)\"/s', $get_main_page['content'], $ip_h);
preg_match('/name=\"lg_h\" value=\"(.*?)\"/s', $get_main_page['content'], $lg_h);
 
// посылаем запрос на авторизацию
$post_auth = post('https://login.vk.com/?act=login', array(
 'params' => 'act=login&role=al_frame&_origin='.urlencode('http://vk.com').'&ip_h='.$ip_h[1].'&lg_h='.$lg_h[1].'&email='.urlencode(LOGIN).'&pass='.urlencode(PASS),
 'headers' => array(
  'accept: '.$headers['accept'],
  'content-type: '.$headers['content-type'],
  'user-agent: '.$headers['user-agent']
 ),
 'cookies' => $get_main_page['cookies']
));
 
// получаем ссылку для редиректа после авторизации
preg_match('/Location\: (.*)/u', $post_auth['headers'], $post_auth_location);
 
if(!preg_match('/\_\_q\_hash=/s', $post_auth_location[1])) {
 echo 'Не удалось авторизоваться <br /> <br />'.$post_auth['headers'];
 
 exit;
}
 
// переходим по полученной для редиректа ссылке
$get_auth_location = post(trim(str_replace('_http', '_https', $post_auth_location[1])), array(
 'headers' => array(
  'accept: '.$headers['accept'],
  'content-type: '.$headers['content-type'],
  'user-agent: '.$headers['user-agent']
 ),
 'cookies' => $post_auth['cookies']
));

// получаем ссылку на свою страницу
preg_match('/"uid"\:"([0-9]+)"/s', $get_auth_location['content'], $my_page_id);
 
$my_page_id = $my_page_id[1];
 
while(true){
 $get_my_page = getUserPage($my_page_id, $get_auth_location['cookies']);
 
// если запрошена проверка безопасности
if(preg_match('/act=security\_check/u', $get_my_page['headers'])) {
 preg_match('/Location\: (.*)/s', $get_my_page['headers'], $security_check_location);
 
 if(isset($security_check_location[1])) {
  $security_check_location = explode("\n", $security_check_location[1]);
 }
 
 // переходим на страницу проверки безопасности
 $get_security_check_page = post('https://vk.com'.trim($security_check_location[0]), array(
  'headers' => array(
   'accept: '.$headers['accept'],
   'content-type: '.$headers['content-type'],
   'user-agent: '.$headers['user-agent']
  ),
  'cookies' => $get_auth_location['cookies']
 ));
 
 // получаем hash для запроса на проверку мобильного телефона
 preg_match('/hash: \'(.*?)\'/s', $get_security_check_page['content'], $get_security_check_page_hash);
 
 // вводим запрошенные цифры мобильного телефона
 $post_security_check_code = post('https://vk.com/login.php', array(
  'params' => 'act=security_check&code='.SECUR_CODE.'&al_page=2&hash='.$get_security_check_page_hash[1],
  'headers' => array(
   'accept: '.$headers['accept'],
   'content-type: '.$headers['content-type'],
   'user-agent: '.$headers['user-agent']
  ),
  'cookies' => $get_auth_location['cookies']
 ));
 
 echo 'Запрошена проверка безопасности';
 
 // отображаем свою страницу после проверки безопасности
 $get_my_page = getUserPage($my_page_id, $get_auth_location['cookies']);
 
 //echo iconv('windows-1251', 'utf-8', $get_my_page['content']);
} else {
// echo iconv('windows-1251', 'utf-8', $get_my_page['content']);
    for ($i = 0; $i < 3; $i++) {
     $info = scan($i,$get_my_page);
       if(($info['read']!='')&&(is_importent($info['id'],$fd)==true)){
           $info['read'] = mb_strtolower($info['read']);         
           $info['read'] = rtrim($info['read'],"!?.,/");
           $info['read'] = ltrim($info['read'],"!?.,/");
           $info['read'] = rtrim($info['read'], " ");
           $info['read'] = ltrim($info['read'], " ");
           $msg = answer($info['dialog'],$mysqli,$fd);
            if($msg!='none') {
                 $time = date('H:i:s');
                 logs($fd,"\t[$time] \x1b[32mПолучено: [{$info['dialog']}], от [{$info['id']}]\x1b[0m \n");
                 $msg = urlencode($msg);
                 send_msg($msg,$get_auth_location['cookies'],$info['id'],$fd);
                 $msg='none';
             } 
       }
    }
 }

 sleep(2);   
}
function scan($i,$get_my_page){
    $doc = phpQuery::newDocument($get_my_page['content']);
    $info = array();
    $info['dialog'] = $doc->find("#im_dialogs li:eq($i) .nim-dialog--text-preview")->text();
    $info['read'] = $doc->find("#im_dialogs li:eq($i) .nim-dialog--unread")->text();
    $info['id'] = $doc->find("#im_dialogs li:eq($i)")->attr('data-list-id');
    $doc -> unloadDocument();

    unset($doc);
    
    return $info;
 }
function getUserPage($id = null, $cookies = null) {
 global $headers;
 
 $get = post('https://vk.com/im?tab=unread', array(
  'headers' => array(
   'accept: '.$headers['accept'],
   'content-type: '.$headers['content-type'],
   'user-agent: '.$headers['user-agent']
  ),
  'cookies' => $cookies
 ));
 
 return $get;
}
function get_active($cook){
  $my = post('https://vk.com/al_im.php?', array(
 'params' => "act=a_start&al=1&im_v=2&peer=120161867&prevpeer=0",
 'cookies' => $cook
)); 

$pieces = explode("<!--", $my['headers']);
$str = iconv('windows-1251', 'utf-8', $pieces[1]);
$str = json_decode($str);
$str = $str -> payload;
$str = $str[1];
$str = $str[0];
$str = $str -> lastmsg_meta;
$str = $str[4];
echo $str;
return $str;
}
 
function post($url = null, $params = null, $proxy = null, $proxy_userpwd = null) {
 $ch = curl_init();
 
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_HEADER, 1);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
 
 if(isset($params['params'])) {
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params['params']);
  
 }
 
 if(isset($params['headers'])) {
  curl_setopt($ch, CURLOPT_HTTPHEADER, $params['headers']);
 }
 
 if(isset($params['cookies'])) {
  curl_setopt($ch, CURLOPT_COOKIE, $params['cookies']);
 }
 
 if($proxy) {
  curl_setopt($ch, CURLOPT_PROXY, $proxy);
 
  if($proxy_userpwd) {
   curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_userpwd);
  }
 }
 $result = curl_exec($ch);
 $result_explode = explode("\r\n\r\n", $result);
 
 $headers = ((isset($result_explode[0])) ? $result_explode[0]."\r\n" : '').''.((isset($result_explode[1])) ? $result_explode[1] : '');
 $content = $result_explode[count($result_explode) - 1];
 
 preg_match_all('|Set-Cookie: (.*);|U', $headers, $parse_cookies);
 
 $cookies = implode(';', $parse_cookies[1]);
 
 curl_close($ch);
 
 return array('headers' => $headers, 'cookies' => $cookies, 'content' => $content);
}
//$post_auth['cookies'];

//send_msg('Bivaet',$get_auth_location['cookies'],237467639);
function send_msg($msg,$cook,$touser,$fd){
  $newr =rand(0, 1000000000);
  $ne =rand(0, 1000000000);
  $usr_hash = get_hash($touser,$cook);
  $my = post('https://vk.com/al_im.php?', array(
 'params' => "act=a_send&al=1&entrypoint=&gid=0&guid={$ne}&hash={$usr_hash}&im_v=3&media=&msg={$msg}&random_id={$newr}&to={$touser}",
 'cookies' => $cook
)); 
$msg = urldecode($msg);
$time = date('H:i:s');
logs($fd,"\t[$time] \x1b[36mОтправленно: [$msg] \x1b[0m \n\n");
//var_dump($my);
}

function get_hash($user_id,$cook){
  $my = post('https://vk.com/al_im.php?', array(
 'params' => "act=a_start&al=1&im_v=2&peer={$user_id}&prevpeer=0",
 'cookies' => $cook
)); 

$pieces = explode("<!--", $my['headers']);
$str = iconv('windows-1251', 'utf-8', $pieces[1]);
$str = json_decode($str);
$str = $str -> payload;
$str = $str[1];
$str = $str[0];
$str = $str -> hash;
//var_dump($str);
return $str;
}
function logs($fd,$text){
fwrite($fd, $text);
echo($text);
}
function is_importent($id,$fd){
    //echo("\n\t $id \n");
    $ignore_array = array();
    $ignore_array[] = 249926258;
    $ignore_array[] = 237467639;
    /*$ignore_array[] = 424690246;
    $ignore_array[] = 441036718;
    $ignore_array[] = 152695014;*/

    $ans = false;
    foreach ($ignore_array as $i => $value) {
        if(($id==$value) || $id<2000000000) {
            //$ans=true;
        }
    }
    /*$time = date('H:i:s');
    if($ans==true){
        logs($fd,"\t[$time] \x1b[33mПользователь:\x1b[0m \x1b[36m[$id]\x1b[0m  \x1b[33mважен?\x1b[0m  - \x1b[32m Да\x1b[0m \n\n");
    } else {
        logs($fd,"\t[$time] \x1b[33mПользователь:\x1b[0m \x1b[36m[$id]\x1b[0m  \x1b[33mважен?\x1b[0m  - \x1b[31m Нет\x1b[0m \n\n");
    }*/
    return $ans;
}
?>