<?php
$root = $_SERVER["DOCUMENT_ROOT"];
require_once $_SERVER["DOCUMENT_ROOT"].'/library/autoload.php';
include $root."/json/JsonServerClass.php";
include $root."/CurlRequest.php";

error_reporting(E_ALL);
ini_set('display_errors',1);
//echo  phpinfo();

//$jsonSber=JsonServerClass::SBERRestAccount('','','');
//echo $jsonSber;
//echo '|'.  str_replace(chr(26),"","HILINS");




