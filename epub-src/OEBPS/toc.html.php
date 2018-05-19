<?php
chdir(dirname(__FILE__).'/../../');
require('vendor/autoload.php');

$toc = json_decode(file_get_contents('toc.json'), true);
$newtoc = [];

$currentpart = false;
$previouspart = false;
$chapters = [];


echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="en">
<head>
  <title>API Security Table of Contents</title>
</head>
<body>
  <h1>Table of Contents</h1>
  <div>
    <ul>
      <?
      $ch = 0;
      foreach($toc as $chapter):
        if($chapter['class'] == 'chapter') $ch++;
      ?>
      <li>
        <a href="content/<?= $chapter['file'] ?>#<?= $chapter['id'] ?>"><?=
          ($chapter['class'] == 'chapter' ? $ch.'. ' : '').$chapter['name']
        ?></a>
        <?php
        if(isset($chapter['children'])):
          echo '<ul>';
          foreach($chapter['children'] as $child):
            ?>
            <li><a href="content/<?= $chapter['file'] ?>#<?= $child['id'] ?>"><?= $child['name'] ?></a></li>
            <?php
          endforeach;
          echo '</ul>';
        endif;
        ?>
      </li>
      <? endforeach ?>
    </ul>
  </div>
</body>
</html>
