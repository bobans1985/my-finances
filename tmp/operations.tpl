<style type="text/css">
    .ui-jqgrid tr.jqgrow td {
        word-wrap: break-word; /* IE 5.5+ and CSS3 */
        white-space: pre-wrap; /* CSS3 */
        white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
        white-space: -pre-wrap; /* Opera 4-6 */
        white-space: -o-pre-wrap; /* Opera 7 */
        overflow: hidden;
        height: auto;
        vertical-align: middle;
        padding-top: 3px;
        padding-bottom: 3px
    }
</style>
<link rel="stylesheet" type="text/css" media="screen" href="/css/ui.jqgrid.css" />
<!--<link rel="stylesheet" type="text/css" media="screen" href="/css/ui-lightness/jquery-ui-1.10.4.custom.css" />-->
<link rel="stylesheet" type="text/css" media="screen" href="/css/redmond/jquery-ui-1.10.4.custom.css" />
<script type="text/javascript" src="/js/jquery.jqGrid.min.js"></script>
<script type="text/javascript" src="/js/grid.locale-ru.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="/js/ui.datepicker-ru.js"></script>
<script type="text/javascript" src="/js/plugins/jquery.contextmenu.js"></script>

<div class="header-page">
<a href="http://www.my-finances.ru"><img src="/tmp/img/logo.gif" alt="Мои финансы" title="Мои финансы"></a>
<div>
<p><? echo $page["user"]; ?></p>
<a href="/?user=page">Счета</a>
&emsp;|&emsp;<a href="/?user=operations"><font color="red">Операции</font></a>
</div>
</div>

<table class="table_user">
<tr>
<td class="td_systems">
    
   
<div class="system_title" width="100px"><span>Ваши счета</span></div>
<? echo $page["systems"]; ?> 

<div class="system_title"><span>Фильтр</span></div>
<table class="system_table" width="100px" id="FilterTable">
     <tr><td><input style="text-align:center"  readonly="true" size="15px" type="text" id="date1"/> </td></tr>
     <tr><td><input style="text-align:center"  readonly="true" size="15px" type="text" id="date2"/> </td></tr>
     <tr><td><button id="ftrbtn">Применить</button> </td></tr>
</table>

<div class="system_title"><span>Управление категориями</span></div>
<table class="system_table" width="100px" id="CategoryTable">
    <tr><td><button id="ctgrbtn">Категоризировать</button> </td></tr>
     <tr><td><button id="showctgrbtn">Управление категориями</button> </td></tr>
</table>

 </td>  
    <td>

    <table width="600" id="Operations"></table>


</td></tr></table>


 <div class="contextMenu" id="ContextMenu" style="display:none;">
        <ul style="width: 100px">
            <li id="addnew">
                <span class="ui-icon ui-icon-plus" style="float:left"></span>
                <span style="font-size:11px; font-family:Verdana">Создать новую категорию</span>
            </li>
            <li id="addfor">
                <span class="ui-icon ui-icon-pencil" style="float:left"></span>
                <span style="font-size:11px; font-family:Verdana">Добавить к существующей</span>
            </li>
        </ul>
</div>

<!-- Блоки PopUp------------------------------->
<div id="AddNewCategory" align="center" style="display:none;" >
        <div id="AddNewCategoryResult" ></div>
          <table width="100%">
              <tr><td></td></tr>
            <!--  <tr><td>ID</td><td><input type="text" size="4px" readonly id="CategoryId" name="CategoryId" ></td></tr>-->
              <tr><td>Название категории:</td><td><input type="text" size="50px" id="AddCategoryName"  name="AddCategoryName" ></td></tr>
              <tr><td>Текст для фильтрации</td><td><input type="text" size="50px" id="AddCategoryText" name="AddCategoryText" ></td></tr>
              <tr><td></td><td>
              <tr><td><div align="left"><input id="ButtonCloseNewCategory" type="submit" value="Закрыть"></div></td>
                  <td><div align="right"><input id="ButtonAddNewCategory" type="submit" value="Добавить"></div> </td></tr>
          </table>
</div>

<div id="UpdateCategory" align="center" style="display:none;" >
        <div id="UpdateCategoryResult" ></div>
          <table width="100%">
              <tr><td></td></tr>
              <tr><td>Название категории:</td><td><select style="width:100%" id="UpdateCategorySelect" name="UpdateCategorySelect"></select></td></tr>
              <tr><td>Текст для фильтрации</td><td><input type="text" size="50px" id="UpdateCategoryText" name="UpdateCategoryText" ></td></tr>
              <tr><td></td><td>
              <tr><td><div align="left"><input id="ButtonCloseUpdateCategory" type="submit" value="Закрыть"></div></td>
                  <td><div align="right"><input id="ButtonUpdateCategory" type="submit" value="Обновить"></div> </td></tr>
          </table>
</div>
<!-- Блоки PopUp------------------------------->



<!-- Переменные------------------------------->
<input type="hidden"  id="SelectedText"/>
<input type="hidden"  id="Group" value="FALSE"/>



<div class="footer-line"></div>
<? include $main["footer"]; ?>
 
<script type="text/javascript">
  
$(function() {
    $.datepicker.setDefaults(
        $.extend($.datepicker.regional["ru"])
    );
    
    $( "#date1, #date2" ).datepicker({ dateFormat: 'dd.mm.yy' });
    
    //Кнопка применения фильтра
    $( "#ftrbtn").button(); 
    //Кнопки в навигации категорий
    $( "#ctgrbtn").button(); 
    $( "#showctgrbtn").button(); 
    //Кнопки при добавлении категорий
    $( "#ButtonCloseNewCategory").button();
    $( "#ButtonAddNewCategory").button();
    $( "#ButtonCloseUpdateCategory").button();
    $( "#ButtonUpdateCategory").button();    
    
    
    $( "#ftrbtn" ).click(function() {
        var StartDate = $('#date1').datepicker("getDate");
        var EndDate = $('#date2').datepicker("getDate");
        var FLAG = $("#Group").val();
        if (StartDate!= undefined  &&
            EndDate!= undefined && FLAG=="FALSE")
        {
               OnChangeGridDateFltr(StartDate,EndDate);
        } else  
        if (FLAG=="TRUE"){
            //Подставим даты в запрос если они заданны
            var StartDate = $('#date1').datepicker({ dateFormat: 'dd-mm-yy' }).val(); 
            var EndDate = $('#date2').datepicker({ dateFormat: 'dd-mm-yy' }).val(); var url_="";
            if (StartDate!= undefined  && EndDate!= undefined ) {url_="&StartDate="+StartDate+"&EndDate="+EndDate;} 
            jQuery("#Operations").jqGrid()
                        .setCaption("Сгруппированные операции по счету \"<? echo $page["AccountName"]; ?>\" ")
                        .hideCol('DocumentDate') 
                        .setGridWidth("600")
                        .setGridParam({
                                datatype:'json',
                                url : '/inc/data.json.php?module=GroupOperations&accid=<? echo $page["accid"]; ?>'+url_,
                    }).trigger("reloadGrid"); 
                    
        } else alert ("Не заполены даты");
      });

    $('#ctgrbtn').click(function() {
        //Ставим признак граппировки
        if ($("#Group").val()=="FALSE") { //Если не группированно
            $("#Group").val("TRUE");
            $("#ctgrbtn").html('<span class="ui-button-text">Снять категории</span>');
            //Подставим даты в запрос если они заданны
            var StartDate = $('#date1').datepicker({ dateFormat: 'dd-mm-yy' }).val(); 
            var EndDate = $('#date2').datepicker({ dateFormat: 'dd-mm-yy' }).val(); var url_="";
            if (StartDate!= undefined  && EndDate!= undefined ) {url_="&StartDate="+StartDate+"&EndDate="+EndDate;} 
            var postData = jQuery("#Operations").jqGrid('getGridParam', 'postData');
            $.extend(postData, {filters: JSON.stringify({groupOp: "AND", rules:[]})});
            jQuery("#Operations").jqGrid()
                        .setCaption("Сгруппированные операции по счету \"<? echo $page["AccountName"]; ?>\" ")
                        .hideCol('DocumentDate') 
                        .setGridWidth("600")
                        .setGridParam({
                                datatype:'json',
                                url : '/inc/data.json.php?module=GroupOperations&accid=<? echo $page["accid"]; ?>'+url_,
                                sortname: 'Sum', 
                                sortorder: 'desc'
                    }).trigger("reloadGrid"); 
        } else
        {
            $("#Group").val("FALSE");
            $("#ctgrbtn").html('<span class="ui-button-text">Категоризировать</span>');
            location.reload(false); 
        }
      });

     //Нажатие на кнопку категории
     $( "#showctgrbtn" ).click(function() {
     });

     $( "#ButtonCloseNewCategory" ).click(function() {
        $("#AddNewCategory").dialog('close');
     });
     $( "#ButtonCloseUpdateCategory" ).click(function() {
        $("#UpdateCategory").dialog('close');
     });
     $( "#ButtonAddNewCategory" ).click(function() {
       $.ajax({
                type: "POST",
                url: "/inc/data.category.php",
                data: "module=NewCategory&CategoryName="+$("#AddCategoryName").val()+"&CategoryText="+$("#AddCategoryText").val(),
                success: function(data){
                               $("#AddNewCategoryResult").html(data);
                               setTimeout(function() {
                                        $("#AddNewCategory").dialog('close');
                                        $("#Operations").jqGrid('setGridParam',{datatype:'json'}).trigger('reloadGrid');
                                },5000);
                         },
                error: function (res) {
                       $("#AddNewCategoryResult").html("Произошла непредвиденная ошибка:(");
                }
               });
       });
     
     $( "#ButtonUpdateCategory" ).click(function() {
       $.ajax({
                type: "POST",
                url: "/inc/data.category.php",
                data: "module=UpdateCategory&CategoryId="+$('#UpdateCategorySelect').val()+"&UpdateCategoryText="+$("#UpdateCategoryText").val(),
                success: function(data){
                               $("#UpdateCategoryResult").html(data);
                               setTimeout(function() {
                                        $("#UpdateCategory").dialog('close');
                                        $("#Operations").jqGrid('setGridParam',{datatype:'json'}).trigger('reloadGrid');
                                },5000);
                         },
                error: function (res) {
                       $("#AddNewCategoryResult").html("Произошла непредвиденная ошибка:(");
                }
               });
      });
     });
    
    function AddNewCategory(text,Flag) {
        if (text.length==0) {
           alert("Выделите для начала текст");
           return;
        }
        $("#AddCategoryText").val("");
        $("#UpdateCategoryText").val("");
        $("#AddNewCategoryResult").html(""); //текст резалта очищаем
        $("#UpdateCategoryResult").html(""); 
    
        //Передали текст
        if (text!= undefined) {
            $("#AddCategoryText").val(text);
            $("#AddCategoryName").val(text);
            $("#UpdateCategoryText").val(text);
        }
    
        if (Flag) {
            $("#UpdateCategory").dialog({
                maxHeight:300,
                maxWidth:600,
                minHeight:300,
                minWidth:600,
                modal:true,
                title:"Добавление к существующей категории"
            });
            $('#UpdateCategorySelect').html('<option value="">Loading...</option>'); 
            $.ajax({
                    type: "post",
                    url: "/inc/ajax_data.php",
                    data: "groupupdate=return",
                    success: function(output) {
                    $('#UpdateCategorySelect').html(output);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert(xhr.status + " "+ thrownError);
                    }});
            }
            else { 
                $("#AddNewCategory").dialog({
                    maxHeight:300,
                    maxWidth:600,
                    minHeight:300,
                    minWidth:600,
                    modal:true,
                    title:"Добавление новой категории"
                });
            }
        }
</script>


     <script type="text/javascript">
     jQuery("#Operations").jqGrid({
        autoencode: true,
        url:'/inc/data.json.php?module=Operations&accid=<? echo $page["accid"]; ?>',
	datatype: "json",
        jsonReader:
                {
                 root:"rows",
                 id:"Sum",
                 repeatitems:false,
                },
	height: 550,
        width: 600,
   	colNames:['Дата','Сумма','Детали операции'],
   	colModel:[
                {name:'DocumentDate', width:"10", align:"right",sorttype:'date', datefmt:'Y-m-d'},
                {name:'Sum', width:"10", align:"right",sortable:true,sorttype:"float",formatter:'currency', formatoptions:{decimalSeparator:",", thousandsSeparator: " ", decimalPlaces: 2} },
   		{name:'Ground',index:'Ground', width:"60", sortable:true,sorttype:"text"},
   	],
   	caption: "Ваши операции по счету \"<? echo $page["AccountName"]; ?>\" ",  
        sortorder:"desc",
        loadonce:true,
        rowNum:10000,
       /*shrinkToFit:true, 
       multiselect: true,
       rownumbers:true,
       sortname:'DocumentDate',
       scrollOffset:0,
       gridview:true
       grouping:true,
       groupingView : { 
               groupField: ['Ground'],
               groupColumnShow: [false],
               groupCollapse: [true], 
               groupOrder: ['asc']  
        }, */
    
                loadComplete: function() {
                   
                    var $this = $(this);
                    if ($this.jqGrid('getGridParam', 'datatype') === 'json') {
                    if ($this.jqGrid('getGridParam', 'sortname') !== '') {
                    setTimeout(function () {
                            $this.triggerHandler('reloadGrid');
                        }, 50);
                    }
                    }
                    $("tr.jqgrow", this).contextMenu('ContextMenu', {
                        bindings: {
                            'addnew': function(trigger) {
                                AddNewCategory($("#SelectedText").val(),false);
                            },
                            'addfor': function(/*trigger*/) {
                                AddNewCategory($("#SelectedText").val(),true);
                            },
                        },
                        menuStyle: { width: '200px' },
                        onContextMenu: function(event/*, menu*/) {
                            //Подставляем в хайден поле выделенный текст
                            $("#SelectedText").val(getSelectedText());
                            return true;
                        }
                    });
                },
 });
 
 
  

function OnChangeGridSelect (fieldName, searchText) {    
    var filterObj = {"field":fieldName,"op":"eq","data":searchText};
    var grid = jQuery("#Operations");
    var postdata = grid.jqGrid('getGridParam', 'postData');
    if(postdata != undefined 
       && postdata.filters != undefined 
       && postdata.filters.rules != undefined) 
    {
        //Remove if current field exists
        postdata.filters.rules = $.grep(postdata.filters.rules, function(value) {
            if(value.field != fieldName)
                return value;
        });
 
        //Add new filter
        postdata.filters.rules.push(filterObj);
    }
    else
    {
        $.extend(postdata, {
            filters:  { 
                "groupOp":"AND",
                "rules":[ filterObj ] 
            }
        });
    }
 
    grid.jqGrid('setGridParam', { search: true, postData: postdata });
    grid.trigger("reloadGrid", [{ page: 1}]);
}

function OnChangeGridDateFltr ( startDate,endDate) {    
 var startDateFilter = { "field": "DocumentDate", "op": "ge", "data": startDate };
 var endDateFilter = { "field": "DocumentDate", "op": "le", "data": endDate };
    var grid = jQuery("#Operations");
    var postdata = grid.jqGrid('getGridParam', 'postData');
        $.extend(postdata, {
            filters:  { 
                 // Add new filters
                "groupOp":"AND",
                "rules":[ startDateFilter,endDateFilter]

            }
        });
    grid.jqGrid('setGridParam', { search: true, postData: postdata });
    grid.trigger("reloadGrid", [{ page: 1}]);
}

// функция для получение выделенного текста
function getSelectedText(){
    var text = "";
    if (window.getSelection) {
        text = window.getSelection();
    }else if (document.getSelection) {
        text = document.getSelection();
    }else if (document.selection) {
        text = document.selection.createRange().text;
    }
    return text;
}

    </script>
    
    
    
   
