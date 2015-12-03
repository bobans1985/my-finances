<? if (!defined("INDEX") == true) { header("Location: /index.php"); }


// Получаем $_GET запросы и обрабатываем данные
$get = $_GET;
if (isset($get["page"]) and !preg_match("/^[a-zA-Z0-9]+$/", $get["page"])) {
  $get = false;
  $error404 = true;
} else if (!isset($get["page"]) and !isset($get["user"])) {
  $get["page"] = "home";
}
if (isset($get["user"]) and !preg_match("/^[a-zA-Z0-9]+$/", $get["user"])) {
  $get = false;
  $error404 = true;
}

// Открываем сессию для записи данных пользователя
// Подключаем обработчик запросов к БД и файл функций
$root = $_SERVER["DOCUMENT_ROOT"];
include $_SERVER["DOCUMENT_ROOT"]."/library/autoload.php";

startSession();
//session_start();

include $root."/inc/db.php";
include $root."/inc/user.php";

// Задаем переменные
$main = array(
    "title" => "Мои финансы - система анализа личных финансов",
    "meta_keys" => "Ключи",
    "meta_desc" => "Описание",
    "header" => "Содержание",
    "footer" => $root."/tmp/footer.tpl",
    "feedback"=>""
);

// Достаем менюшки
$select = query("SELECT `alias`, `title`, `topmenu`, `bottommenu` FROM `pages` WHERE `Public`='1'");
$header_menu = "";
$footer_menu = "";
if (mysqli_num_rows($select) > 0) {
  while ($row = $select->fetch_assoc()) {
    if ($row["topmenu"] == "1") {
      $header_menu[] = "<a class=\"item\" href=\"/index.php?page=".$row["alias"]."\">".$row["title"]."</a>";
    }
    if ($row["bottommenu"] == "1") {
      $footer_menu[] = "<a href=\"/index.php?page=".$row["alias"]."\">".$row["title"]."</a>";
    }
  }
  if (is_array($header_menu)) {
    $header_menu = implode("&emsp;|&emsp;", $header_menu);
  }
  if (is_array($footer_menu)) {
    $footer_menu = implode("&emsp;|&emsp;", $footer_menu);
  }
}
$select->close();


// Непосредственно, обработчик статических страниц. 
// Если есть запрос на страницу
if (isset($get["page"]) and !isset($get["user"])) {
  $select = query("SELECT `title`, `content`, `metadesc`, `metakeys` FROM `pages` WHERE `alias`='".$get["page"]."' LIMIT 1");
  if (mysqli_num_rows($select) > 0) {
    $page = mysqli_fetch_assoc($select);
    $content = $root."/tmp/static.tpl";
//    $main["title"] = $page["title"]." - ".$main["title"];
    $main["title"] = $main["title"];
    $main["meta_keys"]=$page["metakeys"];
    $main["meta_desc"]= $page["metadesc"]; 
    
    //Добавляем в подвал контента отзывную страницу
    if ($get["page"]=="feedback") $main["feedback"]=$root."/inc/feedback.php";
    
  } else {
    $error404 = true;
  }
}

// Задаем белый список страниц пользователя, что-бы user не могу ползучить доступ к другим
$white = array(
         1 => "page",
         2 => "setting",
         3 => "settings_user",
         4 => "register",
         5 => "restore",
         6 => "operations" );


// Если есть запрос на динамические странице личных данных пользователя
if (isset($get["user"]) and !isset($get["page"])) {
    if ($user["loginid"]<>"") {
        $select = query("SELECT * FROM `usersettings` WHERE `loginid`='".$user["loginid"]."'");
        if (mysqli_num_rows($select) > 0) {
        // Если настройки найдены, подключаем файл согласно get запросу
             if (in_array($get["user"], $white))  include $root."/inc/user.".$get["user"].".php";
             else  $error404 = true;
        } else {
        // Если настройки не найдены, подключаем файл конфигурации
        include $root."/inc/user.setting.php";
        }
    } else {
          if (in_array($get["user"], $white)) include $root."/inc/user.".$get["user"].".php";
          else  $error404 = true;     
    }
}

// Если в процессе обработки был получен 404
// Подключаем шаблон 404 и задаем заголовок
if (isset($error404) and $error404 == true) {
  header("HTTP/1.0 404 Not Found");
  $content = $root."/tmp/404.tpl";
}
include $root."/tmp/main.tpl";

?>