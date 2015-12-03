<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

$main["title"] = "".$main["title"];

if ($user == true) {
    
  $select = query("SELECT id FROM usersettings WHERE LoginId='".$user["loginid"]."' LIMIT 1");
  $true = mysqli_num_rows($select);
  $select->close();
  if ($true > 0) {
      
  //Если вдруг клиент нажал кнопку обновить      
  if (isset($_GET["update"]) and  $_GET["update"]==1)  $_SESSION["dataUpdate"]=1; 
 
  // Получаем валлюту, профиль и формируем html код блоков
  // Делаем массив page, участвующий в формировании шаблона
  // И прогоняем файл через шаблонизатор
  $page["user"] = "
Здравствуйте, <a href=\"/?user=setting\" class=\"abutton\">".$user["login"]."</a>
&emsp;<a href=\"/?user=setting\" class=\"abutton\">Мои банки</a>
&emsp;<a href=\"/?user=logout\" class=\"abutton\">Выход</a>";
  
  $page["balance"] = "<div class=\"balance_load\"></div>";

  // Получаем все системы и достаем аккаунты пользователя
  // К каждой системе
  $select = query("SELECT * FROM `typeofsystem`");
  if (mysqli_num_rows($select) > 0) {
    $system = "";
    while ($row = $select->fetch_assoc()) {
      $settings = query("SELECT id FROM usersettings WHERE LoginId='".$user["loginid"]."' and TypeSysId='".$row["TypeSysId"]."' LIMIT 1");
      $setting = mysqli_num_rows($settings);
      $settings->close();
      if ($setting > 0) {
        $system = $system."
<div class=\"system_title\"><span>".$row["Name"]."</span></div>
<table class=\"system_table\" id=\"system".$row["TypeSysId"]."\">
<tr><td class=\"system_load\"></td></tr>
</table>";
      }
    }
  }
  $select->close();

  $page["systems"] = $system;
  $page["valute"]="<div class=\"balance_load\"></div>";
  $content = $root."/tmp/page.tpl";

  } else {
    include $root."/inc/user.setting.php";
  }

} else {

$content = $root."/tmp/login.tpl";

}

?>