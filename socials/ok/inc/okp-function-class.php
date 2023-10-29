<?php

class OKPOSTERFUNCTION {

	const METHOD_URL_OK = 'https://api.ok.ru/fb.do?'; //URL отправки запросов на добавление записей на сайт ok.ru / URL of sending requests to add entries to ok.ru

	/**
	 * Условия для обновления версии плагина
	 */
	public function IfElseUpdate() {
		$okposter_posttype = get_option( 'okposter_posttype' ); //Типы постов
		if ( empty( $okposter_posttype ) ) { //установка опции по умолчанию
			update_option( 'okposter_posttype', [ 'post' => 'post' ] );
		}
	}

	/**
	 * Основная функция
	 * Создавалка записи на стене вконтакте
	 */
	public function setOkWall( $post_id ) {

		$ERRORS = [
			1    => "UNKNOWN Неизвестная ошибка",
			2    => "SERVICE Сервис временно недоступен",
			3    => "METHOD Метод не существует",
			4    => "REQUEST Не удалось обработать запрос, так как он неверный",
			7    => "ACTION_BLOCKED Запрошенное действие временно заблокировано для текущего пользователя",
			8    => "FLOOD_BLOCKED Выполнение метода заблокировано вследствие флуда",
			9    => "IP_BLOCKED Выполнение метода заблокировано по IP-адресу вследствие подозрительных действий текущего пользователя",
			10   => "PERMISSION_DENIED Отказ в разрешении. Возможная причина - пользователь не авторизовал приложение на выполнение операции",
			11   => "LIMIT_REACHED Достигнут предел вызовов метода",
			12   => "CANCELLED Операция прервана пользователем",
			22   => "NOT_ACTIVATED Пользователь должен активировать свой аккаунт",
			23   => "NOT_YET_INVOLVED Пользователь не вовлечён в приложение",
			24   => "NOT_OWNER Пользователь не является владельцем объекта",
			25   => "NOT_ACTIVE Ошибка рассылки нотификаций. Пользователь неактивен в приложении",
			26   => "TOTAL_LIMIT_REACHED Ошибка рассылки нотификаций. Достигнут лимит нотификаций для приложения",
			30   => "NETWORK Слишком большое тело запроса или проблема в обработке заголовков",
			31   => "NETWORK_TIMEOUT Клиент слишком долго передавал тело запроса",
			50   => "NOT_ADMIN У пользователя нет административных прав для выполнения данного метода",
			100  => "PARAM Отсутствующий или неверный параметр",
			101  => "PARAM_API_KEY Параметр application_key не указан или указан неверно",
			102  => "PARAM_SESSION_EXPIRED Истек срок действия ключа сессии",
			103  => "PARAM_SESSION_KEY Неверный ключ сессии",
			104  => "PARAM_SIGNATURE Неверная подпись",
			105  => "PARAM_RESIGNATURE Неверная повторная подпись",
			160  => "PARAM_GROUP_ID Неверный идентификатор группы",
			200  => "PARAM_PERMISSION Приложение не может выполнить операцию. В большинстве случаев причиной является попытка получения доступа к операции без авторизации от пользователя.",
			210  => "PARAM_APPLICATION_DISABLED Приложение отключено",
			211  => "PARAM_DECISION Неверный идентификатор выбора",
			300  => "NOT_FOUND Информация о запросе не найдена",
			451  => "NOT_SESSION_METHOD Указан ключ сессии, но метод должен быть вызван вне сессии",
			453  => "SESSION_REQUIRED Ключ сессии не указан для метода, требующего сессии",
			454  => "CENSOR_MATCH Текст отклонен цензором",
			456  => "GROUP_RESTRICTION Невозможно выполнить операцию, так как группа установила на нее ограничение",
			457  => "UNAUTHORIZED_RESTRICTION Неавторизованный доступ",
			458  => "PRIVACY_RESTRICTION То же, что и FRIEND_RESTRICTION",
			511  => "IDS_BLOCKED Ошибка проверки антиспама",
			514  => "IDS_SESSION_VERIFICATION_REQUIRED Пользователю необходимо пройти верификацию",
			600  => "MEDIA_TOPIC_BLOCK_LIMIT Слишком много параметров “медиа”",
			601  => "MEDIA_TOPIC_TEXT_LIMIT Достигнут лимит длины текста",
			607  => "MEDIA_TOPIC_LINK_BAD_FORMAT Неверный формат ссылки в медиатопике",
			704  => "TIMEOUT_EXCEEDED Время редактирования истекло",
			900  => "NO_SUCH_APP Возвращается при попытке получить открытую информацию для несуществующего приложения",
			5000 => "INVALID_RESPONSE Недопустимый ответ (например, указан несуществующий формат)",
			9999 => "SYSTEM Критическая системная ошибка. Оповестите об этом службу поддержки",
		]; //a description of possible errors for logging


		$postData = get_post( $post_id );

		$categories_post = wp_get_post_categories( $postData->ID );
		if ( in_array( 517, $categories_post ) ) {
			//return "Новости партнеров исключены";
		}

		$title     = $postData->post_title;
		$text      = $postData->post_content;
		$excerpt   = $postData->post_excerpt;
		$link_post = get_permalink( $post_id ); // ссылка на запись (теперь ЧПУ)

		$okposter_aid         = get_option( 'okposter_aid' ); //Токен приложения Application ID
		$okposter_gid         = get_option( 'okposter_gid' ); //От чьего имени публиковать
		$okposter_accesstoken = get_option( 'okposter_accesstoken' ); //Токен приложения access_token
		$okposter_seckey      = get_option( 'okposter_seckey' ); //Секретный ключ приложения
		$okposter_pubkey      = get_option( 'okposter_pubkey' ); //Публичный ключ приложения
		$okposter_text_link   = get_option( 'okposter_text_link' ); //разворачивать ссылку

		$okposter_signed    = get_option( 'okposter_signed' );
		$okposter_counttext = get_option( 'okposter_counttext' );
		$postType           = get_post_type( $post_id );

		preg_match( '/<p>(.*)<\/p>/iUs', $text, $matches );
		$matches[1] = str_replace( [ " \n ", " \n", "\n ", "\n" ], ' ', $matches[1] );
		$text       = str_replace( '<!--more-->', '', strip_tags( strip_shortcodes( $text ) ) ) . "\n\n"; //вырезаем шорткоды, теги, "далее"//
		$text       = str_replace( '&nbsp;', ' ', $text ); //---------------------------me edit

		$content = [ 'media' => [] ];
		$symbs   = [ ':' ];
		if ( $okposter_counttext == 0 ) { //пост без ограничений
			$paragraph = wp_kses( $excerpt, 'strip' );

			$words     = [];
			$sentence  = [];
			$sentences = [];
			$abbr      = [ "ул.", "д.", "г.", "ч.", "г.в.", "кв.", "м.", "мкр.", "корп.", "ст.", "Л." ];
			$last_symb = [ ".", ":" ];
			$words     = explode( ' ', $paragraph );

			foreach ( $words as $word ) {
				$word = trim( $word );
				if ( strlen( $word ) > 2 && $word[ strlen( $word ) - 1 ] == '.' && ! in_array( strtolower( $word ), $abbr ) ) {
					$sentence[]  = $word;
					$sentences[] = $sentence;
					$sentence    = [];
					break;
				} elseif ( ! empty( $word ) ) {
					$sentence[] = $word;
				}
			}

			if ( empty( $sentence ) ) {
				$paragraph = implode( ' ', $sentences[0] );
			} else {
				$paragraph = implode( ' ', $sentence );
			}

			if ( in_array( $paragraph[ strlen( $paragraph ) - 1 ], $symbs ) ) {
				$paragraph = substr( $paragraph, 0, strlen( $paragraph ) - 1 );
				$paragraph .= '...';
			}

			$short_text = wp_kses( $paragraph, 'strip' );

		} elseif ( $okposter_counttext > 0 ) { //Пост с обрезкой до кол-ва знаков указанных пользователем
			$short_text = wp_trim_words( wp_kses( $text, 'strip' ), $okposter_counttext, '...' );
		}

		$text_clear = wp_kses( $title, 'strip' )
		              . "\n\n"
		              . ( $short_text ? $short_text . "\n\n" : '' )
		              . "Подробнее: {$link_post}";

		$content['media'][] = [ 'type' => 'text', 'text' => $text_clear ];

		unset( $text );

		$content['caption'] = $title;

		$link = ( $okposter_text_link ) ? 'true' : 'false';

		//$content['media'][] = array('type' => 'link', 'url' => $link_post);


		$secKey = md5( $okposter_accesstoken . $okposter_seckey );

		$images_post = get_attached_file( get_post_thumbnail_id( $post_id ) );

		$arguments = [
			'application_key' => $okposter_pubkey,
			'format'          => 'json',
			'gid'             => $okposter_gid,
			'method'          => 'photosV2.getUploadUrl',
		];

		$signature_img = '';
		foreach ( $arguments as $key => $value ) {
			$signature_img .= $key . '=' . $value;
		}
		$sig_img                   = md5( $signature_img . $secKey );
		$arguments['sig']          = $sig_img;
		$arguments['access_token'] = $okposter_accesstoken;
		$curlinfo                  = wp_remote_post( self::METHOD_URL_OK, [ 'body' => $arguments, 'timeout' => 10 ] );

		if ( is_wp_error( $curlinfo ) ) {
			$errMessage = $curlinfo->get_error_message();

			return 'Ошибка отправки: ' . $errMessage;
		}

		$answer_img = json_decode( $curlinfo['body'] );
		$photo_id   = $answer_img->photo_ids[0];

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $answer_img->upload_url );

		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		//if (self::compareOldPHPVer('5.6.0', '<')) {
		//  curl_setopt($curl, CURLOPT_POSTFIELDS, array('file1' => '@' . $images_post));
		//} elseif (self::compareOldPHPVer('5.5.0', '>')) {
		curl_setopt( $curl, CURLOPT_POSTFIELDS, [ 'file1' => new CurlFile( $images_post ) ] );
		//}

		$curlinfo = curl_exec( $curl ); //Результат запроса
		$curlinfo = json_decode( $curlinfo, true );


		$content['media'][] = [
			'type' => 'photo',
			'list' => [ [ 'id' => $curlinfo['photos'][ $photo_id ]['token'] ] ],
		];


		$jsonContent = json_encode( $content );


		$signature = '';

		$parameters = [
			'application_key'   => $okposter_pubkey,
			'attachment'        => $jsonContent,
			'format'            => 'json',
			'gid'               => $okposter_gid,
			'method'            => 'mediatopic.post',
			'text_link_preview' => $link,
			'type'              => 'GROUP_THEME',
		];
		foreach ( $parameters as $key => $value ) {
			$signature .= $key . '=' . $value;
		}

		$sig                        = md5( $signature . $secKey );
		$parameters['sig']          = $sig;
		$parameters['access_token'] = $okposter_accesstoken;

		$curlinfo = wp_remote_post( self::METHOD_URL_OK, [ 'body' => $parameters ] ); // send link, text and caption to ok.ru

		if ( is_wp_error( $curlinfo ) ) {
			$errMessage = $curlinfo->get_error_message();

			return 'Ошибка отправки: ' . $errMessage;
		}

		$answer = json_decode( $curlinfo['body'], true );

		if ( ( is_array( $answer ) ) and ( isset( $answer['error_code'] ) ) ) {

			$error_code = (int) $answer['error_code'];
			$text       = ( isset( $ERRORS[ $error_code ] ) ) ? $ERRORS[ $error_code ] : 'UNKNOWN ERROR';
			$to_log     = "Error: " . $error_code . ' ' . $text;
			$to_log     .= $curlinfo['body'];

		} else {
			$to_log = "OK: " . (int) $answer;
		}

		return $to_log;
	}

	/**
	 * Функция логирования, для вкладки журнал
	 */
	public function logJornal( $idpost, $title, $status ) {

		$okposter_jornal_old = get_option( 'okposter_jornal' );
		if ( count( $okposter_jornal_old ) >= 50 ) {
			$okposter_jornal_old = array_slice( $okposter_jornal_old, - 40 );
		}
		$time                 = current_time( 'mysql' );
		$okposter_jornal_temp = [ 'time' => $time, 'idpost' => $idpost, 'title' => $title, 'status' => $status ];
		$okposter_jornal_new  = $okposter_jornal_old;
		array_push( $okposter_jornal_new, $okposter_jornal_temp );
		update_option( 'okposter_jornal', $okposter_jornal_new );
	}

	/**
	 * Сравнивает версии PHP
	 * Пример 5.3.0
	 * Возвращает true если версия PHP меньше или больше указанной, зависит от знака
	 *
	 * @param $zn < или >
	 * @param $php_v версия
	 *
	 * @return bool true если текущая PHP менье указаной
	 */
	static public function compareOldPHPVer( $php_v, $zn ) {
		//PHP<5.3
		if ( ! defined( 'PHP_VERSION_ID' ) ) {
			$version = explode( '.', PHP_VERSION );
			define( 'PHP_VERSION_ID', ( $version[0] * 10000 + $version[1] * 100 + $version[2] ) );
		}
		if ( version_compare( PHP_VERSION, $php_v, "{$zn}" ) ) {
			return true;
		} else {
			return false;
		}
	}

}