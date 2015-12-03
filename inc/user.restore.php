<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

if ($user == false) {

  $content = $root."/tmp/restore.tpl";
  $main["title"] = "Восстановление пароля - ".$main["title"];

} else {
  header("Location: /index.php?user=page");
}

?>