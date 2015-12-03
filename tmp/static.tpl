<div class="header">
<a href="http://www.my-finances.ru"><img src="/tmp/img/logo.gif" alt="Мои финансы" title="Мои финансы"></a>
<div>
<a class="log_in" href="/?user=page"><span>Войти</span></a>
&emsp;|&emsp;<a href="/?user=register" class="sign_up"><span>Регистрация</span></a><br><br><br>

    <? echo $header_menu; ?>
</div>
</div>
<div class="banner">
<img src="" alt="Баннер" title="Баннер">
</div>

<h1><? echo $page["title"]; ?></h1>
<? echo $page["content"]; ?>
<? if ($main["feedback"] <> "" ) {echo('<div class="result"></div>'); include $main["feedback"]; }?>

<div class="footer-line"></div>
<? include $main["footer"]; ?>