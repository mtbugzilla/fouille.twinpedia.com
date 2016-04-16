<?php
include_once('include/session.php');
$page_title = "Fouille H-24";
include_once('include/header.php');

if (isset($_SESSION['name'])) {
  echo "<h2>Bienvenue, " . $_SESSION['name'] . " !</h2>\n";
} else {
  echo "<h2>Bienvenue !</h2>\n";
}
?>

<p>Ce site permet de générer des « grilles de fouilles » pour une des
épreuves de l'animation « <a href="http://twd.io/e/Ry7G0w/0">Les 24
Heures de Hordes</a> » (H-24).</p>

<p>Ces grilles à poster sur le forum sont constituées d'icônes ou de
textes cachant des liens vers diverses images ou pages dont une seule
est « gagnante ».  Les joueurs doivent suivre les liens et découvrir
quelle est cette case gagnante dans la grille.  Les liens sont des
liens de redirection et cachent leur destination réelle pour qu'un
joueur ne puisse pas trouver la case gagnante rien qu'en examinant les
liens dans le code source du message.</p>

<p>Exemple de grille (la case gagnante est au centre) :</p>

<p><em>(suppression temporaire de l'exemple pendant les tests)</em></p>

<p>Pour les <strong>joueurs</strong> qui participent à l'animation, ce
site est presque invisible : à part durant l'identification Twinoid ou
en cas d'erreur, les joueurs ne verront rien puisqu'il s'agit surtout
de redirections immédiates vers de nouvelles pages ou images.</p>

<p>Pour les <strong>organisateurs de l'animation</strong>, ce site
permet de préparer des grilles de fouilles et de définir la taille de
chaque grille, le type d'icônes à utiliser, le lien gagnant et sa
position dans la grille, les liens de remplissage, etc.  La page d'<a
href="exemples.php">exemples</a> montre quelques types de grilles et
de paramètres qui peuvent être utilisés.</p>

<p>Bon amusement !</p>

<?php include_once('include/footer.php'); ?>
