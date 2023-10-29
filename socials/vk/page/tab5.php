<?php

use Coderun\VkPoster\CorePlugin;
use Coderun\VkPoster\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
$optionsName         = CorePlugin::PREF_PLG;
$plugin              = CorePlugin::getInstance();
$vkposter_prooptions = $plugin->getOptions( 'vkposter_prooptions' );

$b = 1;
?>
<h2><?php _e( 'Расширенные настройки плагина  ' . $plugin::NAME_TITLE_PLUGIN_PAGE ) ?></h2>
<span class="description">Для сохранения легкости настройки и работы плагина придуман режим с PRO настройками — для тех кому базового функционала мало.</span>

<form method="post" action="options.php">
	<?php wp_nonce_field( 'update-options' ); ?>
	<?php settings_fields( sprintf( '%s_options', $plugin::PREF_PLG_PRO ) ); ?>
    <table class="form-table">
        <h3>PRO Настройки</h3>
        <tr valign="top">
            <th scope="row">ПРО?</th>
            <td>
                <input type="checkbox"
                       name="<?php echo $plugin::PREF_PLG_PRO; ?>[vkposter_prooptions][chekpro]" <?php checked( $vkposter_prooptions['chekpro'] ?? '', 'on', 1 ); ?>/>
                <span class="description">Включить опции ПРО. Если галка стоит тогда все настройки ниже будут иметь смысл.</span>
            </td>
        </tr>
    </table>
    <fieldset>
        <legend>Опции для Woocommerce</legend>
        <span><?php echo( ( Services::getInstance()->isWoo() ) ? '' : '<div class="notice notice-success"><p>У вас не установлен Woocommerce</p></div>' ); ?></span>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Итеграция с Woo</th>
                <td>
                    <input type="checkbox"
                           name="<?php echo $plugin::PREF_PLG_PRO; ?>[vkposter_prooptions][woo_chek]" <?php checked( $vkposter_prooptions['woo_chek'] ?? '', 'on', 1 ); ?>/>
                    <span class="description">Включить поддержку Woocommerce.Если галка стоит тогда все настройки ниже будут иметь смысл. Данные настройки всего лишь добавляют указанные опции в запись, которая отправляется на стену VK.COM.</span>
                </td>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Цена</th>
                <td>
                    <input type="checkbox"
                           name="<?php echo $plugin::PREF_PLG_PRO; ?>[vkposter_prooptions][woo_price]" <?php checked( $vkposter_prooptions['woo_price'] ?? '', 'on', 1 ); ?>/>
                    <span class="description">Отправлять на стену цену товара.</span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Метки</th>
                <td>
                    <input type="checkbox"
                           name="<?php echo $plugin::PREF_PLG_PRO; ?>[vkposter_prooptions][woo_met]" <?php checked( $vkposter_prooptions['woo_met'] ?? '', 'on', 1 ); ?>/>
                    <span class="description">Отправлять на стену метки товара в виде хэШтэга.</span>
                </td>
            </tr>

        </table>
    </fieldset>
    <fieldset>
        <legend>Опции для Записей WordPress</legend>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Метки</th>
                <td>
                    <input type="checkbox"
                           name="<?php echo $plugin::PREF_PLG_PRO; ?>[vkposter_prooptions][wp_met]" <?php checked( $vkposter_prooptions['wp_met'] ?? '', 'on', 1 ); ?>/>
                    <span class="description">Отправлять на стену метки записи в виде хэШтэга.</span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Дополнительный текст для отправки</th>
                <td>
					<?php
					wp_editor( $vkposter_prooptions['wp_insert_global_text'] ?? '', 'wp_insert_global_text', array(
						'wpautop'          => false,
						'media_buttons'    => 0,
						'textarea_name'    => $plugin::PREF_PLG_PRO . '[vkposter_prooptions][wp_insert_global_text]',
						//нужно указывать!
						'textarea_rows'    => 5,
						'tabindex'         => null,
						'editor_css'       => '',
						'editor_class'     => '',
						'teeny'            => 0,
						'dfw'              => 0,
						'tinymce'          => 0,
						'quicktags'        => 0,
						'drag_drop_upload' => false
					) );
					?>
                    <span class="description">Этот текст будет добавлен в конец каждого отправляемого сообщения.</span>
                </td>
            </tr>
        </table>
    </fieldset>
    <input type="hidden" name="action" value="update"/>
    <input type="hidden" name="page_options" value="<?php echo $plugin::PREF_PLG_PRO; ?>"/>
    <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>"/>
    </p>

</form>
