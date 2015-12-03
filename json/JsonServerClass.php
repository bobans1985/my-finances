<?php
require_once __DIR__.'/../library/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


class JsonServerClass
{
     /**
     * @param string $LoginName
     * @param string $Password
     * @param string $Period
     * @return string $XML
     */
function SDMRestAccount($LoginName,$Password,$Period=false)
{  
$Dir_path= str_replace('\\', '/', __DIR__);
$logger = new Logger('logger_service');
$logger->pushHandler(new StreamHandler($Dir_path.'/../logs/sdm_json_debbug-'.$LoginName.'.log', Logger::DEBUG, false));
$logger->pushHandler(new StreamHandler($Dir_path.'/../logs/sdm_json_error-'.$LoginName.'.log', Logger::WARNING, false));
$logger->pushHandler(new StreamHandler($Dir_path.'/../logs/sdm_json_info-'.$LoginName.'.log', Logger::INFO, false));
$logger->pushHandler(new FirePHPHandler());
$logger->addInfo('------------------------------------------------------------------------------------------');
$logger->addInfo('Start running SDMRestAccount class');

$sLogin=$LoginName;
$sPassword=$Password;
$postData = 'password='.$sPassword.'&username='.$sLogin; //Логин и пароль

$data=  http_requestSDM('https://retail.sdm.ru/logon',true,$postData,false,'','Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',60);
if (!$data) {
    $logger->addError('нет ответа от сервера');
    return 'нет ответа от сервера';
}
//echo($data);
$logger->addDebug('Starting SDM Http trafic for: '.$postData.'; Result Data:'.$data);
$cookie=substr($data,strpos($data,'cookie=')+7,strlen($data));
if (!$cookie)    {
    $logger->addError('не смогли залогиниться - нет кукисов');
    return 'не смогли залогиниться - нет кукисов';
}
    $logger->addDebug('Cookie- '.$cookie);
    
    
    
//$data=  http_requestSDM('https://retail.sdm.ru//user/confirmlogon',false,'','https://retail.sdm.ru','','Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',60);
//echo $data;

$RequestToken=str_replace('__RequestVerificationToken" type="hidden" value="','', strstr($data,'__RequestVerificationToken" type="hidden" value="'));
$RequestToken= substr($RequestToken,0,strpos($RequestToken,'"'));
//echo '$RequestToken='.$RequestToken;

$postData='otp=&mode=nosms&returnUrl=&__RequestVerificationToken='.$RequestToken;
$data=  http_requestSDM('https://retail.sdm.ru/user/confirmlogon',true,$postData,'https://retail.sdm.ru','','Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',60);
//echo '$postData='.$postData;
//echo $data;

    if (!$data) {
        $logger->addError('не можем получить информацию по счетам');
        return 'не можем получить информацию по счетам';
    }

    
   $json=Array (
        "AccountsList"=> Array(),
        "CardList"=> Array(),
        "AcountStatements"=>Array()
    
    );
    
    
    $logger->addDebug('NEXT SDM Http trfafic; Result Data:'.$data);
    $body=substr($data,strpos($data,'body=')+5,strlen($body)-8);
    //echo($body);
    $doc= new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($body);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($doc);


    $TableTrAccount = $xpath->query('//div[@class="Content-Center"]/div[1]//table/tr/td');
    $CountAccount= ($TableTrAccount->length/4);
    
    //По счетам
    $ArrayAccountID=array();
    for ($i=0; $i<$CountAccount;$i++) {
        for ($j = 0; $j < 4; $j++) //4 <td>
        {
            $logger->addInfo('i='.$i.';j='.$j.';'.nodeContent($TableTrAccount->item($i*4+$j)));
                      switch ($j)
                    {
                        case 3:
                            $AccountId=
                                          str_replace('" "ajax-window"История платежей/a','',
                                          str_replace('a href="/finances/account/','',  
                                          nodeContent($TableTrAccount->item($i*4 + $j)) 
                                          ));
                            $ArrayAccountID[]=$AccountId;
                            break;
                        default:
                            break;
                    }
        }
    }
   $ArrayAccount=array();
   $logger->addInfo('$ArrayAccountID: '. implode(' ',$ArrayAccountID) );
     foreach ($ArrayAccountID as $ArrayAccountID_) {
       $logger->addInfo('Дергаем информацию по счету с ID=' .$ArrayAccountID_);
       $Start_date=date("d.m.Y",mktime(0, 0, 0, date("m"),   date("d")-1,   date("Y")));
       switch ($Period)
         { 
          case "7days": $Start_date=date("d.m.Y",mktime(0, 0, 0, date("m"),   date("d")-7,   date("Y")));
                        break; 
          case "month": $Start_date=date("d.m.Y",mktime(0, 0, 0, date("m")-1,   date("d"),   date("Y")));
                        break;
          case "halfyear":  $Start_date=date("d.m.Y",mktime(0, 0, 0, date("m")-6,   date("d"),   date("Y")));
                         break;  
          case "year":  $Start_date=date("d.m.Y",mktime(0, 0, 0, date("m"),   date("d"),   date("Y")-1));
                         break;  
          case "5years":$Start_date=date("d.m.Y",mktime(0, 0, 0, date("m"),   date("d"),   date("Y")-5)); 
                         break; 
          default: 
                         break;
         }
       $postData = 'endDate='.date("d.m.Y").'&id='.$ArrayAccountID_.'&periodtype=&startDate='.$Start_date;       
       $logger->addInfo('PostData:' .$postData);
       $data=  http_requestSDM('https://retail.sdm.ru/finances/account/'.$ArrayAccountID_,true,$postData,false,$cookie,'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',120);
       $logger->addDebug('SDM extract account http trafic for: '.$postData.'; Result Data:'.$data);
       if (!$data) {
           $logger->addError('не можем получить информацию по выписке');
           return 'не можем получить информацию по выписке';
       }
       $body=substr( $data,strpos( $data,'body=')+5,strlen($data)-8-strpos( $data,'body='));
       //$logger->addInfo( 'BODY_1='. $body );
       $doc->loadHTML($body);
       libxml_clear_errors();
       $xpath = new DOMXPath($doc);


       //Выписка                      
         $TableTrStatement = $xpath->query('/html/body/div/div[1]/form/div[4]/div[2]/table/tbody/tr/td');
 $TableTrStatementBlocking = $xpath->query('/html/body/div/div[1]/form/div[5]/table/tbody/tr/td');
        $StatementIncoming = $xpath->query('//*[@id="account_data"]/table/tr[2]/td[1]');

        $Account = $xpath->query('//*[@id="account_data"]/table/tr[1]/td[1]');
        $Account_=utf8_decode($Account->item(0)->nodeValue);
        $ArrayAccount[]=array("AccountId"=>$ArrayAccountID_,"Account"=>substr($Account_,0,strpos($Account_," ")));

        $StatementIncoming_=str_replace(chr(194),'',
                            str_replace(chr(160),'',
                            str_replace(chr(13),'',
                            str_replace(chr(10),'',$StatementIncoming->item(0)->nodeValue))));
        $StatementIncoming_ = substr($StatementIncoming_,0,  strlen($StatementIncoming_)-strpos($StatementIncoming_,' '));
        $StatementIncoming_float=floatval($StatementIncoming_);
        $td_count=8;

       $CountStatement= (($TableTrStatement->length-4)/$td_count);
       $CountStatementBlocking=$TableTrStatementBlocking->length/3;
       $logger->addInfo('CountStatement:'.$CountStatement);
       $logger->addInfo('CountStatementBlocking:'.$CountStatementBlocking);
       $logger->addInfo('StatementIncoming:'.$StatementIncoming_);
       
       //$ExtractList=Array();
               
       /* foreach ($TableTrStatement as $tag1) {
                  $logger->addInfo('NodeContent1:'.(utf8_decode($tag1->nodeValue)));
          }*/
       
       //По выписке
       for ($i=0; $i<$CountStatement;$i++) {  //По td_count строчек в каждом table/tr
         for ($j = 0; $j < $td_count; $j++) //td_count <td>
           {
             $logger->addInfo('i='.$i.';j='.$j.';'.utf8_decode($TableTrStatement->item($i*$td_count  + $j)->nodeValue).'|');
             switch ($j)
                    {                       
                     case 0:
                            $DocumentDate=  $TableTrStatement->item($i*$td_count+$j)->nodeValue;
                            break;
                     case 1:
                            $DocumentNumber=  $TableTrStatement->item($i*$td_count+$j)->nodeValue;
                            break;
                     case 2:
                            $Debit= str_replace('P','',
                                    cp1251_to_utf8(
                                                     preg_replace('#\s#', '', utf8_to_cp1251($TableTrStatement->item($i*$td_count+$j)->nodeValue))
                                    ));
                            break;
                     case 3:
                            $Credit= str_replace('P','',
                                    cp1251_to_utf8(
                                                     preg_replace('#\s#', '', utf8_to_cp1251($TableTrStatement->item($i*$td_count+$j)->nodeValue))
                                    ));
                            break;
                     case 4:
                            $Ground= utf8_decode($TableTrStatement->item($i*$td_count  + $j)->nodeValue);
                            break;
                     case 5:
                            $DateOperation= nodeContent($TableTrStatement->item($i*$td_count+$j));
                            break;
                     case 6:
                            $CardNumber= nodeContent($TableTrStatement->item($i*$td_count+$j));                         
                            break;
                     case 7:
                            $Ammount_Curr= $TableTrStatement->item($i*$td_count+$j)->nodeValue;
                            $StatementIncoming=$StatementIncoming_float;
                            break;
                     default:
                            break;
                            }
              
           }
                    //$ExtractList[]=Array($ArrayAccountID_=>Array("DocumentDate"=>$DocumentDate,
                      $ExtractList[]=Array("DocumentDate"=>$DocumentDate,
                                         "DocumentNumber"=>$DocumentNumber,
                                         "Debit"=>$Debit,
                                         "Credit"=>$Credit,
                                         "Ground"=>$Ground,
                                         "DateOperation"=>$DateOperation,
                                         "Ammount_Curr"=>$Ammount_Curr,
                                         "CardNumber"=>$CardNumber,
                                         "StatementIncoming"=>$StatementIncoming,
                                         "AbsId"=>$ArrayAccountID_
                             );

         }
         if (count($ExtractList)>1) {
            $json["AcountStatements"]=$ExtractList;
            //unset($ExtractList);
        } //else $json["AcountStatements"][]=Array($ArrayAccountID_=>Array());
             
         
         
 /*        
         //По блокировкам 
       $xmlAcountStatementsBlocking = $xml->createElement("AcountStatementsBlocking");
      
       $xmlAcountStatementsBlockingAttribute1= $xml->createAttribute("Count");
       $xmlAcountStatementsBlockingAttribute1->value=$CountStatementBlocking;
       $xmlAcountStatementsBlocking->appendChild($xmlAcountStatementsBlockingAttribute1);
       $xmlAcountStatementsBlockingAttribute2= $xml->createAttribute("AccountId");
       $xmlAcountStatementsBlockingAttribute2->value=$ArrayAccountID_;
       $xmlAcountStatementsBlocking->appendChild($xmlAcountStatementsBlockingAttribute2);
       $xmlRoot->appendChild($xmlAcountStatementsBlocking);
       $td_count=3;
       for ($i=0; $i<$CountStatementBlocking;$i++) {  //По td_count строчек в каждом table/tr
         $xmlExtractBlockingList=$xml->createElement("ExtractBlockingList");
         $xmlExtractBlockingListAttribute1= $xml->createAttribute("Count");
         $xmlExtractBlockingListAttribute1->value=$CountStatementBlocking;
         $xmlAcountStatementsBlocking->appendChild($xmlExtractBlockingListAttribute1);
         $xmlAcountStatementsBlocking->appendChild($xmlExtractBlockingList);
         for ($j = 0; $j < $td_count; $j++) //td_count <td>
           {
               switch ($j)
                    {                       
                     case 0:
                            $xmlDocumentDateBlocking=$xml->createElement("DocumentDate");
                            $xmlExtractBlockingList->appendChild($xmlDocumentDateBlocking);
                            $xmlDocumentDateBlocking->nodeValue=utf8_decode($TableTrStatementBlocking->item($i*$td_count+j)->nodeValue);
                            break;
                     case 1:
                            $xmlAmountBlocking=$xml->createElement("AmountBlocking");
                            $xmlExtractBlockingList->appendChild($xmlAmountBlocking);
                            $xmlAmountBlocking->nodeValue=utf8_decode($TableTrStatementBlocking->item($i*$td_count+$j)->nodeValue);
                            break;
                     case 2:
                            $xmlWhereBlocking=$xml->createElement("WhereBlocking");
                            $xmlExtractBlockingList->appendChild($xmlWhereBlocking);
                            $xmlWhereBlocking->nodeValue=utf8_decode($TableTrStatementBlocking->item($i*$td_count+$j)->nodeValue);
                            break;
                     default:
                            break;
                     }
             
           }
         } */
       
     }

    $data=  http_requestSDM('https://retail.sdm.ru/',false,'','https://retail.sdm.ru','','Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',60);
    if (!$data) {
        $logger->addError('не можем получить информацию по счетам');
        return 'не можем получить информацию по счетам';
    }
   
 
    $logger->addDebug('NEXT SDM Http trfafic; Result Data:'.$data);
    $body=substr($data,strpos($data,'body=')+5,-8);
    $doc= new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($body);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($doc);
    
    
    $TableTrAccount = $xpath->query('//div[@class="Content-Center"]/div[1]//table/tr/td');
    $TableTrCard = $xpath->query('//div[@class="Content-Center"]/div[2]//table/tr/td');
    if ($TableTrAccount->length>1) $CountAccount= ($TableTrAccount->length/4); else $CountAccount=0;
    if ($TableTrCard->length>1) $CountCard=($TableTrCard->length/6); else $CountCard=0;

    $logger->addInfo('CountAccount='.$CountAccount);
    $logger->addInfo('CountCard='.$CountCard);

    
     //По счетам
    $ArrayAccountID=array();
    for ($i=0; $i<$CountAccount;$i++) {
        for ($j = 0; $j < 4; $j++) //4 <td>
        {
           // $logger->addInfo('i='.$i.';j='.$j.';'.nodeContent($TableTrAccount->item($i*4+$j)));
                      switch ($j)
                    {
                        case 0:
                            $CurrCode=GetSdmTextFromAlt($TableTrAccount->item($i*4));
                            break;
                        case 1:
                            $AccountName=$TableTrAccount->item($i*4 +1)->nodeValue;
                            //$AccountName=nodeContent($TableTrAccount->item($i*4 +j+1));
                            break;
                        case 2:
                            $Rest=   HtmlToFloat($TableTrAccount->item($i*4 + $j));
                            break;
                        case 3:
                            $AccountId=
                                          str_replace('" "ajax-window"История платежей/a','',
                                          str_replace('a href="/finances/account/','',  
                                          nodeContent($TableTrAccount->item($i*4 + $j)) 
                                          ));
                            $ArrayAccountID[]=$AccountId;
                            break;
                        default:
                            break;
                    }
        }
       foreach ($ArrayAccount as $ArrayAccount_) {
           if ($ArrayAccount_["AccountId"]==$AccountId) 
               $Account=$ArrayAccount_["Account"];
       }
       $json["AccountsList"][]=Array("CurrCode"=>$CurrCode,
                                     "AccountName"=>$AccountName,
                                     "Account"=>$Account,
                                     "Rest"=>$Rest,
                                     "AcountID"=>$AccountId
                               ); 
       $Account="";
    }
    
    
    //По картам
    
    for ($i=0; $i<$CountCard;$i++) {
        for ($j = 0; $j < 6; $j++) //6 <td>
        {
              $logger->addInfo('i='.$i.';j='.$j.';'.nodeContent($TableTrCard->item($i*6+$j)));
          //  printf('i='.$i.';j='.$j.';'.nodeContent($TableTrCard->item($i*6+$j)).'<br>');
                     switch ($j)
                    {
                        case 0:
                            $CurrCodeCard=GetSdmTextFromAlt($TableTrCard->item($i*6+$j));
                            break;
                        case 1:
                            //$CardType=GetSdmTextFromAlt($TableTrCard->item($i*6+$j));
                            break;
                        case 2:
                            $CardType=$TableTrCard->item($i*6+$j)->nodeValue;
                            $CardID=GetSdmCardId($TableTrCard->item($i*6+$j));
                            break;
                        case 4:
                            $RestCard= nodeContent($TableTrCard->item($i*6+$j));
                            break;
                        case 5:
                            $CardClose=  str_replace('срок действия: ','',nodeContent($TableTrCard->item($i*6+$j)));
                            break;
                        default:
                            break;
                    }
                    
        }
     $json["CardList"][]=Array("CurrCode"=>$CurrCodeCard,
                               "CardType"=>$CardType,
                               "CardID"=>$CardID,
                               "RestCard"=>$Rest,
                               "CardClose"=>$CardClose
                                );    
    }
    
    
     
     
     

    $logger->addInfo('Good result: '.Json::json_encode($json));
    $logger->addInfo('End SDMRestAccount class');
    return Json::json_encode($json);
       
}



     /**
     * @param string $LoginName
     * @param string $Password
     * @return string $XML
     */
public function SBERRestAccount($LoginName,$Password)
{
$Dir_path= str_replace('\\', '/', __DIR__);
$logger = new Logger('logger_service');
$logger->pushHandler(new StreamHandler($Dir_path.'/../logs/sber_json_debbug-'.$LoginName.'.log', Logger::DEBUG, false));
$logger->pushHandler(new StreamHandler($Dir_path.'/../logs/sber_json_error-'.$LoginName.'.log', Logger::WARNING, false));
$logger->pushHandler(new StreamHandler($Dir_path.'/../logs/sber_json_info-'.$LoginName.'.log', Logger::INFO, false));
$logger->pushHandler(new FirePHPHandler());

$logger->addInfo('------------------------------------------------------------------------------------------');
$logger->addInfo('Start running SBERRestAccount class');

$deviceprint="deviceprintversion=3.4.0.0_2&pm_fpua=mozilla/5.0 (windows nt 6.3) applewebkit/537.36 (khtml, like gecko) chrome/36.0.1985.143 safari/537.36|5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.143 Safari/537.36|Win32&pm_fpsc=24|1280|1024|984&pm_fpsw=&pm_fptz=4&pm_fpln=lang=ru|syslang=|userlang=&pm_fpjv=1&pm_fpco=1&pm_fpasw=widevinecdmadapter|pepflashplayer|internal-remoting-viewer|ppgooglenaclpluginchrome|pdf|npspwrap|npbssplugin|npgoogleupdate3|npdeployjava1|npjp2|npmeetingjoinpluginoc|npvlc|npitunes|npctrl&pm_fpan=Netscape&pm_fpacn=Mozilla&pm_fpol=true&pm_fposp=&pm_fpup=&pm_fpsaw=1280&pm_fpspd=24&pm_fpsbd=&pm_fpsdx=&pm_fpsdy=&pm_fpslx=&pm_fpsly=&pm_fpsfse=&pm_fpsui=&pm_os=Windows&pm_brmjv=36&pm_br=Chrome&pm_inpt=&pm_expt=";
$htmlinjection='htmlinjection={"functions":{"names":[],"excluded":{"size":0,"count":0},"truncated":false},"inputs":[],"iframes":[],"scripts":[],"collection_status":3}';
$manvsmachinedetection='manvsmachinedetection=1,1,INPUT:text,6;2,2,INPUT:text,22@1,3,0;1,1,0;1,1,0;1,1,0;1,1,0;1,1,0;1,1,0;1,1,0;1,4,0@0,1409563058988,0';


$postData=  urldecode('field(login)='.$LoginName.'&field(password)='.$Password.'&operation=button.begin&'.$deviceprint.'&'.$htmlinjection.'&'.$manvsmachinedetection);
//$postData=  urldecode('field(login)='.$LoginName.'&field(password)='.$Password).'&operation=button.begin';
//$logger->addInfo('>>>>>'.$postData);

$data=  http_requestSDM('https://online.sberbank.ru/CSAFront/index.do',false,'',false,'','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537',30);
if (!$data) {
    $logger->addError('не можем получить информацию по счетам URL 1');
    return 'не можем получить информацию по счетам';
}
$logger->addDebug('DATA1:'.$data .'END DATA1');
$cookie=substr($data,strpos($data,'cookie=')+7,strlen($data));
$logger->addInfo('COOKIE1:'.$cookie .'END DATA 1');


$data=  http_requestSDM('https://online.sberbank.ru/CSAFront/login.do',true, $postData ,false,$cookie,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537',30);
if (!$data)  {
    $logger->addError('не можем получить информацию по счетам URL 2');
    return 'не можем получить информацию по счетам';
    }
$logger->addDebug('DATA2:'.$data .'END DATA2');
$URL_WITH_TOKEN=str_replace(';cookie=','',str_replace('">','',strstr($data,'https'))); 
if (!$URL_WITH_TOKEN) 
    {
    $logger->addError('нет кукисов - возможно введен неверный пароль URL_WITH_TOKEN!');
    return 'нет кукисов - возможно введен неверный пароль!';
    }
$logger->addInfo($URL_WITH_TOKEN);

                        
$data=  http_requestSDM($URL_WITH_TOKEN,true, '' ,false,$cookie,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537',30);
if (!$data) return 'не можем получить информацию по счетам URL 3';
$cookie=substr($data,strpos($data,'cookie=')+7,strlen($data));
$logger->addInfo('COOKIE3:'.$cookie);
$logger->addDebug('DATA3:'.$data.'END DATA 3');



//$data=  http_request('https://online.sberbank.ru/PhizIC/private/accounts.do',true, '' ,false,$cookie,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36',60);
//$data=  http_requestSDM('https://online.sberbank.ru/PhizIC/private/cards/list.do',true, '' ,false,$cookie,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537',30);
$data=  http_requestSDM('https://node2.online.sberbank.ru/PhizIC/private/cards/list.do',true, '' ,false,$cookie,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537',30);
if (!$data) return 'не можем получить информацию по счетам';

$logger->addDebug('DATA4:'.$data.'END DATA 4');


   $body=substr($data,strpos($data, 'body=')+5,-8);
  //  echo($body);
if (!$body) return 'не можем получить информацию по счетам';

    $doc= new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($body);
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);
    $CardProductName = $xpath->query('//div[@class="productCover activeProduct"]//span[@class="mainProductTitle mainProductTitleLight"]');
    //$CardProductName = $xpath->query('//span[@class="mainProductTitle mainProductTitleLight"]');
    $CardNumber = $xpath->query('//div[@class="accountNumber decoration-none"]');
    $CardAmount = $xpath->query('//span[@class="overallAmount nowrap"] | //span[@class="overallAmount nowrap negativeAmount"]');
    $CardhRef = $xpath->query('//div[@class="productName"]//a[contains(@href,"/PhizIC/private/cards/info.do?id=")]');

   $CardsArray='';
   $ArrayAccountID='';
   
   $logger->addInfo('Count CardProductName='.count($CardProductName));
   $logger->addInfo('Count CardNumber='.count($CardNumber));
   $logger->addInfo('Count CardAmount='.count($CardAmount));
   $logger->addInfo('Count CardhRef='.count($CardhRef));
   
   $i=0;
   foreach ($CardProductName as $CardProductName_) 
   {
       
       $logger->addInfo('for I='.$i);
       $logger->addInfo('CardProductName:'.GetCardProductNameSber($CardProductName_));
       $logger->addInfo('CardNumber:'.GetCardNumberSber($CardNumber->item($i)));
       $logger->addInfo('CardAmount:'.nodeContent($CardAmount->item($i)));
       $logger->addInfo('CardhRef:'.GetCardhRefValueSber($CardhRef->item($i)));
       
       $CardsArray[]=GetCardProductNameSber($CardProductName->item($i));
       $CardsArray[]=GetCardNumberSber($CardNumber->item($i));
       $CardsArray[]=nodeContent($CardAmount->item($i));
       $CardsArray[]=GetCardhRefValueSber($CardhRef->item($i));;
       $ArrayAccountID[]=GetCardhRefValueSber($CardhRef->item($i));
       //Попробуем с кодами валют
       $logger->addInfo('CurrCode:'.CurrCodeSber($CardAmount->item($i)));       
       $CardsArray[]=CurrCodeSber($CardAmount->item($i));
       
       $i++;
   }
 
    $json=Array (
        "AccountsList"=> Array(),
        "CardList"=> Array(),
        "AcountStatements"=>Array()
    
    );
       
$logger->addInfo('>>>>>RealResult starting');   
$logger->addInfo('Count Array='.count($CardsArray));
for ($i=0;$i<count($CardsArray)/5; $i++) {
for ($j=0; $j<5;$j++)
{
       //echo ('i='.$i.';j='.$j.'<br>');
       switch ($j)
       {
           case 0:
                $logger->addInfo('CardProductName:'.$CardsArray[$i*5+$j]);
                $CardType=$CardsArray[$i*5+$j];
                break;
           case 1:
                $logger->addInfo('CardNumber:'.$CardsArray[$i*5+$j]);
                $CardNumber=$CardsArray[$i*5+$j];
                break;
           case 2:
                $logger->addInfo('CardAmount:'.$CardsArray[$i*5+$j]);
               $Rest=  str_replace(",",".", str_replace(' ','',$CardsArray[$i*5+$j]));
                break;
           case 3:
                $logger->addInfo('CardhRef:'.$CardsArray[$i*5+$j]);
                $CardID=$CardsArray[$i*5+$j];
                break;
           case 4:
                $logger->addInfo('CurrCode:'.$CardsArray[$i*5+$j]);
                $CurrCode=$CardsArray[$i*5+$j];
                break;            
           default:
               break;
       }
}
                if ($CardType<>'') 
                              $json["CardList"][]=Array("CardType"=>$CardType,
                                                 "CardNumber"=>$CardNumber,
                                                 "Rest"=>$Rest,
                                                 "CardID"=>$CardID,
                                                 "CurrCode"=>$CurrCode
                                                 ); 
}

//https://online.sberbank.ru/PhizIC/private/cards/print.do?id=9045783&printAbstract=true

/*
foreach ($ArrayAccountID as $ArrayAccountID_)
    {
  //  sel:c:9045783
//fromDateString:01/01/2014
//toDateString:03/03/2014
  //  $postData= 'fromDateString=01/01/2014&toDateString=03/0132014&sel:c:9045783';
    $data=  http_request('https://online.sberbank.ru/PhizIC/private/accounts/print.do?sel=c:'.$ArrayAccountID_.'&fromDateString=01/01/2014&toDateString=03/03/2014',true, '' ,false,$cookie,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537',30);
    if (!$data) {
           $logger->addError('не можем получить информацию по выписке');
           //return 'не можем получить информацию по выписке';
       }
    $logger->addDebug('DATA5_STATEMENT1:'.$data.'END DATA5_STATEMENT1');


     $body=substr($data,strpos($data, 'body=')+5,-8);
     $logger->addDebug('DATA5:'.$body.'END DATA 5');
   
     $doc->loadHTML($body);
     libxml_clear_errors();
     $xpath = new DOMXPath($doc);

$logger->addInfo('STATEMENT7:');
      //Выписка                      
     $TableTrStatement = $xpath->query('//table');
               foreach ($TableTrStatement as $tag1) {
                  $logger->addInfo('NodeContent1:'.($tag1->nodeValue));
          }  
         
    
    }*/

    $logger->addInfo('Good result: '.Json::json_encode($json));
    $logger->addInfo('End SBERRestAccount class');
    return Json::json_encode($json);

}



}




