<?php
chdir(dirname(__FILE__).'/../../');
require('vendor/autoload.php');

$toc = json_decode(file_get_contents('toc.json'), true);

$images = [];

echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
?>
<package xmlns="http://www.idpf.org/2007/opf" version="2.0" unique-identifier="OAuth2Simplified">
<metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
  <dc:title>API Security</dc:title>
  <dc:language>en-us</dc:language>
  <dc:identifier id="OAuth2Simplified">urn:isbn:9781387814190</dc:identifier>
  <dc:creator opf:file-as="Brandt, Lee" opf:role="aut">Lee Brandt</dc:creator>
  <dc:creator opf:file-as="Casey, Keith" opf:role="aut">Keith Casey</dc:creator>
  <dc:creator opf:file-as="Degges, Randall" opf:role="aut">Randall Degges</dc:creator>
  <dc:creator opf:file-as="Demers, Brian" opf:role="aut">Brian Demers</dc:creator>
  <dc:creator opf:file-as="Franusic, Joël" opf:role="aut">Joël Franusic</dc:creator>
  <dc:creator opf:file-as="Maddali, Sai" opf:role="aut">Sai Maddali</dc:creator>
  <dc:creator opf:file-as="Raible, Matt" opf:role="aut">Matt Raible</dc:creator>
  <dc:publisher>Okta</dc:publisher>
  <dc:subject>Reference</dc:subject>
  <dc:date opf:event="publication">2017-08-28</dc:date>
  <dc:description>Learn how Transport Layer Security protects data in transit, the different kinds of DOS attacks and strategies to mitigate them, and some of the common pitfalls when trying to sanitize data. Also, you’ll pick up best practices for managing API credentials, the core differences between authentication and authorization, and the best ways to handle each. And finally, you’ll explore the role of API gateways.</dc:description>
  <meta name="cover" content="Cover" />
  <? /* <meta property="dcterms:modified" content="<?= date('Y-m-d\TH:i:s\Z') ?>" /> */ ?>
</metadata>
<manifest>

  <item id="Cover" media-type="image/jpeg" href="images/cover-epub.jpg"></item>
  <item id="ncx" media-type="application/x-dtbncx+xml" href="toc.ncx"></item>

  <item id="epub-css" media-type="text/css" href="css/epub.css"></item>
  <item id="proxima-nova-regular" media-type="application/vnd.ms-opentype" href="fonts/Proxima-Nova-Regular.otf"></item>
  <item id="proxima-nova-bold" media-type="application/vnd.ms-opentype" href="fonts/Proxima-Nova-Bold.otf"></item>

  <item id="coverpage" media-type="application/xhtml+xml" href="content/00_start/cover.xhtml"></item>
  <item id="copyright" media-type="application/xhtml+xml" href="content/00_start/copyright.xhtml"></item>
  <item id="table-of-contents" media-type="application/xhtml+xml" href="toc.xhtml"></item>

  <? foreach($toc as $chapter): ?>
    <item id="<?= $chapter['id'] ?>" href="content/<?= $chapter['file'] ?>" media-type="application/xhtml+xml"></item>
<?
      $dir = dirname($chapter['src']);
      $chpimages = glob('epub/OEBPS/content/'.$dir.'/'.$dir.'/images/*');
      foreach($chpimages as $img) {
        $imgid = $chapter['id'].'_'.basename($img);
        if(!in_array($img, $images)) {
          echo '        <item id="'.$imgid.'" href="content/'.$dir.'/'.$dir.'/images/'.basename($img).'" media-type="image/'.str_replace('jpg','jpeg',pathinfo($img, PATHINFO_EXTENSION)).'"></item>'."\n";
          $images[] = $img;
        }
      }
    ?>
  <? endforeach ?>

</manifest>
<spine toc="ncx">

  <itemref idref="copyright"/>
  <itemref idref="table-of-contents"/>

  <? foreach($toc as $chapter): ?>
    <itemref idref="<?= $chapter['id'] ?>"/>
  <? endforeach ?>

</spine>
<guide>
  <reference type="cover" title="Cover" href="content/00_start/cover.xhtml"></reference>
  <reference type="toc" title="Table of Contents" href="toc.xhtml"></reference>
</guide>

</package>
