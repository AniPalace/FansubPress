<?php
/**
 * @package Fifteen
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<div class="featured-image-single">
			<?php the_post_thumbnail(); ?>
		</div>
		<?php the_content(); ?>
    <div class="project-downloads">
			<h2 class="project-downloads-title"><?php _e('Letöltések','fansubpress'); ?></h2>
			<table class="project-downloads-table">
      <?php
			$downloads = types_child_posts('download');
			foreach ($downloads as $download) {
				echo '
					<tr>
						<td>
							<a href="'.get_post_permalink($download->ID).'">
								'.get_the_post_thumbnail($download->ID, 'project-download').'
							</a>
						</td>
						<td>
							<a href="'.get_post_permalink($download->ID).'">
								'.$download->post_title.'
							</a>
						</td>
					</tr>
				';
			}
      ?>
		</table>
    </div>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
		<?php edit_post_link( __( 'Edit', 'fifteen' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
