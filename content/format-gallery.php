<?php

/**
 * @package carrington-personal
 *
 * This file is part of the Carrington Personal Theme for WordPress
 * http://crowdfavorite.com/wordpress/themes/carrington-personal/
 *
 * Copyright (c) 2008-2010 Crowd Favorite, Ltd. All rights reserved.
 * http://crowdfavorite.com
 *
 * **********************************************************************
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * **********************************************************************
 */


if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) { die(); }
if (CFCT_DEBUG) { cfct_banner(__FILE__); }

global $previousday;
$previousday = -1;

?>
<h1>gallery</h1>
<article id="post-<?php the_ID() ?>" <?php post_class('post cleafix') ?>>
	<div class="post-header">
		<h1 class="post-title"><?php the_title() ?></h1>
		<p class="post-date"><a href="<?php the_permalink(); ?>"><?php echo cfcp_date(); ?></a></p>
	</div>
	
	<div id="gallery" class="clearfix">
		<div id="gallery-image">
			full image
		</div>
		<ul id="gallery-thumbs">
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
			<li>img</li>
		</ul>
	</div>
	
	
	<div class="post-meta">
		<p><?php comments_popup_link(__('No Replies', 'carrington-personal'), __('1 Comment', 'carrington-personal'), __('% Comments', 'carrington-personal')); ?></p>
		<?php printf(__('<h5>Categories</h5> <p>%s</p>', 'carrington-personal'), get_the_category_list(', ')) ?>
		<?php the_tags(__('<h5>Tags</h5> <p>', 'carrington-personal'), ', ', '</p>'); ?>
	</div>
	
	<div class="post-content clearfix">
		<?php 
			the_content('<span class="more-link">'.__('Continued...', 'carrington-personal').'</span>'); 
			$args = array(
				'before' => '<p class="pages-link">'. __('Pages: ', 'carrington-personal'),
				'after' => "</p>\n",
				'next_or_number' => 'number'
			);
			wp_link_pages($args);
		?>
	</div><!--post-content-->
</article><!-- .post -->