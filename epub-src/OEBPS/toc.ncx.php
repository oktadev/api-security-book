<?php
chdir(dirname(__FILE__).'/../../');
require('vendor/autoload.php');

echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
?>
<!DOCTYPE ncx>
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1" xml:lang="en">
  <!-- Metadata Section -->
  <head>
    <meta content="urn:isbn:9781387814190" name="dtb:uid"/>
    <meta content="2" name="dtb:depth" /> <!-- Set for 2 if you want a sub-level. It can go up to 4 -->
    <meta content="0" name="dtb:totalPageCount" /> <!-- Do Not change -->
    <meta content="0" name="dtb:maxPageNumber" /> <!-- Do Not change -->
  </head>
  <!-- Title and Author Section -->
  <docTitle>
    <text>API Security</text>
  </docTitle>
  <docAuthor>
    <text>Lee Brandt</text>
  </docAuthor>
  <docAuthor>
    <text>Keith Casey</text>
  </docAuthor>
  <docAuthor>
    <text>Randall Degges</text>
  </docAuthor>
  <docAuthor>
    <text>Brian Demers</text>
  </docAuthor>
  <docAuthor>
    <text>Joël Franusic</text>
  </docAuthor>
  <docAuthor>
    <text>Sai Maddali</text>
  </docAuthor>
  <docAuthor>
    <text>Matt Raible</text>
  </docAuthor>
  <!-- Navigation Map Section -->
  <navMap>
<?php
$toc = json_decode(file_get_contents('toc.json'), true);
$p = 1;
$pt = 0;
$ch = 0;
foreach($toc as $chapter):
  if($chapter['class'] == 'chapter') $ch++;
  if($chapter['class'] == 'part') $pt++;
?>
<navPoint id="<?= $chapter['id'] ?>" playOrder="<?= $p++ ?>">
  <navLabel>
    <text><?= ($chapter['class']=='chapter' ? $ch.'. ' : '').$chapter['name'] ?></text>
  </navLabel>
  <content src="content/<?= $chapter['file'] ?>#<?= $chapter['id'] ?>" />
  <?php
  if(isset($chapter['children'])):
    foreach($chapter['children'] as $child):
    ?>
    <navPoint id="<?= $child['id'] ?>" playOrder="<?= $p++ ?>">
      <navLabel>
        <text><?= $child['name'] ?></text>
      </navLabel>
      <content src="content/<?= $chapter['file'] ?>#<?= $child['id'] ?>" />
    </navPoint>
    <?php
    endforeach;
  endif;
  ?>
</navPoint>
<?php
endforeach;
?>
  </navMap>
</ncx>
