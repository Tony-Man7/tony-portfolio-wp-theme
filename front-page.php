<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<section class="tdp-hero" id="hero">
	<div class="tdp-wrap">
		<span class="tdp-eyebrow">whoami</span>
		<h1><?php echo esc_html( get_theme_mod( 'tdp_hero_headline', "Hi I'm Tony" ) ); ?></h1>
		<p class="tdp-hero-role"><?php echo esc_html( get_theme_mod( 'tdp_hero_role', '' ) ); ?></p>

		<div class="tdp-hero-meta">
			<div><strong data-count-target="<?php echo esc_attr( get_theme_mod( 'tdp_hero_years_number', '3' ) ); ?>" data-count-suffix="<?php echo esc_attr( get_theme_mod( 'tdp_hero_years_suffix', '+ yrs' ) ); ?>">0<?php echo esc_html( get_theme_mod( 'tdp_hero_years_suffix', '+ yrs' ) ); ?></strong><?php echo esc_html( get_theme_mod( 'tdp_hero_years_detail', '' ) ); ?></div>
			<div><strong><?php echo esc_html( get_theme_mod( 'tdp_hero_meta2_strong', '' ) ); ?></strong><?php echo esc_html( get_theme_mod( 'tdp_hero_meta2_detail', '' ) ); ?></div>
			<div><strong><?php echo esc_html( get_theme_mod( 'tdp_hero_meta3_strong', '' ) ); ?></strong><?php echo esc_html( get_theme_mod( 'tdp_hero_meta3_detail', '' ) ); ?></div>
		</div>
	</div>
</section>

<section class="tdp-section" id="expertise">
	<div class="tdp-wrap">
		<span class="tdp-eyebrow">ls ./expertise</span>
		<h2>What I build with</h2>

		<div class="tdp-grid-3">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<div class="tdp-card">
					<span class="tdp-tag"><?php echo esc_html( get_theme_mod( "tdp_expertise_{$i}_tag", '' ) ); ?></span>
					<h3><?php echo esc_html( get_theme_mod( "tdp_expertise_{$i}_title", '' ) ); ?></h3>
					<p><?php echo esc_html( get_theme_mod( "tdp_expertise_{$i}_detail", '' ) ); ?></p>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>

<section class="tdp-section" id="work">
	<div class="tdp-wrap">
		<span class="tdp-eyebrow">ls ./projects</span>
		<h2>Recent work</h2>

		<?php
		$tdp_projects = new WP_Query( array(
			'post_type'      => 'tdp_project',
			'posts_per_page' => 6,
			'post_status'    => 'publish',
		) );
		?>

		<?php if ( $tdp_projects->have_posts() ) : ?>
			<div class="tdp-work-grid" id="tdp-work-grid">
				<?php while ( $tdp_projects->have_posts() ) : $tdp_projects->the_post();
					get_template_part( 'template-parts/project-card' );
				endwhile; wp_reset_postdata(); ?>
			</div>

			<?php if ( $tdp_projects->max_num_pages > 1 ) : ?>
				<div style="text-align:center; margin-top:40px;">
					<button
						id="tdp-load-more"
						class="tdp-btn-primary"
						style="display:inline-block; padding:12px 28px; border-radius:6px; border:0; cursor:pointer; font-family:var(--font-mono); font-size:13px;"
						data-page="2"
					>See more projects</button>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="tdp-empty-state">
				No projects published yet — add one under <strong>Projects → Add New</strong> in wp-admin and it shows up here automatically.
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="tdp-section" id="experience">
	<div class="tdp-wrap">
		<span class="tdp-eyebrow">cat experience.log</span>
		<h2>Where I've worked</h2>

		<div class="tdp-timeline">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<div class="tdp-timeline-item">
					<span class="tdp-timeline-date"><?php echo esc_html( get_theme_mod( "tdp_exp_{$i}_period", '' ) ); ?></span>
					<h3><?php echo esc_html( get_theme_mod( "tdp_exp_{$i}_title", '' ) ); ?></h3>
					<p class="tdp-timeline-company"><?php echo esc_html( get_theme_mod( "tdp_exp_{$i}_company", '' ) ); ?></p>
					<p><?php echo esc_html( get_theme_mod( "tdp_exp_{$i}_detail", '' ) ); ?></p>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>

<section class="tdp-section tdp-contact" id="contact">
	<div class="tdp-wrap">
		<span class="tdp-eyebrow">ping me</span>
		<h2>Open for select freelance work</h2>
		<p><?php echo esc_html( get_theme_mod( 'tdp_contact_availability', '' ) ); ?></p>

		<div class="tdp-contact-links">
			<a class="tdp-btn-primary" href="mailto:<?php echo esc_attr( get_theme_mod( 'tdp_contact_email', '' ) ); ?>">Email me</a>
			<a href="<?php echo esc_url( get_theme_mod( 'tdp_contact_github', '' ) ); ?>" target="_blank" rel="noopener">GitHub</a>
			<a href="<?php echo esc_url( get_theme_mod( 'tdp_contact_linkedin', '' ) ); ?>" target="_blank" rel="noopener">LinkedIn</a>
			<a href="<?php echo esc_url( get_theme_mod( 'tdp_contact_main_portfolio', '' ) ); ?>" target="_blank" rel="noopener">Main portfolio</a>
		</div>
	</div>
</section>

<?php get_footer(); ?>