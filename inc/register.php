<?
  
if (isset($_POST["login"]) and isset($_POST["password"]) and isset($_POST["password1"]) and isset($_POST["email"])) {
  define("INDEX", true);
  $root = $_SERVER["DOCUMENT_ROOT"];
  include $root."/inc/db.php";
  $post = $_POST;
  
  if (!preg_match("/^[a-zA-Z0-9]{5,15}$/", $post["login"])) {
    $error[] = "Недопустимый формат логина";
  } else {
    $select = query("SELECT `Loginid` FROM `user` WHERE `Login`='".$post["login"]."' LIMIT 1");
    if (mysqli_num_rows($select) > 0) {
      $error[] = "Данный логин уже занят, попробуйте другой";
    }
    $select->close();
  }
  
  if (!preg_match("/^[\.a-zA-Z0-9_-]+@[\.a-zA-Z0-9-]+$/", $post["email"])) {
    $error[] = "Недопустимый формат почтового адреса";
  } else {
    $select = query("SELECT `Loginid` FROM `user` WHERE `eMail`='".$post["email"]."' LIMIT 1");
    if (mysqli_num_rows($select) > 0) {
      $error[] = "Пользователь с таким e-Mail адресом уже зарегистрирован";
    }
    $select->close();
  }
  
  if (!preg_match("/^[a-zA-Z0-9]{5,15}$/", $post["password"]) or $post["password"] != $post["password1"]) {
    $error[] = "Недопустимый формат пароля или они не совпадают";
  }
  
  if (!isset($error)) {
    $add = query("INSERT INTO `user` (`Login`, `Password`, `eMail`) VALUES ('".$post["login"]."','".md5(md5($post["password"]."1q2w3e4r5t6y7u"))."','".$post["email"]."')");
    if ($add) {
      $data["answer"] = true;
      $data["solution"] = "Регистрация прошла успешно! Перенаправляем...";
    } else {
      $error[] = "Что-то сломалось";
    }
  }
  
  if (isset($error)) { 
    $data["error"] = implode("<br/>", $error);
    $data["answer"] = false;
  } 

  // Проверяем, ajax ли запрос посетил нас
  if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) and !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) and strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
    echo json_encode($data);
  } else {
    header("Location: /index.php?user=page");
  }
  
} else {
  header("Location: /index.php?page=home");
}
  
?>