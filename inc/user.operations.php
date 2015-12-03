<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

$main["title"] = $main["title"];


if ($user == true) {
    

  $select = query("SELECT id FROM usersettings WHERE LoginId='".$user["loginid"]."' LIMIT 1");
  $true = mysqli_num_rows($select);
  $select->close();
  if ($true > 0) {

  $page["accid"]= "";  
  $page["user"] = "
    Здравствуйте, <a href=\"/?user=setting\" class=\"abutton\">".$user["login"]."</a>
    &emsp;<a href=\"/?user=setting\" class=\"abutton\">Мои банки</a>
    &emsp;<a href=\"/?user=logout\" class=\"abutton\">Выход</a>";
 
      $system="<table class=\"system_table\" width=\"100px\" id=\"AccTable\">";
      $acc = query("SELECT * FROM accounts WHERE LoginId='".$user["loginid"]."'");
      while ($r= $acc->fetch_assoc()) {
          if(isset($_GET['accid']) and $r["AccountId"]==$_GET['accid'] ) 
                $system = $system."<tr><td bgcolor=\"#a6d3ff\"><a href=\"/?user=operations&accid=".$r["AccountId"]."\">".$r["AccountName"]." ".$r["Account"]."</a></td></tr>";                  
          else
                $system = $system."<tr><td><a href=\"/?user=operations&accid=".$r["AccountId"]."\">".$r["AccountName"]." ".$r["Account"]."</a></td></tr>";
      }
   $acc->close();
   $page["systems"] = $system."</table>";
   $page["AccountName"]="Все рублевые счета";
   if(isset($_GET['accid'])) {
       $page["accid"]=$_GET['accid'];
       //Название счета еще бы знать)
       $acc = query("SELECT * FROM accounts WHERE LoginId='".$user["loginid"]."' and AccountId=".$page["accid"]);
       $r= $acc->fetch_assoc(); 
       $page["AccountName"]=$r["AccountName"]." ".$r["Account"];
       $acc->close();
   }
  
$content = $root."/tmp/operations.tpl";

  } else {
    include $root."/inc/user.setting.php";
  }

} else {

$content = $root."/tmp/login.tpl";

}

?>
