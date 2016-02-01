<?php
/**
 * @package Fifteen
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class("archive artmain col-md-4 col-sm-8 col-xs-12"); ?>>
	<div class="main-article curb-effect">
    <div class="feat-thumb-holder">
      <?php the_post_thumbnail('homepage-thumb'); ?>
		</div>
    <div class="main-content">
      <p>
        <?php
          echo substr(get_the_excerpt(),0,150)."...";
        ?>
      </p>
      <a href="<?php the_permalink(); ?>" class="readmore"><?php _e('Read More','fifteen'); ?></a>
    </div>
	</div>
  <h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
</article><!-- #post-## -->
