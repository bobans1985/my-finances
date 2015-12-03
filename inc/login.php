<?

//echo json_encode(array("answer" => false, "error" => "Дошло, спасибо"));

//Функция логина пользователя
if (isset($_POST["login"])) {
  $answer = array();
  $post = $_POST;
  
  define("INDEX", true);
  $root = $_SERVER["DOCUMENT_ROOT"];
  include $root."/inc/db.php";
  require_once $root.'/library/autoload.php';
  
  startSession();
  
  $lc = 0;
  if (isset($_SESSION["lc"])) {
    $lc = $_SESSION["lc"];
  }
  
  if (!isset($post["captcha"]) and $lc > 5 or isset($post["captcha"]) and $post["captcha"] != $_SESSION["captcha"]) {
    $login_error[] = "Вы неправильно ввели изображение с картинки";
  } 
  if (!isset($post["login"]) or $post["login"] == "" or !preg_match("/[a-zA-Z0-9]+/", $post["login"])) {
    $login_error[] = "Формат логина неверен или поле незаполнено!";
  }
  if (!isset($post["password"]) or $post["password"] == "" or !preg_match("/[a-zA-Z0-9]+/", $post["password"])) {
    $login_error[] = "Формат пароля неверен или поле незаполнено!";
  }
  if (!isset($login_error)) {
    $post["password"] = md5(md5($post["password"]."1q2w3e4r5t6y7u"));
    $select = query("SELECT LoginId FROM `user` WHERE `login`='".$post["login"]."' and `password`='".$post["password"]."' LIMIT 1");
    if (mysqli_num_rows($select) > 0) {
      $user = mysqli_fetch_assoc($select);
      $history = query("INSERT INTO `sessionhistory`(`LoginId`,  `ExtIp`, `Operation`) VALUES ('".$user["LoginId"]."','".$_SERVER['REMOTE_ADDR']."','Login')");
      $answer["answer"] = true;
      $answer["solution"] = "Вход выполнен успешно! Переходим в личный кабинет...";
      $_SESSION["login"] = $post["login"];
      $_SESSION["password"] = $post["password"];
      $_SESSION["loginid"] = $user["LoginId"];
      unset($_SESSION["lc"]);
    } else {
      $lc = $lc + 1;
      $login_error[] = "Пользователь с такой комбинацией логин\пароль не найден!";
    }
  }
  
  if (isset($login_error)) {
    $count = count($login_error);
    $self = "";
    for ($i = 0; $i < $count; $i++) {
      $self = $self."<li>".$login_error[$i]."</li>\n";
    }                                   
    $self = "<div class=\"login_error\">\n".$self."</div>";
    $answer["error"] = $self;
    $answer["lc"] = $lc;
    $_SESSION["lc"] = $lc;
    $answer["answer"] = false;
  }

  // Проверяем, ajax ли запрос посетил нас
  if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) and !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) and strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
    echo json_encode($answer);
  } else {
    header("Location: /index.php?user=page");
  }
  
} else {
  header("Location: /index.php?page=home");
}
?>