<?


if (isset($_POST["email"])) {
  $root = $_SERVER["DOCUMENT_ROOT"];
  define("INDEX", true);
  include $root."/library/autoload.php";
  include $root."/inc/db.php";
  $mail = $_POST["email"];
  
  if (!preg_match("/^[\.a-zA-Z0-9_-]+@[\.a-zA-Z0-9-]+$/", $mail)) {
    $error[] = "Недопустимый формат почтового адреса";
  } else {
    $select = query("SELECT `Loginid`, `Login`, `Password` FROM `user` WHERE `eMail`='".$mail."' LIMIT 1");
    if (mysqli_num_rows($select) > 0) {
      $ruser = mysqli_fetch_assoc($select);
    } else {
      $error[] = "Пользователь с таким E-Mail адресом не найден";
    }
    $select->close();
  }
  
  if (isset($ruser) and !isset($error)) {
    $parol = md5(time().$mail.date("s"));
    $parol = substr($parol, -10, 10);
    
    $upd = query("UPDATE `user` SET `Password`='".md5(md5($parol."1q2w3e4r5t6y7u"))."' WHERE `Loginid`='".$ruser["Loginid"]."' LIMIT 1");

    // текст письма
    $message = "
<p>Здравствуйте, ".$ruser["login"]."</p> 
<p>Вы получили данное письмо, потому как воспользовались функцией восстановления пароля.</p>
<p>Ваш новый пароль: ".$parol."</p>
<p>Перейти в личный <a href=\"http://my-finances.ru/index.php?user=page\">кабинет</a>.</p>
<p>С уважением, администрация сайта.</p>
";

    require $_SERVER["DOCUMENT_ROOT"].'/library/PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->isSMTP();  
    $mail->SMTPAuth   = true;
    $mail->Username   = "admin@my-finances.ru"; // SMTP account username
    $mail->Password   = "uhbierjd1985";        // SMTP account password
    $mail->CharSet = 'UTF-8';
    $mail->Host = 'smtp.my-finances.ru';  
    $mail->From = 'admin@my-finances.ru';
    $mail->FromName = 'www.my-finances.ru';
    $mail->addAddress($_POST["email"]);  
    $mail->isHTML(true);  
    //$mail->setLanguage('ru', '/optional/path/to/language/directory/');
    
   $mail->Subject = "Restore Password";
   $mail->Body    = $message;

  
    //Отправляем сообщение
    $upp=$mail->send();
    $data["solution"] = "Что-то сломалось, но сработало";
    if ($upp) {
      $data["answer"] = true;
      $data["solution"] = "Письмо успешно отправлено! Перенаправление...";
    } else {
      $error[] = "Не вышло отправить письмо";
    }

  }
  
  if (isset($error)) {
    $data["answer"] = false;
    $data["error"] = implode("<br/>", $error);
  }
  
  // Проверяем, ajax ли запрос посетил нас
  if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) and !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) and strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
    echo json_encode($data);
  } else {
    header("Location: /data/index.php?user=page");
  }
  
} else {
  header("Location: /data/index.php?page=home");
}
?>