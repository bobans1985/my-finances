<?

//ini_set('display_errors',1);
//Error_Reporting(E_ALL);

// Проверяем, ajax ли запрос посетил нас
//if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) and !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) and strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
  include $_SERVER["DOCUMENT_ROOT"]."/library/autoload.php";
  startSession();
  define("INDEX", true);
  $root = $_SERVER["DOCUMENT_ROOT"];
  include $root."/inc/db.php";
  include $root."/inc/user.php";
  
  
    
  if ($user == true) {
   //Возвращаем json в ajax курсов
 if(isset($_POST['cbr_course']) && ($_POST['cbr_course']=="return")) {
  
    $date = date("d/m/Y"); // Формируем сегодняшнюю дату
    $bank = array();
    $bank["dollar"]=0;
    $bank["euro"]=0;
    $text = ""; 
    $cbr_html="";
   $SelCbr = query("SELECT CurrCode,Sell,Buy FROM exchangeratescbr WHERE OnDate>=DATE_FORMAT(NOW(),'%y%m%d')");
    if (mysqli_num_rows($SelCbr)>0) {
        while ($row = $SelCbr->fetch_assoc()) {
              if ($row["CurrCode"] == '840') { $bank["dollar"] = $row["Sell"]; } 
              if ($row["CurrCode"] == '978') { $bank["euro"]   = $row["Sell"]; }  
        }    
    } else {
         $link = "http://www.cbr.ru/scripts/XML_daily.asp"; // Формируем ссылку
           $link = "http://sdm.ru"; // Формируем ссылку
         $fd = fopen($link, "r"); // Загружаем HTML-страницу 
         if (!$fd) {
              $cbr_html="Невозможно загрузить данные";
         } else {     
              while (!feof ($fd)) {// Чтение содержимого файла в переменную $text
                $text .= fgets($fd, 4096); 
              }      
            fclose ($fd); // Закрыть открытый файловый дескриптор    
         }  
         //echo $text;
         // Разбираем содержимое, при помощи регулярных выражений  
         $pattern = "#<Valute ID=\"([^\"]+)[^>]+>[^>]+>([^<]+)[^>]+>[^>]+>[^>]+>[^>]+>[^>]+>[^>]+>([^<]+)[^>]+>[^>]+>([^<]+)#i";  
         preg_match_all($pattern, $text, $out, PREG_SET_ORDER);  
         foreach ($out as $cur) {  
               if ($cur[2] == 840) { $bank["dollar"] = str_replace(",",".",$cur[4]); } 
               if ($cur[2] == 978) { $bank["euro"]   = str_replace(",",".",$cur[4]); }
         }  
         $InsertCbr = query("Insert into  exchangeratescbr(OnDate,CurrCode,Sell,Buy) VALUES (now(),'840',".$bank['dollar'].",".$bank['dollar'].")");
         $InsertCbr = query("Insert into  exchangeratescbr(OnDate,CurrCode,Sell,Buy) VALUES (now(),'978',".$bank['euro'].",".$bank['euro'].")");
         
      }
      
      
  if ($cbr_html<>"") $cbr_course[]=array("id" => "cbr_course", "content" =>$cbr_html);
  else  $cbr_course[]=array("id" => "cbr_course", "content" => "
        <span>".$bank["dollar"]."</span>
        Доллар:<br/>
        <span>".$bank["euro"]."</span>
        Евро:
        ");

  echo json_encode($cbr_course);
}    
   

//-----------------
if(isset($_POST['accounts']) && ($_POST['accounts']=="return")) {   
      
      if (isset($_SESSION["dataUpdate"]) and $_SESSION["dataUpdate"]==1)
      { //обновляем данные только при входе
        include $root."/inc/data.update.php";
        $_SESSION["dataUpdate"]=0;
      }
  // Получаем все системы и достаем счета пользователя
  // К каждой системе
  $select = query("SELECT * FROM `typeofsystem`");
  if (mysqli_num_rows($select) > 0) {
  $balance = array("rur" => 0, "usd" => 0, "eur" => 0);
  $system = "";
  $i = 0;
  while ($row = $select->fetch_assoc()) {
    $numer = $i++;
    $accounts[$numer]["id"] = "system".$row["TypeSysId"];
    $accountselect = query("SELECT * FROM `accounts` WHERE LoginId='".$user["loginid"]."' and TypeSysId='".$row["TypeSysId"]."' order by rest desc");
    if (mysqli_num_rows($accountselect) > 0) {
      $accounts[$numer]["content"] = "<tr><td><strong>Номер счета</strong></td><td><strong>Имя счета</strong></td><td><strong>Остаток</strong></td></tr>";
      while ($row1 = $accountselect->fetch_assoc()) {
        $balance[strtolower($row1["CurrCode"])] = $balance[strtolower($row1["CurrCode"])] + $row1["Rest"];
        $row1["numer"] = "Недоступно";
        $row1["name"] = "Недоступно";
        if ($row1["Account"] != "") {
          $row1["numer"] = "<a href=\"\?user=operations&accid=".$row1["AccountId"]."\">".$row1["Account"]."</a>";
          $row1["name"] = "<a href=\"\?user=operations&accid=".$row1["AccountId"]."\">".$row1["AccountName"]."</a>";
          $row1["Rest"] =  "<a href=\"\?user=operations&accid=".$row1["AccountId"]."\">".$row1["Rest"]." ".strtoupper($row1["CurrCode"])."</a>";
        } else if ($row1["Card"] != "") {
          $row1["numer"] = "<a href=\"\?user=operations&accid=".$row1["Card"]."</a>";
          $row1["name"] = "<a href=\"\?user=operations&accid=".$row1["CardName"]."</a>";
          $row1["Rest"] =  "<a href=\"\?user=operations&accid=".$row1["AccountId"]."\">".$row1["Rest"]." ".strtoupper($row1["CurrCode"])."</a>";
        }
        $accounts[$numer]["content"] = $accounts[$numer]["content"]."\n
<tr><td>".$row1["numer"]."</td><td>".$row1["name"]."</td><td>".$row1["Rest"]."</td></tr>
        ";
      }
    } else {
      $accounts[$numer]["content"] = "<tr><td>К сожалению,  у нас нет такой информации</td></tr>";
    }  
    $accountselect->close();
    
    $settings = query("SELECT id FROM usersettings WHERE LoginId='".$user["loginid"]."' and TypeSysId='".$row["TypeSysId"]."' LIMIT 1");
    $setting = mysqli_num_rows($settings);
    $settings->close();
    $accounts[] = array("id" => "balance", "content" => "
<span>
".$balance["rur"]." RUR<br/>
".$balance["usd"]." USD<br/>
".$balance["eur"]." EUR<br/>
</span>Ваш баланс:");

    if ($setting == "1") {
      $system = $accounts;
    }
  }
  }
 // $select->close();

  //sleep(3);
  echo json_encode($system);
    }
  
}
/*} else {
  header("Location: /index.php?user=page");
}*/




//-----------------
//Возвращаем список категорий
if ($_SESSION["loginid"]<>0) {
if(isset($_POST['groupupdate']) && ($_POST['groupupdate']=="return")) {  
  $context=""; 
  $select = query("SELECT CategoryId,CategoryName FROM  `categoryname` where LoginId in (".$user["loginid"].",0)");
  if (mysqli_num_rows($select) > 0) {
      while ($row = $select->fetch_assoc()) {     
           $context=$context."<option value='".$row["CategoryId"]."'>".$row["CategoryName"]."</option>";
      }
  } else echo ("Нет созданных ранее групп");
echo $context;
}
}