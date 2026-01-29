<?php
/**
 * Plugin Name: BP Testimonials Slider
 * Description: Carrusel de testimonios mediante shortcode, con tipo de contenido "Testimonios" y opciones de visualización.
 * Version: 1.0.0
 * Author: Blogpocket
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bp-testimonials-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BP_Testimonials_Slider {

	const VERSION     = '1.0.0';
	const CPT         = 'bp_testimonial';
	const OPTION_KEY  = 'bp_tslider_options';
	const NONCE_FIELD = 'bp_tslider_nonce';

	/**
	 * Singleton.
	 *
	 * @var BP_Testimonials_Slider|null
	 */
	private static $instance = null;

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	private $plugin_url = '';

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	private $plugin_path = '';

	/**
	 * Get instance.
	 *
	 * @return BP_Testimonials_Slider
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->plugin_path = plugin_dir_path( __FILE__ );

		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_assets' ) );

		add_shortcode( 'testimonios_slider', array( $this, 'shortcode' ) );
		add_shortcode( 'bp_testimonials_slider', array( $this, 'shortcode' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Activation: set default options.
	 *
	 * @return void
	 */
	public function activate() {
		$defaults = array(
			'bg_image_id' => 0,
			'font_size'   => 18, // px.
			'autoplay_ms' => 5000,
		);

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		update_option( self::OPTION_KEY, wp_parse_args( $options, $defaults ) );
	}

	/**
	 * Register CPT.
	 *
	 * @return void
	 */
	public function register_cpt() {
		$labels = array(
			'name'               => __( 'Testimonios', 'bp-testimonials-slider' ),
			'singular_name'      => __( 'Testimonio', 'bp-testimonials-slider' ),
			'menu_name'          => __( 'Testimonios', 'bp-testimonials-slider' ),
			'add_new'            => __( 'Añadir nuevo', 'bp-testimonials-slider' ),
			'add_new_item'       => __( 'Añadir nuevo testimonio', 'bp-testimonials-slider' ),
			'edit_item'          => __( 'Editar testimonio', 'bp-testimonials-slider' ),
			'new_item'           => __( 'Nuevo testimonio', 'bp-testimonials-slider' ),
			'view_item'          => __( 'Ver testimonio', 'bp-testimonials-slider' ),
			'search_items'       => __( 'Buscar testimonios', 'bp-testimonials-slider' ),
			'not_found'          => __( 'No se encontraron testimonios.', 'bp-testimonials-slider' ),
			'not_found_in_trash' => __( 'No se encontraron testimonios en la papelera.', 'bp-testimonials-slider' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-format-quote',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'exclude_from_search'=> true,
			'publicly_queryable' => false,
			'has_archive'        => false,
			'rewrite'            => false,
		);

		register_post_type( self::CPT, $args );
	}

	/**
	 * Register settings page under Testimonios menu.
	 *
	 * @return void
	 */
	public function register_settings_page() {
		add_submenu_page(
			'edit.php?post_type=' . self::CPT,
			__( 'Ajustes del carrusel', 'bp-testimonials-slider' ),
			__( 'Ajustes', 'bp-testimonials-slider' ),
			'manage_options',
			'bp-testimonials-slider-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'bp_tslider_settings',
			self::OPTION_KEY,
			array( $this, 'sanitize_options' )
		);

		add_settings_section(
			'bp_tslider_section_main',
			__( 'Opciones de visualización', 'bp-testimonials-slider' ),
			function() {
				echo '<p>' . esc_html__( 'Configura el fondo y el tamaño de fuente para el carrusel de testimonios.', 'bp-testimonials-slider' ) . '</p>';
			},
			'bp-testimonials-slider-settings'
		);

		add_settings_field(
			'bp_tslider_bg_image',
			__( 'Imagen de fondo', 'bp-testimonials-slider' ),
			array( $this, 'field_bg_image' ),
			'bp-testimonials-slider-settings',
			'bp_tslider_section_main'
		);

		add_settings_field(
			'bp_tslider_font_size',
			__( 'Tamaño de fuente (px)', 'bp-testimonials-slider' ),
			array( $this, 'field_font_size' ),
			'bp-testimonials-slider-settings',
			'bp_tslider_section_main'
		);
	}

	/**
	 * Sanitize options.
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function sanitize_options( $input ) {
		$input = is_array( $input ) ? $input : array();

		$options = $this->get_options();

		$bg_image_id = isset( $input['bg_image_id'] ) ? absint( $input['bg_image_id'] ) : 0;

		$font_size = isset( $input['font_size'] ) ? absint( $input['font_size'] ) : 18;
		if ( $font_size < 10 ) {
			$font_size = 10;
		}
		if ( $font_size > 60 ) {
			$font_size = 60;
		}

		$options['bg_image_id'] = $bg_image_id;
		$options['font_size']   = $font_size;

		return $options;
	}

	/**
	 * Admin assets for media uploader.
	 *
	 * @param string $hook Hook suffix.
	 * @return void
	 */
	public function admin_enqueue( $hook ) {
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return;
		}

		$is_settings = ( 'bp_testimonial_page_bp-testimonials-slider-settings' === $screen->id );
		if ( ! $is_settings ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'bp-tslider-admin',
			$this->plugin_url . 'assets/admin.js',
			array( 'jquery' ),
			self::VERSION,
			true
		);

		wp_enqueue_style(
			'bp-tslider-admin',
			$this->plugin_url . 'assets/admin.css',
			array(),
			self::VERSION
		);

		$options = $this->get_options();
		wp_localize_script(
			'bp-tslider-admin',
			'BPTSliderAdmin',
			array(
				'bgImageId' => (int) $options['bg_image_id'],
				'strings'   => array(
					'choose' => __( 'Elegir imagen', 'bp-testimonials-slider' ),
					'use'    => __( 'Usar esta imagen', 'bp-testimonials-slider' ),
					'remove' => __( 'Quitar', 'bp-testimonials-slider' ),
				),
			)
		);
	}

	/**
	 * Register frontend assets (loaded on demand).
	 *
	 * @return void
	 */
	public function frontend_register_assets() {
		wp_register_style(
			'bp-tslider',
			$this->plugin_url . 'assets/slider.css',
			array(),
			self::VERSION
		);

		wp_register_script(
			'bp-tslider',
			$this->plugin_url . 'assets/slider.js',
			array(),
			self::VERSION,
			true
		);
	}

	/**
	 * Get options with defaults.
	 *
	 * @return array
	 */
	private function get_options() {
		$defaults = array(
			'bg_image_id' => 0,
			'font_size'   => 18,
			'autoplay_ms' => 5000,
		);

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return wp_parse_args( $options, $defaults );
	}

	/**
	 * Field: background image.
	 *
	 * @return void
	 */
	public function field_bg_image() {
		$options = $this->get_options();
		$image_id = absint( $options['bg_image_id'] );
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
		?>
		<div class="bp-tslider-field">
			<input type="hidden" id="bp_tslider_bg_image_id" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[bg_image_id]" value="<?php echo esc_attr( $image_id ); ?>" />
			<div class="bp-tslider-preview" <?php echo $image_url ? 'style="background-image:url(' . esc_url( $image_url ) . ');"' : ''; ?>></div>
			<div class="bp-tslider-buttons">
				<button type="button" class="button" id="bp_tslider_choose_image"><?php esc_html_e( 'Elegir imagen', 'bp-testimonials-slider' ); ?></button>
				<button type="button" class="button button-link-delete" id="bp_tslider_remove_image" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Quitar', 'bp-testimonials-slider' ); ?></button>
			</div>
			<p class="description"><?php esc_html_e( 'Se usará como fondo del carrusel (opcional).', 'bp-testimonials-slider' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Field: font size.
	 *
	 * @return void
	 */
	public function field_font_size() {
		$options = $this->get_options();
		$font_size = absint( $options['font_size'] );
		?>
		<input type="number" min="10" max="60" step="1" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[font_size]" value="<?php echo esc_attr( $font_size ); ?>" />
		<p class="description"><?php esc_html_e( 'Tamaño de fuente para el texto del testimonio.', 'bp-testimonials-slider' ); ?></p>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'BP Testimonials Slider - Ajustes', 'bp-testimonials-slider' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'bp_tslider_settings' );
				do_settings_sections( 'bp-testimonials-slider-settings' );
				submit_button();
				?>
			</form>
			<hr />
			<h2><?php esc_html_e( 'Shortcode', 'bp-testimonials-slider' ); ?></h2>
			<p><code>[testimonios_slider]</code> <?php esc_html_e( 'o', 'bp-testimonials-slider' ); ?> <code>[bp_testimonials_slider]</code></p>
			<p><?php esc_html_e( 'Opciones: posts_per_page (por defecto: todos), order (DESC/ASC).', 'bp-testimonials-slider' ); ?></p>
			<p><code>[testimonios_slider posts_per_page="10" order="DESC"]</code></p>
		</div>
		<?php
	}

	/**
	 * Shortcode output.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => -1,
				'order'          => 'DESC',
			),
			$atts,
			'testimonios_slider'
		);

		$ppp   = intval( $atts['posts_per_page'] );
		$order = strtoupper( sanitize_text_field( $atts['order'] ) );
		$order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

		$query = new WP_Query(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'publish',
				'posts_per_page' => $ppp,
				'orderby'        => 'date',
				'order'          => $order,
				'no_found_rows'  => true,
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		$options   = $this->get_options();
		$bg_id     = absint( $options['bg_image_id'] );
		$bg_url    = $bg_id ? wp_get_attachment_image_url( $bg_id, 'full' ) : '';
		$font_size = absint( $options['font_size'] );
		$autoplay  = absint( $options['autoplay_ms'] );

		// Enqueue assets only if shortcode renders.
		wp_enqueue_style( 'bp-tslider' );
		wp_enqueue_script( 'bp-tslider' );

		$uid = 'bp-tslider-' . wp_generate_uuid4();

		$wrapper_styles = array();
		if ( $bg_url ) {
			$wrapper_styles[] = 'background-image:url(' . esc_url( $bg_url ) . ')';
		}
		$wrapper_style_attr = $wrapper_styles ? ' style="' . esc_attr( implode( ';', $wrapper_styles ) ) . '"' : '';

		ob_start();
		?>
		<section class="bp-tslider" id="<?php echo esc_attr( $uid ); ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>" data-font-size="<?php echo esc_attr( $font_size ); ?>"<?php echo $wrapper_style_attr; ?>>
			<div class="bp-tslider__overlay" aria-hidden="true"></div>
			<div class="bp-tslider__viewport">
				<div class="bp-tslider__track">
					<?php
					$index = 0;
					while ( $query->have_posts() ) :
						$query->the_post();
						$title   = get_the_title();
						$content = get_the_content();
						$avatar  = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
						?>
						<article class="bp-tslider__slide" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( ( $index + 1 ) . ' / ' . $query->post_count ); ?>">
							<div class="bp-tslider__inner">
								<?php if ( $avatar ) : ?>
									<img class="bp-tslider__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $title ? $title : __( 'Avatar del testimonio', 'bp-testimonials-slider' ) ); ?>" loading="lazy" />
								<?php endif; ?>
								<div class="bp-tslider__text" style="<?php echo esc_attr( 'font-size:' . $font_size . 'px' ); ?>">
									<?php echo wp_kses_post( wpautop( $content ) ); ?>
								</div>
								<?php if ( $title ) : ?>
									<cite class="bp-tslider__cite"><?php echo esc_html( $title ); ?></cite>
								<?php endif; ?>
							</div>
						</article>
						<?php
						$index++;
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>

			<div class="bp-tslider__controls">
				<button type="button" class="bp-tslider__btn bp-tslider__btn--prev" aria-label="<?php echo esc_attr__( 'Anterior', 'bp-testimonials-slider' ); ?>">
					<span aria-hidden="true">‹</span>
				</button>

				<div class="bp-tslider__dots" aria-label="<?php echo esc_attr__( 'Navegación del carrusel', 'bp-testimonials-slider' ); ?>"></div>

				<button type="button" class="bp-tslider__btn bp-tslider__btn--next" aria-label="<?php echo esc_attr__( 'Siguiente', 'bp-testimonials-slider' ); ?>">
					<span aria-hidden="true">›</span>
				</button>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}
}

BP_Testimonials_Slider::instance();
