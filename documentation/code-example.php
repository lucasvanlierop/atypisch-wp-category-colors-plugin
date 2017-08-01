<?php
/*
example code to be placed in a wordpress template
*/
if (have_posts()) {
	while (have_posts()) {
		the_post();
		$the_post_id = $post->ID;
		//get the category
		$category = get_the_category(); 
		//get the category id from the first category
		$the_category_id = $category[0]->cat_ID;
		//check if the function 'cc_get_color' exists and then run it
		if(function_exists('cc_get_color')) { $category_color = cc_get_color($the_category_id); } 		
		?>
	
	<div class="post">
	
		<div class="entry">
			<h1 class="title" style="color: #<?php echo $category_color; ?>;"><?php the_title(); ?></h1>
		</div>
	
	</div>
			
<?php
	} // end while
} //end if
?>
