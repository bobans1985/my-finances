<?php
require_once 'autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

//Клас правильной конвертации array to json
class Json{
    static function json_encode($data){
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
                function($val){ 
                   return mb_decode_numericentity('&#'.intval($val[1], 16).';', array(0, 0xffff, 0, 0xffff), 'utf-8');
                }, json_encode($data) 
         ); 
     }
}

// Converts a DOMNodeList to an Array that can be easily foreached
function dnl2array($domnodelist) {
    $return = array();
    for ($i = 0; $i < $domnodelist->length; ++$i) {
        $return[] = $domnodelist->item($i);
    }
    return $return;
}

function nodeContent($n, $outer=false) {
    //$d = new DOMDocument('1.0','cp1251');
    //$d = new DOMDocument();
    $d = new DOMDocument('1.0','UTF-8');
    $b = $d->importNode($n->cloneNode(true),true);
    $d->appendChild($b); 
   // $h = $d->saveHTML();
    $h = $d->saveXML();

    if (!$outer) $h = 
        str_replace('" "ajax-window"История платежей/a','',
        str_replace('a href="/finances/account/','',      
        str_replace(' руб','',
        str_replace(' руб.','',          
        str_replace('a href="/PhizIC/private/cards/info.do?id=','',
        //str_replace('&#8722;','-',
        str_replace('span "mainProductTitle mainProductTitleLight"','',
        str_replace('div "accountNumber decoration-none"','',
        str_replace('span "overallAmount nowrap"','',
        str_replace('span "overallAmount nowrap negativeAmount"','',
        //str_replace('" onclick="return false;"span "mainProductTitle"Electron/span','',


            str_replace('td','',
            str_replace('class=','',
            str_replace('td class="text-right"','',
            str_replace('td width="30%" class="text-right"','',
                  
                       str_replace('>','',
                       str_replace('<','',
                       str_replace('&nbsp;','',
                       substr($h,strpos($h,'>')+2,-(strlen($n->nodeName)+4))
            )))))))
            
            )))))))//)
            ));                
    
    return $h;
}

 function nodeContent2($n, $outer=false) {
    $d = new DOMDocument('1.0','utf-8');
    // $d = new DOMDocument();
    $b = $d->importNode($n->cloneNode(true),true);
    $d->appendChild($b); 
    $h = $d->saveHTML();
   // $h = $d->saveXML();

    if (!$outer) $h =  

                  
                       str_replace('>','',
                       str_replace('<','',
                       str_replace('&nbsp;','',
                       substr($h,strpos($h,'>')+2,-(strlen($n->nodeName)+4))
            )));                
    
    return $h;
}

 function GetCardProductNameSber($n, $outer=false) {
    $CardProductNameSber=nodeContent($n);
    $CardProductNameSber=str_replace('"','',
                         mb_strrchr($CardProductNameSber,'"'));                    
    return $CardProductNameSber;
}
 function GetCardNumberSber($n, $outer=false) {
    $CardNumberSber=nodeContent($n);
    $CardNumberSber=substr($CardNumberSber,0,strpos($CardNumberSber,','));                    
    return $CardNumberSber;
}

function GetCardhRefValueSber($n, $outer=false) {
    $CardhRefValueSber=nodeContent($n);
    $CardhRefValue=substr($CardhRefValueSber,0,strpos($CardhRefValueSber,'"'));
    return $CardhRefValue;
}


 function CurrCodeSber($n, $outer=false) {
    $CurrCode='RUR';
    $d = new DOMDocument('1.0','utf-8');
    $b = $d->importNode($n->cloneNode(true),true);
    $d->appendChild($b); 
    $h = $d->saveHTML();
    if (strpos($h,'&#1088;&#1091;&#1073;') > 0) $CurrCode='RUR';
    else $CurrCode=$h; // будем смотреть какие существуют
    return $CurrCode;
}

 function GetSdmTextFromAlt($n, $outer=false) {
    $TextFromAlt='';
    $d = new DOMDocument('1.0','utf-8');
    $b = $d->importNode($n->cloneNode(true),true);
    $d->appendChild($b); 
    $h = $d->saveHTML();
    $PosAlt=strpos($h,'alt="');
    if ($PosAlt > 0) {
        $PosEndAlt=strpos($h,'"',$PosAlt+5);
        if ($PosEndAlt > 0) $TextFromAlt=substr($h,$PosAlt+5,$PosEndAlt-$PosAlt-5);
        else $TextFromAlt=$h;
    }
    else $TextFromAlt=$h; // будем смотреть какие существуют
    return $TextFromAlt;
}

 function GetSdmCardId($n, $outer=false) {
    $CardId='';
    $d = new DOMDocument('1.0','utf-8');
    $b = $d->importNode($n->cloneNode(true),true);
    $d->appendChild($b); 
    $h = $d->saveHTML();
    $PosCardId=strpos($h,'/finances/card/');
    if ($PosCardId > 0) {
        $PosEndCardId=strpos($h,'"',$PosCardId+15);
        if ($PosEndCardId > 0) $CardId=substr($h,$PosCardId+15,$PosEndCardId-$PosCardId-15);
        else $CardId=$h;
    } else $CardId=$h;
    return $CardId;
}

 function HtmlToFloat($n, $outer=false) {
    $d = new DOMDocument('1.0','utf-8');
    $b = $d->importNode($n->cloneNode(true),true);
    $d->appendChild($b); 
    $h = $d->saveHTML();
   
    $result= str_replace('&nbsp;','',
            str_replace('<td class="text-right">','',
            str_replace(chr(194),'',
             str_replace(chr(160),'',
             str_replace(chr(13),'',
             str_replace(chr(10),'',   
             str_replace('</td>','',        
             $h)))))));
    
    if ($result <> '') return $result;
    else return '0.00';
}

function cp1251_to_utf8($s) 
  { 
  if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "WINDOWS-1251") 
    { 
    $c209 = chr(209); $c208 = chr(208); $c129 = chr(129); 
    for($i=0; $i<strlen($s); $i++) 
      { 
      $c=ord($s[$i]); 
      if ($c>=192 and $c<=239) $t.=$c208.chr($c-48); 
      elseif ($c>239) $t.=$c209.chr($c-112); 
      elseif ($c==184) $t.=$c209.$c209; 
      elseif ($c==168)    $t.=$c208.$c129; 
      else $t.=$s[$i]; 
      } 
    return $t; 
    } 
  else 
    { 
    return $s; 
    } 
   } 
  
  function utf8_to_cp12511($utf8) {
 
    $windows1251 = "";
    $chars = preg_split("//",$utf8);
 
    for ($i=1; $i<count($chars)-1; $i++) {
        $prefix = ord($chars[$i]);
        $suffix = ord($chars[$i+1]);
 
        if ($prefix==215) {
            $windows1251 .= chr($suffix+80);
            $i++;
        } elseif ($prefix==214) {
            $windows1251 .= chr($suffix+16);
            $i++;
        } else {
            $windows1251 .= $chars[$i];
        }
    }
 
    return $windows1251;
}
 
function Utf8Win($str,$type="w")  {
    static $conv='';
 
    if (!is_array($conv))  {
        $conv = array();
 
        for($x=128;$x<143;$x++)  {
            $conv['u'][]=chr(209).chr($x);
            $conv['w'][]=chr($x+112);
 
        }
 
        for($x=144;$x<=191;$x++)  {
            $conv['u'][]=chr(208).chr($x);
            $conv['w'][]=chr($x+48);
        }
 
        $conv['u'][]=chr(208).chr(129);
        $conv['w'][]=chr(168);
        $conv['u'][]=chr(209).chr(145);
        $conv['w'][]=chr(184);
        $conv['u'][]=chr(208).chr(135);
        $conv['w'][]=chr(175);
        $conv['u'][]=chr(209).chr(151);
        $conv['w'][]=chr(191);
        $conv['u'][]=chr(208).chr(134);
        $conv['w'][]=chr(178);
        $conv['u'][]=chr(209).chr(150);
        $conv['w'][]=chr(179);
        $conv['u'][]=chr(210).chr(144);
        $conv['w'][]=chr(165);
        $conv['u'][]=chr(210).chr(145);
        $conv['w'][]=chr(180);
        $conv['u'][]=chr(208).chr(132);
        $conv['w'][]=chr(170);
        $conv['u'][]=chr(209).chr(148);
        $conv['w'][]=chr(186);
        $conv['u'][]=chr(226).chr(132).chr(150);
        $conv['w'][]=chr(185);
    }
 
    if ($type == 'w') {
        return str_replace($conv['u'],$conv['w'],$str);
    } elseif ($type == 'u') {
        return str_replace($conv['w'], $conv['u'],$str);
    } else {
        return $str;
    }
}

   
   
function utf8_to_cp1251($s) 
  { 
  $byte2='';
  $i=0;
  if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "UTF-8") 
    { 
    for ($c=0;$c<strlen($s);$c++) 
      { 
      $i=ord($s[$c]); 
      if ($i<=127) $out.=$s[$c]; 
      if ($byte2) 
        { 
        $new_c2=($c1&3)*64+($i&63); 
        $new_c1=($c1>>2)&5; 
        $new_i=$new_c1*256+$new_c2; 
        if ($new_i==1025) 
          { 
          $out_i=168; 
          } else { 
          if ($new_i==1105) 
            { 
            $out_i=184; 
            } else { 
            $out_i=$new_i-848; 
            } 
          } 
        $out.=chr($out_i); 
        $byte2=false; 
        } 
        if (($i>>5)==6) 
          { 
          $c1=$i; 
          $byte2=true; 
          } 
      } 
    return $out; 
    } 
  else 
    { 
    return $s; 
    } 
  } 


//Заменитель стандартного CURL, для возможности заменить CURLOPT_FOLLOWLOCATION
function curl_redir_exec($ch,$cookies=false,$lastcookies=false) {
	static $curl_loops = 0;
	static $curl_max_loops = 20;
	if ($curl_loops++ >= $curl_max_loops) {
		$curl_loops = 0;
		return FALSE;
	}
   
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        if ($cookies) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookies);
		$lastcookies=$cookies;
	}
        






/*
static $post='username=&password=';
echo $curl_loops;
if ($curl_loops==2) {
    curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.8) Gecko/20061025 Firefox/1.5.0.8');
$header = array("Host: retail.sdm.ru","Origin: https://retail.sdm.ru");
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

curl_setopt($ch,CURLOPT_AUTOREFERER,true);
curl_setopt($ch,CURLOPT_HTTP_VERSION,1.1);
curl_setopt($ch, CURLOPT_REFERER, "https://retail.sdm.ru/logon");
}*/



	//Поехали
	$data = curl_exec($ch);
        
        //echo($data);
	if (curl_error($ch)) return false;
	$data_=$data;
        //echo $data_.'<br>';
	list($header, $data) = explode("\n\r", $data, 2);
	//echo $header;
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$redirect_url = '/Location: (^\n)/i';
	if ($http_code == 301 || $http_code == 302) {
		$matches = array();
		preg_match($redirect_url, $header, $matches);
		$new_url = str_replace('Location: ', '',
			current(
				explode("\n",
					substr($header,
						strpos($header, "Location:"), 500))));
		//Бывает, что в локайшене не весь путь
                if (strpos($new_url,"https")!==0) $new_url='https://retail.sdm.ru'.$new_url;
	    //echo('NEW-URL:'.$new_url.'<br>');
	    preg_match_all('/Set-Cookie: (.*?)=(.*?);/i',$header,$res);
        			$cookie_='';
                        foreach ($res[1] as $key => $value) {
                            $cookie_.=$value.'='.$res[2][$key].'; ';
                        }
		//if ($cookie_<>'') echo('<br>NEW-COOKIES-'.$cookie_.'<br>');
		if (!$new_url) {
			$curl_loops = 0;
			return 'header='.$header.';body='.$data.';cookie='.$lastcookies;
		}
		curl_setopt($ch, CURLOPT_URL, $new_url);
		if ($cookie_<>'') return curl_redir_exec($ch, $cookie_);
		else if ($lastcookies) return curl_redir_exec($ch,$lastcookies,$lastcookies);
		else return curl_redir_exec($ch);
	}
	else
	{
		$curl_loops = 0;
        if (!$lastcookies) {
        preg_match_all('/Set-Cookie: (.*?)=(.*?);/i',$header,$res);
        $cookie_='';
        	foreach ($res[1] as $key => $value) {
                      $cookie_.=$value.'='.$res[2][$key].'; ';
	        }
        } else  $cookie_=$lastcookies;
 
 		 return 'header='.$header.';body='.$data.';cookie='.$cookie_;
	}
}



// Универсальная функция для HTTP(S) GET/POST запросов
	function http_request($url,$post=FALSE, $data='', $referer=FALSE, $cookie=FALSE, $user_agent=FALSE, $timeout=30) {
	    /*
	      ПОДРОБНАЯ ИНФОРМАЦИЯ: 
	      $url          -   URL адрес запроса
	      $post         -   POST запрос: TRUE или FALSE (не обязательно)
	      $data         -   Данные POST запроса (не обязательно)
	      $referer      -   HTTP Referer (не обязательно)
	      $cookie       -   Строка значений cookies (не обязательно)
	      $user_agent   -   Используемый User Agent (не обязательно)
	      $timeout      -   Максимальное время ожидания в секундах (не обязательно)
	    */
	    $http = FALSE;
	    $url = trim($url);
//      $header = array("Host: retail.sdm.ru","Referer: https://retail.sdm.ru/logon","Origin: https://retail.sdm.ru");
	    if(!empty($url)) {
	        $post = ($post?TRUE:FALSE);
	        $timeout = ($timeout<0?0:intval($timeout));
	        if(function_exists('curl_init')) {
	            if($curl = curl_init()) {
//	            echo ('<br>'.$url.'<br>');
	                curl_setopt($curl, CURLOPT_URL, $url);
                        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);   Отключили, потому что используем ручную обработку ридиректа
                        curl_setopt($curl, CURLOPT_HEADER, TRUE);
//               curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	                curl_setopt($curl, CURLOPT_POST, $post);
	                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	                if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
	                if($cookie) curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	                if($user_agent) curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
	                if($post) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	                curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
                      //$http = curl_exec($curl);
	                $http = curl_redir_exec($curl);
                    //echo $http;
	               /* if (strpos($http,'header=')) {
                        $header=substr($http,0,  curl_getinfo($curl,CURLINFO_HEADER_SIZE));
                        $body=substr($http,curl_getinfo($curl,CURLINFO_HEADER_SIZE));
                        preg_match_all('/Set-Cookie: (.*?)=(.*?);/i',$header,$res);
                        $cookie_='';
                        foreach ($res[1] as $key => $value) {
                            $cookie_.=$value.'='.$res[2][$key].'; ';
                        }

                       } */
        	                curl_close($curl);
	            }
	        }
	    }
	   return $http;
//	  return 'header='.$header.';body='.$body.';cookie='.$cookie_;
	}
        
        
  // Универсальная функция для HTTP(S) GET/POST запросов
	function http_requestSDM($url,$post=FALSE, $data='', $referer=FALSE, $cookie=FALSE, $user_agent=FALSE, $timeout=30) {
	    /*
	      ПОДРОБНАЯ ИНФОРМАЦИЯ: 
	      $url          -   URL адрес запроса
	      $post         -   POST запрос: TRUE или FALSE (не обязательно)
	      $data         -   Данные POST запроса (не обязательно)
	      $referer      -   HTTP Referer (не обязательно)
	      $cookie       -   Строка значений cookies (не обязательно)
	      $user_agent   -   Используемый User Agent (не обязательно)
	      $timeout      -   Максимальное время ожидания в секундах (не обязательно)
	    */
	    $http = FALSE;
	    $url = trim($url);
	    if(!empty($url)) {
	        $post = ($post?TRUE:FALSE);
	        $timeout = ($timeout<0?0:intval($timeout));
	        if(function_exists('curl_init')) {
	            if($curl = curl_init()) {
	                curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);  // Отключили, потому что используем ручную обработку ридиректа
                        curl_setopt($curl, CURLOPT_HEADER, TRUE);
	                curl_setopt($curl, CURLOPT_POST, $post);
	                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	                if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
                        curl_setopt($curl, CURLOPT_COOKIE,'tmpfile.tmp');
                        curl_setopt($curl, CURLOPT_COOKIEJAR,'tmpfile.tmp');
                        curl_setopt($curl, CURLOPT_COOKIEFILE,'tmpfile.tmp');
	                if($user_agent) curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
	                if($post) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	                curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
                        $http = curl_exec($curl);
                        $header=substr($http,0,  curl_getinfo($curl,CURLINFO_HEADER_SIZE));
                        $body=substr($http,curl_getinfo($curl,CURLINFO_HEADER_SIZE));
                        preg_match_all('/Set-Cookie: (.*?)=(.*?);/i',$header,$res);
                        $cookie_='';
                        foreach ($res[1] as $key => $value) {
                            $cookie_.=$value.'='.$res[2][$key].'; ';
                        }
        	                curl_close($curl);
	            }
	        }
	    }
	  return 'header='.$header.';body='.$body.';cookie='.$cookie_;
	}
        
        
        
/*

   //--------------------------------------Sql выборки-----------------------------------------------     

//Получаем LoginId, если не существует - создаем
function ReturnLoginId($login) {      
$logger = new Logger('Library');
$logger->pushHandler(new StreamHandler('../'.__DIR__.'/Log/Library.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());
$logger->addInfo('Start running library:ReturnLoginId');


$LoginId=0;
$query = 'Select ID from loginInIbs where TypeSystem="SDM" and  LoginName="'.$login.'"';
$result=mysql_query($query);
$r=mysql_fetch_row($result);
 if (!isset($r[0])) {
     $query = 'INSERT INTO loginInIbs Set TypeSystem="SDM" , LoginName="'.$login.'"';
     $logger->addInfo('SQL:'.$query );
     if (!mysql_query($query)) {
            $logger->addInfo('SQL result error:'.mysql_error());
            die('Error on work with SQL');
        }
    
     $query = 'Select ID from loginInIbs where TypeSystem="SDM" and  LoginName="'.$login.'"';
     $logger->addInfo('SQL:'.$query );
     $result=mysql_query($query);
     $r=mysql_fetch_row($result);
     $LoginId=$r[0];
 }
 else $LoginId=$r[0];
 if ($LoginId > 0) $GLOBALS['Login_Id']=$LoginId;
 $logger->addInfo('Login_Id=:'.$LoginId );
 return $LoginId;
 
} 

function OperationsCount($login){
    $Login_Id=ReturnLoginId($login);
    if ($Login_Id==0) die('Not check LoginId');
    
    $query = 'Select count(*) from operations where Login_Id="'.$Login_Id.'"';
    $result=mysql_query($query);
     if (!$result) {
            $logger->addInfo('SQL result error:'.mysql_error());
            die('Error on work with SQL');
        }
     $r=mysql_fetch_row($result);
     return $r[0];
}

function AccountsCount($login){
    $Login_Id=ReturnLoginId($login);
    if ($Login_Id==0) die('Not check LoginId');
    
    $query = 'Select count(*) from accounts where Login_Id="'.$Login_Id.'"';
    $result=mysql_query($query);
     if (!$result) {
            $logger->addInfo('SQL result error:'.mysql_error());
            die('Error on work with SQL');
        }
     $r=mysql_fetch_row($result);
     return $r[0];
}*/

         
        
    function startSession($isUserActivity=true, $prefix=null) {
    $sessionLifetime = 900; //15 минут сессия
    $idLifetime = 60;

    if ( session_id() ) return true;
    session_name('MYFINANCESRU'.($prefix ? '_'.$prefix : ''));
    ini_set('session.cookie_lifetime', 0);
    if ( ! session_start() ) return false;

    $t = time();

    if ( $sessionLifetime ) {
        if ( isset($_SESSION['lastactivity']) && $t-$_SESSION['lastactivity'] >= $sessionLifetime ) {
            destroySession();
            return false;
        }
        else {
            if ( $isUserActivity ) $_SESSION['lastactivity'] = $t;
        }
    }

    if ( $idLifetime ) {
        if ( isset($_SESSION['starttime']) ) {
            if ( $t-$_SESSION['starttime'] >= $idLifetime ) {
                session_regenerate_id(true);
                $_SESSION['starttime'] = $t;
            }
        }
        else {
            $_SESSION['starttime'] = $t;
        }
    }

    return true;
}

function destroySession() {
    if ( session_id() ) {
        session_unset();
        setcookie(session_name(), session_id(), time()-60*60*24);
        session_destroy();
    }
}




