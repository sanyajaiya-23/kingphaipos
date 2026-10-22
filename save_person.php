<?php 
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Document</title>
</head>
<body>
  <?php
  $action=$_REQUEST['action'];
  $id_person=$_REQUEST['id_person'];
  $name_person=$_REQUEST['name_person'];
  $id_adress=$_REQUEST['id_adress'];
  $name_adress=$_REQUEST['name_adress'];

  if($action=='edit'){
    $sql="
    UPDATE tb_person SET
    name_person='{$name_person}',
    id_adress='{$id_adress}',
    name_adress='{$name_adress}'
    WHERE
    tb_person.id_person='{$id_person}'
    ";
    $db->query($sql);
  }elseif($action=='delete'){
    $sql="
    DELETE FROM tb_person WHERE tb_person.id_person='{$id_person}'
    ";
    $db->query($sql);
  }else {
    
    $sql="
    INSERT INTO tb_person(name_person,id_adress,name_adress)
    VALUES(
      '$name_person','$id_adress','$name_adress'
    )
    ";
    $db->query($sql);
    
  }
  header("location:name_story.php?search=&search_select=$id_adress");
  ?>
</body>
</html>