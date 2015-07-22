<?php
/*
Plugin Name: Caleb Heisey custom plugin
Description: Custom plugin for Caleb Heisey
Version: 1
Author: Will Vedder
Author URI: http://willvedder.com
*/


//--------------------------------------------
// Project custom post type
//--------------------------------------------

add_action( 'init', 'custom_posts_project' );

function custom_posts_project() {
  $labels = array(
    'name'               => _x( 'Project', 'post type general name' ),
    'singular_name'      => _x( 'Project', 'post type singular name' ),
    'add_new'            => _x( 'Add New', 'project' ),
    'add_new_item'       => __( 'Add New Project' ),
    'edit_item'          => __( 'Edit Project' ),
    'new_item'           => __( 'New Project' ),
    'all_items'          => __( 'All Project' ),
    'view_item'          => __( 'View Project' ),
    'search_items'       => __( 'Search Project' ),
    'not_found'          => __( 'No projects found' ),
    'not_found_in_trash' => __( 'No projects found in the Trash' ), 
    'parent_item_colon'  => '',
    'menu_name'          => 'Project'
  );
  $args = array(
    'labels'        => $labels,
    'description'   => 'Portfolio Project',
    'public'        => true,
    'menu_position' => 5,
    'supports'      => array( 'title'),
    'has_archive'   => true,
  );

  register_post_type( 'project', $args ); 

};

//--------------------------------------------
// Featured custom post type
//--------------------------------------------

add_action( 'init', 'custom_posts_featured' );

function custom_posts_featured() {
  $labels = array(
    'name'               => _x( 'Featured', 'post type general name' ),
    'singular_name'      => _x( 'Featured', 'post type singular name' ),
    'add_new'            => _x( 'Add New', 'featured' ),
    'add_new_item'       => __( 'Add New Featured' ),
    'edit_item'          => __( 'Edit Featured' ),
    'new_item'           => __( 'New Featured' ),
    'all_items'          => __( 'All Featured' ),
    'view_item'          => __( 'View Featured' ),
    'search_items'       => __( 'Search Featured' ),
    'not_found'          => __( 'No featured found' ),
    'not_found_in_trash' => __( 'No featured found in the Trash' ), 
    'parent_item_colon'  => '',
    'menu_name'          => 'Featured'
  );
  $args = array(
    'labels'        => $labels,
    'description'   => 'Portfolio Featured',
    'public'        => true,
    'menu_position' => 5,
    'supports'      => array( 'title'),
    'has_archive'   => true,
  );

  register_post_type( 'featured', $args ); 

};

//--------------------------------------------
// Themes custom taxonomy
//--------------------------------------------

add_action( 'init', 'custom_posts_create_portfolio_type_tax' );

function custom_posts_create_portfolio_type_tax() {
    register_taxonomy(
        'portfolio_type',
        'project',
        array(
            'label' => __( 'Portfolio Type' ),
            'rewrite' => array( 'slug' => 'portfolio_type' ),
            'hierarchical' => false,
        )
    );
}


//--------------------------------------------
// Allows custom fields to be added to JSON
//--------------------------------------------

add_filter('json_api_encode', 'json_api_encode_acf');

function json_api_encode_acf($response) {
    if(isset($response['portfolio_types'])){
        foreach ($response['portfolio_types'] as $category) {
          json_api_add_acf_category($category, $response['taxonomy']);
          json_api_add_acf_tax($category);
        }
    };
    if(isset($response['projects'])){
        foreach ($response['projects'] as $project){
            if(isset($project->custom_fields->subtext)){
                $project->subtext = $project->custom_fields->subtext[0];
            }
            if(isset($project->custom_fields->images)){
                $images = get_field( 'images' , $project->id );
                $project->images = $images;
            }
            if(isset($project->custom_fields->category)){
                $project_type = get_field('category',$project->id);
                foreach ($project_type as $proj){ 
                    $proj = get_term($proj,'portfolio_type');
                    $project->project_type[] = $proj->slug;
                }
            }
            if(isset($project->custom_fields->mobile_appear)){
                $project->mobile_appear = $project->custom_fields->mobile_appear[0];
            }
        } 
    };
    //removing all the unncessary fields accross all content types
    if(isset($response)){
        foreach($response as $responses){
            foreach($responses as $fields){
                unset($fields->comments);
                unset($fields->comment_count);
                unset($fields->comment_status);
                unset($fields->author);
                unset($fields->content);
                unset($fields->attachments);
                unset($fields->modified);
                //unset($fields->custom_fields);
                unset($fields->status);
                unset($fields->date);
                unset($fields->categories);
                unset($fields->tags);
                unset($fields->excerpt);
                unset($fields->acf);
                unset($fields->url);
            }
        }
    }
    return $response;
}
 
function json_api_add_acf(&$post) {
    $post->acf = get_fields($post->id);
}

//prepping theme images
function json_api_add_acf_tax(&$post) {
    $image = get_field('image','themes_'.$post->term_id);
    if(!empty($image)){
        $post->image = $image;
    }
}

function json_api_add_acf_category(&$category,&$taxonomy) {
    $fields = get_fields($taxonomy . '_' . $category->id);
    $category->acf = $fields;
}


?>