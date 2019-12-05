<?php
/*
Plugin Name: Bookmarks Posts
Description: The plugin that allows you to add posts to your bookmarks after user’s login.
Author: Miro
Author URI: https://fabrik.top
Version: 1.0
*/

class Bookmarks{

   private $user;
   private $favorites;

     public function __construct(){
		add_action( 'plugins_loaded',         [$this,'is_favorites'] ); 
	    add_action( 'wp_enqueue_scripts',   [$this,'bm_favorites_scripts'] );
	    add_action( 'wp_ajax_bm',           [$this,'wp_ajax_bm'] );
        add_filter( 'wp_nav_menu_items',  [$this,'add_menu_item'], 10, 2 );
        add_filter( 'the_content',        [$this,'add_news_marks'] );
        add_filter( 'login_redirect',     [$this,'login']);
        register_activation_hook(__FILE__,[$this,'create_page_for_bookmarks']);

    }

/*********************Differences between selected and regular posts****************/
    public function is_favorites($post_id){
			 $this->user = wp_get_current_user();
	         $this->favorites = get_user_meta( $this->user->ID, 'bm_favorites' );
		     if(in_array($post_id, $this->favorites)){ return true;
	      }
	     return false;
        }
  /************************Redirect on Home Page After Login***********************/
    function login(){
		return '/';
	  }

  /************************Add Marks for News Posts after Logged*******************/
    function add_news_marks($text){

      if( is_user_logged_in()&& (is_archive()|is_page(['blog','NEWS'])|is_single())  ) {

	    $id = get_the_ID();
	    $img_src = plugins_url( 'bookmarks-posts/img/loader.gif');

		  if ( $this->is_favorites($id) ){
		     echo '<p class="bm-favorites bm-link '. $id .'" data-action="delete" data-post="'. $id .'"><span class="bm-hidden '. $id .'"><img src="'. $img_src .'" alt=""></span>
		     <a href="#"><i class="fa fa-star fa-2x" aria-hidden="true"></i><span class="link">Remove from Bookmarks</span></a></p>
			 <p class="bm-link2 '. $id .'"><i class="fa fa-star-o fa-2x"></i>Removed from <a href="/bookmarks" class="link">Bookmarks</a></p>'.$text;
		    } else {
		        echo '<p class="bm-favorites bm-link '. $id .'" data-action="add" data-post="'. $id .'"><span class="bm-hidden '. $id .'"><img src="'. $img_src .'" alt=""></span>
	            <a href="#"><i class="fa fa-star-o fa-2x" aria-hidden="true"></i><span class="link">&plus;Add to Bookmarks</span></a></p>
			    <p class="bm-link2 '. $id .'"><i class="fa fa-star fa-2x"></i>Added to <a href="/bookmarks" class="link">Bookmarks</a></p>'.$text;
		  }

      }	else
		  echo $text;

  }

/************************Add Page for Bookmarks after Logged********************/

   function create_page_for_bookmarks(){

	     $template = get_template();
		 $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
		 $filename = $DOCUMENT_ROOT. '/wp-content/themes/'. $template .'/bookmarks.php';

	  if( is_user_logged_in()&&!file_exists($filename) ){
		   $file = file_get_contents(__DIR__.'/bookmarks-template.php');
	       $fh = fopen($filename, 'a');
		   fwrite($fh, $file);
		   fclose($fh);
		}

      wp_insert_post([
        'post_type'         => 'page',
		'page_template'     => 'bookmarks.php',
        'post_title'        => 'Your Bookmarks List',
        'post_content'      => '',
        'comment_status'    => 'closed',
        'post_author'       => $this->user,
		'post_status'       => 'publish',
        'post_name'         => 'bookmarks']
	   );
}

 /*************************Add New Menu "Bookmarks" after User's Login*********/
    function add_menu_item($items, $args){
            if (is_user_logged_in()&&$args->theme_location == ('main_nav'||'primary')) {
            $items .= '<li style="background:brown;" class="menu-item"><a href="/bookmarks/">Bookmarks</a></li>';
	     }
        return $items;
       }

/************************* Add and Delete Bookmarks from List*******************/
    function wp_ajax_bm(){

	        $act = $_POST['arg'];
		    $post_id = (int)$_POST['postId'];

	        if( !wp_verify_nonce( $_POST['security'], 'bm-favorites' ) ){
		      wp_die('Security bug!');
	        }

			$action = $act . '_user_meta';
			$action( $this->user->ID, 'bm_favorites', $post_id );

	           if( $act=='delete' ){
		         echo '<i class="fa fa-star-o fa-2x" aria-hidden="true"></i>Deleted from Bookmarks</a>';
	            }
	          wp_die();
        }

  /****************************Building of Bookmarks List**********************************/
    function your_bookmarks_list(){

	    if(!$this->favorites){
		     echo 'The list is empty for now.';
	     }
	    $img_src = plugins_url( 'bookmarks-posts/img/loader.gif');

	    echo '<ul class="list">';
	     foreach($this->favorites as $favorite){
		   echo'<li><div class="bm-links '. $favorite .'">'. get_the_post_thumbnail($favorite, 'medium') .'</div>
		        <span class="bm-hidden '. $favorite .'"><img class="left-f" src="' . $img_src . '" alt=""></span>
		        <a href="'. get_the_permalink($favorite) .'"><h1 class="h2">'. get_the_title( $favorite ) .'</h4></a>
				<span class="basket"><a href="#" data-action="delete" data-post="'. $favorite .'" class="bm-favorites">
				<i class="fa fa-trash fa-4x" aria-hidden="true"></i></a></span>
				<span class="bm-favorites-hidden"><img src="'. $img_src .'" alt=""></span>';


		   $content = get_the_content('more...', false, $favorite);
		   $content = substr($content, 0, 460);
		   $text = preg_replace("/<img[^>]+\>/i", '', $content);
		   echo $text;
		   echo '<a href="'. get_the_permalink($favorite) .'">... <b class="more">Read more</b>...</a></li>';
		 }
	 echo '</ul>';
}

 /****************************Add Plugin's Scripts and Styles*********************/

      function bm_favorites_scripts(){
		    $post = get_post();
	        wp_enqueue_script( 'bm-favorites-scripts', plugins_url('/js/bm-favorites-scripts.js', __FILE__), array('jquery'), null, true );
	        wp_enqueue_style( 'bm-favorites-style', plugins_url('/css/bm-favorites-style.css', __FILE__) );
		    wp_localize_script( 'bm-favorites-scripts', 'bmFavorites', ['url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('bm-favorites'), 'postId' => $post->ID] );
      }

}


$bookmarks = new Bookmarks();
