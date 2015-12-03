<div style="display: inline-block; width: 100%; height: 100px;"></div>
<div class="login-logo"><a href="http://www.my-finances.ru"><img src="/tmp/img/logo.gif" title="Мои финансы" alt="Мои финансы"></a></div>
<div class="register">
<table>
<tr>
<td style="width: 290px;">

<h1>Регистрация</h1>
<form name="registerForm" id="registerForm" action="/inc/register.php" method="post">
<div class="result"></div>
<div><span>Логин:</span><br/><input type="text" name="login" value="" class="input"></div>
<div><span>e-Mail:</span><br/><input type="email" name="email" value="" class="input"></div>
<div><span>Пароль:</span><br/><input type="password" name="password" value="" class="input"></div>
<div><span>Повтор пароля:</span><br/><input type="password" name="password1" value="" class="input"></div>
<div><input type="submit" name="submit" value="Регистрация!" id="submit"></div>
</form>

</td>
<td>

<div class="login-info">
<h2>У Вас уже есть аккаунт?</h2>
<p><a href="/?user=page">Войдите</a> в систему</p>
<h2>Забыли пароль?</h2>
<p>Воспользуйтесь <a href="/?user=restore">функцией</a> восстановления</p>
</div>

</td>
</tr>
</table>

</div>

<? include $main["footer"]; ?>