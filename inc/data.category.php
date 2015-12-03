<?php
$root = $_SERVER["DOCUMENT_ROOT"];
define("INDEX", true);
require_once $root.'/library/autoload.php';
include $root."/inc/db.php";
startSession();


error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors',1);
$LoginId=$_SESSION["loginid"];
if ($LoginId <>"") {

//--------------------
    if(isset($_POST['module']) && ($_POST['module']=="NewCategory"))  //Новая категория
        if( (isset($_POST['CategoryName']) && ($_POST['CategoryName']<>"")) 
            && (isset($_POST['CategoryText']) && ($_POST['CategoryText']<>"")) ) {
            $CategoryName=$_POST['CategoryName'];
            $CategoryText=$_POST['CategoryText'];
            $CategoryId=0;
            
            $sql="insert into categoryname (LoginId,CategoryName) values (".$LoginId.",'".$CategoryName."')";
            //echo $sql;
            $addCatName = query($sql);
            if ($addCatName){
                    $sql="select CategoryId from categoryname where LoginId=".$LoginId." and CategoryName='".$CategoryName."' ";
                    //echo $sql;
                    $SelCatId = query($sql); 
                    if ($row = $SelCatId->fetch_assoc()) {
                        //echo ('>'.$CategoryId.'<');
                        $CategoryId=$row["CategoryId"];
                        $sql="insert into categorytext (LoginId,CategoryId,CategoryText) values (".$LoginId.",".$CategoryId.",'".$CategoryText."')";
                        //echo $sql;
                        $addCatText = query($sql);
                         if ($addCatName) echo " Данные успешно сохранены...";
                             else echo " Не получилось происертить в таблицу categorytext";
                    } else "Не получилось получить CategoryId";
                    
            } else echo " Не получилось происертить в таблицу categoryname";
            
    }
    
    
 //--------------------   
 if(isset($_POST['module']) && ($_POST['module']=="UpdateCategory"))  //Обновление категория
        if( (isset($_POST['CategoryId']) && ($_POST['CategoryId']<>"")) 
            && (isset($_POST['UpdateCategoryText']) && ($_POST['UpdateCategoryText']<>"")) ) {
                $CategoryId=$_POST['CategoryId'];
                $UpdateCategoryText=$_POST['UpdateCategoryText'];
               
                $sql="select Id,CategoryId from categorytext where LoginId in (".$LoginId.",0) and Upper(CategoryText) like Upper('%". $UpdateCategoryText."%') ";
                $CheckCatText = query($sql);    
                if (mysqli_num_rows($CheckCatText) > 0){
                    echo " !!!Этот текст уже добавлен для какой-то категории!!! ";
                    return;
                }
                    
                $sql="insert into categorytext (LoginId,CategoryId,CategoryText) values (".$LoginId.",".$CategoryId.",'".$UpdateCategoryText."')";
                $addCatText = query($sql);
                if ($addCatText) echo " Данные успешно сохранены...";
                else echo " Не получилось происертить в таблицу categorytext";
    }    
    
   
}
    
