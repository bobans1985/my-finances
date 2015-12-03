<div class="header-page">
<a href="http://www.my-finances.ru"><img src="/tmp/img/logo.gif" alt="Мои финансы" title="Мои финансы"></a>
<div>
<p><? echo $page["user"]; ?></p>
<a href="/?user=page"><font color="red">Счета</font></a>
&emsp;|&emsp;<a href="/?user=operations">Операции</a>
</div>
</div> 

<table class="table_user">
<tr>
<td class="td_systems">

<? echo $page["systems"]; ?>

</td>
<td class="td_info">

    <div class="system_title">
        
        <table >
            <tr><td width="195px" align="center">&nbsp; &nbsp; Ваш баланс </td>
                <td> <a href="/index.php?user=page&update=1"><img  src="/tmp/img/reload.png" alt="Обновить данные" title="Обновить данные" width="16" height="16" hspace="0" vspace="2" align="right"></a></td>
            </tr>
    
</table>
        
    </div>
<div class="balance_block" id="balance">
<? echo $page["balance"]; ?>
</div>

<div class="system_title"><span>Курсы валют</span></div>
<div class="bank_block" id="cbr_course">
<? echo $page["valute"]; ?>
</div>

</td>
</tr>
</table>

<div class="footer-line"></div>
<? include $main["footer"]; ?>

<script type="text/javascript" src="/js/ajax_data.js"></script>