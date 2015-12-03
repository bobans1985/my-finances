<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

if (isset($_POST["add"]) or isset($_POST["save"])) {

  $post = $_POST;
  if (!isset($post["title"]) or isset($post["title"]) and !preg_match("/^[^\"]{5,200}$/", $post["title"])) {
    $error[] = "Формат заголовка неверен!";
  }
  if (!isset($post["content"]) or isset($post["content"]) and $post["content"] == "") {
    $error[] = "Содержимое страницы не может быть пустым!";
  }
  if (!isset($post["alias"]) or isset($post["alias"]) and !preg_match("/^[a-zA-Z0-9]{4,50}$/", $post["alias"])) {
    $error[] = "Неверный формат алиаса";
  }
  
  if (!isset($error) and isset($post["add"])) {
    $add = query("INSERT INTO `pages`(`alias`, `Title`, `Content`, `MetaDesc`, `MetaKeys`, `Public`, `topmenu`, `bottommenu`) VALUES ('".$post["alias"]."','".$post["title"]."','".$post["content"]."','".$post["meta_desc"]."','".$post["meta_keys"]."','".$post["public"]."','".$post["topmenu"]."','".$post["bottommenu"]."')");
    if ($add) {
      header("Location: /system/");
    } else {
      $error[] = "Что-то сломалось :(";
    }
  } else if (!isset($error) and isset($post["save"])) {
    $upd = query("UPDATE `pages` SET `alias`='".$post["alias"]."',`Title`='".$post["title"]."',`Content`='".$post["content"]."',`MetaDesc`='".$post["meta_desc"]."',`MetaKeys`='".$post["meta_keys"]."',`Public`='".$post["public"]."',`topmenu`='".$post["topmenu"]."',`bottommenu`='".$post["bottommenu"]."' WHERE `Pageid`='".$page["Pageid"]."' LIMIT 1");
    if ($upd) {
      header("Location: /system/?page=".$page["Pageid"]);
    } else {
      $error[] = "Что-то сломалось :(";
    }
  }
  
}

$err = "";
if (isset($error)) {
  $err = implode("<br/>", $error);
  $err = "<div style=\"border: 1px dotted red; padding: 5px;\">".$err."</div>";
  unset($error);
}

?>