<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="tdp-section" style="padding-top:140px;">
	<div class="tdp-wrap">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div><?php the_excerpt(); ?></div>
		<?php endwhile; else : ?>
			<p>Nothing here yet.</p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
