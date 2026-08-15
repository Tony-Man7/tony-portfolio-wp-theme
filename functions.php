<?php
/**
 * Tony Dev Portfolio — theme functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'TDP_VERSION', '1.0.0' );

/**
 * Theme setup
 */
function tdp_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );

	// Custom editor color palette so Gutenberg blocks match the theme.
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => 'Ink',    'slug' => 'tdp-ink',    'color' => '#10121a' ),
		array( 'name' => 'Surface','slug' => 'tdp-surface','color' => '#181b27' ),
		array( 'name' => 'Fog',    'slug' => 'tdp-fog',    'color' => '#e9e7e2' ),
		array( 'name' => 'Amber',  'slug' => 'tdp-amber',  'color' => '#f5b942' ),
		array( 'name' => 'Slate',  'slug' => 'tdp-slate',  'color' => '#7b8299' ),
	) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'tony-portfolio' ),
	) );
}
add_action( 'after_setup_theme', 'tdp_theme_setup' );

/**
 * Enqueue styles & scripts
 */
function tdp_enqueue_assets() {
	wp_enqueue_style( 'tdp-google-fonts', 'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap', array(), null );
	wp_enqueue_style( 'tdp-style', get_stylesheet_uri(), array(), TDP_VERSION );
	wp_enqueue_style( 'tdp-main', get_template_directory_uri() . '/assets/css/main.css', array( 'tdp-style' ), filemtime( get_template_directory() . '/assets/css/main.css' ) );
	wp_enqueue_script( 'tdp-main', get_template_directory_uri() . '/assets/js/main.js', array(), filemtime( get_template_directory() . '/assets/js/main.js' ), true );

	wp_localize_script( 'tdp-main', 'tdpAjax', array(
		'url'   => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'tdp_load_more_nonce' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'tdp_enqueue_assets' );

/**
 * AJAX: load more projects for the "See more" button on the homepage.
 * Returns rendered card HTML (reuses template-parts/project-card.php)
 * plus whether there are more pages left.
 */
function tdp_load_more_projects() {
	check_ajax_referer( 'tdp_load_more_nonce', 'nonce' );

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 2;
	$per_page = 6;

	$query = new WP_Query( array(
		'post_type'      => 'tdp_project',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'post_status'    => 'publish',
	) );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/project-card' );
		}
	}
	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success( array(
		'html'     => $html,
		'has_more' => $page < $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_tdp_load_more_projects', 'tdp_load_more_projects' );
add_action( 'wp_ajax_nopriv_tdp_load_more_projects', 'tdp_load_more_projects' );

/**
 * Custom Post Type: Projects
 * This is the "dynamic" half of the theme
 * normal WP admin + block editor, no code required to add new work.
 */
function tdp_register_project_cpt() {
	$labels = array(
		'name'          => 'Projects',
		'singular_name' => 'Project',
		'add_new_item'  => 'Add New Project',
		'edit_item'     => 'Edit Project',
		'all_items'     => 'All Projects',
	);

	register_post_type( 'tdp_project', array(
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-portfolio',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		'show_in_rest'  => true, // enables Gutenberg block editor for this CPT
		'rewrite'       => array( 'slug' => 'project' ),
	) );

	register_taxonomy( 'tdp_project_category', 'tdp_project', array(
		'labels'       => array( 'name' => 'Project Categories', 'singular_name' => 'Project Category' ),
		'public'       => true,
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'project-category' ),
	) );
}
add_action( 'init', 'tdp_register_project_cpt' );

/**
 * A couple of custom meta fields for the project cards (client, role, year, link).
 * Shown in the block editor sidebar via a simple meta box
 * per project the same way fill in any WP custom field.
 */
function tdp_register_project_meta() {
	$fields = array( 'tdp_client', 'tdp_role', 'tdp_year', 'tdp_project_url', 'tdp_tech_stack' );
	foreach ( $fields as $field ) {
		register_post_meta( 'tdp_project', $field, array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
			'auth_callback'=> function() { return current_user_can( 'edit_posts' ); },
		) );
	}
}
add_action( 'init', 'tdp_register_project_meta' );

function tdp_project_meta_box() {
	add_meta_box( 'tdp_project_details', 'Project Details', 'tdp_project_meta_box_html', 'tdp_project', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'tdp_project_meta_box' );

function tdp_project_meta_box_html( $post ) {
	$client = get_post_meta( $post->ID, 'tdp_client', true );
	$role   = get_post_meta( $post->ID, 'tdp_role', true );
	$year   = get_post_meta( $post->ID, 'tdp_year', true );
	$tech_stack = get_post_meta( $post->ID, 'tdp_tech_stack', true );
	$url    = get_post_meta( $post->ID, 'tdp_project_url', true );
	wp_nonce_field( 'tdp_save_project_meta', 'tdp_project_meta_nonce' );
	?>
	<p><label>Client<br><input type="text" style="width:100%" name="tdp_client" value="<?php echo esc_attr( $client ); ?>"></label></p>
	<p><label>Role<br><input type="text" style="width:100%" name="tdp_role" value="<?php echo esc_attr( $role ); ?>"></label></p>
	<p><label>Year<br><input type="text" style="width:100%" name="tdp_year" value="<?php echo esc_attr( $year ); ?>"></label></p>
	<p><label>Tech Stack<br><input type="text" style="width:100%" name="tdp_tech_stack" value="<?php echo esc_attr( $tech_stack ); ?>"></label></p>
	<p><label>Live URL<br><input type="url" style="width:100%" name="tdp_project_url" value="<?php echo esc_attr( $url ); ?>"></label></p>
	<?php
}

function tdp_save_project_meta( $post_id ) {
	if ( ! isset( $_POST['tdp_project_meta_nonce'] ) || ! wp_verify_nonce( $_POST['tdp_project_meta_nonce'], 'tdp_save_project_meta' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	foreach ( array( 'tdp_client', 'tdp_role', 'tdp_year', 'tdp_project_url', 'tdp_tech_stack' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
		}
	}
}
add_action( 'save_post_tdp_project', 'tdp_save_project_meta' );

/**
 * Register a block pattern for a single project card, so if ever
 * wants to hand-place a project inside a Gutenberg page (instead of the
 * automatic archive loop), can insert it from the pattern library.
 */
function tdp_register_block_patterns() {
	register_block_pattern_category( 'tony-portfolio', array( 'label' => 'Tony Portfolio' ) );

	register_block_pattern( 'tony-portfolio/project-card', array(
		'title'      => 'Project Card',
		'categories' => array( 'tony-portfolio' ),
		'content'    => '<!-- wp:group {"className":"tdp-card"} -->
<div class="wp-block-group tdp-card">
<!-- wp:heading {"level":3} --><h3>Project Title</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Short one-line description of the project and stack used.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->',
	) );
}
add_action( 'init', 'tdp_register_block_patterns' );

/**
 * Fallback menu if no menu is assigned in Appearance > Menus.
 */
function tdp_fallback_menu() {
	echo '<ul class="tdp-nav-links">
		<li><a href="#hero">// home</a></li>
		<li><a href="#expertise">// expertise</a></li>
		<li><a href="#work">// work</a></li>
		<li><a href="#experience">// experience</a></li>
		<li><a href="#contact">// contact</a></li>
	</ul>';
}

/**
 * Registers all editable site content (Hero, Expertise, Experience,
 * Contact) in the WordPress Customizer. Single source of truth —
 * both front-page.php AND tdp_get_site_context_for_ai() read from
 * these same theme_mods, so editing in Appearance → Customize
 * updates the site and the AI's knowledge at the same time.
 */
function tdp_register_customizer_content( $wp_customize ) {

	$wp_customize->add_panel( 'tdp_content_panel', array(
		'title'    => 'Portfolio Content',
		'priority' => 30,
	) );

	/* ---------------- Hero ---------------- */
	$wp_customize->add_section( 'tdp_hero_section', array(
		'title' => 'Hero',
		'panel' => 'tdp_content_panel',
	) );

	$wp_customize->add_setting( 'tdp_hero_headline', array( 'default' => "Hi I'm Tony", 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_headline', array( 'label' => 'Headline (H1)', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_hero_role', array(
		'default'           => "Building sites, CRMs, and SEO-ready front ends for teams and direct clients. Available for freelance projects — WordPress builds, Elementor sites, and front-end fixes.",
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'tdp_hero_role', array( 'label' => 'Role / tagline', 'section' => 'tdp_hero_section', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'tdp_hero_years_number', array( 'default' => '3', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_years_number', array( 'label' => 'Years of experience (number)', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_hero_years_suffix', array( 'default' => '+ yrs', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_years_suffix', array( 'label' => 'Years suffix', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_hero_years_detail', array( 'default' => 'professional dev experience', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_years_detail', array( 'label' => 'Years detail text', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_hero_meta2_strong', array( 'default' => 'WordPress', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_meta2_strong', array( 'label' => 'Stat 2: Bold label', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_hero_meta2_detail', array( 'default' => 'Elementor · Gutenberg · WooCommerce', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_meta2_detail', array( 'label' => 'Stat 2: Detail text', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_hero_meta3_strong', array( 'default' => 'Full-stack', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_meta3_strong', array( 'label' => 'Stat 3: Bold label', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_hero_meta3_detail', array( 'default' => 'Laravel · Vue.js · PHP · JS', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'tdp_hero_meta3_detail', array( 'label' => 'Stat 3: Detail text', 'section' => 'tdp_hero_section', 'type' => 'text' ) );

	/* ---------------- Expertise (3 cards) ---------------- */
	$wp_customize->add_section( 'tdp_expertise_section', array(
		'title' => 'Expertise',
		'panel' => 'tdp_content_panel',
	) );

	$expertise_defaults = array(
		1 => array( 'tag' => '01 · WordPress', 'title' => 'WordPress Development', 'detail' => 'Custom themes, Elementor and Gutenberg builds, WooCommerce stores, plugin troubleshooting, and technical SEO with Yoast.' ),
		2 => array( 'tag' => '02 · Frontend', 'title' => 'Frontend Engineering', 'detail' => 'HTML, CSS, JavaScript, and Vue.js interfaces built for speed and clean UX, wired up to Google Tag Manager and Analytics.' ),
		3 => array( 'tag' => '03 · Backend', 'title' => 'Backend & CRM', 'detail' => 'PHP and Laravel applications, CRM integrations, server troubleshooting, cPanel management, and Git-based workflows.' ),
	);

	foreach ( $expertise_defaults as $i => $card ) {
		$wp_customize->add_setting( "tdp_expertise_{$i}_tag", array( 'default' => $card['tag'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( "tdp_expertise_{$i}_tag", array( 'label' => "Card {$i}: Tag label", 'section' => 'tdp_expertise_section', 'type' => 'text' ) );

		$wp_customize->add_setting( "tdp_expertise_{$i}_title", array( 'default' => $card['title'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( "tdp_expertise_{$i}_title", array( 'label' => "Card {$i}: Title", 'section' => 'tdp_expertise_section', 'type' => 'text' ) );

		$wp_customize->add_setting( "tdp_expertise_{$i}_detail", array( 'default' => $card['detail'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp_customize->add_control( "tdp_expertise_{$i}_detail", array( 'label' => "Card {$i}: Detail", 'section' => 'tdp_expertise_section', 'type' => 'textarea' ) );
	}

	/* ---------------- Experience (3 entries) ---------------- */
	$wp_customize->add_section( 'tdp_experience_section', array(
		'title' => 'Experience',
		'panel' => 'tdp_content_panel',
	) );

	$experience_defaults = array(
		1 => array( 'period' => 'Present', 'title' => 'Web Developer', 'company' => 'Coliseum Global Sports Venue Alliance — Dubai Media City', 'detail' => 'Handling WordPress development, technical SEO, CRM workflows, and day-to-day technical troubleshooting for a global sports venue platform.' ),
		2 => array( 'period' => 'Ongoing', 'title' => 'Freelance Developer', 'company' => 'Independent — remote, part-time', 'detail' => 'Taking on WordPress builds, Elementor and Gutenberg sites, and front-end fixes for direct clients alongside full-time work.' ),
		3 => array( 'period' => '2023 – 2024', 'title' => 'Web Developer', 'company' => 'Fbanta Corp. — Philippines', 'detail' => 'Built and maintained e-commerce and marketing sites with HTML, CSS, JavaScript, and PHP. Customized WordPress themes using Elementor, integrated plugins, and designed high-converting landing pages while improving site speed, mobile responsiveness, and usability.' ),
	);

	foreach ( $experience_defaults as $i => $entry ) {
		$wp_customize->add_setting( "tdp_exp_{$i}_period", array( 'default' => $entry['period'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( "tdp_exp_{$i}_period", array( 'label' => "Entry {$i}: Period", 'section' => 'tdp_experience_section', 'type' => 'text' ) );

		$wp_customize->add_setting( "tdp_exp_{$i}_title", array( 'default' => $entry['title'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( "tdp_exp_{$i}_title", array( 'label' => "Entry {$i}: Job title", 'section' => 'tdp_experience_section', 'type' => 'text' ) );

		$wp_customize->add_setting( "tdp_exp_{$i}_company", array( 'default' => $entry['company'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( "tdp_exp_{$i}_company", array( 'label' => "Entry {$i}: Company", 'section' => 'tdp_experience_section', 'type' => 'text' ) );

		$wp_customize->add_setting( "tdp_exp_{$i}_detail", array( 'default' => $entry['detail'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp_customize->add_control( "tdp_exp_{$i}_detail", array( 'label' => "Entry {$i}: Detail", 'section' => 'tdp_experience_section', 'type' => 'textarea' ) );
	}

	/* ---------------- Contact ---------------- */
	$wp_customize->add_section( 'tdp_contact_section', array(
		'title' => 'Contact',
		'panel' => 'tdp_content_panel',
	) );

	$wp_customize->add_setting( 'tdp_contact_availability', array( 'default' => "Have a WordPress project, a bug that needs fixing, or a site that needs rebuilding? Send a message and I'll get back to you.", 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'tdp_contact_availability', array( 'label' => 'Availability text', 'section' => 'tdp_contact_section', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'tdp_contact_email', array( 'default' => 'antman.dev.7@gmail.com', 'sanitize_callback' => 'sanitize_email' ) );
	$wp_customize->add_control( 'tdp_contact_email', array( 'label' => 'Email', 'section' => 'tdp_contact_section', 'type' => 'text' ) );

	$wp_customize->add_setting( 'tdp_contact_github', array( 'default' => 'https://github.com/Tony-Man7?tab=repositories', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'tdp_contact_github', array( 'label' => 'GitHub URL', 'section' => 'tdp_contact_section', 'type' => 'url' ) );

	$wp_customize->add_setting( 'tdp_contact_linkedin', array( 'default' => 'https://www.linkedin.com/in/anthony-manansala-027691276/', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'tdp_contact_linkedin', array( 'label' => 'LinkedIn URL', 'section' => 'tdp_contact_section', 'type' => 'url' ) );

	$wp_customize->add_setting( 'tdp_contact_main_portfolio', array( 'default' => 'https://portfolio-newest.vercel.app/', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'tdp_contact_main_portfolio', array( 'label' => 'Main portfolio URL', 'section' => 'tdp_contact_section', 'type' => 'url' ) );
}
add_action( 'customize_register', 'tdp_register_customizer_content' );

/**
 * Returns bio, expertise, experience, and contact info as structured
 * data for the AI — reads from the SAME theme_mods as the Customizer
 * above, so it always mirrors what's live on the site.
 */
function tdp_get_site_context_for_ai() {
	$expertise = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$expertise[] = array(
			'area'   => get_theme_mod( "tdp_expertise_{$i}_title", '' ),
			'detail' => get_theme_mod( "tdp_expertise_{$i}_detail", '' ),
		);
	}

	$experience = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$experience[] = array(
			'period'  => get_theme_mod( "tdp_exp_{$i}_period", '' ),
			'title'   => get_theme_mod( "tdp_exp_{$i}_title", '' ),
			'company' => get_theme_mod( "tdp_exp_{$i}_company", '' ),
			'detail'  => get_theme_mod( "tdp_exp_{$i}_detail", '' ),
		);
	}

	return array(
		'bio' => sprintf(
			'%s. %s %s years of professional dev experience. Also skilled in: %s (%s), and %s (%s).',
			get_theme_mod( 'tdp_hero_headline', "Hi I'm Tony" ),
			get_theme_mod( 'tdp_hero_role', '' ),
			get_theme_mod( 'tdp_hero_years_number', '3' ),
			get_theme_mod( 'tdp_hero_meta2_strong', '' ),
			get_theme_mod( 'tdp_hero_meta2_detail', '' ),
			get_theme_mod( 'tdp_hero_meta3_strong', '' ),
			get_theme_mod( 'tdp_hero_meta3_detail', '' )
		),
		'expertise'  => $expertise,
		'experience' => $experience,
		'contact'    => array(
			'availability'   => get_theme_mod( 'tdp_contact_availability', '' ),
			'email'          => get_theme_mod( 'tdp_contact_email', '' ),
			'github'         => get_theme_mod( 'tdp_contact_github', '' ),
			'linkedin'       => get_theme_mod( 'tdp_contact_linkedin', '' ),
			'main_portfolio' => get_theme_mod( 'tdp_contact_main_portfolio', '' ),
		),
	);
}

/**
 * Custom REST endpoint that returns all published projects as JSON.
 * The AI chat widget will call this to get real project data as context —
 * no need to hardcode anything, it always reflects what's actually published.
 */
function tdp_register_projects_api() {
	register_rest_route( 'tdp/v1', '/projects', array(
		'methods'             => 'GET',
		'callback'            => 'tdp_get_projects_for_ai',
		'permission_callback' => '__return_true', // public endpoint, read-only
	) );
}
add_action( 'rest_api_init', 'tdp_register_projects_api' );

function tdp_get_projects_for_ai() {
	$query = new WP_Query( array(
		'post_type'      => 'tdp_project',
		'posts_per_page' => -1, // all of them
		'post_status'    => 'publish',
	) );

	$projects = array();

	while ( $query->have_posts() ) {
		$query->the_post();
		$cats = get_the_terms( get_the_ID(), 'tdp_project_category' );

		$projects[] = array(
			'title'    => get_the_title(),
			'excerpt'  => wp_strip_all_tags( get_the_excerpt() ),
			'category' => ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '',
			'client'   => get_post_meta( get_the_ID(), 'tdp_client', true ),
			'role'     => get_post_meta( get_the_ID(), 'tdp_role', true ),
			'year'     => get_post_meta( get_the_ID(), 'tdp_year', true ),
			'tech_stack' => get_post_meta( get_the_ID(), 'tdp_tech_stack', true ),
		);
	}

	wp_reset_postdata();

	return $projects;
}

/**
 * AI chat endpoint — takes a visitor's question, feeds it real project
 * data as context, asks Gemini, and returns just the answer text.
 */
function tdp_register_ai_ask_api() {
	register_rest_route( 'tdp/v1', '/ask', array(
		'methods'             => 'POST',
		'callback'            => 'tdp_handle_ai_ask',
		'permission_callback' => '__return_true',
	) );
}
add_action( 'rest_api_init', 'tdp_register_ai_ask_api' );

/**
 * Rate limiting: checks both a per-visitor limit (by IP) and a global
 * limit (across all visitors) before we even call Gemini. This saves
 * quota by failing fast instead of burning a request that would just
 * get rejected by Google anyway.
 */
function tdp_check_rate_limit() {
	// Global limit: max 12 requests per rolling minute across ALL visitors.
	// Kept under Gemini's 15 RPM ceiling to leave headroom.
	$global_key   = 'tdp_ai_global_count';
	$global_count = (int) get_transient( $global_key );

	if ( $global_count >= 12 ) {
		return new WP_Error( 'rate_limited', 'The AI assistant is busy right now — please try again in a minute.', array( 'status' => 429 ) );
	}

	set_transient( $global_key, $global_count + 1, MINUTE_IN_SECONDS );

	// Per-visitor limit: max 8 requests per 10 minutes, keyed by IP.
	$ip           = tdp_get_visitor_ip();
	$visitor_key  = 'tdp_ai_visitor_' . md5( $ip );
	$visitor_count = (int) get_transient( $visitor_key );

	if ( $visitor_count >= 5 ) {
		return new WP_Error( 'rate_limited', 'You\'ve reached the question limit for now — please try again in a few minutes.', array( 'status' => 429 ) );
	}

	set_transient( $visitor_key, $visitor_count + 1, 10 * MINUTE_IN_SECONDS );

	return true;
}

function tdp_get_visitor_ip() {
	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
	}
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$forwarded = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		return trim( $forwarded[0] );
	}
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
}

/**
 * Shared helper: sends a prompt to Gemini with retry/error handling.
 * Returns array( 'success' => true, 'text' => '...' ) on success,
 * or array( 'success' => false, 'wp_error' => WP_Error ) on failure.
 */
function tdp_call_gemini( $prompt_text ) {
	if ( ! defined( 'TDP_GEMINI_API_KEY' ) || empty( TDP_GEMINI_API_KEY ) ) {
		return array( 'success' => false, 'wp_error' => new WP_Error( 'no_api_key', 'AI is not configured yet.', array( 'status' => 500 ) ) );
	}

	$body = array(
		'contents' => array(
			array( 'parts' => array( array( 'text' => $prompt_text ) ) ),
		),
		'generationConfig' => array(
			'maxOutputTokens' => 1024,
		),
	);

	$max_retries = 3;
	$data = null;

	for ( $attempt = 0; $attempt < $max_retries; $attempt++ ) {
		$response = wp_remote_post(
			'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent',
			array(
				'headers' => array(
					'Content-Type'   => 'application/json',
					'X-goog-api-key' => TDP_GEMINI_API_KEY,
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'success' => false, 'wp_error' => new WP_Error( 'api_failed', 'Could not reach the AI service: ' . $response->get_error_message(), array( 'status' => 500 ) ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code === 200 ) {
			break;
		}

		if ( $code === 503 && $attempt < $max_retries - 1 ) {
			sleep( pow( 2, $attempt ) );
			continue;
		}

		if ( $code === 429 ) {
			$retry_after = null;
			if ( ! empty( $data['error']['details'] ) ) {
				foreach ( $data['error']['details'] as $detail ) {
					if ( isset( $detail['retryDelay'] ) ) {
						$retry_after = $detail['retryDelay'];
					}
				}
			}
			return array( 'success' => false, 'wp_error' => new WP_Error( 'rate_limited', 'Getting a lot of questions right now — please try again in a bit.', array(
				'status'      => 429,
				'retry_after' => $retry_after,
			) ) );
		}

		return array( 'success' => false, 'wp_error' => new WP_Error( 'api_error', $data['error']['message'] ?? 'The AI service returned an unexpected error.', array( 'status' => $code ) ) );
	}

	$answer = '';
	if ( ! empty( $data['candidates'][0]['content']['parts'] ) ) {
		foreach ( $data['candidates'][0]['content']['parts'] as $part ) {
			if ( ! empty( $part['text'] ) ) {
				$answer .= $part['text'];
			}
		}
	}

	if ( empty( $answer ) ) {
		return array( 'success' => false, 'wp_error' => new WP_Error( 'empty_response', 'The AI did not return an answer. Please try rephrasing your question.', array( 'status' => 500 ) ) );
	}

	return array( 'success' => true, 'text' => $answer );
}

/**
 * "Ask" endpoint — general Q&A about Tony's work.
 */
function tdp_handle_ai_ask( $request ) {
	$rate_check = tdp_check_rate_limit();
	if ( is_wp_error( $rate_check ) ) {
		return $rate_check;
	}

	$question = sanitize_text_field( $request->get_param( 'question' ) );

	if ( empty( $question ) ) {
		return new WP_Error( 'no_question', 'Please include a question.', array( 'status' => 400 ) );
	}

	$projects = tdp_get_projects_for_ai();
	$site     = tdp_get_site_context_for_ai();

	$prompt = "You are a helpful assistant on Anthony (Tony) Manansala's WordPress developer portfolio. "
		. "Answer visitor questions about Tony ONLY using the data below (his bio, expertise, work experience, contact info, and projects). "
		. "Be concise and friendly. If something isn't covered by the data, say you're not sure rather than guessing.\n\n"
		. "BIO:\n" . $site['bio']
		. "\n\nEXPERTISE (JSON):\n" . wp_json_encode( $site['expertise'] )
		. "\n\nEXPERIENCE (JSON):\n" . wp_json_encode( $site['experience'] )
		. "\n\nCONTACT (JSON):\n" . wp_json_encode( $site['contact'] )
		. "\n\nPROJECTS (JSON):\n" . wp_json_encode( $projects )
		. "\n\nVISITOR QUESTION: " . $question;

	$result = tdp_call_gemini( $prompt );

	if ( ! $result['success'] ) {
		return $result['wp_error'];
	}

	return array( 'answer' => $result['text'] );
}

/**
 * "Job match" endpoint — takes a pasted job description, generates a
 * short "why I'm a fit" summary grounded in Tony's actual project data.
 */
function tdp_register_ai_job_match_api() {
	register_rest_route( 'tdp/v1', '/job-match', array(
		'methods'             => 'POST',
		'callback'            => 'tdp_handle_ai_job_match',
		'permission_callback' => '__return_true',
	) );
}
add_action( 'rest_api_init', 'tdp_register_ai_job_match_api' );

function tdp_handle_ai_job_match( $request ) {
	$rate_check = tdp_check_rate_limit();
	if ( is_wp_error( $rate_check ) ) {
		return $rate_check;
	}

	$job_description = sanitize_textarea_field( $request->get_param( 'job_description' ) );

	if ( empty( $job_description ) ) {
		return new WP_Error( 'no_job_description', 'Please paste a job description.', array( 'status' => 400 ) );
	}

	// Guard against extremely long pastes — keeps token usage (and cost) predictable.
	if ( mb_strlen( $job_description ) > 6000 ) {
		return new WP_Error( 'job_description_too_long', 'That job description is too long — please paste up to ~6000 characters.', array( 'status' => 400 ) );
	}

	$projects = tdp_get_projects_for_ai();
	$site     = tdp_get_site_context_for_ai();

	$prompt = "You are a career-matching assistant on Anthony (Tony) Manansala's WordPress developer portfolio. "
		. "A recruiter or hiring manager has pasted a job description below. "
		. "Using ONLY Tony's real data below (bio, expertise, experience, and projects), write a 3-4 sentence 'why I'm a fit' summary written in first person as Tony, "
		. "highlighting the specific skills, experience, projects, or tech stack that genuinely match the job description. "
		. "Be honest — if the match is weak or partial, say so briefly rather than overselling. Do not invent skills or projects not in the data.\n\n"
		. "BIO:\n" . $site['bio']
		. "\n\nEXPERTISE (JSON):\n" . wp_json_encode( $site['expertise'] )
		. "\n\nEXPERIENCE (JSON):\n" . wp_json_encode( $site['experience'] )
		. "\n\nPROJECTS (JSON):\n" . wp_json_encode( $projects )
		. "\n\nJOB DESCRIPTION:\n" . $job_description;

	$result = tdp_call_gemini( $prompt );

	if ( ! $result['success'] ) {
		return $result['wp_error'];
	}

	return array( 'summary' => $result['text'] );
}

/**
 * Passes the REST API base URL to JS so the AI widget doesn't need
 * to hardcode the site domain (works across local/staging/production).
 */
function tdp_localize_ai_widget() {
	wp_localize_script( 'tdp-main', 'tdpAi', array(
		'restUrl' => esc_url_raw( rest_url( 'tdp/v1/' ) ),
	) );
}
add_action( 'wp_enqueue_scripts', 'tdp_localize_ai_widget', 20 );