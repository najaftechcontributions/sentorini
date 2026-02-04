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
</style>
<main id="content" class="site-main">
	<div class="sbt-step-header">
		<h3>Tours For You</h3>
		<p class="sbt-step-description">Browse tours</p>
	</div>
	<?php echo do_shortcode('[sbt_tour_archive columns="3"]'); ?>


</main>