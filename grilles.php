<?php
$keep_query_params = true;
include_once('include/session.php');
$page_title = "Grilles de fouille";
include_once('include/header.php');

function get_user_name($mysqli, $uid) {
  // HACK!
  $known_users = [ 148 => 'Bugzilla',
                   524014 => 'Jery',
                   1730792 => 'Onirine' ];
  if (isset($known_users[$uid])) {
    return $known_users[$uid];
  } else {
    return "???";
  }
}

function format_user($mysqli, $uid) {
  return "<a href=\"http://twinoid.com/user/" . $uid . "\">"
    . get_user_name($mysqli, $uid) . "</a>";
}

function split_lines($lines) {
  $result_array = explode("\n", str_replace("\r", "", $lines));
  $result_array = array_diff($result_array, array("", "\n"));
  return $result_array;
}

function delete_links($mysqli, $grid_id) {
  $sql = "DELETE FROM diglinks WHERE grid=" . db_quote_int($grid_id);
  if ($mysqli->query($sql) == TRUE) {
    // FIXME
  } else {
    // FIXME
  }
}

function create_links($mysqli, $grid) {
  $rand_urls = split_lines($grid->rand_urls);
  //echo "DEBUG: <pre>\n";
  //var_dump($rand_urls);
  //echo "</pre>\n";
  if (! isset($rand_urls) || count($rand_urls) < 1) {
    return false;
  }
  $rand_icons = split_lines($grid->rand_icons);
  if (! isset($rand_icons) || count($rand_icons) < 1) {
    $rand_icons = array(':hordes_dig:');
  }
  if (isset($grid->seed)) {
    mt_srand($grid->seed);
  }
  $id = $grid->grid * 100000;
  for ($y = 0; $y < $grid->height; $y++) {
    echo "<p>";
    for ($x = 0; $x < $grid->width; $x++) {
      $secret = mt_rand(0, 1000000000);
      if (($x == $grid->win_x) && ($y == $grid->win_y) && $grid->win_url) {
        $url = $grid->win_url;
      } else if (($x == $grid->bonus_x) && ($y == $grid->bonus_y) && $grid->bonus_url) {
        $url = $grid->bonus_url;
      } else if ($rand_urls) {
        $url = $rand_urls[mt_rand(0, count($rand_urls) - 1)];
      } else {
        $url = NULL;
      }
      $icon = $rand_icons[$secret % count($rand_icons)];
      $enc_id = base_convert($id, 10, 36);
      $enc_secret = base_convert($secret, 10, 36);
      $dig_url = "http://fouille.twinpedia.com/dig.php?i=$enc_id&s=$enc_secret";
      $sql = "INSERT INTO diglinks (id, grid, secret, url) VALUES ("
        . db_quote_str($mysqli, $enc_id) . ", "
        . db_quote_int($grid->grid) . ", "
        . db_quote_str($mysqli, $enc_secret) . ", "
        . db_quote_str($mysqli, $url)
        . ")";
      if ($mysqli->query($sql) == TRUE) {
        // FIXME
      } else {
        // FIXME
      }
      $id++;
    }
  }
  mt_srand();
  return true;
}

function list_grids($mysqli) {
  echo "<ol type=\"1\">\n";
  $result = $mysqli->query("SELECT * FROM grids");
  while ($row = $result->fetch_assoc()) {
    $grid_num = $row['grid'];
    $width = $row['width'];
    $height = $row['height'];
    $desc = $row['description'];
    if (! $desc) {
      $desc = "(pas de description)";
    }
    $ctime = strtotime($row['ctime']);
    $mtime = strtotime($row['mtime']);
    $cuser = format_user($mysqli, $row['cuser']);
    $muser = format_user($mysqli, $row['muser']);
    echo "  <li value=\"$grid_num\"><a href=\"grilles.php?g=$grid_num\">"
      . "$desc</a><small><br />"
      . "Taille : <strong>$width x $height</strong>. "
      . "Créée le " . date('d/m/Y', $ctime)
      . " à " . date('H:i:s', $ctime)
      . " par $cuser. "
      . "Modifiée le " . date('d/m/Y', $mtime)
      . " à " . date('H:i:s', $mtime)
      . " par $muser.</small></li>\n";
  }
  $result->free();
  echo "</ol>\n";
}

function load_grid($mysqli, $grid_num) {
  $result = $mysqli->query("SELECT * FROM grids WHERE grid="
                           . db_quote_int($grid_num));
  if ($row = $result->fetch_assoc()) {
    $result->free();
    $row = (object)$row;
    return $row;
  }
  $result->free();
  return NULL;
}

function save_grid($mysqli, $grid) {
  //echo "<p>DEBUG POST:</p><pre>"; print_r($_POST); echo "</pre>";
  //echo "<p>DEBUG GET:</p><pre>"; print_r($_GET); echo "</pre>";
  //echo "<p>DEBUG grid:</p><pre>"; print_r($grid); echo "</pre>";
  if ($grid->grid) {
    $sql = "UPDATE grids SET "
      . "width=" . db_quote_int($grid->width) . ", "
      . "height=" . db_quote_int($grid->height) . ", "
      . "win_x=" . db_quote_int($grid->win_x) . ", "
      . "win_y=" . db_quote_int($grid->win_y) . ", "
      . "win_url=" . db_quote_str($mysqli, $grid->win_url) . ", "
      . "bonus_x=" . db_quote_int($grid->bonus_x) . ", "
      . "bonus_y=" . db_quote_int($grid->bonus_y) . ", "
      . "bonus_url=" . db_quote_str($mysqli, $grid->bonus_url) . ", "
      . "format=" . db_quote_str($mysqli, $grid->format) . ", "
      . "fmt_li_start=" . db_quote_str($mysqli, $grid->fmt_li_start) . ", "
      . "fmt_li_end=" . db_quote_str($mysqli, $grid->fmt_li_end) . ", "
      . "fmt_gr_start=" . db_quote_str($mysqli, $grid->fmt_gr_start) . ", "
      . "fmt_gr_end=" . db_quote_str($mysqli, $grid->fmt_gr_end) . ", "
      . "rand_urls=" . db_quote_str($mysqli, $grid->rand_urls) . ", "
      . "rand_icons=" . db_quote_str($mysqli, $grid->rand_icons) . ", "
      . "mtime=NOW(), muser=" . db_quote_int($_SESSION['uid']) . ", "
      . "description=" . db_quote_str($mysqli, $grid->description) . ", "
      . "seed=" . db_quote_int($grid->seed)
      . " WHERE grid=" . db_quote_int($grid->grid);
  } else {
    $sql = "INSERT INTO grids (width, height, win_x, win_y, win_url, "
      . "bonus_x, bonus_y, bonus_url, "
      . "format, fmt_li_start, fmt_li_end, fmt_gr_start, fmt_gr_end, "
      . "rand_urls, rand_icons, "
      . "ctime, cuser, mtime, muser, description, seed) VALUES ("
      . db_quote_int($grid->width) . ", "
      . db_quote_int($grid->height) . ", "
      . db_quote_int($grid->win_x) . ", "
      . db_quote_int($grid->win_y) . ", "
      . db_quote_str($mysqli, $grid->win_url) . ", "
      . db_quote_int($grid->bonus_x) . ", "
      . db_quote_int($grid->bonus_y) . ", "
      . db_quote_str($mysqli, $grid->bonus_url) . ", "
      . db_quote_str($mysqli, $grid->format) . ", "
      . db_quote_str($mysqli, $grid->fmt_li_start) . ", "
      . db_quote_str($mysqli, $grid->fmt_li_end) . ", "
      . db_quote_str($mysqli, $grid->fmt_gr_start) . ", "
      . db_quote_str($mysqli, $grid->fmt_gr_end) . ", "
      . db_quote_str($mysqli, $grid->rand_urls) . ", "
      . db_quote_str($mysqli, $grid->rand_icons) . ", "
      . "NOW(), " . db_quote_int($_SESSION['uid']) . ", "
      . "NOW(), " . db_quote_int($_SESSION['uid']) . ","
      . db_quote_str($mysqli, $grid->description) . ", "
      . db_quote_int($grid->seed) . ")";
  }
  $result = $mysqli->query($sql);
  if ($result === false) {
    // FIXME: error message
    return false;
  }
  if (! $grid->grid) {
    $grid->grid = strval($mysqli->insert_id);
  }
  return true;
}

function delete_grid($mysqli, $grid) {
  // FIXME: check if allowed
  $sql = "DELETE FROM grids WHERE grid=" . db_quote_int($grid->grid);
  $mysqli->query($sql);
  // FIXME: check result
}

function display_grid_form($grid, $operation = 'modify') {
  if ($operation == 'create') {
    echo '<h2>Création de nouvelle grille</h2>';
  } else if ($operation == 'delete') {
    echo '<h2>Suppression de la grille ' . $grid->grid . '</h2>
<p class="error_box">Attention, la suppression sera définitive.<br />
Confirmez ci-dessous que vous voulez supprimer cette grille de fouilles.</p>
';
  } else {
    echo '<h2>Modification de la grille ' . $grid->grid . '</h2>';
  }
  echo '
<form action="grilles.php" method="post">
  <input type="hidden" name="grid" value="' . $grid->grid . '">
  <table><tbody>
    <tr><td>Description :</td><td><input type="text" name="description" value="' . htmlspecialchars($grid->description) . '" style="width: 40em"></td></tr>
    <tr><td>Taille :</td><td><input type="text" name="width" value="' . $grid->width . '" style="width: 2em"> x <input type="text" name="height" value="' . $grid->height . '" style="width: 2em"></td></tr>
    <tr><td>Position gagnante :</td><td><input type="text" name="win_x" value="' . $grid->win_x . '" style="width: 2em">, <input type="text" name="win_y" value="' . $grid->win_y . '" style="width: 2em"> <small><em>Coin supérieur droit = (0, 0).</em></small></td></tr>
    <tr><td>Adresse gagnante :</td><td><input type="text" name="win_url" value="' . htmlspecialchars($grid->win_url) . '" style="width: 40em"></td></tr>
    <tr><td>Position bonus :</td><td><input type="text" name="bonus_x" value="' . $grid->bonus_x . '" style="width: 2em">, <input type="text" name="bonus_y" value="' . $grid->bonus_y . '" style="width: 2em"> <small><em>Coin supérieur droit = (0, 0).</em></small></td></tr>
    <tr><td>Adresse bonus :</td><td><input type="text" name="bonus_url" value="' . htmlspecialchars($grid->bonus_url) . '" style="width: 40em"></td></tr>
    <tr><td>Adresses<br /> de remplissage :</td><td><textarea name="rand_urls" rows="10" cols="65">' . htmlspecialchars($grid->rand_urls) . '</textarea><br />
    <small><em>Une adresse d\'image ou de page par ligne.</em></small></td></tr>
    <tr><td>Icônes ou textes :</td><td><textarea name="rand_icons" rows="5" cols="25">' . htmlspecialchars($grid->rand_icons) . '</textarea><br />
    <small><em>Un code d\'icône ou de texte par ligne.  Utilisé par %i.</em></small></td></tr>
    <tr><td>Formattage forum :</td><td><input type="text" name="format" value="' . htmlspecialchars($grid->format) . '" style="width: 40em"><br>
    <small><em>%u = URL image, %i = icône ou texte, %x = coordonnée X, %y = coordonnée Y, %g = numéro de grille.</em></small></td></tr>
    <tr><td>Format début grille :</td><td><input type="text" name="fmt_gr_start" value="' . htmlspecialchars($grid->fmt_gr_start) . '" style="width: 40em"></td></tr>
    <tr><td>Format fin grille :</td><td><input type="text" name="fmt_gr_end" value="' . htmlspecialchars($grid->fmt_gr_end) . '" style="width: 40em"></td></tr>
    <tr><td>Format début ligne :</td><td><input type="text" name="fmt_li_start" value="' . htmlspecialchars($grid->fmt_li_start) . '" style="width: 40em"></td></tr>
    <tr><td>Format fin ligne :</td><td><input type="text" name="fmt_li_end" value="' . htmlspecialchars($grid->fmt_li_end) . '" style="width: 40em"></td></tr>
    <tr><td>Graine PRNG :</td><td><input type="text" name="seed" value="' . $grid->seed . '" style="width: 8em"><br />
    <small><em>Indiquer un nombre ici permet de regénérer la même grille (mêmes nombres pseudo-aléatoires).</em></small></td></tr>
  </tbody></table>
  <div class="buttonbox">';
  if ($operation == 'create') {
    echo '<input type="submit" name="submit" value="Créer la grille" class="button">';
  } else if ($operation == 'delete') {
    echo '<a href="grilles.php?do=delconf&g=' . $grid->grid . '" class="button">Confirmer la suppression</a>';
  } else {
    echo '<input type="submit" name="submit" value="Modifier" class="button">';
    echo '<a href="grilles.php?do=del&g=' . $grid->grid . '" class="button">Supprimer</a>';
  }
  echo '
  </div>
</form>';
}

function echo_tmpl($template, $grid, $x, $y, $dig_url = "", $dig_icon = "") {
  $f_text = str_replace(array("%u", "%x", "%y", "%X", "%Y",
                              "%g", "%i"),
                        array($dig_url, $x, $y, ($x + 1), ($y + 1),
                              $grid->grid, $dig_icon),
                        $template);
  echo htmlspecialchars($f_text);
}

function display_grid_links($mysqli, $grid) {
  $x = 0;
  $y = 0;
  $rand_icons = split_lines($grid->rand_icons);
  if (! isset($rand_icons) || count($rand_icons) < 1) {
    $rand_icons = array(':hordes_dig:');
  }
  echo '
<h2>Texte à copier pour le forum</h2>
<textarea name="dig_links" rows="30" cols="80">
';
  if ($grid->fmt_gr_start) {
    echo_tmpl($grid->fmt_gr_start, $grid, $x, $y);
    echo "\n";
  }
  if ($grid->fmt_li_start) {
    echo_tmpl($grid->fmt_li_start, $grid, $x, $y);
  }
  $result = $mysqli->query("SELECT * FROM diglinks WHERE grid=" . $grid->grid);
  while ($row = $result->fetch_assoc()) {
    if (isset($row['secret'])) {
      $secret = base_convert($row['secret'], 36, 10);
    } else {
      $secret = 0;
    }
    $icon = $rand_icons[$secret % count($rand_icons)];
    $dig_url = "http://fouille.twinpedia.com/dig.php?i=" . $row['id']
      . "&s=" . $row['secret'];
    echo_tmpl($grid->format, $grid, $x, $y, $dig_url, $icon);
    $x++;
    if ($x >= $grid->width) {
      $x = 0;
      if ($grid->fmt_li_end) {
        echo_tmpl($grid->fmt_li_end, $grid, $x, $y);
      }
      $y++;
      echo "\n";
      if ($grid->fmt_li_start && $y < $grid->height) {
        echo_tmpl($grid->fmt_li_start, $grid, $x, $y);
      }
    }
  }
  if ($grid->fmt_gr_end) {
    echo_tmpl($grid->fmt_gr_end, $grid, $x, $y);
    echo "\n";
  }
  $result->free();
  echo '</textarea><br />
<small><em>Conseil : cliquer dans la zone de texte, Ctrl-A pour tout sélectionner, Ctrl-C pour copier.</em></small>
';
}

if (isset($_SESSION['uid'])) {
  // - - - - - - - - - - Utilisateurs connectés :

    if ($_SESSION['role'] >= ROLE_DEVEL) {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
      printf("Erreur de connexion à la base de données: %s\n", $mysqli->error);
      exit; // FIXME
    }
    if (! $mysqli->set_charset('utf8')) {
      printf("Erreur pour passer en utf8: %s\n", $mysqli->error);
      exit; // FIXME
    }
    if (isset($_POST['grid'])) {
      $grid_num = $_POST['grid'];
      $grid = (object)array('grid' => $grid_num,
                            'width' => $_POST['width'],
                            'height' => $_POST['height'],
                            'win_x' => $_POST['win_x'],
                            'win_y' => $_POST['win_y'],
                            'win_url' => $_POST['win_url'],
                            'bonus_x' => $_POST['bonus_x'],
                            'bonus_y' => $_POST['bonus_y'],
                            'bonus_url' => $_POST['bonus_url'],
                            'format' => $_POST['format'],
                            'fmt_li_start' => $_POST['fmt_li_start'],
                            'fmt_li_end' => $_POST['fmt_li_end'],
                            'fmt_gr_start' => $_POST['fmt_gr_start'],
                            'fmt_gr_end' => $_POST['fmt_gr_end'],
                            'rand_urls' => $_POST['rand_urls'],
                            'rand_icons' => $_POST['rand_icons'],
                            'description' => $_POST['description'],
                            'seed' => $_POST['seed']);
      if (! $grid->seed) {
        $grid->seed = mt_rand(0, 1000000000);
      }
      if (save_grid($mysqli, $grid)) {
        delete_links($mysqli, $grid->grid);
        echo "<p class=\"info_box\">Modifications enregistrées.</p>\n";
        if (! create_links($mysqli, $grid)) {
          echo "<p class=\"error_box\">Liste de liens manquante.</p>\n";
        }
      } else {
        echo "<p class=\"error_box\">Données incorrectes.</p>\n";
        echo "<!--" . $mysqli->error . "-->\n";
      }
    } else if (isset($_GET['g'])) {
      $grid_num = $_GET['g'];
    }
    if (isset($grid_num)) {
      if ($grid_num == 'new') {
        $grid = (object)array('grid' => NULL,
                              'description' => NULL,
                              'width' => 5,
                              'height' => 5,
                              'win_x' => NULL,
                              'win_y' => NULL,
                              'win_url' => NULL,
                              'bonus_x' => NULL,
                              'bonus_y' => NULL,
                              'bonus_url' => NULL,
                              'format' => '[lien=%u]%i[/lien] ',
                              'fmt_li_start' => NULL,
                              'fmt_li_end' => NULL,
                              'fmt_gr_start' => NULL,
                              'fmt_gr_end' => NULL,
                              'rand_urls' => NULL,
                              'rand_icons' => NULL);
        display_grid_form($grid, 'create');
      } else {
        $grid = load_grid($mysqli, $grid_num);
        if ($grid) {
          if (isset($_GET['do'])) {
            if ($_GET['do'] == 'del') {
              display_grid_form($grid, 'delete');
            } else if ($_GET['do'] == 'delconf') {
              delete_grid($mysqli, $grid);
              echo "<p class=\"info_box\">Grille $grid_num supprimée.</p>\n";
            }
          } else {
            display_grid_form($grid, 'modify');
            display_grid_links($mysqli, $grid);
          }
        } else {
          echo "<p class=\"error_box\">Grille $grid_num non trouvée.</p>\n";
        }
      }
      echo "<p><a href=\"grilles.php\">Retour à la liste de grilles</a></p>\n";
    } else {
      echo "<h2>Mes grilles de fouille</h2>\n";
      list_grids($mysqli);
      echo "<p><a href=\"grilles.php?g=new\" class=\"button\">Créer une nouvelle grille</a></p>\n";
    }
    $mysqli->close();
  } else {
    // niveau insuffisant
    echo "<p>Il semble que vous n'ayez accès à aucune grille de fouille existante.</p>\n";
    echo "<p>De plus, vous n'avez pas l'autorisation d'en créer de nouvelles.</p>\n";
  }
?>

<?php
} else {
// - - - - - - - - - - Utilisateurs non connectés :
?>

<p class="note_box">Pour gérer des grilles de fouilles, il faut être
identifié.  Le bouton <strong>Connexion</strong> dans la barre
ci-dessus permet de se connecter à l'aide d'un compte Twinoid.</p>

<?php
// - - - - - - - - - - Find de page commune :
}
include_once('include/footer.php');
?>
