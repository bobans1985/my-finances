<?

//Это если меняем настройки
if (isset($_POST["id"])) {
  $post = $_POST;
  define("INDEX", true);
  include $_SERVER["DOCUMENT_ROOT"]."/library/autoload.php";
  startSession();
  //session_start();
  include $_SERVER["DOCUMENT_ROOT"]."/inc/db.php";
  include $_SERVER["DOCUMENT_ROOT"]."/inc/user.php";
  
  if (isset($post["id"]) and $post["id"] == "save" and $user == true) {
    $post["password2"] = md5(md5($post["password2"]."1q2w3e4r5t6y7u"));
    if (!isset($post["password2"]) or $post["password2"] != $user["password"]) {
      $error[] = "Действующий пароль введен неверно";
    }
    if (!isset($post["password"]) or !preg_match("/^[a-zA-Z0-9]{5,15}$/", $post["password"]) or $post["password"] != $post["password1"]) {
      $error[] = "Формат пароля неверен или они не совпадают";
    }
    if (!isset($error)) {
      $post["password"] = md5(md5($post["password"]."1q2w3e4r5t6y7u"));
      $upd = query("UPDATE `user` SET `Password`='".$post["password"]."' WHERE `Loginid`='".$user["loginid"]."'");
      if ($upd) {
        $_SESSION["password"] = $post["password"];
        $data["answer"] = true;
        $data["solution"] = "Настройки изменены успешно успешно! Перенаправляем...";
        $history = query("INSERT INTO `sessionhistory` (`LoginId`, `ExtIp`, `Operation`) VALUES ('".$user["loginid"]."','".$_SERVER['REMOTE_ADDR']."','Change settings')");
      } else {
        $error[] = "Что-то сломалось";
      }
    }
  }
  
  if (isset($post["on"]) and $post["on"] == "0" and isset($post["id"]) and isset($user["loginid"]) and is_numeric($post["id"])) {
    $delete = query("DELETE FROM `usersettings` WHERE `LoginID`='".$user["loginid"]."' and `TypeSysID`='".$post["id"]."' LIMIT 1");
    $delAcc = query("DELETE FROM `accounts` WHERE `LoginID`='".$user["loginid"]."' and `TypeSysID`='".$post["id"]."' LIMIT 99999");
    $delOper = query("DELETE FROM `operations` WHERE `LoginID`='".$user["loginid"]."' and `TypeSysID`='".$post["id"]."' LIMIT 99999");
    $history = query("INSERT INTO `sessionhistory`(`LoginId`,  `ExtIp`, `Operation`) VALUES ('".$user["loginid"]."','".$_SERVER['REMOTE_ADDR']."','Change settings')");
    $data["answer"] = true; 
    $data["solution"] = "Настройки изменены успешно успешно! Перенаправляем...";
  }
  
  if (isset($post["on"]) and $post["on"] == "1" and isset($post["id"]) and isset($user["loginid"]) and is_numeric($post["id"])) {
  
    if (isset($post["login"]) and !preg_match("/^[a-zA-Z0-9]{5,15}$/", $post["login"])) {
      $error[] = "Неверный формат логина";
    }
    if (isset($post["password"]) and !preg_match("/^[a-zA-Z0-9]{5,15}$/", $post["password"]) or isset($post["password"]) and $post["password"] != $post["password1"]) {
      $error[] = "Неверный формат пароля или они не совпадают";
    }
    $select = query("SELECT * FROM `usersettings` WHERE `LoginID`='".$user["loginid"]."' and `TypeSysID`='".$post["id"]."' LIMIT 1");
    if (mysqli_num_rows($select) > 0) {
      if (!isset($error)) {
        $upd = query("UPDATE `usersettings` SET `LoginInSys`='".$post["login"]."', `PasswordInSys`='".$post["password"]."', `DaysForUpdate`='".$post["DaysForUpdate"]."' WHERE `LoginID`='".$user["loginid"]."' and `TypeSysID`='".$post["id"]."' LIMIT 1");
        if ($upd) {
          $data["answer"] = true;
          $data["solution"] = "Настройки изменены успешно успешно! Перенаправляем...";
          $history = query("INSERT INTO `sessionhistory` (`LoginId`,  `ExtIp`, `Operation`) VALUES ('".$user["loginid"]."','".$_SERVER['REMOTE_ADDR']."','Change settings')");
        } else {
          $error[] = "Что-то снова сломалось";
        }
      }
    } else {
      if (!isset($error)) {
        $add = query("INSERT INTO `usersettings` (`LoginID`, `TypeSysID`, `LoginInSys`, `PasswordInSys`) VALUES ('".$user["loginid"]."','".$post["id"]."','".$post["login"]."','".$post["password"]."')");
        $history = query("INSERT INTO `sessionhistory` (`LoginId`, `ExtIp`, `Operation`) VALUES ('".$user["loginid"]."','".$_SERVER['REMOTE_ADDR']."','Change settings')");
        $data["answer"] = true;
        $data["solution"] = "Настройки изменены успешно успешно! Перенаправляем...";
        if (!$add) {
          $error[] = "Что-то сломалось";
        }
      }
    }
  }
  
  //Удаляем учетную запись и настройки
  if (isset($post["id"]) and $post["id"] == "delete" and $user == true) {
      //Удаление всех данных
      $del = query("delete from accounts where loginid=".$user["loginid"]);
      $del1 = query("delete from categoryname where loginid=".$user["loginid"]);
      $del2 = query("delete from categoryoperations where loginid=".$user["loginid"]);
      $del3 = query("delete from categorytext where loginid=".$user["loginid"]);
      $del4 = query("delete from operations where loginid=".$user["loginid"]);
      $del5 = query("delete from user where loginid=".$user["loginid"]);
      $del6 = query("delete from usersettings where loginid=".$user["loginid"]);
      unset($_SESSION["login"]); unset($_SESSION["password"]);
      destroySession();   
  }
  
  
  
  if (isset($error)) { 
    $data["answer"] = false;
    $data["error"] = implode("<br/>", $error); 
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