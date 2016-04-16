<?php
include_once('include/session.php');
$page_title = "Utilisateurs";
include_once('include/header.php');
?>
<h2>Liste des utilisateurs</h2>

<?php
  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (! $mysqli->set_charset('utf8')) {
    printf("Erreur pour passer en utf8: %s\n", $mysqli->error);
    exit;
  }
  $sql = "SELECT * FROM users";
  if (isset($_GET['s'])) {
    $sql .= " ORDER BY " . $mysqli->real_escape_string($_GET['s']);
  }
  if (isset($_GET['sd'])) {
    $sql .= " ORDER BY " . $mysqli->real_escape_string($_GET['sd']) . " DESC";
  }
  $result = $mysqli->query($sql);
  if (isset($_GET['f']) && $_GET['f'] == 'raw') {
    echo "<pre>\n";
    print_r($result);
    while ($row = $result->fetch_assoc()) {
      print_r($row);
    }
    echo "</pre>\n";
  } else {
    echo "<table class=\"users\"><tbody>\n";
    echo "<tr><th colspan=\"2\">Nom</th><th>Role</th><th>Enregistré</th><th>Modifié</th><th>Accès</th><th>Fouille</th></tr>\n";
    while ($row = $result->fetch_assoc()) {
      echo "<tr>";
      if ($row['avatar']) {
        echo "<td><a href=\"http://twinoid.com/user/" . $row['uid'] . "\" target=\"_blank\"><img src=\"" . $row['avatar'] . "\" style=\"max-width: 40px; max-height: 40px;\" /></a></td>";
      } else {
        echo "<td>?</td>";
      }
      if ($row['name']) {
        echo "<td><a href=\"http://twinoid.com/user/" . $row['uid'] . "\" target=\"_blank\">" . $row['name'] . "</td>";
      } else {
        echo "<td>?</td>";
      }
      if ($row['role']) {
        echo "<td>" . $row['role'] . "</td>";
      } else {
        echo "<td>-</td>";
      }
      if ($row['ctime']) {
        echo "<td>" . $row['ctime'] . "</td>";
      } else {
        echo "<td>-</td>";
      }
      if ($row['mtime']) {
        echo "<td>" . $row['mtime'] . "</td>";
      } else {
        echo "<td>-</td>";
      }
      if ($row['atime']) {
        echo "<td>" . $row['atime'] . "</td>";
      } else {
        echo "<td>-</td>";
      }
      if ($row['digtime']) {
        echo "<td>" . $row['digtime'] . "</td>";
      } else {
        echo "<td>-</td>";
      }
      echo "</tr>\n";
    }
    echo "</tbody></table>\n";
  }
  $result->free();
  $mysqli->close();
?>

<h2>Explications</h2>

<p>Cette liste d'utilisateurs n'est visible que par ceux qui ont un rôle de
développeur ou d'administrateur.</p>

<p>Rôles :</p>

<ul>
  <li>0 = Banni</li>
  <li>1 = (pas utilisé)</li>
  <li>2 = Débutant (moins de 100 points Twinoid)</li>
  <li>3 = Normal (entre 100 et 1000 points Twinoid)</li>
  <li>4 = Joueur sérieux (plus de 1000 points Twinoid)</li>
  <li>5 = (pas utilisé)</li>
  <li>6 = Arbitre ou vérificateur (peut voir les grilles sans les modifier)</li>
  <li>7 = Développeur (peut voir et créer des grilles)</li>
  <li>8 = Modérateur (peut aussi voir cette liste)</li>
  <li>9 = Administrateur de ce site</li>
</ul>

<p>Les rôles 2, 3 et 4 sont attribués automatiquement en fonction du
nombre total de points Twinoid gagnés sur l'ensemble des jeux.  Cela sert
à estimer à quel point le joueur pourrait tenir à son compte.</p>

<?php include_once('include/footer.php'); ?>
