<?php
/**
 * @package Fifteen
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php if (has_post_thumbnail() ) : ?>
			<div class="featured-image-single">
				<?php the_post_thumbnail(); ?>
			</div>
		<?php endif; ?>
		<?php the_content(); ?>
		<div class="entry-download">
			<?php
				// Ha a felhasználó be van lépve, csak akkor jelenítjük meg neki a letöltési linkeket
				if ( is_user_logged_in() ) {
					$subtitle_url = types_render_field( "subtitle-url", array("output" => "raw") );
					if ($subtitle_url) {
						echo '<a href="'.site_url('fsp_download/subtitle/'.get_the_id()).'" target="_blank">Felirat letöltése</a>';
					}
				} else { // Ha nincsen belépve, akkor egy egyszerű üzenetet írunk ki
					_e('A letöltéshez előbb be kell jelentkezned!', 'FansubPress');
				}
			?>
		</div>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
		<?php edit_post_link( __( 'Edit', 'fifteen' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
