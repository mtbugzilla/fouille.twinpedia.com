<?php
// Si l'utilisateur n'est pas déjà connecté, session.php le redirigera
// automatiquement.  La valeur 'true' pour $keep_query_params permet de
// conserver les coordonnées de la case à fouiller.
$keep_query_params = true;
include_once('include/session.php');

const AUTH_DELAY        = 6;
const DIG_DELAY_NORMAL  = 3;
const DIG_DELAY_TOOFAST = 3;

// Si la fouille peut être effectuée imméditatement, on redirige au lieu
// d'afficher une page.
if (isset($_SESSION['uid'])) {
  if (isset($_GET['i'])) {
    if ($_SESSION['digtime'] && ($_SESSION['digtime'] > time())) {
      // attente forcée
      $_SESSION['digtime'] = $_SESSION['digtime'] + DIG_DELAY_TOOFAST;
      $delay = $_SESSION['digtime'] - time();
      $delay_href = $_SERVER['REQUEST_URI'];
      $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
      if ($mysqli->connect_error) {
        printf("Erreur de connexion à la base de données: %s\n",
               $mysqli->error);
        exit; // FIXME
      }
      if (! $mysqli->set_charset('utf8')) {
        printf("Erreur pour passer en utf8: %s\n", $mysqli->error);
        exit; // FIXME
      }
      $mysqli->query("UPDATE users SET digtime=FROM_UNIXTIME("
                     . $_SESSION['digtime'] . "), atime=NOW() WHERE uid="
                     . $_SESSION['uid']);
      $mysqli->close();
      $page_title = "Fouille en cours...";
      $quotes = ['Pause pipi...',
                 'Et ça repart...',
                 'Il fait chaud ici...'];
      $text = $quotes[mt_rand(0, count($quotes) - 1)]
        . " <span id=\"countdown\">$delay</span>";
    } else {
      // fouille
      $_SESSION['digtime'] = time() + DIG_DELAY_NORMAL;
      $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
      if ($mysqli->connect_error) {
        printf("Erreur de connexion à la base de données: %s\n",
               $mysqli->error);
        exit; // FIXME
      }
      if (! $mysqli->set_charset('utf8')) {
        printf("Erreur pour passer en utf8: %s\n", $mysqli->error);
        exit; // FIXME
      }
      $mysqli->query("UPDATE users SET digtime=FROM_UNIXTIME("
                     . $_SESSION['digtime'] . "), atime=NOW() WHERE uid="
                     . $_SESSION['uid']);
      $result = $mysqli->query("SELECT url, secret FROM diglinks WHERE id="
                               . db_quote_str($mysqli, $_GET['i']));
      $row = $result->fetch_assoc();
      $result->free();
      $mysqli->close();
      if (isset($row['url']) && (! isset($row['secret'])
                                 || ($row['secret'] == $_GET['s']))) {
        // fouille correcte, on redirige immédiatement
        header("Location: " . $row['url']);
        // en principe, ce qui suit ne sera jamais affiché
        $page_title = "Résultat de fouille";
        $text = "En fouillant, vous trouvez <a href=\"" . $row['url']
          . "\">ceci</a>";
      } else {
        // url incorrect ou supprimé, code secret incorrect
        $page_title = "Fouille infructueuse";
        $text = "Vous ne fouillez pas au bon endroit.  Cette zone est épuisée.";
      }
    }
  } else {
    // Paramètre manquant
    $page_title = "???";
  }
} else {
  // Utilisateur non connecté
  $page_title = "Qui êtes-vous ?";
  $delay = AUTH_DELAY;
}
include_once('include/header-mini.php');

if (isset($_SESSION['uid'])) {
  echo "<p>$text</p>\n";
  //echo "<h2>Test</h2>";
  //echo "<p>Digtime: " . $_SESSION['digtime'] . "</p>";
  //echo "<p>Curtime: " . time() . "</p>";
  //echo "<p>I = " . $_GET['i'] . "</p>";
  //echo "<p>S = " . $_GET['s'] . "</p>";
} else {
  $delay_href = twin_auth_href();
  echo "<p>Il faut vous connecter avec votre compte Twinoid pour pouvoir fouiller...</p>\n";
  echo "<p>Vous pouvez vous connecter <a href='$delay_href'>ici</a>.</p>\n";
  echo "<p>Vous allez être redirigé automatiquement dans <span id=\"countdown\">$delay</span> secondes...</p>\n";
}

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
