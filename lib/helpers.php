<?php
use Michelf\MarkdownExtra;

libxml_use_internal_errors(true);

function relative_dir($dir) {
  $base = str_replace('/lib', '', __DIR__);
  return str_replace($base.'/', '', $dir);
}

function get_name_id_from_html($html) {
  $doc = new DOMDocument();
  $doc->loadHTML($html);
  $xpath = new DOMXPath($doc);

  $name = '';
  $id = '';

  foreach($xpath->query('//h1') as $el) {
    $name = ''.$el->textContent;
    $id = ''.$el->getAttribute('id');
  }

  return [
    'name' => $name,
    'id' => $id,
  ];
}

function parse_markdown_chapter($part, $chapter, $markdown) {
  $html = MarkdownExtra::defaultTransform($markdown);
  $info = get_name_id_from_html($html);

  // Remove the h1 from the markdown source
  $markdown = preg_replace('/^# .+/', '', $markdown);
  $html = MarkdownExtra::defaultTransform($markdown);

  ob_start();
  ?>
    <section class="h-entry <?= $part == 0 ? 'frontmatter' : 'chapter' ?>" id="<?= $info['id'] ?>">
      <h1 class="p-name"><?= $info['name'] ?></h1>
      <data class="p-uid" value="<?= $info['id'] ?>"/>

      <div class="e-content">
        <?= $html ?>
      </div>
    </section>
  <?php
  $html = ob_get_clean();

  return [
    'id' => $info['id'],
    'name' => $info['name'],
    'html' => $html,
  ];
}
