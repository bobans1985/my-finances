<?

if (isset($_POST["message"]) and isset($_POST["email"]) and isset($_POST["Name"])) {
    require $_SERVER["DOCUMENT_ROOT"].'/library/PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->isSMTP();  
    $mail->SMTPAuth   = true;
    $mail->Username   = "admin@my-finances.ru"; // SMTP account username
    $mail->Password   = "uhbierjd1985";        // SMTP account password
    $mail->CharSet = 'UTF-8';
    $mail->Host = 'smtp.my-finances.ru';  
    $mail->From = 'admin@my-finances.ru';
    $mail->FromName =  'Feedback User';
    $mail->addAddress('bobans@inbox.ru');  
    $mail->isHTML(true);  
    
   $mail->Subject = "FeedBack";
   $mail->Body    = "Имя пользователя:".$_POST["Name"]."<br> Email:".$_POST["email"]."<br>Сообщение:".$_POST["message"];
   //$mail->SMTPDebug  = 1;
  
    //Отправляем сообщение
    $upp=$mail->send();
    if ($upp) {
      $answer["answer"] = false;
      $answer["feedback"] = true;
      $answer["solution"] = "<div class=\"login_error\">\n<b>Ваше обращение принято</b>\n</div>";
      echo json_encode($answer);
    } else {
      $self = "<li>Произошла какае-то ошибка(</li>\n";
      $self = "<div class=\"login_error\">\n".$self."</div>";
      $answer["error"] = $self;
      $answer["answer"] = false;
      echo json_encode($answer);
    }
}
else echo '
<form method="post" action="\inc\feedback.php">
    <table cellpadding="2" cellspacing="0" border="0">
        <tbody>
            <tr>
                <td valign="middle" align="right">Как к вам обращаться:</td>
                <td valign="middle" align="left">
                    <input type="text" value="" name="Name" size="25" maxlength="255" />
                </td>
            </tr>
            <tr>
                <td valign="middle" align="right">E-mail:</td>
                <td valign="middle" align="left">
                    <input type="text" value="" name="email" size="25" maxlength="255" /><br/>
                </td>
            </tr>
            <tr>
                <td valign="top" align="right">Сообщение:</td>
                <td valign="middle" align="left">
                    <textarea name="message" rows="5" cols="30"></textarea>
                </td>
            </tr>
            <tr>
                <td valign="middle" align="right" colspan="2">
                    <input type="submit" value="Отправить" name="send" />
                </td>
            </tr>
        </tbody>
    </table>
</form>
';   ?>