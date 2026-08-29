<?php
/** Homepage panel for a project marked as a selected system. */
if ( ! defined( 'ABSPATH' ) ) exit;

$tdp_cats       = get_the_terms( get_the_ID(), 'tdp_project_category' );
$tdp_role       = get_post_meta( get_the_ID(), 'tdp_role', true );
$tdp_year       = get_post_meta( get_the_ID(), 'tdp_year', true );
$tdp_tech_stack = get_post_meta( get_the_ID(), 'tdp_tech_stack', true );
$tdp_gallery    = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( get_the_ID(), 'tdp_case_study_gallery', true ) ) ) );
$tdp_labels     = array_map( 'trim', explode( '|', (string) get_post_meta( get_the_ID(), 'tdp_case_study_gallery_labels', true ) ) );
$tdp_is_active  = ! empty( $args['is_active'] );

if ( empty( $tdp_gallery ) && has_post_thumbnail() ) {
	$tdp_gallery = array( get_post_thumbnail_id() );
}
?>
<article class="tdp-system-case-study" data-system-panel<?php echo $tdp_is_active ? '' : ' hidden'; ?> role="tabpanel">
	<div class="tdp-system-visual-column">
		<header class="tdp-system-visual-heading">
			<!-- <?php if ( $tdp_cats && ! is_wp_error( $tdp_cats ) ) : ?>
				<span class="tdp-project-cat"><?php echo esc_html( $tdp_cats[0]->name ); ?></span>
			<?php endif; ?> -->
			<!-- <h3><?php the_title(); ?></h3> -->
		</header>

	<div class="tdp-proof-deck" data-proof-deck>
		<div class="tdp-proof-stack">
			<?php foreach ( $tdp_gallery as $tdp_index => $tdp_image_id ) :
				$tdp_label = ! empty( $tdp_labels[ $tdp_index ] ) ? $tdp_labels[ $tdp_index ] : get_the_title( $tdp_image_id );
			?>
				<figure class="tdp-proof-card<?php echo 0 === $tdp_index ? ' is-active' : ''; ?>" data-proof-card>
					<?php echo wp_get_attachment_image( $tdp_image_id, 'large', false, array( 'loading' => 0 === $tdp_index ? 'eager' : 'lazy' ) ); ?>
					<!-- <figcaption><?php echo esc_html( $tdp_label ); ?></figcaption> -->
				</figure>
			<?php endforeach; ?>
		</div>
		<?php if ( count( $tdp_gallery ) > 1 ) : ?>
			<div class="tdp-proof-controls">
				<button type="button" class="tdp-proof-arrow" data-proof-prev aria-label="Previous proof image">←</button>
				<span data-proof-count>01 / <?php echo esc_html( str_pad( (string) count( $tdp_gallery ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<button type="button" class="tdp-proof-arrow" data-proof-next aria-label="Next proof image">→</button>
			</div>
		<?php endif; ?>
	</div>
	</div>

	<div class="tdp-system-case-study-body">
		<?php if ( has_excerpt() ) : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>

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

		<span class="tdp-system-note">Swipe or use the arrows to explore the build.</span>
	</div>
</article>
