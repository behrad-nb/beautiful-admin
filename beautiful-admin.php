<?php
/**
 * Plugin Name: Beautiful Admin (پنل زیبا)
 * Description: Makes the WordPress admin beautiful and calm. 4 page styles, 6 colors, 4 languages (EN / FA / AR / RU). Never moves menus or buttons — only the look changes.
 * Version: 1.0.0
 * Author: Beautiful Admin
 * License: GPL-2.0-or-later
 * Text Domain: beautiful-admin
 */
if ( ! defined( 'ABSPATH' ) ) exit;

final class BA_Plugin {
	const OPT  = 'ba_settings';
	const SLUG = 'beautiful-admin';

	static $styles  = array( 'glass', 'solid', 'minimal', 'aurora' );
	static $colors  = array(
		'white' => array( '#ffffff', '#cfd6e6' ),
		'cream' => array( '#fff6e0', '#e6cf9f' ),
		'blue'  => array( '#4c8dff', '#0b2a6b' ),
		'red'   => array( '#ff5b6e', '#5a0f18' ),
		'green' => array( '#34d399', '#0b4a30' ),
		'black' => array( '#3a3a44', '#0b0b0e' ),
	);
	static $locales = array( 'en' => 'en_US', 'fa' => 'fa_IR', 'ar' => 'ar', 'ru' => 'ru_RU' );

	static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'redirect' ) );
		add_action( 'admin_post_ba_save', array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'locale', array( __CLASS__, 'locale' ) );
		register_activation_hook( __FILE__, function () { set_transient( 'ba_redirect', 1, 60 ); } );
	}

	static function defaults() {
		return array( 'lang' => 'fa', 'style' => 'glass', 'color' => 'black', 'wp_locale' => 0, 'done' => 0 );
	}
	static function get() {
		return wp_parse_args( get_option( self::OPT, array() ), self::defaults() );
	}

	static function locale( $l ) {
		if ( is_admin() ) {
			$o = self::get();
			if ( $o['wp_locale'] && isset( self::$locales[ $o['lang'] ] ) ) return self::$locales[ $o['lang'] ];
		}
		return $l;
	}

	static function body_class( $c ) {
		$o = self::get();
		return $c . ' ba-style-' . $o['style'] . ' ba-color-' . $o['color'];
	}

	static function assets( $hook ) {
		$u = plugin_dir_url( __FILE__ ) . 'assets/';
		wp_enqueue_style( 'ba-admin', $u . 'admin.css', array(), '1.0.0' );
		if ( 'settings_page_' . self::SLUG === $hook ) {
			wp_enqueue_script( 'ba-setup', $u . 'setup.js', array(), '1.0.0', true );
			wp_add_inline_script( 'ba-setup', 'window.BA=' . wp_json_encode( array( 'strings' => self::strings() ) ) . ';', 'before' );
		}
	}

	static function menu() {
		add_options_page( 'Beautiful Admin', 'Beautiful Admin', 'manage_options', self::SLUG, array( __CLASS__, 'page' ) );
	}

	static function redirect() {
		if ( get_transient( 'ba_redirect' ) && ! isset( $_GET['activate-multi'] ) ) {
			delete_transient( 'ba_redirect' );
			wp_safe_redirect( admin_url( 'options-general.php?page=' . self::SLUG ) );
			exit;
		}
	}

	static function save() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No access' );
		check_admin_referer( 'ba_save' );
		$o = array(
			'lang'      => isset( $_POST['lang'] ) && isset( self::$locales[ $_POST['lang'] ] ) ? $_POST['lang'] : 'fa',
			'style'     => isset( $_POST['style'] ) && in_array( $_POST['style'], self::$styles, true ) ? $_POST['style'] : 'glass',
			'color'     => isset( $_POST['color'] ) && isset( self::$colors[ $_POST['color'] ] ) ? $_POST['color'] : 'black',
			'wp_locale' => empty( $_POST['wp_locale'] ) ? 0 : 1,
			'done'      => 1,
		);
		if ( $o['wp_locale'] ) {
			$loc = self::$locales[ $o['lang'] ];
			if ( 'en_US' !== $loc && ! in_array( $loc, get_available_languages(), true ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/translation-install.php';
				wp_download_language_pack( $loc );
			}
		}
		update_option( self::OPT, $o );
		wp_safe_redirect( admin_url( 'options-general.php?page=' . self::SLUG . '&saved=1' ) );
		exit;
	}

	static function strings() {
		return array(
			'en' => array(
				'title' => 'Beautiful Admin', 'sub' => 'Make your WordPress dashboard beautiful and calm. Nothing is moved — only the look changes.',
				's1' => 'Language', 's2' => 'Page style', 's3' => 'Color', 's4' => 'Finish',
				'lang_hint' => 'Choose the language of this plugin.', 'style_hint' => 'Choose how admin pages look.', 'color_hint' => 'Pick your color. Changes apply instantly.',
				'glass' => 'Glass', 'solid' => 'Soft cards', 'minimal' => 'Minimal', 'aurora' => 'Aurora',
				'glass_d' => 'Blurred glass panels with glowing gradients', 'solid_d' => 'Clean cards with soft shadows', 'minimal_d' => 'Flat, thin lines, very calm', 'aurora_d' => 'Big soft glows and rounded shapes',
				'white' => 'White', 'cream' => 'Cream', 'blue' => 'Blue', 'red' => 'Red', 'green' => 'Green', 'black' => 'Black',
				'next' => 'Next', 'back' => 'Back', 'finish' => 'Finish', 'save' => 'Save changes',
				'wp_locale' => 'Also switch the WordPress admin language', 'wp_locale_d' => 'The language pack is downloaded automatically if needed.',
				'done_text' => 'All set! Press Finish to apply your look.', 'saved' => 'Settings saved.',
			),
			'fa' => array(
				'title' => 'پنل زیبا', 'sub' => 'پنل وردپرس را زیبا و آرام کنید. هیچ دکمه‌ای جابه‌جا نمی‌شود؛ فقط ظاهر عوض می‌شود.',
				's1' => 'زبان', 's2' => 'سبک صفحه', 's3' => 'رنگ', 's4' => 'پایان',
				'lang_hint' => 'زبان افزونه را انتخاب کنید.', 'style_hint' => 'ظاهر صفحه‌های مدیریت را انتخاب کنید.', 'color_hint' => 'رنگ دلخواه را انتخاب کنید. تغییر همان لحظه دیده می‌شود.',
				'glass' => 'شیشه‌ای', 'solid' => 'کارت نرم', 'minimal' => 'مینیمال', 'aurora' => 'شفق',
				'glass_d' => 'پنل‌های شیشه‌ای مات با گرادیان درخشان', 'solid_d' => 'کارت‌های تمیز با سایه ملایم', 'minimal_d' => 'تخت، خطوط نازک، بسیار آرام', 'aurora_d' => 'درخشش‌های بزرگ و شکل‌های گرد',
				'white' => 'سفید', 'cream' => 'کرمی', 'blue' => 'آبی', 'red' => 'قرمز', 'green' => 'سبز', 'black' => 'سیاه',
				'next' => 'بعدی', 'back' => 'قبلی', 'finish' => 'پایان', 'save' => 'ذخیره تغییرات',
				'wp_locale' => 'زبان خود وردپرس را هم تغییر بده', 'wp_locale_d' => 'در صورت نیاز بسته زبان خودکار دانلود می‌شود.',
				'done_text' => 'همه‌چیز آماده است! برای اعمال ظاهر، «پایان» را بزنید.', 'saved' => 'تنظیمات ذخیره شد.',
			),
			'ar' => array(
				'title' => 'اللوحة الجميلة', 'sub' => 'اجعل لوحة ووردبريس جميلة ومريحة. لا يتم نقل أي زر، يتغير المظهر فقط.',
				's1' => 'اللغة', 's2' => 'نمط الصفحة', 's3' => 'اللون', 's4' => 'إنهاء',
				'lang_hint' => 'اختر لغة الإضافة.', 'style_hint' => 'اختر شكل صفحات الإدارة.', 'color_hint' => 'اختر لونك. يظهر التغيير فورًا.',
				'glass' => 'زجاجي', 'solid' => 'بطاقات ناعمة', 'minimal' => 'بسيط', 'aurora' => 'الشفق',
				'glass_d' => 'لوحات زجاجية ضبابية مع تدرجات متوهجة', 'solid_d' => 'بطاقات نظيفة بظلال ناعمة', 'minimal_d' => 'مسطح بخطوط رفيعة وهادئ جدًا', 'aurora_d' => 'توهجات كبيرة وأشكال مستديرة',
				'white' => 'أبيض', 'cream' => 'كريمي', 'blue' => 'أزرق', 'red' => 'أحمر', 'green' => 'أخضر', 'black' => 'أسود',
				'next' => 'التالي', 'back' => 'السابق', 'finish' => 'إنهاء', 'save' => 'حفظ التغييرات',
				'wp_locale' => 'غيّر لغة ووردبريس أيضًا', 'wp_locale_d' => 'تُنزَّل حزمة اللغة تلقائيًا عند الحاجة.',
				'done_text' => 'كل شيء جاهز! اضغط «إنهاء» لتطبيق المظهر.', 'saved' => 'تم حفظ الإعدادات.',
			),
			'ru' => array(
				'title' => 'Красивая админка', 'sub' => 'Сделайте панель WordPress красивой и приятной. Ничего не перемещается — меняется только оформление.',
				's1' => 'Язык', 's2' => 'Стиль страниц', 's3' => 'Цвет', 's4' => 'Готово',
				'lang_hint' => 'Выберите язык плагина.', 'style_hint' => 'Выберите вид страниц админки.', 'color_hint' => 'Выберите цвет. Изменения видны сразу.',
				'glass' => 'Стекло', 'solid' => 'Мягкие карточки', 'minimal' => 'Минимализм', 'aurora' => 'Аврора',
				'glass_d' => 'Матовые стеклянные панели со свечением', 'solid_d' => 'Чистые карточки с мягкими тенями', 'minimal_d' => 'Плоский, тонкие линии, очень спокойно', 'aurora_d' => 'Большие мягкие свечения и скругления',
				'white' => 'Белый', 'cream' => 'Кремовый', 'blue' => 'Синий', 'red' => 'Красный', 'green' => 'Зелёный', 'black' => 'Чёрный',
				'next' => 'Далее', 'back' => 'Назад', 'finish' => 'Завершить', 'save' => 'Сохранить',
				'wp_locale' => 'Также сменить язык WordPress', 'wp_locale_d' => 'Языковой пакет скачается автоматически при необходимости.',
				'done_text' => 'Всё готово! Нажмите «Завершить», чтобы применить оформление.', 'saved' => 'Настройки сохранены.',
			),
		);
	}

	static function page() {
		$o = self::get();
		$S = self::strings();
		$t = $S[ $o['lang'] ];
		$wizard = ! $o['done'];
		$rtl = in_array( $o['lang'], array( 'fa', 'ar' ), true );
		$langs = array( 'en' => 'English', 'fa' => 'فارسی', 'ar' => 'العربية', 'ru' => 'Русский' );
		?>
		<div class="wrap">
		<div id="ba-app" class="ba-app" data-wizard="<?php echo $wizard ? '1' : '0'; ?>" dir="<?php echo $rtl ? 'rtl' : 'ltr'; ?>" lang="<?php echo esc_attr( $o['lang'] ); ?>">
			<?php if ( isset( $_GET['saved'] ) ) : ?><div class="ba-saved" data-i18n="saved"><?php echo esc_html( $t['saved'] ); ?></div><?php endif; ?>
			<header class="ba-head">
				<h1 data-i18n="title"><?php echo esc_html( $t['title'] ); ?></h1>
				<p data-i18n="sub"><?php echo esc_html( $t['sub'] ); ?></p>
			</header>
			<?php if ( $wizard ) : ?>
			<ol class="ba-dots">
				<?php foreach ( array( 's1', 's2', 's3', 's4' ) as $i => $k ) : ?>
					<li data-dot="<?php echo (int) $i; ?>"><span><?php echo (int) $i + 1; ?></span><em data-i18n="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $t[ $k ] ); ?></em></li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ba_save">
				<?php wp_nonce_field( 'ba_save' ); ?>

				<section class="ba-step">
					<h2 data-i18n="s1"><?php echo esc_html( $t['s1'] ); ?></h2>
					<p class="ba-hint" data-i18n="lang_hint"><?php echo esc_html( $t['lang_hint'] ); ?></p>
					<div class="ba-cards ba-cards-4">
						<?php foreach ( $langs as $k => $n ) : ?>
						<label class="ba-card"><input type="radio" name="lang" value="<?php echo esc_attr( $k ); ?>" <?php checked( $o['lang'], $k ); ?>>
							<span class="ba-card-in ba-center"><b class="ba-big"><?php echo esc_html( $n ); ?></b></span></label>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="ba-step">
					<h2 data-i18n="s2"><?php echo esc_html( $t['s2'] ); ?></h2>
					<p class="ba-hint" data-i18n="style_hint"><?php echo esc_html( $t['style_hint'] ); ?></p>
					<div class="ba-cards ba-cards-4">
						<?php foreach ( self::$styles as $k ) : ?>
						<label class="ba-card"><input type="radio" name="style" value="<?php echo esc_attr( $k ); ?>" <?php checked( $o['style'], $k ); ?>>
							<span class="ba-card-in"><span class="ba-mini ba-mini-<?php echo esc_attr( $k ); ?>"><i></i><i></i></span>
							<b data-i18n="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $t[ $k ] ); ?></b>
							<small data-i18n="<?php echo esc_attr( $k ); ?>_d"><?php echo esc_html( $t[ $k . '_d' ] ); ?></small></span></label>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="ba-step">
					<h2 data-i18n="s3"><?php echo esc_html( $t['s3'] ); ?></h2>
					<p class="ba-hint" data-i18n="color_hint"><?php echo esc_html( $t['color_hint'] ); ?></p>
					<div class="ba-cards ba-cards-6">
						<?php foreach ( self::$colors as $k => $g ) : ?>
						<label class="ba-card"><input type="radio" name="color" value="<?php echo esc_attr( $k ); ?>" <?php checked( $o['color'], $k ); ?>>
							<span class="ba-card-in ba-center"><span class="ba-sw" style="background:linear-gradient(135deg,<?php echo esc_attr( $g[0] ); ?>,<?php echo esc_attr( $g[1] ); ?>)"></span>
							<b data-i18n="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $t[ $k ] ); ?></b></span></label>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="ba-step">
					<h2 data-i18n="s4"><?php echo esc_html( $t['s4'] ); ?></h2>
					<p class="ba-hint" data-i18n="done_text"><?php echo esc_html( $t['done_text'] ); ?></p>
					<label class="ba-check"><input type="checkbox" name="wp_locale" value="1" <?php checked( $o['wp_locale'], 1 ); ?>>
						<span><b data-i18n="wp_locale"><?php echo esc_html( $t['wp_locale'] ); ?></b><br><small data-i18n="wp_locale_d"><?php echo esc_html( $t['wp_locale_d'] ); ?></small></span></label>
				</section>

				<div class="ba-nav">
					<button type="button" class="button ba-back" data-i18n="back"><?php echo esc_html( $t['back'] ); ?></button>
					<button type="button" class="button button-primary ba-next" data-i18n="next"><?php echo esc_html( $t['next'] ); ?></button>
					<button type="submit" class="button button-primary ba-finish" data-i18n="<?php echo $wizard ? 'finish' : 'save'; ?>"><?php echo esc_html( $wizard ? $t['finish'] : $t['save'] ); ?></button>
				</div>
			</form>
		</div>
		</div>
		<?php
	}
}
BA_Plugin::init();
