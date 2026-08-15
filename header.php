<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="tdp-header">
	<div class="tdp-wrap tdp-header-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tdp-logo">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.svg' ); ?>" alt="<?php bloginfo( 'name' ); ?>" height="24">
		</a>

		<button class="tdp-nav-toggle" aria-label="Toggle menu">menu</button>

		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'tdp-nav-links',
			) );
		} else {
			tdp_fallback_menu();
		}
		?>
	</div>
</header>
