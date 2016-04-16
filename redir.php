<?php
include_once('include/session.php');
// Contrairement aux autres, ce fichier n'inclut pas les fichiers qui
// génèrent du code HTML car ce fichier-ci ne fait que rediriger vers une
// autre page en fonction du résultat de l'identification Twinoid.

// Initialise $_SESSION['token'] et $_SESSION['token_refresh'].
function do_twinoid_auth($code) {
  global $error_msg;
  if (! isset($code)) {
    $error_msg = 'bug';
    return false;
  }
  $json = do_post_json('https://twinoid.com/oauth/token',
    array(
      'client_id' => APP_TWINOID_ID,
      'client_secret' => APP_SECRET_KEY,
      'redirect_uri' => APP_REDIRECT_URI,
      'code' => $code,
      'grant_type' => 'authorization_code'
    )
  );
  if (is_string($json)) {
    $error_msg = "Erreur de connexion au serveur Twinoid :<br /><em>$json</em>";
    return false;
  }
  if (isset($json->access_token)) {
    $_SESSION['token'] = $json->access_token;
    if (isset($json->expires_in)) {
      $_SESSION['token_refresh'] = time() + $json->expires_in - 60;
    } else {
      $_SESSION['token_refresh'] = time() + 300;
    }
    unset($error_msg);
    return true;
  } else if (isset($json->error)) {
    $error_msg = "Échec lors de l'identification :<br /><em>" . $json->error
      . "</em>.";
    return false;
  } else {
    $error_msg = "Échec du décodage de la réponse du serveur Twinoid.";
    return false;
  }
}

// Initialise $_SESSION['uid'], $_SESSION['name'] et $_SESSION['avatar'].
function get_twinoid_user_info() {
  global $error_msg;
  if (! isset($_SESSION['token'])) {
    return false;
  }
  $json = do_post_json('http://twinoid.com/graph/me',
    array(
      'access_token' => $_SESSION['token'],
      'fields' => 'id,name,picture,locale,sites.fields(npoints)'
    )
  );
  if (is_string($json)) {
    $error_msg = "Erreur de connexion au serveur Twinoid : " . $json;
    return false;
  }
  if (isset($json->error)) {
    $error_msg = "Erreur de récupération des informations Twinoid : " . $json->error;
    return false;
  }
  if (! is_numeric($json->id)) {
    $error_msg = "Informations Twinoid incorrectes: id=" . $json->id;
  }
  $_SESSION['uid'] = intval($json->id);
  $_SESSION['name'] = $json->name;
  $_SESSION['locale'] = $json->locale;
  //  $_SESSION['sites'] = $json->sites;
  $_SESSION['h_score'] = 0;
  $_SESSION['t_score'] = 0;
  $_SESSION['t_nulls'] = 0;
  if (isset($json->sites)) {
    foreach ($json->sites as $site) {
      if (isset($site->npoints)) {
        $_SESSION['t_score'] += $site->npoints;
        if ($site->npoints > $_SESSION['h_score']) {
          $_SESSION['h_score'] = $site->npoints;
        }
      } else {
        $_SESSION['t_nulls']++;
      }
    }
  }
  if (isset($json->picture) && isset($json->picture->url)) {
    $_SESSION['avatar'] = $json->picture->url;
  }
  return true;
}

// Vérifie si l'utilisateur peut accéder à un rôle plus important
function check_role_upgrade($role) {
  if (($role == ROLE_NEWBIE) && ($_SESSION['t_score'] > 100)) {
    $role = ROLE_NORMAL;
  }
  if (($role == ROLE_NORMAL) && ($_SESSION['t_score'] > 1000)) {
    $role = ROLE_TRUSTED;
  }
  return $role;
}

// Initialise $_SESSION['role'] et met à jour la base de données.
function get_db_user_info() {
  global $error_msg;
  if (! isset($_SESSION['uid']) || ! is_int($_SESSION['uid'])) {
    return false;
  }
  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if ($mysqli->connect_error) {
    $error_msg = "Erreur de connexion à la base de données: "
      . $mysqli->error;
    return false;
  }
  if (! $mysqli->set_charset('utf8')) {
    $error_msg = "Erreur pour passer en utf8: " . $mysqli->error;
    return false;
  }
  $sql_uid = db_quote_int($_SESSION['uid']);
  $sql_avatar = db_quote_str($mysqli, $_SESSION['avatar']);
  $sql_name = db_quote_str($mysqli, $_SESSION['name'], "'???'");
  $result = $mysqli->query("SELECT * FROM users WHERE uid=$sql_uid");
  if ($row = $result->fetch_assoc()) {
    // mise à jour d'un utilisateur déjà connu
    $_SESSION['role'] = check_role_upgrade($row['role']);
    if (($_SESSION['name'] != $row['name'])
        || ($_SESSION['avatar'] != $row['avatar'])
        || ($_SESSION['role'] != $row['role'])) {
      $mysqli->query("UPDATE users SET name=$sql_name, avatar=$sql_avatar, role=" . $_SESSION['role'] . ", mtime=NOW(), atime=NOW() WHERE uid=$sql_uid");
    } else {
      $mysqli->query("UPDATE users SET atime=NOW() WHERE uid=$sql_uid");
    }
    if ($row['digtime']) {
      $_SESSION['digtime'] = strtotime($row['digtime']);
    } else {
      $_SESSION['digtime'] = 0;
    }
  } else {
    // création des données pour un nouvel utilisateur
    $_SESSION['role'] = check_role_upgrade(ROLE_NEWBIE);
    $mysqli->query("INSERT INTO users (uid, name, avatar, role, ctime, mtime, atime) VALUES ($sql_uid, $sql_name, $sql_avatar, " . $_SESSION['role'] . ", NOW(), NOW(), NOW())");
  }
  $result->free();
  $mysqli->close();
  return true;
}

// Documentation : http://twinoid.com/developers/doc
if (isset($_GET['state'])) {
  // Opération inverse de ce qui est fait par twin_auth_href()
  $redir_link = $_GET['state'];
  $redir_link = str_replace(array("^-", "^=", "^+"),
                            array(";",  "&",  "^"),
                            $redir_link);
} else {
  $redir_link = "";
}
if (isset($_SERVER["HTTP_HOST"])) {
  $redir_link = $_SERVER["HTTP_HOST"] . "/" . $redir_link;
} else {
  $redir_link = $_SERVER["SERVER_NAME"] . "/" . $redir_link;
}
if (strpos($redir_link, "://") === false) {
  $redir_link = "http://" . $redir_link;
}
if (isset($_GET['code'])) {
  $_SESSION = array();
  if (do_twinoid_auth($_GET['code'])) {
    if (get_twinoid_user_info()) {
      if (get_db_user_info()) {
        header("Location: " . $redir_link);
        exit();
        //$info_msg = "Location: " . $redir_link;
      }
    }
  }
} elseif (isset($_GET['error'])) {
  $_SESSION = array();
  $error_msg = 'Erreur de redirection Twinoid: ' . $_GET['error'];
} else {
  $error_msg = 'Appel incorrect.  Paramètres manquants.';
}

$page_title = "Erreur";
include_once('include/header.php');
//echo '<h2>Test</h2>';
//echo "<p>Debug:</p><pre>$XXX</pre>";
//echo "<p>Get :</p><pre>";
//print_r ($_GET);
//echo "</pre>\n";
//echo "<p>Post :</p><pre>";
//print_r ($_POST);
//echo "</pre>\n";
//echo "<p>Session :</p><pre>";
//print_r ($_SESSION);
//echo "</pre>\n";
//echo "<p>Server :</p><pre>";
//print_r ($_SERVER);
//echo "</pre>\n";
echo "
<p>La connexion au serveur Twinoid a échoué.  Vous n'êtes pas identifié et
ne pourrez pas profiter de certaines fonctions de ce site.</p>
";
include_once('include/footer.php');
?>
