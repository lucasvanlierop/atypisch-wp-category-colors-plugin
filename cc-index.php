<?php
/*
Plugin Name: Category Colors
Plugin URI: http://www.atypisch.nl/plugins/category-colors/
Description: Set a custom color per Post Category with a jQuery Colorpicker
Version: 1.1
Author: M.Timan
Author URI: http://www.atypisch.nl/
*/

//actions and Wordpress hooks
add_action('admin_menu', 'cc_add_page');
add_action('edit_category_form_fields', 'cc_add_picker');

//define your variables
$cat_id = $_GET['tag_ID'];
$the_cat_id = $_POST['the_cat_id'];
$cat_color = $_POST['colorpickerField1'];
$default_cat_color = $_POST['default_colorpickerField'];
//get plugin url
$plugin_url = plugin_dir_url( __FILE__ );
$colorpicker_icon = '<img src="' . $plugin_url . 'documentation/picker_icon.jpg" />';

//check if color form was submitted
if(isset($_POST["submitcolor"])) {
  cc_check($the_cat_id, $cat_color);
} 

//check if the default color form was submitted
if(isset($_POST["change_default_color"]) && !empty($default_cat_color)) {
  cc_check('default', $default_cat_color);
} 

//////////////////////
//database functions//
//////////////////////

//Database Queries; check if a row exists, the update or add row
function cc_check($the_cat_id, $cat_color) {
    global $wpdb;
    $cat_theme = $wpdb->get_results("SELECT * FROM category_colors WHERE the_cat_id = '$the_cat_id' ");
    
    if(is_array($cat_theme) && sizeof($cat_theme) > 0) {
        cc_update_db($the_cat_id,$cat_color);
    } else {
        cc_write_db($the_cat_id,$cat_color);
    }
}
    
//write to db
function cc_write_db($the_cat_id,$cat_color) {
    $query = "INSERT INTO category_colors (the_cat_id, cat_color) ";
	$query .= "VALUES ('$the_cat_id', '$cat_color')";
   	//echo $query;
	$q = mysql_query($query) or die (" db problemen: " . mysql_error());
    return mysql_insert_id();	
}

//update db    
function cc_update_db($the_cat_id,$cat_color) {
	$query= "UPDATE category_colors SET cat_color='$cat_color' WHERE the_cat_id='$the_cat_id'";
   	$q = mysql_query($query) or die ("db problems: " . mysql_error());
    //echo $query
    return $q;
}

//get the Category Color. Main function for Wordpress Tempate usage    
function cc_get_color($the_cat_id) {
    global $wpdb;
    $the_cat_color = $wpdb->get_row("SELECT cat_color, the_cat_id FROM category_colors WHERE the_cat_id = '$the_cat_id' ", ARRAY_A);
        if(is_array($the_cat_color) && sizeof($the_cat_color) > 0) {
           $cat_color = $the_cat_color['cat_color'];
        } else {
           $cat_color="default"; 
        }
        return $cat_color;
}

//create the table
function cc_createtable() {
     global $wpdb;
     $table_name = "category_colors";
     
	 if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
       
       $sql = "CREATE TABLE IF NOT EXISTS $table_name (
       the_cat_id varchar(255) unique NOT NULL , 
       cat_color varchar(255) NOT NULL )";

       require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
       dbDelta($sql);
          //echo "TABLE $table_name is created ";
      } else {
          //echo "TABLE $table_name already exists. ";
      }
}

//////////////////////////////
///// plugin functions ///////
//////////////////////////////

//add the functions to the right pages
function cc_add_page() {
    //first add scripts to Category page
	$mypage = "edit-tags.php";
	add_action( "admin_print_scripts-$mypage", 'cc_admin_head' );
		
	//add options page under settings
	$hook = add_options_page('Category Colors', 'Category Colors', 7, __FILE__, 'cc_options_page');
	
	//add the scripts to the options page as well
	add_action( 'admin_print_scripts-' . $hook, 'cc_admin_head' );
	
	//add the database table function
    cc_createtable();
	
}

//add the colorpicker
function cc_add_picker() {
    global $cat_color, $cat_id, $wpdb, $colorpicker_icon;
    
	$current_default_color = cc_get_color('default');
	
    $cat_theme = $wpdb->get_row("SELECT * FROM category_colors WHERE the_cat_id = '$cat_id' ", ARRAY_A);
    
    if(is_array($cat_theme) && sizeof($cat_theme) > 0) {
        $cat_color=$cat_theme['cat_color'];
    } else {
        if(empty($current_default_color)) {
			$cat_color = "00ff00"; 
		} else {
			$cat_color = $current_default_color;
		}
    }
	
	
	$colorpicker_table .= "<tr class='form-field'>\n";
	$colorpicker_table .= "<th scope='row' valign='top'><label for='description'>Choose your Category Color</label></th>\n";
	$colorpicker_table .= "<td>";
	$colorpicker_table .= "<table class=\"form-table\" style=\"width: 400px;\">";
    $colorpicker_table .= "<tr>\n";
    $colorpicker_table .= "<td>\n"; 
    $colorpicker_table .= "<b>current color: </b><br /><span id=\"colorpreviewField1\" style=\"background-color: #$cat_color; float: left; width: 25px; height: 25px; padding:0; margin:0 10px 10px 0;\">&nbsp;</span>\n";
    $colorpicker_table .= "</td>\n";
    $colorpicker_table .= "</tr>\n";
    $colorpicker_table .= "<tr>\n";
    $colorpicker_table .= "<td>\n"; 
    $colorpicker_table .= "Click on the textfield below for the Colorpicker. <br /> After choosing a color click on the select icon  $colorpicker_icon to change your color, then click on Update below.<br />\n";
    $colorpicker_table .= "<b>new color</b>: #<input type=\"text\" maxlength=\"6\" size=\"6\" name=\"colorpickerField1\" id=\"colorpickerField1\" class=\"alignleft\" value=\"$cat_color\" />";
    $colorpicker_table .= "<input type=\"hidden\" name=\"the_cat_id\" id=\"the_cat_id\" value=\"$cat_id\" />";
    $colorpicker_table .= "<input type=\"hidden\" name=\"submitcolor\" id=\"submitcolor\" class=\"button alignleft\" value=\"Choose\" />";
    $colorpicker_table .= "</td>\n";
    $colorpicker_table .= "</tr>\n";
    $colorpicker_table .= "</table>\n";
   
	$colorpicker_table .= "<br /> <br /> <hr style=\"clear: both; color: #b7b7b7;\" />";
	$colorpicker_table .= "</td>";
	$colorpicker_table .= "</tr>\n"; 
    
	echo $colorpicker_table;
}

//create the options page
function cc_options_page() {
	global $colorpicker_icon, $plugin_url;
	
	//check if a current default color is set
	$current_default_color = cc_get_color('default');
	
	if(empty($current_default_color)) {
		$default_color = "00ff00";
	} else {
		$default_color = $current_default_color;
	}
	
	?>
   <div class="wrap">
	   <h2>Category Colors</h2><br />
		   
		   <!-- start postbox -->
		   <div class="postbox">
		   
				<h3 style="padding: 7px 10px;"><span>Settings</span></h3>
		   
				<div class="inside">
				
				   
				   
				    <form action="#" method="post">
						<h4>Current Default Color:</h4>
						<span id="default_colorpreviewField" style="background-color: #<?php echo $default_color; ?>; float: left; width: 25px; height: 25px; padding:0; margin:0 10px 10px 0;">&nbsp;</span>
						<br />
						<br />
						
						<p>
						You can change your default color here. Click on the textfield below for the Colorpicker. <br /> 
						After choosing a color click on the select icon  <?php echo $colorpicker_icon; ?> to change your color, then click on 'Change Color' below.<br />
						</p>
						
						<ul>
						
							<li>
							  <label for="default_color">change default color to: #</label>
							  <input type="text" name="default_colorpickerField" id="colorpickerField1" value="<?php echo $default_color; ?>" />
							</li>
						
						
						</ul>
					  
						<input type="submit" name="change_default_color" id="change_default_color" class="button" value="Change Color" />
				  
				  </form>

				   
			 
			   </div>
		   </div>

	   <!-- /end postbox -->
	   
	   
	   <!-- start postbox -->
	   <div class="postbox">
	   
			<h3 style="padding: 7px 10px;"><span>Template Usage</span></h3>
	   
		    <div class="inside">
		
			   <h4>How to use the Category Colors plugin</h4>
			   To call the color from witin your template you need to retrieve the Category ID first. 
			   There are several ways to get it. <br />You can get the Category ID by Category Name like so: <code><&#63;php get_cat_ID( &#36;cat_name ); &#63;> </code> (more info online <a href="http://codex.wordpress.org/Function_Reference/get_cat_ID">here</a> ). <br />
			   You can also retrieve the Category ID through an Array. In your template use this code within <a href="http://codex.wordpress.org/The_Loop" target="_blank">the loop</a>:
			  
			   
		<pre>
		<code><&#63;php  
		&#36;category = get_the_category(); 
		&#36;the_category_id = &#36;category[0]->cat_ID;
		&#63;> </code>
		</pre>
		
		<p> 
		Note that you now only retrieve the first given Category in the variable <code>&#36;the_category_id</code>. To retrieve multiple Category IDs just add more variables. <br /> For instance,
		<code>&#36;the_category_id2 = &#36;category[1]->cat_ID; &#36;the_category_id3 = &#36;category[2]->cat_ID;</code> etc. More info online <a href="http://codex.wordpress.org/Function_Reference/get_the_category">here</a>.
		</p>
		
		<p>
		Now, call the Category Colors function and use the Category variable to retrieve the Category Color.
		</p>
		
		<pre>
		<code><&#63;php 
		if(function_exists('cc_get_color')) { 
			&#36;category_color = cc_get_color(&#36;the_category_id); 
		} 
		&#63;></code>
		</pre>
		
		<p>
		You can use the variable <code><&#63;php echo &#36;category_color; &#63;></code> anywhere in your template file after the call. <br />
		Now you can spice up your Posts and Categories with your own custom colors, have fun!
		</p>
		 
		   </div>
	   </div>
 
   <!-- /end postbox -->
   
   
   		   
	<!-- start postbox -->
    <div class="postbox">
	   
			<h3 style="padding: 7px 10px;"><span>About this plugin</span></h3>
	   
			<div class="inside">
				
			   <p>
			   type: Wordpress Plugin <br>
			   languages: PHP, HTML, CSS, MySQL and jQuery javascript, and Colorpicker library <br>
			   version nr: 1.1 <br>
			   release date: 16/09/2013 <br>
			   This plugin is compatible with all newer versions of Wordpress, tested up to Wordpress version 3.6.1 <br>
			   Plugin documentation: Main documentation in the <a target="_blank" href="<?php echo $plugin_url; ?>documentation/index.html">Documentation section</a> of this plugin. <br />
			   Extra documentation online: <a href="http://www.atypisch.nl/plugins/category-colors/">http://www.atypisch.nl/plugins/category-colors/</a> <br>
			   </p>
				
			   <p>
			   For installation guidelines and a more general expanation about the plugin visited the documentation page online or <a target="_blank" href="<?php echo $plugin_url; ?>documentation/index.html">view the HELP FILE</a> in the documentation section of this plugin.
			   </p>
			   
			   <p>
			   This plugin is entirely written by <a href="http://nl.linkedin.com/in/mtiman/">Marten Timan</a>, owner and founder of <a href="http://www.atypisch.nl">Atypisch</a> Webdesign & Webdevelopment currently based in Utrecht, The Netherlands.
			   <br /><br />
			   Please feel free to make a donation to stimulate further development of this or other plugins.
			   </p>
				
			   <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="AQTXT72XD49JS">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1">
			   </form>

			  
		 
		   </div>
	   </div>
  
   <!-- /end postbox -->

   
   <?php
   /*
   no use in writing large metabox class. 
   Instead to keep it lightweight just mnaually add the right div classes and execute a jQuery show/hide script.
   */
   ?>
   <script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			 $("div.postbox h3").click(function () { $(this).parent(".postbox").toggleClass("closed"); });
		});
		//]]>
	</script>
   
    </div> <!-- / end wrap -->
	<?php
}

//define admin scripts for header
function cc_admin_head() {
	//get plugin homebase directory
    $plugindir = get_settings('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
	//enqueue css
	wp_enqueue_style('cat-colors-picker-css', $plugindir . '/jquery-colorpicker/css/colorpicker.css');
	wp_enqueue_style('cat-colors-layout-css', $plugindir . '/jquery-colorpicker/css/layout.css');
	//enqueue scripts
    wp_enqueue_script('cat-colors-jquery', $plugindir . '/jquery-colorpicker/js/jquery-1.9.1.min.js');
    wp_enqueue_script('cat-colors-colorpicker', $plugindir . '/jquery-colorpicker/js/colorpicker.js');
	wp_enqueue_script('cat-colors-eye', $plugindir . '/jquery-colorpicker/js/eye.js');
    wp_enqueue_script('cat-colors-layout', $plugindir . '/jquery-colorpicker/js/layout.js?ver=1.0.2');
    wp_enqueue_script('cat-colors-utils', $plugindir . '/jquery-colorpicker/js/utils.js');
}
?>