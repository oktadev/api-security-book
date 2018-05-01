<?php
use Michelf\MarkdownExtra;

require('vendor/autoload.php');

$MODE = 'pdf';

?>
<!DOCTYPE html>
<html>
<head>
  <title>API Security</title>
  <link rel="stylesheet" href="css/fonts.css"/>
  <link rel="stylesheet" href="css/book.css"/>
</head>
<body>
<?php

include('0_00_pdf/cover.html');
include('0_00_pdf/titlepage.html');
include('0_00_start/copyright.html');

$toc = json_decode(file_get_contents('toc.json'), true);

?>
<section id="toc" class="toc">
  <h1>Table of Contents</h1>
  <ul class="toc">
    <?
    foreach($toc as $chapter):
      ?>
        <li class="<?= $chapter['class'] ?>"><a href="#<?= $chapter['id'] ?>"><?= $chapter['name'] ?></a>
          <?
            if(isset($chapter['children'])):
              echo "\n      <ul>\n";
              foreach($chapter['children'] as $child):
                echo '        <li>';
                  echo '<a href="#' . $child['id'] . '">';
                    echo $child['name'];
                  echo '</a>';
                echo '</li>' . "\n";
              endforeach;
              echo "      </ul>\n";
            endif;
          ?>
        </li>
      <?php
    endforeach;
    ?>
  </ul>
</section>
<?
foreach($toc as $chapter) {
  if(preg_match('/.md$/', $chapter['src'])) {
    $info = parse_markdown_chapter($chapter['part'],
      $chapter['chapter'], file_get_contents($chapter['src']));
    echo $info['html'];
  } else {
    include($chapter['src']);
  }
  echo "\n\n";
}

include('0_00_pdf/back.html');
?>
</body>
</html>
