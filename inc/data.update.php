<?php
$root = $_SERVER["DOCUMENT_ROOT"];
define("INDEX", true);
include $root."/json/JsonServerClass.php";

error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors',1);
//include $root."/inc/db.php";
 
 
require_once $root.'/library/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

$logger = new Logger('logger_service');
$logger->pushHandler(new StreamHandler($root.'/logs/data_update_error.log', Logger::WARNING, false));
$logger->pushHandler(new StreamHandler($root.'/logs/data_update_info.log', Logger::INFO, false));
$logger->pushHandler(new FirePHPHandler());
$logger->addInfo('------------------------------------------------------------------------------------------');
$logger->addInfo('Start running Data Update');


//Функция определения AccountId по AbsID
function GetAccId($AbsId=false,$LoginId) {
   if  ($AbsId) {
    $SelAccId = query("select AccountID from accounts where absid=".$AbsId ." and loginid=".$LoginId); 
    if ($row = $SelAccId->fetch_assoc())
        return $row["AccountID"];
    else return 0;
   } else return 0;
}

//Функция определения дубликатов в таблице операций
function GetDublicate($LoginId,$TypeSysId,$DocumentDate,$DocumentNumber,$Debit,$Credit,$UserSettingId) {
    $sql="select * from operations where LoginId='".$LoginId."' " 
            . "and TypeSysId='".$TypeSysId."' and DocumentDate=STR_TO_DATE('".$DocumentDate."', '%d.%m.%Y') "
            . "and  DocumentNumber='".$DocumentNumber."' and  Debit='".$Debit."' and Credit='".$Credit."'"
            . "and UserSettingId=".$UserSettingId;
    //echo $sql;
    $SelDubl = query($sql); 
    if (mysqli_num_rows($SelDubl) > 0)
       return true;
    else return false;
}

//Функция непосредственного обновления таблицы со счетами
function UpdateAcc($LoginId,$TypeSysId,$Account,$AccountName,$CurrCode,$Rest,$AbsId,$UserSettingId){
    $AccId=GetAccId($AbsId,$LoginId);
    if ($AccId>0) { //уже существует запись, значит обновляем
        $sql="update  accounts Set rest='".$Rest."' , Account='".$Account."' , AccountName='".$AccountName."'  , UserSettingId='".$UserSettingId."' , LastUpdateTime=Now() where accountid=".$AccId;
        $updAcc = query($sql);
       // echo $sql;
        if ($updAcc) return 0;
        else return -1;
    } else
    {//Значит добавляем
        $sql="insert into accounts (LoginId,TypeSysId,Account,AccountName, CurrCode,Rest,AbsId,UserSettingId) values (".$LoginId.",".$TypeSysId.",'".$Account."','".$AccountName."','".$CurrCode."','".$Rest."',".$AbsId.",".$UserSettingId.")";
        //echo $sql;
        $addAcc = query($sql);
        if ($addAcc) return 0;
        else return -1;
    }
}

//Функция непосредственного обновления таблицы с операциями
function UpdateOperation($LoginId,$TypeSysId,$DocumentDate,$DocumentNumber,
                         $Debit, $Credit, $Ground, $DateOperation, $CardNumber,
                         $Ammount_Curr, $AbsId,$Rest,$UserSettingId){
    $AccId=GetAccId($AbsId,$LoginId);
    if ($AccId>0) { //Есть счет, значит можно добавлять 
      if  (!GetDublicate($LoginId,$TypeSysId,$DocumentDate,$DocumentNumber,$Debit, $Credit,$UserSettingId))
      {
        $sql="insert into operations (LoginId,TypeSysId,AccountId,DocumentDate,DocumentNumber, Debit,Credit,Ground,DateOperation,CardNumber,Ammount_Curr,AbsId,Rest,UserSettingId) 
                 values ('".$LoginId."','".$TypeSysId."','".$AccId."',STR_TO_DATE('".$DocumentDate."', '%d.%m.%Y'),'".$DocumentNumber."','".$Debit."','".$Credit."','".$Ground."',STR_TO_DATE('".$DocumentDate."', '%d.%m.%Y'),'".$CardNumber."','".$Ammount_Curr."','".$AbsId."','".$Rest."',".$UserSettingId." )";
        //echo $sql;
        $addOper = query($sql);
        if ($addOper) return 0;
        else return -1;
      }
    } else return -1;
    
}
 

//Собственно само обновление
//$user["loginid"]=2; 
if ($user == true) {
    $select = query("select Id,TypeSysId,LoginInSys,PasswordInSys,DaysForUpdate from usersettings where LoginId=".$user["loginid"]);
    if (mysqli_num_rows($select) > 0) {
        while ($row = $select->fetch_assoc()) {
            if ($row["TypeSysId"] == "1") {
                $logger->addInfo('login-'.$row["LoginInSys"]);
                $logger->addInfo('password-'.$row["PasswordInSys"]);
                
                //Количество обновляемых дней
                $DaysForUpdate="halfyear";
                if ($row["DaysForUpdate"]>"") $DaysForUpdate=$row["DaysForUpdate"];

                //echo 'login-'.$row["LoginInSys"].'<br>';
                //echo 'password-'.$row["PasswordInSys"].'<br>';
                
                //$jsonSdm=JsonServerClass::SDMRestAccount($row["LoginInSys"],$row["PasswordInSys"],'5years');
                $jsonSdm=JsonServerClass::SDMRestAccount($row["LoginInSys"],$row["PasswordInSys"],$DaysForUpdate);
                //$jsonSdm=JsonServerClass::SDMRestAccount($row["LoginInSys"],$row["PasswordInSys"],'7days');
                $logger->addInfo('json-'.$jsonSdm);
                
                //echo 'json-'.$jsonSdm.'<br>';
                if (!$arraySdm=json_decode($jsonSdm,TRUE)) {
                    $logger->addError('Not retrive data from sdm');
                    //echo 'Not retrive data from sdm';
                }
                //var_dump($arraySdm);
                $CountAcc=count($arraySdm["AccountsList"]);
                for ($i=0;$i<$CountAcc;$i++) {
                    /*echo "<br>";
                    echo $arraySdm["AccountsList"][$i]["CurrCode"];
                    echo $arraySdm["AccountsList"][$i]["AccountName"];
                    echo $arraySdm["AccountsList"][$i]["Rest"];
                    echo $arraySdm["AccountsList"][$i]["AcountID"];
                    */
                    
                  if  ( UpdateAcc($user["loginid"],1,
                            $arraySdm["AccountsList"][$i]["Account"],
                            $arraySdm["AccountsList"][$i]["AccountName"],
                            $arraySdm["AccountsList"][$i]["CurrCode"],
                            $arraySdm["AccountsList"][$i]["Rest"],
                            $arraySdm["AccountsList"][$i]["AcountID"],
                            $row["Id"])  < 0 ) {
                                $logger->addError('Error on UpdateAcc');
                                //echo "Error on UpdateAcc"; 
                            }
                 }
                 
                $CountStm=count($arraySdm["AcountStatements"]);
                for ($i=0;$i<$CountStm;$i++) {
                  if  ( UpdateOperation($user["loginid"],1,
                            $arraySdm["AcountStatements"][$i]["DocumentDate"],
                            $arraySdm["AcountStatements"][$i]["DocumentNumber"],
                            $arraySdm["AcountStatements"][$i]["Debit"],
                            $arraySdm["AcountStatements"][$i]["Credit"],
                            str_replace(chr(26),"",$arraySdm["AcountStatements"][$i]["Ground"]), // пытаемся заменить кавычку `
                            $arraySdm["AcountStatements"][$i]["DateOperation"],
                            $arraySdm["AcountStatements"][$i]["CardNumber"],
                            $arraySdm["AcountStatements"][$i]["Ammount_Curr"],
                            $arraySdm["AcountStatements"][$i]["AbsId"],
                            $arraySdm["AcountStatements"][$i]["StatementIncoming"],
                            $row["Id"])  < 0 ) {
                                $logger->addError('Error on UpdateOperation');
                                //echo "Error on UpdateOperation"; 
                            }
                 }                 
                
                
            }
            
            if ($row["TypeSysId"] == "2") {
                $logger->addInfo('login-'.$row["LoginInSys"]);
                $logger->addInfo('password-'.$row["PasswordInSys"]);
                
                //echo 'login-'.$row["LoginInSys"].'<br>';
                //echo 'password-'.$row["PasswordInSys"].'<br>';
                $jsonSber=JsonServerClass::SBERRestAccount($row["LoginInSys"],$row["PasswordInSys"]);
                $logger->addInfo('json-'.$jsonSber);
                //echo 'json-'.$jsonSber.'<br>';
                if (!$arraySber=json_decode($jsonSber,TRUE)) {
                    $logger->addError('Not retrive data from sber');
                    //echo 'Not retrive data from sber';
                }
                //var_dump($arraySber);
                
                $CountCard=count($arraySber["CardList"]);
                for ($i=0;$i<$CountCard;$i++) {
                    /*echo "<br>";
                    echo $arraySdm["AccountsList"][$i]["CurrCode"];
                    echo $arraySdm["AccountsList"][$i]["AccountName"];
                    echo $arraySdm["AccountsList"][$i]["Rest"];
                    echo $arraySdm["AccountsList"][$i]["AcountID"];
                    */
                    
                  if  ( UpdateAcc($user["loginid"],2,
                            $arraySber["CardList"][$i]["CardNumber"],
                            $arraySber["CardList"][$i]["CardType"],
                            $arraySber["CardList"][$i]["CurrCode"],
                            $arraySber["CardList"][$i]["Rest"],
                            $arraySber["CardList"][$i]["CardID"],
                            $row["Id"])  < 0 ) {
                                $logger->addError('Error on UpdateAcc');
                                //echo "Error on UpdateAcc"; 
                            }

                }
                
                
            }
            
            
 
        }
        
    } //esle нет натсроенных систем
    
    
}





