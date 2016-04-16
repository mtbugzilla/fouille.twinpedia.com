<?php
// Ce fichier est destiné à être inclus avant le début de la page.  Il doit
// modifier les headers de la réponse HTTP et il est donc indispensable de
// ne rien afficher (echo, print, etc.), même pas une ligne vide.

// Infos privées (APP_SECRET_KEY, DB_USER, DB_PASS, etc.)
include_once('secrets.php');

// Rôle de l'utilisateur
const ROLE_BANNED   = 0; // Ne peut pas s'identifier, bloqué par admin
const ROLE_UNUSED1  = 1; // -
const ROLE_NEWBIE   = 2; // Utilisateur de base
const ROLE_NORMAL   = 3; // Plus de 100 points
const ROLE_TRUSTED  = 4; // Plus de 1000 points
const ROLE_UNUSED2  = 5; // -
const ROLE_REVIEWER = 6; // Peut voir les grilles de fouille sans les modifier
const ROLE_DEVEL    = 7; // Peut ajouter et modifier des grilles de fouille
const ROLE_MODERATE = 8; // Peut voir la liste des utilisateurs
const ROLE_ADMIN    = 9; // Peut tout faire

// Envoie une requête HTTP POST et attend une réponse au format JSON.
// Retour : objet JSON ou message en cas d'erreur.
function do_post_json($url, $params, $http_options = array()) {
  $default_options = array(
    'method' => 'POST',
    'timeout' => 10,
    'header' => 'Content-type: application/x-www-form-urlencoded',
    'content' => http_build_query($params)
  );
  $http_options = array_merge($default_options, $http_options);
  $context = stream_context_create(array('http' => $http_options));
  $fp = fopen($url, 'rb', false, $context);
  if (!$fp) {
    $err = error_get_last();
    return $err['message'];
  }
  $response = stream_get_contents($fp);
  if ($response === false) {
    $err = error_get_last();
    return $err['message'];
  }
  return json_decode($response);
}

function db_quote_str($mysqli, $str, $if_unset = 'NULL') {
  if (isset($str)) {
    return "'" . $mysqli->real_escape_string($str) . "'";
  } else {
    return $if_unset;
  }
}

function db_quote_int($num, $if_invalid = 'NULL') {
  $val = strval($num);
  if (ctype_digit($val)) {
    return $val;
  } else {
    return $if_invalid;
  }
}

// Génère un lien HTML pour la connexion Twinoid
function twin_auth_href($redir_link = NULL) {
  global $keep_query_params;
  if (! isset($redir_link)) {
    $redir_link = $_SERVER['REQUEST_URI'];
    if (isset($redir_link)) {
      if ($keep_query_params || strpos($redir_link, "?") === false) {
        $redir_link = substr($redir_link, 1);
        $redir_link = str_replace(array("do=logout&", "?do=logout"),
                                  array("",           ""),
                                  $redir_link);
      } else {
        $redir_link = substr($redir_link, 1, strpos($redir_link, "?") - 1);
        if (isset($_GET['css'])) {
          $redir_link .= '?css=' . $_GET['css'];
        }
      }
    } else {
      $redir_link = "./";
    }
  }
  $redir_link = str_replace(array("^",  "&",  ";"),
                            array("^+", "^=", "^-"),
                            $redir_link);
  return 'https://twinoid.com/oauth/auth?client_id=' . APP_TWINOID_ID . '&amp;response_type=code&amp;state=' . htmlspecialchars($redir_link);
}

// Initialisation avec modification des headers HTTP Cookie, Expires, etc.
session_cache_limiter('nocache');
session_name('sid');
session_start();

// Conditions qui causent des changements dans la session
if (isset($_SESSION['token_refresh'])
    && (time() >= $_SESSION['token_refresh'])) {
  header("Location: " . twin_auth_href());
  $_SESSION = array();
  exit();
} elseif (isset($_GET['do']) && ($_GET['do'] == 'logout')) {
  $_SESSION = array();
  $info_msg = "Déconnexion effectuée.";
}

// La session sera fermée par header.php.
$session_write_close = true;
?>
