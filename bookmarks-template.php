<?php
/*
Template Name: Bookmarks Page
*/
?>
<?php get_header(); ?>		
	 <div id="content" class="clearfix row">		
		<div id="main" class="col col-lg-12 clearfix" role="main">
			<h1 style="color:brown;">Your Bookmarks List</h1>			
			   <?php $bookmarks->your_bookmarks_list(); ?>
				</div> 
			</div> 
	   </div> 		
<?php get_footer(); ?>
