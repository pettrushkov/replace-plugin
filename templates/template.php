<?php
/**
 * Template of Replace page
 *
 * @package Replace
 */

?>

<section class="word-changer">

	<div>
		<h1><?php esc_html_e( 'Type any keyword to find coincidences' ); ?></h1>
		<form action="#" class="word-changer-search">
			<input type="text" name="keyword" placeholder="keyword...">
			<input type="submit" value="Search">
			<?php wp_nonce_field(); ?>
		</form>
	</div>

	<p class="word-changer-word" style="visibility: hidden;"><?php esc_html_e( 'Results for', 'replace' ); ?>
		<span></span></p>

	<?php
	$cols = array( 'title', 'content' );

	if ( is_yoast_activated() ) {
		$cols[] = 'meta-title';
		$cols[] = 'meta-description';
	}
	?>

	<div class="word-changer-cols">

		<?php foreach ( $cols as $col ) { ?>
			<div class="word-changer-col word-changer-col--<?php echo esc_html( $col ); ?>">
				<div class="word-changer-col-heading">

					<h2 class="word-changer-col-heading-title">
						<?php
						$col_title = ucfirst( $col );
						echo esc_html( $col_title );
						?>
					</h2>

					<form action="#" class="word-changer-change-form"
					      data-change-field="<?php echo esc_html( $col ); ?>">
						<input type="text" name="new-value" placeholder="new keyword...">
						<input type="submit" value="Replace">
						<?php wp_nonce_field(); ?>
					</form>
				</div>
				<ul class="word-changer-col-results">

				</ul>
			</div>
		<?php } ?>

	</div>

	<div class="word-changer-answer"></div>

</section>
