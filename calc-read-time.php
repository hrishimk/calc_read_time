<?php
/*
Plugin Name: Calc read time
Plugin URI:  http://jijnasu.in/wp-content/uploads/2016/10/Calc-read-time.zip
Description: Estimates read time for posts and shows it near post titles.
Version:     1
Author:      Hrishikesh
Author URI:  http://jijnasu.in/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages

Calc read time is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Calc read time is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Calc read time. If not, see {License URI}.
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//function to save word count of posts
function calc_read_time_calc($data,$postarr) {
  $post_word_count = str_word_count($data['post_content']);

  //update meta data if exists else update
  //meta_key: calc_read_time_word_count
  //meta_value : $post_word_count
  if ( ! add_post_meta( $postarr['ID'], 'calc_read_time_word_count', $post_word_count, true ) ) {
    update_post_meta( $postarr['ID'], 'calc_read_time_word_count', $post_word_count );
  }
	return $data;
}

//function to append read time
//to the post title
function calc_read_time_add_read_text_in_title( $title, $post_id ){

  //add read time to only posts
  //and not pages or other stuff
  //also not in admin pages
  if(get_post_type($post_id)!='post'||is_admin()){
    return $title;
  }

  //retrive word count saved
  //as meta data
  $word_count = get_post_meta( $post_id, 'calc_read_time_word_count', true );

  //if word count was not set
  //set it for this post
  if (empty($word_count)) {
    calc_read_time_calc(array('post_content'=>get_post_field('post_content', $post_id)), array('ID'=>$post_id));
    $word_count = get_post_meta( $post->ID, 'calc_read_time_word_count', true );
  }

  $word_count = (int)$word_count;
  if($word_count == 0){
    return $title;
  }

  //append word count to the title
  return $title.'<span class="calc_read_time_shower_title_span">'.calc_read_time_create_text($word_count).'</span>';
}

//function to calculate
//read time with word_count
function calc_read_time_create_text($post_word_count){
  //words read per minute
  $wpm = 240;

  //words read per second
  $wps = $wpm/60;

  $secs_to_read = ceil($post_word_count/$wps);

  $read_time_text = $secs_to_read < 60 ? $secs_to_read.' sec' : round( $secs_to_read/60 ).' min';
  $read_time_text .= ' read';

  //ex - 5min read
  return $read_time_text;
}

function calc_read_time_add_styles(){
  wp_enqueue_style( 'calc-read-time-styles', plugin_dir_url( __FILE__ ).'calc-read-time-styles.css', array(), null );
}

add_filter( 'wp_insert_post_data', 'calc_read_time_calc', 1, 2 );
add_filter( 'the_title', 'calc_read_time_add_read_text_in_title', 1, 2 );
add_action( 'wp_enqueue_scripts', 'calc_read_time_add_styles' );
