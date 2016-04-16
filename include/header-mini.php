<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link href="https://fonts.googleapis.com/css?family=Trade+Winds" rel="stylesheet" type="text/css" />
  <link href="style-hordes.css" rel="stylesheet" type="text/css" />
  <title><?= isset($page_title) ? $page_title : "fouille.twinpedia.com" ?></title>
</head>
<body>
<?= isset($page_title) ? "<h1>" . $page_title . "</h1>" : "" ?>
<div id="page-mini">
<div id="pagecontents-mini">
<?php
if (isset($error_msg)) {
  echo '<p class="error_box">' . $error_msg . "</p>\n";
}
if (isset($info_msg)) {
  echo '<p class="info_box">' . $info_msg . "</p>\n";
}
if (isset($session_write_close) && $session_write_close) {
  // La page peut continuer à utiliser la session mais sans la modifier.
  // Cela évite de bloquer la session si la page demande un long traitement.
  session_write_close();
}
?>
