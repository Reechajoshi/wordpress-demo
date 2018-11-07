<?php
/**
 * Template Name: Fullwidth page
 * @package by Theme Record
 * @auther: MattMao
 */
get_header(); 
?>
<div id="main" class="fullwidth">

<article id="content">

<?php if (have_posts()) : the_post(); ?>

<div class="post post-single post-fullwidth-single" id="post-<?php the_ID(); ?>">

	<div class="post-format"><?php the_content(); ?></div>

	<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'TR' ), 'after' => '</div>' ) ); ?>

</div>
<!--end post page-->

<?php else : ?>

<!--Begin No Post-->
<div class="no-post">
	<h2><?php _e('Not Found', 'TR'); ?></h2>
	<p><?php _e('Sorry, but you are looking for something that is not here.', 'TR'); ?></p>
</div>
<!--End No Post-->

<?php endif; ?>

</article>
<!--End Content-->

</div>
<!-- #main -->
<?php get_footer(); ?>

