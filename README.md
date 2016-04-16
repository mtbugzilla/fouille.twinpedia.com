# Site de fouille pour les 24 heures de Hordes

## Description

Ces fichiers constituent le code source qui a servi pour le site
[fouille.twinpedia.com](http://fouille.twinpedia.com/) qui permettait
de générer des « grilles de fouilles » pour une des épreuves de
l'animation « [Les 24 Heures de Hordes](http://twd.io/e/Ry7G0w/0) »
(H-24) en septembre 2015.

L'épreuve « [Poser ses Fouilles sur la table](http://twd.io/e/8-TN0w)
» utilisait des grilles à poster sur le forum, constituées d'icônes ou
de textes cachant des liens vers diverses images ou pages dont une
seule était « gagnante ».  Les joueurs devaient suivre les liens et
découvrir quelle était cette case gagnante dans la grille.  Les liens
étaient des liens de redirection et cachaient leur destination réelle
pour qu'un joueur ne puisse pas trouver la case gagnante rien qu'en
examinant les liens dans le code source du message.

## Copyrights

Les images du répertoire `images_mt` sont la propriété de Motion-Twin.

Les images `images\tango-*.png` font partie du projet Tango.  Elles
étaient initialement distribuées sous la license « Creative Commons
Attribution-ShareAlike » mais sont dans le domaine public depuis 2009.

Sauf mention contraire, tout le reste du site et de ses images sont
sous la license **GNU Affero GPL 3.0**.  La principale différence par
rapport à la license GPL 3.0 classique est l'obligation de rendre le
code source disponible (ici, les fichiers PHP et autres) lorsqu'on
utilise ce code pour offrir un service en ligne.  Pour plus de
détails, voir le fichier `LICENSE`.

## Informations pour les développeurs

### API Twinoid

L'identification des utilisateurs est basée sur le protocol OAuth 2
selon les informations données dans [la documentation de l'API
Twinoid](https://twinoid.com/developers/doc).

Le fichier `include/secrets.php` doit contenir le numéro de
l'application ainsi que sa clé secrète. Ce fichier contenant des
données sensibles n'est pas inclus ici mais le fichier d'exemple
`include/secrets.php.template` en donne le format.

### Format des tables dans la base de données
```
mysql> describe diglinks;
+--------+-----------------+------+-----+---------+-------+
| Field  | Type            | Null | Key | Default | Extra |
+--------+-----------------+------+-----+---------+-------+
| id     | varchar(8)      | NO   | PRI | NULL    |       |
| grid   | int(6) unsigned | NO   |     | NULL    |       |
| secret | varchar(8)      | YES  |     | NULL    |       |
| url    | varchar(255)    | YES  |     | NULL    |       |
+--------+-----------------+------+-----+---------+-------+
4 rows in set (0.00 sec)

mysql> describe grids;
+--------------+---------------------+------+-----+---------+----------------+
| Field        | Type                | Null | Key | Default | Extra          |
+--------------+---------------------+------+-----+---------+----------------+
| grid         | int(6) unsigned     | NO   | PRI | NULL    | auto_increment |
| width        | tinyint(1) unsigned | NO   |     | NULL    |                |
| height       | tinyint(1) unsigned | NO   |     | NULL    |                |
| win_x        | tinyint(1) unsigned | YES  |     | NULL    |                |
| win_y        | tinyint(1) unsigned | YES  |     | NULL    |                |
| win_url      | varchar(255)        | YES  |     | NULL    |                |
| bonus_x      | tinyint(1) unsigned | YES  |     | NULL    |                |
| bonus_y      | tinyint(1) unsigned | YES  |     | NULL    |                |
| bonus_url    | varchar(255)        | YES  |     | NULL    |                |
| format       | varchar(255)        | YES  |     | NULL    |                |
| fmt_li_start | varchar(100)        | YES  |     | NULL    |                |
| fmt_li_end   | varchar(100)        | YES  |     | NULL    |                |
| fmt_gr_start | varchar(100)        | YES  |     | NULL    |                |
| fmt_gr_end   | varchar(100)        | YES  |     | NULL    |                |
| rand_urls    | varchar(10000)      | YES  |     | NULL    |                |
| rand_icons   | varchar(5000)       | YES  |     | NULL    |                |
| ctime        | datetime            | NO   |     | NULL    |                |
| cuser        | int(11)             | YES  |     | NULL    |                |
| mtime        | datetime            | NO   |     | NULL    |                |
| muser        | int(11)             | YES  |     | NULL    |                |
| description  | varchar(255)        | YES  |     | NULL    |                |
| seed         | int(11)             | YES  |     | NULL    |                |
+--------------+---------------------+------+-----+---------+----------------+
22 rows in set (0.00 sec)

mysql> describe users;
+---------+--------------+------+-----+---------+-------+
| Field   | Type         | Null | Key | Default | Extra |
+---------+--------------+------+-----+---------+-------+
| uid     | int(11)      | NO   | PRI | NULL    |       |
| name    | varchar(33)  | YES  |     | NULL    |       |
| avatar  | varchar(255) | YES  |     | NULL    |       |
| role    | tinyint(1)   | YES  |     | NULL    |       |
| ctime   | datetime     | NO   |     | NULL    |       |
| mtime   | datetime     | NO   |     | NULL    |       |
| atime   | datetime     | NO   |     | NULL    |       |
| digtime | datetime     | YES  |     | NULL    |       |
+---------+--------------+------+-----+---------+-------+
8 rows in set (0.00 sec)
```
