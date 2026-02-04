<?php

/**
 * The template for displaying archive pages.
 *
 * @package HelloElementor
 */
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

?>
<style>
	.sbt-tour-card-footer {
		flex-direction: column !important;
	}

	@media (min-width: 1200px) {

		.page-header .entry-title,
		.site-footer .footer-inner,
		.site-footer:not(.dynamic-footer),
		.site-header .header-inner,
		.site-header:not(.dynamic-header),
		body:not([class*=elementor-page-]) .site-main {
			max-width: 1480px;
			padding: 0 20px !important;
		}
	}

	@media  (max-width:1199px) {

		.page-header .entry-title,
		.site-footer .footer-inner,
		.site-footer:not(.dynamic-footer),
		.site-header .header-inner,
		.site-header:not(.dynamic-header),
		body:not([class*=elementor-page-]) .site-main {
			max-width: 1480px;
			padding: 0 20px !important;
		}
	}
	.sbt-step-header {
		padding-right: 0 !important;
		padding-left: 0 !important;
		padding-bottom: 0 !important;
	}
</style>
<main id="content" class="site-main">
	<div class="sbt-step-header">
		<h3>Tours For You</h3>
		<p class="sbt-step-description">Browse tours</p>
	</div>
	<?php echo do_shortcode('[sbt_tour_archive columns="3"]'); ?>


</main>