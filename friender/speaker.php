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
$to_users = array(249926258);
//$to_users = array(-178013145,237467639,120161867,460886453);

$messages = array(
"1. Какой был твой первый мобильный телефон?",
"2. Какую самую большую шалость ты совершил в детстве и как тебя за нее наказали?",
"3. Какую самую худшую работу тебе приходилось делать?",
"4. Какого известного человека ты встречал?",
"5. Что самое странное и гадкое ты ел?",
"6. Есть такая еда, который ты б не стал делиться?",
"7. От чего тебе трудней отказаться: от кофе или алкоголя?",
"8. Если бы ты мог изменить страну проживания, куда б отправился?",
"9. Ты бы решился на ограбление банка, если б знал, что тебя не поймают?",
"10. Если б ты выступал в цирке, кем бы ты был?",
"11. Если б тебе пришлось прочитать целую энциклопедию, какую б букву ты выбрал?",
"12. Какое насекомое тебе больше всего не нравится?",
"13. Какая была твоя первая любимая песня?",
"14. Если каждый раз при входе в комнату начинала бы играть одна и та же мелодия, какую б ты выбрал?",
"15. Кто-нибудь когда-нибудь спасал твою жизнь?"," А ты?",
"16. Что для тебя личный ад и рай?",
"17. Какой персонаж из фильмов ужасов, по-твоему, самый страшный?",
"18. Если б твоим дальним родственником оказался король, ты б стал этим гордиться?",
"19. Какую страну ты не хотел бы посещать?",
"20. Если б тебе предложили окзаться на обложке любого журнала, какой бы ты выбрал?",
"21. Какая из диснеевских принцесс самая красивая?",
"22. Сколько литров пива ты можешь выпить за один раз?",
"23. В какой книге ты б изменил конец?",
"24. Какую б песню ты исполнил на «X-фактор»?",
"25. Опиши свой день невидимки.",
"26. Что из прошлого в истории ты б хотел изменить?",
"27. Если б тебе предложили стать президентом на день, чтоб ты изменил?",
"28. Какую самую смешную комедию ты знаешь?",
"29. В какой стране ты бы ни за что не стал жить?",
"30. Не устал?",
"31. Какой последний ужин ты б заказал перед смертной казнью?",
"32. По шкале от 1 до 10 какую боль ты испытал?",
"33. Есть ли у тебя шрамы, и как они появились?",
"34. Каким животным ты б хотел быть и почему?",
"35. А сейчас?",
"37. Если б ты мог воплотить в жизнь вымышленного персонажа, кто б это был?",
"38. Если б ты выиграл миллион, что бы купил в первую очередь?",
"39. Какой твой самый любимый вид спорта на Олимпиаде?",
"40. Если б ты попал на необитаемый остров, смог бы выжить?",
"41. Какой твой самый нелюбимый запах?",
"42. Ты веришь в Лох-несское чудовище или снежного человека?",
"43. Какую последнюю книгу ты прочитал?",
"44. Если б ты мог побить мировой рекорд, что за рекорд это был бы?",
"45. Ты бы смог жить на Марсе, если б это было возможно?",
"46. Какую историческую личность ты б уничтожил и почему?",
"47. Какой самый странный сон ты видел?",
"48. Если б тебе предложили выбрать инструмент и научиться на нем играть, что бы ты выбрал?",
"49. Ты б хотел иметь гарем?"," Если да, то кто в нем я ?",
"50. Если б тебе надо было отказаться от одного из пяти чувств, чтоб ты выбрал?",
"51. Если б тебе предложили остаться в одном возрасте навсегда, какой бы ты выбрал?",
"52. Ты б хотел быть бессмертным?",
"53. Какой твой любимый мультфильм?"
);

sender($to_users,$messages,$get_auth_location['cookies'],$fd);
function sender($to_users = array(),$messages = array(),$cook,$fd){
 for ($i = 0; $i < count($to_users); $i++) {
     if($i==0) send_msg("Надеюсь найдется минутка? Я подготовил список из 53 вопроса, которые помогут стать ближе. Я буду задавать их по очереди на каждое твое сообщение. До тех пор пока не получу все ответы не отстану!",$cook,$to_users[$i],$fd);
   for ($j = 0; $j < count($messages); $j++) {
     $msg_interval = rand(1,2);
     $people_intervar = rand(1,5);
     if(is_readed($to_users[$i],$cook)==1){
         sleep($msg_interval);
       send_msg($messages[$j],$cook,$to_users[$i],$fd);
     } else{
         $j--;
         continue;
     }
     
}  
sleep($people_intervar);
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
function is_readed($user_id,$cook){
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
$str = $str -> unread;
//var_dump($str);
return $str;
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