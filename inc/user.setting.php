<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

$main["title"] = "Настройки - ".$main["title"];

if ($user == true) {

  $system = "
<div class=\"system_title\">
        <table>
            <tr><td width=\"95%\" align=\"center\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Настройки учетной записи </td>
                <td><form name=\"DeleteAccount\" method=\"POST\" action=\"/inc/settings.php\">
                    <a id=\"DelAcc\" href=\"javascript:;\" onclick=\"parentNode.submit();\" \">delete</a>
                    <input type=\"hidden\" name=\"id\" value=\"delete\">
                    </form>
                </td>
            </tr>
       </table>
</div>
<form name=\"ChangeLogin\" method=\"POST\" action=\"/inc/settings.php\">
<table class=\"system_table\">
<tr><td>Логин:</td><td>".$user["login"]."</td></tr>
<tr><td>E-Mail:</td><td>".$user["email"]."</td></tr>
<tr><td>Новый пароль:</td><td><input type=\"password\" name=\"password\"></td></tr>
<tr><td>Подтверждение пароля:</td><td><input type=\"password\" name=\"password1\"></td></tr>
<tr><td>Старый пароль:</td><td><input type=\"password\" name=\"password2\"></td></tr>
<tr><td colspan=\"2\">
<input type=\"hidden\" name=\"id\" value=\"save\">
<input type=\"submit\" id=\"saveLogin\" name=\"save\" value=\"Сохранить\">
</td></tr>
</table>
</form>";

  $selectsys = query("SELECT * FROM `typeofsystem`");
    if (mysqli_num_rows($selectsys) > 0) {
    while ($row = $selectsys->fetch_assoc()) {
      
      $settings = false;
      $row["selected"] = "";
      $selectsettings = query("SELECT * FROM usersettings WHERE LoginId='".$user["loginid"]."' and TypeSysId='".$row["TypeSysId"]."' LIMIT 1");
      if (mysqli_num_rows($selectsettings)) {
        $settings = mysqli_fetch_assoc($selectsettings);
        $row["selected"] = " selected";
      }
      $selectsettings->close();
      
      $row["input"] = "";
      if ($row["Online"] == "1" and $settings == false) { 
        $row["input"] = "
<tr><td>Логин:</td><td>
<input type=\"text\" name=\"login\">
</td></tr>
<tr><td>Пароль:</td><td>
<input type=\"password\" name=\"password\">
</td></tr>
<tr><td>Повторите пароль:</td><td>
<input type=\"password\" name=\"password1\">
</td></tr>"; 
      } else if ($settings == true and $row["Online"] == "1") {
        $row["input"] = "
<tr><td>Логин:</td><td><input type=\"text\" name=\"login\" value=\"".$settings["LoginInSys"]."\"></td></tr>
<tr><td>Новый пароль:</td><td>
<input type=\"password\" name=\"password\">
</td></tr>
<tr><td>Повторите пароль:</td><td>
<input type=\"password\" name=\"password1\">
</td></tr>"; 
      }
      
      //Если система позволяет обновлять операции онлайн
      if ($settings == true and $row["OnlineStm"] == "1") {
       
            $row["input"] = $row["input"]." 
                <tr><td>Количество обновляемых дней</td>
                    <td>
                        <select name=\"DaysForUpdate\">";
            if ($settings["DaysForUpdate"]=="7days") {
                $row["input"] = $row["input"]." <option value=\"7days\" selected>Неделя</option>";
            } else $row["input"] = $row["input"]." <option value=\"7days\">Неделя</option>";
            if ($settings["DaysForUpdate"]=="month") {
                $row["input"] = $row["input"]." <option value=\"month\" selected>Месяц</option>";
            } else $row["input"] = $row["input"]." <option value=\"month\">Месяц</option>";
             if ($settings["DaysForUpdate"]=="halfyear") {
                $row["input"] = $row["input"]." <option value=\"halfyear\" selected>Полгода</option>";
            } else $row["input"] = $row["input"]." <option value=\"halfyear\">Полгода</option>";
            if ($settings["DaysForUpdate"]=="year") {
                $row["input"] = $row["input"]." <option value=\"year\" selected>Год</option>";
            } else $row["input"] = $row["input"]." <option value=\"year\">Год</option>";
            if ($settings["DaysForUpdate"]=="5years") {
                $row["input"] = $row["input"]." <option value=\"5years\" selected>Пять лет</option>";
            } else $row["input"] = $row["input"]." <option value=\"5years\">Пять лет</option>";
           $row["input"] = $row["input"]."</select></td></tr>";

          
      }
      
      
      $system = $system."
<div class=\"system_title\"><span>".$row["Name"]."</span></div>
<form name=\"ChangeSettings\" method=\"POST\" action=\"/inc/settings.php\">
<table class=\"system_table\">
<tr><td>Статус</td>
<td>
<select name=\"on\">
<option value=\"0\">Отключено</option>
<option value=\"1\"".$row["selected"].">Включено</option>
</select>
</td></tr>
".$row["input"]."
<tr><td colspan=\"2\">
<input type=\"hidden\" name=\"id\" value=\"".$row["TypeSysId"]."\">
<input type=\"submit\" id=\"saveSystem\" name=\"save\" value=\"Сохранить\">
</td></tr>
</table>
</form>";
        
      }
    }
    $selectsys->close();
    
  $setting["system"] = $system;
  $setting["user"] = "
    Здравствуйте, <a href=\"/?user=setting\" class=\"abutton\">".$user["login"]."</a>
    &emsp;<a href=\"/?user=setting\" class=\"abutton\"><font color=\"red\">Мои банки</font></a>
    &emsp;<a href=\"/?user=logout\" class=\"abutton\">Выход</a>";
  unset($system);
  $content = $root."/tmp/settings.tpl";

} else {
  $content = $root."/tmp/login.tpl";
}

?>