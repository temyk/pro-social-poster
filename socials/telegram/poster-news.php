<?php
define( 'ASU_TLG_DIR', __DIR__ );
//define( 'ASU_TLG_VIEW', 'message/standard' );
define( 'ASU_TLG_VIEW', 'message/new2023' );

add_action( 'admin_init', 'real_init_poster_news' );
add_action( 'admin_menu', 'register_poster_news_submenu_page' );
$real_poster_telegram = get_option( 'real_poster_telegram' );
if ( isset( $real_poster_telegram['on'] ) && $real_poster_telegram['on'] ) {
	add_action( 'future_to_publish', 'futureSentTelegram', 9 );
	//add_action( 'transition_post_status', 'transitionSentTelegram', 10, 3 );
}

function real_init_poster_news() {
	add_action_poster_telegram();

	if ( isset( $_GET['page'] ) && $_GET['page'] === 'poster-news-forms' ) {

		if ( isset( $_POST['submit'] ) ) {
			if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
				print 'Извините, проверочные данные не соответствуют.';
				exit;
			}
			$options = [];

			if ( isset( $_POST['real_poster_telegram'] ) && is_array( $_POST['real_poster_telegram'] ) ) {
				foreach ( $_POST['real_poster_telegram'] as $key => $value ) {
					$options[ $key ] = $value;
				}
			}

			$updated = update_option( 'real_poster_telegram', $options );

			if ( $updated ) {
				add_action( 'all_admin_notices', function () {
					echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
				} );
			}

		}


	}
}

function register_poster_news_submenu_page() {
	add_submenu_page( 'options-general.php', 'Настройка постинга', 'Telegram Poster', 'edit_others_posts', 'poster-news-forms', 'real_poster_news_forms_settings' );
}

function real_poster_news_forms_settings() {

	echo '<div class="wrap">';
	echo '<h2>' . get_admin_page_title() . '</h2>';
	echo '</div><br/>';


	$tlg_on     = '';
	$bot_token  = '';
	$channel_id = '';
	$options = get_option( 'real_poster_telegram', [] );
	$tlg_logs = get_option( 'log_poster_telegram', [] );
	if ( $options ) {
		if ( isset( $options['on'] ) ) {
			$tlg_on = $options['on'];
		}
		if ( isset( $options['bot_token'] ) ) {
			$bot_token = $options['bot_token'];
		}
		if ( isset( $options['channel_id'] ) ) {
			$channel_id = $options['channel_id'];
		}
		if ( isset( $options['categ_ids'] ) ) {
			$categ_ids = $options['categ_ids'];
		}
		if ( isset( $options['tag_ids'] ) ) {
			$tag_ids = $options['tag_ids'];
		}
		if ( isset( $options['note_categ_ids'] ) ) {
			$note_categ_ids = $options['note_categ_ids'];
		}
	}

	include( 'view/poster-news-form.php' );

}

function add_action_poster_telegram() {
	if ( ! get_option( 'real_poster_telegram' ) ) {
		return;
	}
	if ( ! isset( get_option( 'real_poster_telegram' )['on'] ) ) {
		return;
	}
	if ( ! get_option( 'real_poster_telegram' )['on'] ) {
		return;
	}

	add_action( 'add_meta_boxes', 'setting_metabox_telegram' );
	add_action( 'save_post', 'tlg_metabox_save_post', 9 );
	//add_action( 'publish_post', 'tlg_metabox_save_post', 9 );
	add_filter( 'post_row_actions', 'telegram_action_row', 15, 2 );
	//add_action( 'publish_post', 'send_news_save_post', 11 );
	add_action( 'admin_head-edit.php', 'admin_head_post_listing_telegram' );
}

function send_news_save_post( $post_id ) {

	$postData       = get_post( $post_id );
	$title          = $postData->post_title;
	$status_post    = $postData->post_status;
	$date_create    = $postData->post_date_gmt;
	$date_modificed = $postData->post_modified_gmt;

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	if ( $status_post == 'draft' or $status_post == 'private' or $status_post == 'trash' ) {
		return $post_id;
	}

	$difference = strtotime( $date_modificed ) - strtotime( $date_create );
	if ( $difference < 5 ) {

	}

	return $post_id;
}

function transitionSentTelegram( $new_status, $old_status, \WP_Post $post ) {
	$postType = $post->post_type;
	if ( $postType !== 'post' ) {
		return null;
	}
	if ( $new_status !== 'publish' || $old_status == 'publish' ) {
		return null;
	}
	if ( $new_status === $old_status ) {
		return null;
	}

	send_news_to_telegram( $post->ID, '_future' );
}

function futureSentTelegram( $post ) {

	$postData    = get_post( $post );
	$title       = $postData->post_title;
	$status_post = $postData->post_status; //Статус поста
	if ( $status_post == 'draft' || $status_post == 'private' || $status_post == 'trash' ) {
		return $post;
	}
	send_news_to_telegram( $postData->ID, '_future' );

	return $post;
}

function admin_head_post_listing_telegram() {

	if ( ! isset( $_GET['tlg_repost'] ) ) {
		return;
	}

	$post_id = intval( $_GET['tlg_repost'] );
	$post    = get_post( $post_id );
	if ( in_array( $post->post_type, [ 'post' ] ) and isset( $_GET['tlg_repost'] ) ) {
		send_news_to_telegram( $post_id, '_link' );

		$parts       = parse_url( $_SERVER["REQUEST_URI"] );
		$queryParams = [];
		parse_str( $parts['query'], $queryParams );
		unset( $queryParams['tlg_repost'] );
		$queryString            = http_build_query( $queryParams );
		$_SERVER["REQUEST_URI"] = $parts['path'] . '?' . $queryString;

		add_action( 'all_admin_notices', function () {
			echo '<div class="notice notice-success"><p>Запись #' . $_GET['tlg_repost'] . ' отправлена в Telegram!</p></div>';
		} );
	}
}

function telegram_action_row( $actions, $post ) {

	if ( in_array( $post->post_type, [ 'post' ] ) ) {

		$parts       = parse_url( $_SERVER["REQUEST_URI"] );
		$queryParams = [];
		parse_str( $parts['query'] ?? '', $queryParams );
		$queryParams['tlg_repost'] = $post->ID;
		$queryString               = http_build_query( $queryParams );
		$url                       = $parts['path'] . '?' . $queryString;
		$img                       = '';
		if ( get_post_meta( $post->ID, '_tlg_poster_meta_value_key', true ) == 'on' ) {
			$img = '<span class="dashicons dashicons-format-image" style="font-size: 100%; padding-top: 4px;" title="В сообщение добавлена миниатюра"></span>';
		}
		$actions['tlg-post'] = '<a href="http://' . $_SERVER["HTTP_HOST"] . $url . '">Отправить в Telegram' . $img . '</a>';
	}

	return $actions;
}

function send_news_to_telegram( $post_id, $from = '' ) {
	$token          = get_option( 'real_poster_telegram' )['bot_token'];
	$channel_id     = get_option( 'real_poster_telegram' )['channel_id'];
	$categ_ids      = get_option( 'real_poster_telegram' )['categ_ids'];
	$tag_ids        = get_option( 'real_poster_telegram' )['tag_ids'];
	$note_categ_ids = get_option( 'real_poster_telegram' )['note_categ_ids'];
	$url            = "https://api.telegram.org/bot" . $token . "/sendMessage";

	$categ_ids = trim( $categ_ids );
	if ( ! $categ_ids || $categ_ids == 0 ) {
		$categ_ids = [];
	} else {
		$categ_ids = explode( ',', str_replace( ' ', '', $categ_ids ) );
	}

	$tag_ids = trim( $tag_ids );
	if ( ! $tag_ids || $tag_ids == 0 ) {
		$tag_ids = [];
	} else {
		$tag_ids = explode( ',', str_replace( ' ', '', $tag_ids ) );
	}

	$note_categ_ids = trim( $note_categ_ids );
	if ( ! $note_categ_ids || $note_categ_ids == 0 ) {
		$note_categ_ids = [];
	} else {
		$note_categ_ids = explode( ',', str_replace( ' ', '', $note_categ_ids ) );
	}

	$news_categs      = $categ_ids;//array(3, 8);
	$news_tags        = $tag_ids;//array(3955, 4864, 5129, 5132);
	$news_note_categs = $note_categ_ids;//array(3);

	$post        = get_post( $post_id );
	$title       = $post->post_title;
	$text        = $post->post_content;
	$link_post   = get_permalink( $post_id );
	$images_post = get_attached_file( get_post_thumbnail_id( $post_id ), true );

	//error_log( "Telegram log: '{$title}' sending" );

	$curr_categs = get_the_category( $post_id );
	$curr_tags   = get_the_tags( $post_id );

	$to_send    = false;
	$note_title = false;
	if ( ! empty( $news_categs ) ) {
		if ( $curr_categs ) {
			foreach ( $curr_categs as $category ) {
				if ( in_array( $category->term_id, $news_categs ) ) {
					$to_send = true;
				}
				if ( ! empty( $news_note_categs ) && in_array( $category->term_id, $news_note_categs ) ) {
					$note_title = true;
				}
			}
		}
	}

	if ( ! empty( $news_tags ) ) {
		if ( $curr_tags ) {
			foreach ( $curr_tags as $tag ) {
				if ( in_array( $tag->term_id, $news_tags ) ) {
					$to_send = true;
				}
			}
		}
	}

	if ( empty( $news_categs ) && empty( $news_tags ) ) {
		$to_send = true;
	}

	if ( $from === '_link' ) {
		$to_send = true;
	}

	if ( ! $to_send ) {
		log_send_to_telegram( $post_id . $from, $title, 'Категория/метка не подходит' );
		error_log( "Telegram log: '{$title}' sending: " . json_encode( $to_send ) );
		error_log( "Telegram log categs: " . json_encode( $curr_categs ) );
		error_log( "Telegram log tags: " . json_encode( $curr_tags ) );

		return;
	}

	$text = str_replace( '&nbsp;', ' ', $text );
	preg_match( '/<p>(.*)<\/p>/iUs', $text, $pgph_tmp );
	$pgph_tmp[1] = str_replace( [ " \n ", " \n", "\n ", "\n" ], ' ', $pgph_tmp[1] );
	$paragraph   = wp_kses( $pgph_tmp[1], 'strip' );

	$symbs = [ ':' ];

	if ( in_array( $paragraph[ strlen( $paragraph ) - 1 ], $symbs ) ) {
		$paragraph = substr( $paragraph, 0, strlen( $paragraph ) - 1 );
		$paragraph .= '...';
	}
	$note_sing = '';
	if ( $note_title ) {
		$note_sing = hex2bin( 'E29D97' ) . ' ';
	}

	$args       = [
		'after'      => $note_sing,
		'text'       => $paragraph,
		'link'       => add_query_arg( [ 'utm_source' => 'tg' ], $link_post ),
		'title'      => wp_kses( $title, 'strip' ),
		'short_link' => wp_get_shortlink( $post_id ),
	];
	$text_clear = tlg_render_template( ASU_TLG_VIEW, $args );

	$message = trim( $text_clear );

	$post_fields = [
		'chat_id'                  => $channel_id,
		'parse_mode'               => 'HTML',
		'disable_web_page_preview' => true,
	];

	$method = 'sendMessage';

	if ( get_post_meta( $post->ID, '_tlg_poster_meta_value_key', true ) == 'on' && $images_post ) {
		$post_fields['photo']   = new CURLFile( $images_post );
		$post_fields['caption'] = $message;
		$method                 = 'sendPhoto';
		$from                   .= '_pic';
	} else {
		$post_fields['text'] = $message;
	}

	$result = json_decode( curl_send_data( $token, $method, $post_fields ) );
	//error_log( "Telegram log: '{$title}' result sending: " . json_encode( $result ) );
	if ( isset( $result->ok ) ) {
		if ( $result->ok ) {
			$status = 'OK';
		} else {
			$status = $result->description ?? 'Неизвестная ошибка';
		}
	} else {
		$status = $result->description ?? 'Ошибка отправки';
	}
	if ( isset( $result->ok ) && ! $result->ok ) {
		//file_put_contents( 'TelegramNewsPostingErrorLog.txt', json_encode( $result ) );
	}
	$post_id = $post->ID ?? $post_id;
	log_send_to_telegram( $post_id . $from, $title, $status );
}

function curl_send_data( $token, $method, $post_fields ) {
	$ch       = curl_init();
	$optArray = [
		CURLOPT_POST           => true,
		CURLOPT_URL            => "https://api.telegram.org/bot" . $token . "/" . $method,
		CURLOPT_RETURNTRANSFER => true,
		//CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
		//CURLOPT_PROXY => 'tgp.real.su:444',
		//CURLOPT_PROXYUSERPWD => 'real:real5546',
		CURLOPT_POSTFIELDS     => $post_fields,
	];
	curl_setopt_array( $ch, $optArray );
	$result = curl_exec( $ch );
	curl_close( $ch );

	return $result;

}


function log_send_to_telegram( $idpost, $title = ' - ', $status = ' - ' ) {
	$tlg_log_old = ( get_option( 'log_poster_telegram' ) ? get_option( 'log_poster_telegram' ) : [] );

	if ( count( $tlg_log_old ) >= 50 ) {
		$tlg_log_old = array_slice( $tlg_log_old, - 40 );
	}
	$time         = current_time( 'mysql' );
	$tlg_log_temp = [ 'time' => $time, 'idpost' => $idpost, 'title' => $title, 'status' => $status ];
	$tlg_log_new  = $tlg_log_old;
	array_push( $tlg_log_new, $tlg_log_temp );
	update_option( 'log_poster_telegram', $tlg_log_new );

}

function setting_metabox_telegram() {

	$array_posts = [ 'post' ];
	foreach ( $array_posts as $k => $v ) {
		add_meta_box( 'tlg - poster - metabox', 'Telegram posting', 'tlg_metabox_html', "$v", 'side', 'high' );
	}

}

function tlg_metabox_html( $post ) {

	wp_nonce_field( __FILE__, 'tlg_poster_noncename' );

	if ( get_post_meta( $post->ID, '_tlg_poster_meta_value_key', true ) == 'on' ) {
		$cheked = 'checked';
	} else {
		$cheked = '';
	}

	echo "<input type='checkbox' name='telegram_image' {$cheked}/>";
	echo '<span class="description" > Добавить миниатюру в сообщение Telegram при публикации ?</span > ';

}

function tlg_metabox_save_post( $post_id ) {

	if ( ! wp_verify_nonce( $_POST['tlg_poster_noncename'] ?? '', __FILE__ ) ) {
		return $post_id;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	$data = ( isset( $_POST['telegram_image'] ) ) ? 'on' : '';
	update_post_meta( $post_id, '_tlg_poster_meta_value_key', $data );

}

function asu_sharing_image_poster( $post_id ) {
	return get_post_meta( $post_id, '_sharing_image', true );
}

/**
 * Method renders layout template
 *
 * @param string $template_name Template name without ".php"
 * @param array $args Template arguments
 *
 * @return string
 */
function tlg_render_template( $template_name, $args = [] ) {
	$path   = ASU_TLG_DIR . "/view/$template_name.php";
	$result = '';
	if ( file_exists( $path ) ) {
		ob_start();
		include $path;

		$result = ob_get_clean();
	}

	return $result;
}
