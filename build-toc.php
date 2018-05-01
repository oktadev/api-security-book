<?php
use Michelf\MarkdownExtra;

require('vendor/autoload.php');

$MODE = 'epub';

$toc = [];

$chapters = array_merge(glob('*/index.php'), glob('*/index.md'));
foreach($chapters as $chapterfile) {
  $dir = dirname($chapterfile);

  preg_match('/(\d)_(\d+)/', $dir, $match);
  $partnum = $match[1];
  $chapternum = $match[2];

  if($partnum == 0)
    $class = 'frontmatter';
  elseif((int)$chapternum == 0)
    $class = 'part';
  else
    $class = 'chapter';

  $info = parse_markdown_chapter($chapterfile, $partnum, $chapternum);
  $html = $info['html'];

  $item = [
    'src' => $chapterfile,
    'file' => $dir.'/index.xhtml',
    'id' => $info['id'],
    'name' => $info['name'],
    'class' => $class,
    'part' => (int)$partnum,
    'chapter' => (int)$chapternum,
  ];

  // Find all elements with an ID and add them to the subsection list
  $doc = new DOMDocument();
  $doc->loadHTML($html);
  $xpath = new DOMXPath($doc);


  foreach($xpath->query('//h2[@id]') as $el) {
    if(!isset($item['children']))
      $item['children'] = [];
    $item['children'][] = [
      'name' => ''.$el->textContent,
      'id' => ''.$el->getAttribute('id')
    ];
  }

  $toc[] = $item;
}


echo json_encode($toc, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
echo "\n";
