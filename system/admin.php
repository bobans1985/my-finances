<? if (!defined("INDEX") == true) { header("Location: /index.php"); }

$admin = false;
$da = "";
session_start();
if (isset($_SESSION["admin"])) {
  if ($_SESSION["admin"] == $da) {
    $admin = true;
  }
}
// 
if (isset($_POST["login"])) {
  if (md5($_POST["password"]) == $da) {
    $admin = true;
    $_SESSION["admin"] = $da;
    header("Refresh: 0");
  }
}

?>