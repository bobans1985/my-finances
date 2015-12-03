<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

$config = parse_ini_file($_SERVER["DOCUMENT_ROOT"]."/inc/config.ini");

$db = @new mysqli($config["db_host"], $config["db_login"], $config["db_pass"], $config["db_name"]);
  if (mysqli_connect_errno()) {
    echo "Невозможно подключиться: ".mysqli_connect_error();
}
$db->query("SET NAMES utf8");

static $cq = 0;

function query($query) {
  global $cq; $cq++;
  
  global $db;
  //return $db->query($query);
  $result=$db->query($query);
  if( !$result ) echo ($db->error);
  else
  return $result;
}

?>