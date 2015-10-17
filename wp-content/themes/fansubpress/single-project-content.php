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
      <?php
			$downloads = types_child_posts('download');
			foreach ($downloads as $download) {
				echo $download->post_title;
			}
      ?>
    </div>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
		<?php edit_post_link( __( 'Edit', 'fifteen' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
