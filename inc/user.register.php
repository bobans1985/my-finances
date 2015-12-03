<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

if ($user == false) {

  $content = $root."/tmp/register.tpl";
  $main["title"] = "Регистрация - ".$main["title"];

} else {
  header("Location: /index.php?user=page");
}

?>