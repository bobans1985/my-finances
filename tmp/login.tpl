<div style="display: inline-block; width: 100%; height: 100px;"></div>
<div class="login-logo"><a href="http://www.my-finances.ru"><img src="/tmp/img/logo.gif" title="Мои финансы" alt="Мои финансы"></a></div>
<div class="login">
<table>
<tr>
<td style="width: 290px;">
<h1>Войти в систему</h1>
<form name="loginForm" id="loginForm" action="/inc/login.php" method="post">
<div class="result"></div>
<div><span>Логин:</span>
<br/><input type="text" name="login" value="" class="input"></div>
<div><span>Пароль:</span><br/>
<input type="password" name="password" value="" class="input"></div>
<div class="captcha">
<? if (isset($_SESSION["lc"]) and $_SESSION["lc"] > 5): ?>
<img src="/inc/captcha.php?random=<? echo rand(1000,9999); ?>" alt="Оп" title="Оп"><input type="text" name="captcha" value="">
<? endif; ?>
</div>
<div><input type="submit" name="login_submit" value="Войти!" id="submit"></div>
</form>

</td>
<td>

<div class="login-info">
<h2>Первый раз в системе?</h2>
<p><a href="/?user=register">Зарегистрируйтесь</a></p>
<h2>Забыли пароль?</h2>
<p>Воспользуйтесь <a href="/?user=restore">функцией</a> восстановления</p>
</div>

</td>
</tr>
</table>

</div>

<? include $main["footer"]; ?>