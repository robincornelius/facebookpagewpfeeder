<?php

/*
 * Facebook graph API tools and wordpress insertion
 * Copyright (c) 2015 Robin Cornelius <robin.cornelius@gmail.com>
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// This file is the business end of the plugin
// It contains the code to download the feed from facebook
// parses the graph to exract posts and full size image links
// uploads these to the WP media library
// generates WP blog posts

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
 
class Facebook_parser
{
    public function run()
    {

        $synclimit = get_option('facebookfeed_synclimit');
        $nextsyncfrom = get_option('facebookfeed_nextsyncfrom');
                
        if($nextsyncfrom>time())
        {
            exit;
        }
        else
        {
            $nextsyncfrom=time()+$synclimit;
        }
        
        update_option('facebookfeed_nextsyncfrom', $nextsyncfrom);

        $path = plugin_dir_path( __FILE__ );   
        $parts = explode('/', $path);
        array_pop($parts);
        array_pop($parts);
        $path = implode('/', $parts);

        define('FACEBOOK_SDK_V4_SRC_DIR', "$path/facebook-php-sdk-v4-4.0-dev/src/Facebook/");
        require "$path/facebook-php-sdk-v4-4.0-dev/autoload.php";
            
        $appid = get_option('facebookfeed_appid');
        $appsecret = get_option('facebookfeed_appsecret');
                
        FacebookSession::setDefaultApplication($appid,$appsecret);
        $access_token = "$appid|$appsecret";
        $session = new FacebookSession($access_token);

        $posts = $this->getposts($session);
        $storys = $this->getstory($posts);
                
        if(is_null($storys))
            return;
                
        $dateset=FALSE;
               
        foreach($storys as $entry)
        {                   
            $imgattachments = [];
            $imghtml = "";
            
            // TODO currently we insert all found images into the media library
            // we only use the first one as featured image
            // the others are linked di rectly from fbcdn so this inconsistency
            // needs addressing and/or options added
            $imghtml = "[facebook_sdk]";  
            
            foreach($entry['img'] as $img)
            {
                $attachid = $this->insert_media_from_url($img);
                array_push($imgattachments,$attachid);
                         
                $imghtml.="<img alt='Facebook photo' src='".$img."' </img>";
            }
            
            if($entry['type']=="video")
            {
                $videoid = $entry['mediatarget'];
                $imghtml = "[facebook_sdk][facebook_video videoid=\"$videoid\"]";
            }
            
            $href = $entry['link'];
            $imghtml .= "<br>[facebook_like]";
            
            //The above is a bit dirty, eg for a video post there will be an img attachment
            //the static preview image of the video and this gets pished to $imgattachments
            //then later used for the preview image for the post, which is good.
            //but its using imghtml to convey the rest of the media
            //Also what other media types do we need to support and in what other ways?

            //This is not currently used but generates a link back to the original facebook post
            $linkhtml = "<br /><a href='".$entry['link']."'>See original facebook post here</a><br />";
                       
            // Facebook date is 2015-04-16T17:19:31+0000
            // Wordpress expects Y-m-d H:i:s
            // At least they are the same basic format give or take some delimiters
            // https://xkcd.com/1179/
            // However it should be noted that you can't pass Graph API the timezone 
            // offset eg +0000 or it chokes ;-/
            
            $pattern = '/([0-9]*)-([0-9]*)-([0-9]*)T([0-9]*):([0-9]*):([0-9]*)\+[0-9]*/';
            $date = preg_replace($pattern, "$1-$2-$3 $4:$5:$6", $entry['time']);

            $poster = get_option('facebookfeed_appid');
            
            //TODO save the objectID for this post then we can
            //1. Check its not already here
            //2. If it is already here check for modifications based on date 
            // stamps and update if needed
                     
            // Create post object
            $my_post = array(
                'post_title'    => $entry['title'],
                'post_content'  => $entry['message']."<br>".$imghtml,
                'post_status'   => 'publish',
                'post_author'   => get_option('facebookfeed_postas'),
                'post_category' => array(get_option('facebookfeed_postcategory')),
                'post_type'     => 'post',
                'post_date'     => $date,
                'post_date_gmt' => $date,
		'filter' => true 
            );

	    remove_filter('content_save_pre', 'wp_filter_post_kses');
                    
            // Insert the post into the database
            $new_post_id = wp_insert_post( $my_post );
                    
            if(sizeof($imgattachments)>0)
            {
                update_post_meta( $new_post_id, '_thumbnail_id', $imgattachments[0] );
            }

		add_filter('content_save_pre', 'wp_filter_post_kses');

            // The facebook feed is newest first so only set the lastfbpost for the
            // first item
            if($dateset==FALSE)
            {
                update_option( "facebookfeed_lastfbpost", $entry['time'] );
                $dateset=TRUE;
            }     
        }
        
        // I feel we should do something better than this, if we dont exit we 
        // just hit the main site with a 404
        exit();
    }

    
    function getposts($session)
    {
        $retdata =[];
        $pageid = get_option('facebookfeed_pageid');
        $since = get_option('facebookfeed_lastfbpost');
           
        $pattern = '/\+[0-9]*/';
        $since = preg_replace($pattern, "", $since);

        $request = new FacebookRequest(
            $session,
            'GET',
            "/$pageid/posts?fields=story,message,attachments,link,full_picture,type,description,status_type&since=$since"
        );

        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        $array = $graphObject->asArray();

        return $array;
    }
        
    function getstory($ids)
    {

        $data = [];
        if(is_null($ids['data']))
            return NULL;
            
		$whoposted = get_option('facebookfeed_who');
		 
        foreach($ids['data'] as $id)
        {
            if($id->story!="")
            {
                $story = $id->story;
            }
            else
            {
                if($id->status_type=="added_photos")
                {
                    $story = $whoposted." added photos";
                }
                if($id->status_type=="mobile_status_update")
                {
                    $story = $whoposted." posted an update";      
                }
            }    

	    if($story=="")
	    {
                    $story = $whoposted." posted on facebook";      
	    }
   
            $entry['title'] = $story;

            $message = $id->message;
            if($message=="")
                $message=$id->description;

            //$message = preg_replace('~[^\x20-\x7E\xA3]*~','',$message);
            $message = preg_replace('~[^\x20-\x7E]*~','',$message);
            $entry['message'] = $message;

            $img = [];
             if(!is_null($id->attachments->data))
            {     
                foreach($id->attachments->data as $at)
                {  
                    $entry['mediatarget']=$at->target->id;

                    if($at->media->image->src!="")
                    {
                        array_push($img,$at->media->image->src);       
                    } 

                    if(!is_null($at->subattachments->data))
                    {
                        foreach($at->subattachments->data as $at2)
                        {
                            if($at2->media->image->src!="")
                            {
                                 array_push($img,$at2->media->image->src);
                            }
                        }
                    }
                }
            }
            
            $entry['img'] = $img;
            $entry['link'] = $id->link;
            $entry['time'] = $id->created_time;
            $entry['type'] = $id->type;

            array_push($data,$entry);
        }

        return $data;
    }
    
    function generateRandomString($length = 10) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    function downloadFile($url, $path) 
    {

        $newfname = $path;
        $file = fopen ($url, "rb");
        if ($file) {
          $newf = fopen ($newfname, "wb");

          if ($newf)
          while(!feof($file)) {
            fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
          }
        }

        if ($file) {
          fclose($file);
        }

        if ($newf) {
          fclose($newf);
        }
   }
    
    //We should do better here, we could save the media via the object ID for the post
    //then we could also stop duplicating any media in the library
    function insert_media_from_url($url)
    {
        $upload_dir = wp_upload_dir();

        $filename = $upload_dir['path']."/".$this->generateRandomString().".jpg";       
        
        $this->downloadFile ($url, $filename);

        // The ID of the post this attachment is for.
        $parent_post_id = 0;

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype( basename( $filename ), null );

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        // Prepare an array of post data for the attachment.
        $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
                'post_author'    => '470'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        
        return $attach_id;
    }
}
