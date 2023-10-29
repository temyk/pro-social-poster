<?php
$tlg_on         = $tlg_on ?? false;
$bot_token      = $bot_token ?? '';
$channel_id     = $channel_id ?? '';
$categ_ids      = $categ_ids ?? '';
$tag_ids        = $tag_ids ?? '';
$note_categ_ids = $note_categ_ids ?? '';
?>

<style>
    label[for*="real_poster_telegram"] {
        min-width: 15em;
        vertical-align: baseline;
    }
</style>
<?php if ( current_user_can( 'manage_options' ) ): ?>
    <form method="post">
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><strong>Телеграм</strong></th>
                <td>
                    <fieldset>
                        <label for="real_poster_telegram_on"><i>Включить</i></label>
                        <input name="real_poster_telegram[on]" type="hidden" value="0">
                        <input name="real_poster_telegram[on]" type="checkbox" id="real_poster_telegram_on"
                               value="1" <?php echo( $tlg_on ? 'checked' : '' ); ?> >
                        <br>
                        <label for="real_poster_telegram_token"><i>Бот (токен)</i></label>
                        <input name="real_poster_telegram[bot_token]" type="text" id="real_poster_telegram_token"
                               value="<?php echo esc_html( $bot_token ); ?>" size="60">
                        <br>
                        <label for="real_poster_telegram_channel"><i>ID телеграм-канала</i></label>
                        <input name="real_poster_telegram[channel_id]" type="text" id="real_poster_telegram_channel"
                               value="<?php echo esc_html( $channel_id ); ?>">
                        <br>
                        <label for="real_poster_telegram_categ"><i>ID рубрик для публикации</i></label>
                        <input name="real_poster_telegram[categ_ids]" type="text" id="real_poster_telegram_categ"
                               value="<?php echo esc_html( $categ_ids ); ?>" size="30"><i> пример: 1, 2, 509</i>
                        <br>
                        <label for="real_poster_telegram_tag"><i>ID меток для публикации</i></label>
                        <input name="real_poster_telegram[tag_ids]" type="text" id="real_poster_telegram_tag"
                               value="<?php echo esc_html( $tag_ids ); ?>" size="30"><i> пример: 1, 2, 509</i>
                        <br>
                        <label for="real_poster_telegram_note_categ"><i>Отметить рубрики знаком (!)</i></label>
                        <input name="real_poster_telegram[note_categ_ids]" type="text"
                               id="real_poster_telegram_note_categ"
                               value="<?php echo esc_html( $note_categ_ids ); ?>" size="30">
                        <i> укажите ID рубрик. пример: 1, 2, 509</i>
                        <br>
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>
		<?php wp_nonce_field(); ?>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                 value="Сохранить изменения"></p>
    </form>
<?php endif; ?>

<?php
$plugins_url = admin_url() . 'options-general.php?page=poster-news-forms'; //URL страницы плагина
if ( isset( $_GET['clearjornal'] ) ) {
	update_option( 'log_poster_telegram', array() );
	?>
    <script type="text/javascript">
        document.location.href = "<?php echo $plugins_url; ?>";
    </script>
	<?php
}
?>
<h3>Журнал отправленных записей в Телеграм</h3>
<?php
if ( count( $tlg_logs ) ) { ?>
<a class="button" href="<?php echo $plugins_url . '&clearjornal'; ?>">Очистить журнал</a><br/><br/>

<table class="wp-list-table widefat fixed striped posts">
    <thead>
    <tr>
        <th>Дата и время добавления</th>
        <th>Номер записи (id поста В Wordpress)</th>
        <th>Заголовок записи</th>
        <th>Ответ сервера Telegram (статус)</th>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( array_reverse( $tlg_logs ) as $jornalprint ) { ?>
        <tr class="info">
            <th><?php echo $jornalprint['time']; ?></th>
            <th><?php echo $jornalprint['idpost']; ?></th>
            <th><?php echo $jornalprint['title']; ?></th>
            <th><?php echo $jornalprint['status']; ?></th>
        </tr>
	<?php } ?>
    </tbody>
	<?php } else { ?>
        <p>Нет записей</p>
	<?php } ?>

</table>