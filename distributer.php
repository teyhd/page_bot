<?php
date_default_timezone_set('Europe/Ulyanovsk');
require_once('config.php');
require_once('db.php');
require('phpQuery/phpQuery/phpQuery.php');
$time = date('H:i:s');
logs($fd,"\t[$time] \x1b[33mЗапущена рассылка сообщений... \x1b[0m \n\n");
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
//ddos(315502055,"Я запустил ддос.\nИзвини...",$get_auth_location['cookies'],$fd);
function ddos($to_user,$mess,$cook,$fd){
    $counter = 0;
    while(true){
        $counter++;
        send_msg("[$counter] $mess",$cook,$to_user,$fd);
        $rand = rand(1,2);
        sleep($rand);
    }
}
$to_users = array(
555601347,
224366530,
254088396,
237467639,
237467639,
553271940,
505845678,
181702058,
69725769,
93903804,
315502055,
503843362,
460886453,
227699020,
549826116,
408831105,
38778623,
226763276,
159414626,
261596561,
260182733,
301808000,
120161867,
397016024,
434715300,
502770707,
256700059,
344793924,
260298884,
253492903,
476570153,
383881711,
457218510,
361830949,
407915055,
);
//$to_users = array(-178013145,237467639,120161867,460886453);
$messages = array("Подошел к концу 2019 год❗","Искренне желаю в новом году, познать больше счастья.🍸 \nДостигать новых вершин, самореализовываться. 🧰🧰\n Заниматься тем, что приносит удовольствие. 👍🏻👍🏻👍🏻\n А еще побольше радостных моментов, которые перейдут в приятные воспоминания, и встреч с истинными друзьями.\n Хочу сказать спасибо за 2019 год.🧡💛💚\n Каков бы он ни был он уходит.💥💥\nОставив за собой массу впечатлений");
sender($to_users,$messages,$get_auth_location['cookies'],$fd);
function sender($to_users = array(),$messages = array(),$cook,$fd){
 for ($i = 0; $i < count($to_users); $i++) {
   for ($j = 0; $j < count($messages); $j++) {
     send_msg($messages[$j],$cook,$to_users[$i],$fd);
      sleep(1);
}  
sleep(5);
}
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
logs($fd,"\t[$time] \x1b[36mОтправленно: \x1b[33m[$msg]\x1b[0m \x1b[36mпользователю [{$touser}] \x1b[0m \n\n");
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
?>