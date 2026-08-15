<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$client = get_post_meta( get_the_ID(), 'tdp_client', true );
	$role   = get_post_meta( get_the_ID(), 'tdp_role', true );
	$year   = get_post_meta( get_the_ID(), 'tdp_year', true );
	$url    = get_post_meta( get_the_ID(), 'tdp_project_url', true );
?>
	<article class="tdp-single-project">
		<div class="tdp-wrap">
			<a class="tdp-back-link" href="<?php echo esc_url( home_url( '/#work' ) ); ?>">&larr; back to work</a>

			<span class="tdp-eyebrow">cat ./project.md</span>
			<h1><?php the_title(); ?></h1>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="tdp-project-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>

			<div class="tdp-single-meta-row">
				<?php if ( $client ) : ?><div><strong>Client</strong><?php echo esc_html( $client ); ?></div><?php endif; ?>
				<?php if ( $role ) : ?><div><strong>Role</strong><?php echo esc_html( $role ); ?></div><?php endif; ?>
				<?php if ( $year ) : ?><div><strong>Year</strong><?php echo esc_html( $year ); ?></div><?php endif; ?>
				<?php if ( $url ) : ?><div><strong>Live</strong><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">Visit site &rarr;</a></div><?php endif; ?>
			</div>

			<div class="tdp-project-content">
				<?php the_content(); ?>
			</div>
		</div>
	</article>
<?php endwhile;

get_footer();
