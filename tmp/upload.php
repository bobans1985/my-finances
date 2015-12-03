<html>
<head>
  <title>Результат загрузки файла</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php
   if($_FILES["filename"]["size"] > 1024*3*1024)
   {
     echo ("Файл превышает размер");
     exit;
   }
   if(is_uploaded_file($_FILES["filename"]["tmp_name"]))
   {
     move_uploaded_file($_FILES["filename"]["tmp_name"], "/uploads/".$_FILES["filename"]["name"]);
   } else {
      echo("Что то пошло не так");
   }
?>
</body>