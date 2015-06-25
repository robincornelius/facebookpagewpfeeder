<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */
 
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
<h2>Facebook feed options</h2>

<form method="post" action="options.php"> 
<?
settings_fields( 'FacebookPageFeed' );
do_settings_sections( 'FacebookPageFeed' );
?>

 <table class="form-table">
        <tr valign="top">
        <th scope="row">Facebook page id</th>
        <td><input type="text" name="facebookfeed_pageid" value="<?php echo esc_attr( get_option('facebookfeed_pageid') ); ?>" /></td>
        </tr>
        
         <tr valign="top">
        <th scope="row">Facebook app id</th>
        <td><input type="text" name="facebookfeed_appid" value="<?php echo esc_attr( get_option('facebookfeed_appid') ); ?>" /></td>
        </tr>
        
         <tr valign="top">
        <th scope="row">Facebook app secret</th>
        <td><input type="text" name="facebookfeed_appsecret" value="<?php echo esc_attr( get_option('facebookfeed_appsecret') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Last Update</th>
        <td><input type="text" name="facebookfeed_lastfbpost" value="<?php echo esc_attr( get_option('facebookfeed_lastfbpost') ); ?>" /></td>
        </tr>
        
         <tr valign="top">
        <th scope="row">Sync limit (s)</th>
        <td><input type="text" name="facebookfeed_synclimit" value="<?php echo esc_attr( get_option('facebookfeed_synclimit') ); ?>" /></td>
        </tr>
                
        <tr valign="top">
        <th scope="row">Post as</th>
        <td>
            
            
            <?php 
            $author = esc_attr( get_option('facebookfeed_postas'));
            wp_dropdown_users(array('name' => 'facebookfeed_postas', 'who' => 'authors', 'selected' => $author)); 
            
            ?>
           </td>
        </tr>
		
		  <tr valign="top">
        <th scope="row">Title type</th>
        <td>
            
            <?php 

            $category =  get_option('facebookfeed_titletype'); ?>
           <select name="facebookfeed_titletype">
		   <option value="story" <?php selected( $category, "story" ); ?> >story</option>
		   <option value="summary" <?php selected( $category, "summary" ); ?> >summary</option>
		   </select>

            
           </td>
        </tr>
		
		<tr valign="top">
        <th scope="row">fallback post title </th>
        <td><input type="text" name="facebookfeed_who" value="<?php echo esc_attr( get_option('facebookfeed_who') ); ?>" /></td>
        </tr>

        
         <tr valign="top">
        <th scope="row">Category</th>
        <td>
            
            <?php 

            $category =  get_option('facebookfeed_postcategory');
            wp_dropdown_categories( "show_count=0&hierarchical=1&hide_empty=0&name=facebookfeed_postcategory&selected=$category" ); 
            
            ?>
           </td>
        </tr>
        
        
       
        
 </table>

<?php submit_button(); ?>
</form>
</div>

