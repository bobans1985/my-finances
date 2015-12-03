<?php
$root = $_SERVER["DOCUMENT_ROOT"];
define("INDEX", true);
require_once $root.'/library/autoload.php';
include $root."/inc/db.php";
startSession();


error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors',1);



$loginid=$_SESSION["loginid"];
//$loginid=2;
if ($loginid <>"") {
 if(isset($_GET['module']) && ($_GET['module']=="Operations")) {
     $AccId="select AccountId from accounts where CurrCode='RUR'";
     if(isset($_GET['accid']) and $_GET['accid']<>"")  
       $AccId = $_GET['accid'];  
     
    $CredQuery=query('SELECT CONCAT( IF(credit>0.01, CONCAT("+",ROUND(credit, 2)), "-"),IF(Debit>0.01, ROUND(Debit, 2),"" ) ) as Sum_ , DocumentDate, DocumentNumber, ground FROM operations where  LoginID='.$loginid.' and AccountId in ('.$AccId.')  order by  DocumentDate desc');
    if (mysqli_num_rows($CredQuery) > 0) {
        $rows=array();
        $credit_array=array();$credit_array['page']=1;$credit_array['total']=1; 
        while ($r= $CredQuery->fetch_assoc()) {
            $rows[]=array('Sum'=>$r['Sum_'],'DocumentDate'=>$r['DocumentDate'],'DocumentNumber'=>$r['DocumentNumber'],'Ground'=>$r['ground']);
            //$rows[]=array('Sum'=>$r['Sum_'],'DocumentDate'=>$r['DocumentDate'],'DocumentNumber'=>$r['DocumentNumber'],'Ground'=>str_replace(chr(26),"",str_replace("`","",$r['ground'])));
        }
             
        $credit_array['rows']=$rows; 
        echo Json::json_encode($credit_array);
        $CredQuery->close();
    }   
    
   }
   
    if(isset($_GET['module']) && ($_GET['module']=="GroupOperations")) {
     $AccId="select AccountId from accounts where CurrCode='RUR'";
     if(isset($_GET['accid']) and $_GET['accid']<>"")  
       $AccId = $_GET['accid'];  
    
     //Если заданны даты
     $DateFltr="";
     if ( (isset($_GET['StartDate']) and $_GET['StartDate']<>"")  
        and (isset($_GET['EndDate']) and $_GET['EndDate']<>"")) {
             $StartDate = DateTime::createFromFormat('d.m.Y', $_GET['StartDate'])->format('Y-m-d'); 
             $EndDate = DateTime::createFromFormat('d.m.Y', $_GET['EndDate'])->format('Y-m-d'); 
             $DateFltr=" and DocumentDate>='".$StartDate."' and DocumentDate<='".$EndDate."' ";
        }
    $sql='SELECT a.CategoryId,a.CategoryName,b.CategoryText FROM categoryname a,categorytext b where a.CategoryId=b.CategoryId and a.LoginId in ('.$loginid.',0) order by a.CategoryId';
    $Categories=query($sql); 
    $i=0;$CategoryId=0;
    $ArrayCategories=array();
    $CategoryName='';
    if (mysqli_num_rows($Categories) > 0) { 
        while ($rows=  $Categories->fetch_assoc()) {
            $i++;
            if ($CategoryId==$rows['CategoryId']) {$CategoryText=$CategoryText.'|'.$rows['CategoryText'];$CategoryName=$rows['CategoryName'];}
            else {
                if ($i>1) {$ArrayCategories[]=array($CategoryId,$CategoryName,$CategoryText,0);}
                $CategoryId=$rows['CategoryId'];
                $CategoryText=$rows['CategoryText'];
                $CategoryName=$rows['CategoryName'];
            }
        }
        $ArrayCategories[]=array($CategoryId,$CategoryName,$CategoryText,0);
    }     
    
   // print_r($ArrayCategories);
    $sql='SELECT ROUND(SUM(CONCAT( IF(credit>0.01, CONCAT("+",ROUND(credit, 2)), "-"),IF(Debit>0.01, ROUND(Debit, 2),"" ) )),2) as Sum_ , ground FROM operations where  LoginID='.$loginid.' and AccountId in ('.$AccId.') '.$DateFltr.' group by ground order by  Sum_  desc';
    //echo $sql;
    $Transact=query($sql);
    $rows=array();
    mb_internal_encoding('UTF-8'); //для правильной работы upper
   // mb_internal_encoding('CP1251'); //для правильной работы upper
   // echo mb_strpos(' АТМАТТМАТМАТМ ','ATM');
   // echo mb_strpos(mb_strtoupper('Оплата услуги;МТС;9150183274'),mb_strtoupper('МТС'));
    //echo  mb_strtoupper('Получение наличных в АТМ BANKOMAT 391240 25772577\RUZA');
   // echo  utf8_to_cp1251('ATM');
  //  echo chr(208);
  //  echo ord(Utf8Win('М')).ord(Utf8Win('Т')).ord(Utf8Win('С'));
    $Transact_array=array();
    while ($r= $Transact->fetch_assoc()) {
        $flag=false;
        for ($i=0;$i<count($ArrayCategories);$i++) {
               $Transact_array=explode('|',$ArrayCategories[$i][2]);
               foreach ($Transact_array as $Transact_array_) {
                //   echo '<'.$r['ground'].'=='.$Transact_array_.'>'.mb_strpos($r['ground'], $Transact_array_).'<br>';
                   if (mb_strpos( mb_strtoupper($r['ground']), mb_strtoupper($Transact_array_))!==false)
                   {
                      // echo 'YESSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS';
                      // echo '<'.$r['ground'].'=='.$Transact_array_.'><br>';
                         $flag=true;
                         $ArrayCategories[$i][3]=  floatval($ArrayCategories[$i][3]+ floatval($r['Sum_']));
                   }
               }
        }
        if (!$flag and floatval($r['Sum_'])<>0) {
            $rows[]=array('Sum'=>$r['Sum_'],'Ground'=>$r['ground']);
        }
        }
    
      //  print_r($rows);
       // print_r($ArrayCategories);
        foreach ($ArrayCategories as $ArrayCategories_) {
            if (floatval($ArrayCategories_[3])<>0)
                $rows[]=array('Sum'=>$ArrayCategories_[3],'Ground'=>$ArrayCategories_[1]);
        }
    
        
        //На всякий случай пишем сгуппированные данные в таблицу
        $sql="delete from categoryoperations where  LoginId=".$loginid;
        $delCatOper = query($sql);
        foreach ($rows as $rows_) {
               $sql="insert into categoryoperations (LoginId,Sum,CategoryText) 
                     values ('".$loginid."','".$rows_['Sum']."','".$rows_['Ground']."' )";
               //echo $sql;
               $addCatOper = query($sql);
        }
        //--------------------------------------------------------

        
        
        echo Json::json_encode($rows);
    
    
   }

       
}