<?php
include_once('include/session.php');

const AUTH_DELAY = 6;

if (isset($_SESSION['uid'])) {
  $page_title = "Vous n'avez rien trouvé";
  $text = "<p>Désolé " . $_SESSION['name'] . ", il n'y a rien ici.</p>";
  if ($_SESSION['digtime'] && ($_SESSION['digtime'] > time())) {
    $delay = $_SESSION['digtime'] - time();
    $delay_href = $_SERVER['REQUEST_URI'];
    $text .= "<p>Vous pourrez tenter une nouvelle fouille dans <span id=\"countdown\">$delay</span> secondes.</p>";
  } else {
    $text .= "<p>Tentez de fouiller ailleurs, vous aurez peut-être plus de chance ?</p>";
  }
} else {
  // Utilisateur non connecté
  $page_title = "Qui êtes-vous ?";
  $delay = AUTH_DELAY;
  $delay_href = twin_auth_href();
  $text = "
<p>Il faut vous connecter avec votre compte Twinoid pour pouvoir fouiller...</p>
<p>Vous pouvez vous connecter <a href=\"$delay_href\">ici</a>.</p>
<p>Vous allez être redirigé automatiquement dans <span id=\"countdown\">$delay</span> secondes...</p>";
}
include_once('include/header-mini.php');

echo "$text\n";

if (isset($delay) && isset($delay_href)) {
  echo "<script>
function startTimer(duration, elem) {
  setInterval(function () {
    duration--;
    elem.innerHTML = duration;
    if (duration <= 0) {
      window.location.href = '$delay_href';
    }
  }, 1000);
}

window.onload = function () {
  startTimer($delay, document.getElementById('countdown'));
};
</script>";
}
include_once('include/footer.php');
?>
