<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

// Задаем изначальную переменную пользователя как false
// Обрабатываем сессию, если есть
// И если данный пользователь найден, присваиваем данные переменной (true)

$user = false;
if (!isset($_SESSION["login"])) $_SESSION["dataUpdate"]=1; //переменная для обновления данных при входе

if (isset($_SESSION["login"]) and isset($_SESSION["password"]) and $_SESSION["login"] != "" and $_SESSION["password"] != "") {
  if (preg_match("/[a-zA-Z0-9]+/", $_SESSION["login"]) and preg_match("/[a-zA-Z0-9]+/", $_SESSION["password"])) {
    
    $select = query("SELECT `loginid`, `login`, `password`, `email` FROM `user` WHERE `login`='".$_SESSION["login"]."' and `password`='".$_SESSION["password"]."' LIMIT 1");
    if (mysqli_num_rows($select) > 0) {
      $user = mysqli_fetch_assoc($select);
    } 
    $select->close();
    
  }
}

if (isset($get["user"]) and $get["user"] == "logout") {
  unset($_SESSION["login"]); unset($_SESSION["password"]);
  destroySession();
  header("Location: /index.php?user=page");
  die;
}

?>