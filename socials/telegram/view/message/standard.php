<?php
/** @var array $args */
$note_sing = $args['after'];
$link      = $args['link'];
$title     = $args['title'];
$text      = $args['text'];
?>
<?php echo $note_sing; ?>
<a href="<?php echo $link; ?>"><?php echo $title; ?></a>

<?php echo $text; ?>
