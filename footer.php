<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<footer class="tdp-footer">
	<div class="tdp-wrap">
		&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — built with WordPress, no template kit.
	</div>
</footer>

<div class="tdp-ai-widget" id="tdp-ai-widget">
	<button class="tdp-ai-toggle" id="tdp-ai-toggle" aria-label="Open AI assistant">
		<span>$</span>
	</button>

	<div class="tdp-ai-panel" id="tdp-ai-panel" hidden>
		<div class="tdp-ai-header">
			<span class="tdp-ai-header-title">ask.tony()</span>
			<button class="tdp-ai-close" id="tdp-ai-close" aria-label="Close">&times;</button>
		</div>

		<div class="tdp-ai-tabs">
			<button class="tdp-ai-tab is-active" data-mode="ask">chat</button>
			<button class="tdp-ai-tab" data-mode="job-match">job match</button>
		</div>

		<div class="tdp-ai-view" id="tdp-ai-view-ask">
			<div class="tdp-ai-thread" id="tdp-ai-thread">
				<div class="tdp-ai-message tdp-ai-message--bot">Hi, I'm Tony's AI assistant. Ask me anything about his projects — tech stack, roles, years, whatever.</div>
			</div>
			<form class="tdp-ai-input-row" id="tdp-ai-form">
				<input type="text" id="tdp-ai-input" placeholder="Ask about Tony's work..." autocomplete="off">
				<button type="submit" class="tdp-ai-send" aria-label="Send">&rarr;</button>
			</form>
		</div>

		<div class="tdp-ai-view" id="tdp-ai-view-jobmatch" hidden>
			<div class="tdp-ai-jobmatch">
				<textarea id="tdp-ai-job-input" placeholder="Paste a job description here..."></textarea>
				<button id="tdp-ai-job-submit" class="tdp-btn-primary">Check fit</button>
				<div class="tdp-ai-job-result" id="tdp-ai-job-result"></div>
			</div>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
