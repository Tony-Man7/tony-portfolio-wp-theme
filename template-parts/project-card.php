<?php
/**
 * Single project card. Expects the loop to already be on the right post
 * (called inside a WP_Query while() loop via the_post()).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$tdp_cats = get_the_terms( get_the_ID(), 'tdp_project_category' );
$tdp_role = get_post_meta( get_the_ID(), 'tdp_role', true );
$tdp_year = get_post_meta( get_the_ID(), 'tdp_year', true );
$tdp_tech_stack = get_post_meta( get_the_ID(), 'tdp_tech_stack', true );

?>
<a class="tdp-project-card" href="<?php the_permalink(); ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="tdp-project-thumb"><?php the_post_thumbnail( 'medium_large' ); ?></div>
	<?php endif; ?>
	<div class="tdp-project-body">
		<?php if ( $tdp_cats && ! is_wp_error( $tdp_cats ) ) : ?>
			<span class="tdp-project-cat"><?php echo esc_html( $tdp_cats[0]->name ); ?></span>
		<?php endif; ?>
		<h3><?php the_title(); ?></h3>
		
		<div class="tdp-project-meta">
			<?php if ( $tdp_role ) : ?><span><?php echo esc_html( $tdp_role ); ?></span><?php endif; ?>
			<?php if ( $tdp_year ) : ?><span><?php echo esc_html( $tdp_year ); ?></span><?php endif; ?>
		</div>
		<?php if ( $tdp_tech_stack ) : ?>
			<div class="tdp-project-tech">
				<?php foreach ( array_map( 'trim', explode( ',', $tdp_tech_stack ) ) as $tech ) : if ( ! $tech ) continue; ?>
					<span class="tdp-tech-chip"><?php echo esc_html( $tech ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</a>
