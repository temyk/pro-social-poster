<?php
/** @var array $args */
$note_sing  = $args['after'];
$link       = $args['link'];
$title      = $args['title'];
$text       = $args['text'];
$short_link = $args['short_link'];
?>
<?php echo $note_sing; ?>
    <strong><?php echo $title; ?></strong>

<?php echo $text; ?>


    Подробнее: <?php echo $short_link; ?>