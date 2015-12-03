<div class="header-page">
<a href="http://www.my-finances.ru"><img src="/tmp/img/logo.gif" alt="Мои финансы" title="Мои финансы"></a>
<div>
<p><? echo $setting["user"]; ?></p>
<a href="/?user=page">Счета</a>
&emsp;|&emsp;<a href="/?user=operations">Операции</a>
</div>
</div> 

<table class="table_user">
<tr>
<td class="td_systems">
<div class="result"></div>
<? echo $setting["system"]; ?>

</td>
</tr>
</table>

<div class="footer-line"></div>
<? include $main["footer"]; ?>

<link rel="stylesheet" type="text/css" media="screen" href="/css/redmond/jquery-ui-1.10.4.custom.css" />
<script type="text/javascript" src="/js/jquery-ui-1.10.4.custom.min.js"></script>

<script type="text/javascript">
$(function() {
    $( "input[type=submit]").button(); 
});
</script>