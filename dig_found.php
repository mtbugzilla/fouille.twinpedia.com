<?php
include_once('include/session.php');
$page_title = "Vous avez trouvé !";
include_once('include/header-mini.php');

if (isset($_SESSION['uid'])) {
  echo "<p>Bravo " . $_SESSION['name'] . " !</p>";
} else {
  echo "<p>Bravo !</p>";
}
echo "<p>Vous avez creusé au bon endroit.</p>";

if ($_GET['secret']) {
  echo "<p>Le code secret est : <strong>" . $_GET['secret'] . "</strong>.</p>";
}

include_once('include/footer.php');
?>
