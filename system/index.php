<?

define("INDEX", true);

$root = $_SERVER["DOCUMENT_ROOT"];
include $root."/inc/db.php";
include $root."/system/admin.php";

ini_set('display_errors',1);
Error_Reporting(E_ALL);

$main = array("title" => "Админ панель");

if ($admin == true) {

$get = $_GET;
if (isset($get["page"]) and !preg_match("/^[0-9]+$/", $get["page"]) and $get["page"] != "add") {
  unset($get);
  $error404 = true;
}

if (!isset($get["page"])) {
  $select = query("SELECT * FROM pages");
  if (mysqli_num_rows($select) > 0) {
    $html = "";
    $i = 1;
    while ($row = $select->fetch_assoc()) {
      $html = $html."
<li>
<span>
<a href=\"/system/?page=".$row["Pageid"]."\">Редактировать</a> &
<a href=\"/system/?page=".$row["Pageid"]."&action=delete\">Удалить</a>
</span>".$i++.". ".$row["Title"]."
</li>
";
    }
    $html = "<span style=\"float: right;\"><a href=\"/system/?page=add\">Добавить страницу</a></span>Список всех страниц<hr/><ul>".$html."</ul>";
  } else {
    $html = "На сайте нет страниц";
  }
  $select->close();
  $content = $html;
} 

if (isset($get["page"]) and $get["page"] != "add") {
  $select = query("SELECT * FROM pages WHERE PageId='".$get["page"]."' LIMIT 1");
  if (mysqli_num_rows($select) > 0) {
    $page = mysqli_fetch_assoc($select);
    include $root."/system/edit.php";
    $selected = "";
    $selected1 = "";
    $selected2 = "";
    if ($page["Public"] == "1") {
      $selected = " selected";
    }
    if ($page["topmenu"] == "1") {
      $selected1 = " selected";
    }
    if ($page["bottommenu"] == "1") {
      $selected2 = " selected";
    }
    $html = "
<span style=\"float: right;\"><a href=\"/system/\">Список страниц</a></span>Редактировать страницу<hr/>
<form name=\"PageEdit\" id=\"PageEdit\" action=\"#\" method=\"post\">
".$err."
<table>
<tr><td></td><td></td></tr>
<tr><td>Заголовок:</td><td><input name=\"title\" type=\"text\" value=\"".$page["Title"]."\"></td></tr>
<tr><td>Текст:</td><td><textarea rows=\"10\" cols=\"50\" name=\"content\">".$page["Content"]."</textarea></td></tr>
<tr><td>Алиас:</td><td><input name=\"alias\" type=\"text\" value=\"".$page["alias"]."\"></td></tr>
<tr><td>Ключи:</td><td><input name=\"meta_keys\" type=\"text\" value=\"".$page["MetaKeys"]."\"></td></tr>
<tr><td>Писание:</td><td><input name=\"meta_desc\" type=\"text\" value=\"".$page["MetaDesc"]."\"></td></tr>
<tr><td>Статус:</td><td><select name=\"public\"><option value=\"0\">Скрыто</option><option value=\"1\"".$selected.">Опубликовано</option></select></td></tr>
<tr><td>Отображать в верхнем меню?</td><td><select name=\"topmenu\"><option value=\"0\">Нет</option><option value=\"1\"".$selected1.">Да</option></select></td></tr>
<tr><td>Отображать в нижнем меню?</td><td><select name=\"bottommenu\"><option value=\"0\">Нет</option><option value=\"1\"".$selected2.">Да</option></select></td></tr>
<tr><td colspan=\"2\"><input name=\"save\" type=\"submit\" value=\"Сохранить!\"></td></tr>
</table>
</form>
    ";
     $content = $html;
  } else {
    $content = "";
  }
} else if (isset($get["page"]) and $get["page"] == "add") {
  include $root."/system/edit.php";
  $html = "
<span style=\"float: right;\"><a href=\"/system/\">Список страниц</a></span>Добавить страницу<hr/>
".$err."
<form name=\"PageEdit\" id=\"PageEdit\" action=\"#\" method=\"post\">
<table>
<tr><td></td><td></td></tr>
<tr><td>Заголовок:</td><td><input name=\"title\" type=\"text\" value=\"\"></td></tr>
<tr><td>Текст:</td><td><textarea rows=\"10\" cols=\"50\" name=\"content\"></textarea></td></tr>
<tr><td>Алиас:</td><td><input name=\"alias\" type=\"text\" value=\"\"></td></tr>
<tr><td>Ключи:</td><td><input name=\"meta_keys\" type=\"text\" value=\"\"></td></tr>
<tr><td>Писание:</td><td><input name=\"meta_desc\" type=\"text\" value=\"\"></td></tr>
<tr><td>Статус:</td><td><select name=\"public\"><option value=\"0\">Скрыто</option><option value=\"1\">Опубликовано</option></select></td></tr>
<tr><td>Отображать в верхнем меню?</td><td><select name=\"topmenu\"><option value=\"0\">Нет</option><option value=\"1\">Да</option></select></td></tr>
<tr><td>Отображать в нижнем меню?</td><td><select name=\"bottommenu\"><option value=\"0\">Нет</option><option value=\"1\">Да</option></select></td></tr>
<tr><td colspan=\"2\"><input name=\"add\" type=\"submit\" value=\"Добавить!\"></td></tr>
</table>
</form>
";
  $content = $html;
}

} else {
  $html = "
<div style=\"text-align: center;\"><form name=\"AdminLogin\" id=\"AdminLogin\" action=\"#\" method=\"post\">
Пароль: <input type=\"password\" name=\"password\" value=\"\"><input name=\"login\" type=\"submit\" value=\"Войти!\">
</form>
  ";
  $content = $html;
}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru" dir="ltr">
<head>
<meta charset="UTF-8" />
<meta name="keywords" content="Админ, Управление, Система" />
<meta name="description" content="Админ панель" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="/js/jquery-1.8.3.min.js"></script>
<link href="/system/style.css" rel="stylesheet" type="text/css" media="screen" />
<title><? echo $main["title"]; ?></title>
</head>
<body>

<div class="content">
<? echo $content; ?>
</div>

</body>
</html>