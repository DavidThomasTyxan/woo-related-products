<?php
/*
Plugin Name: Woo Related Products
Plugin URI: https://lb.linkedin.com/
Description: WooCommerce display related products in a slider
             Can Also specify which category to pick from.
Author: Dave Thomas
Author URI: https://lb.linkedin.com/in/
Text Domain: woo-related-products
Domain Path: /languages/
Version: 1.1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


define("WOO_RELATED_PRODUCTS", '1.1.1');

/* Stores the number of related posts */
$related_post_count = 0;


// Check if WooCommerce is enabled
add_action('plugins_loaded', 'wrp_check_woocommerce_enabled', 1);
function wrp_check_woocommerce_enabled(){
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices','wrp_woo_disabled_notice');
        return;
    }

}

 // Display WC disabled notice
function wrp_woo_disabled_notice(){
    echo '<div class="error"><p><strong>' .__('Woo Related Products', 'woo-related-products') .'</strong> ' .sprintf( __( 'requires %sWooCommerce%s to be installed & activated!' , 'woo-related-products' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' ) .'</p></div>';
}


// Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'wrp_woo_related_product_scripts' );
function wrp_woo_related_product_scripts() {
			
      wp_enqueue_style('slick', plugins_url('slick/slick.css', __FILE__ ), array());
	    wp_enqueue_style('slick-theme', plugins_url('slick/slick-theme.css', __FILE__ ), array());
			wp_enqueue_style('wrp',  plugins_url('css/woo-related-products.css',  __FILE__ )  ,array(), filemtime(plugin_dir_path(  __FILE__ ).'css/woo-related-products.css') );
      wp_enqueue_script('slick',  plugins_url('slick/slick.js',  __FILE__ ) ,array());
      wp_enqueue_script('wrp',  plugins_url('js/woo-related-products.js',  __FILE__ )  ,array(), filemtime(plugin_dir_path(  __FILE__ ).'js/woo-related-products.js') );
	
}


/* Controls hooks on single product woo commerce pages */
if ( ! function_exists( 'wrp_single_product_hook_controls' ) ) {
	
	function wrp_single_product_hook_controls()
	{
		
   
   
    /* Restrict the list of related products to be the sub category of the the one we want  */ 
    add_filter( 'woocommerce_related_products', 'wrp_woo_restrict_posts_by_category', 100 ,3 );
    
    /* If more than 4 then put in carousel */
   
    
        /* Change number of posts displayed in related posts */
        add_filter( 'woocommerce_output_related_products_args', 'wrp_related_products_args' );

        /* Add div/ class wrappers around the bits of html to be included in carousel - note they
        will only be wrapped if no of related posts > 4 */
        add_action( 'woocommerce_after_single_product_summary', 'wrp_carousel_start_container_div', 10 );
        add_action( 'woocommerce_after_single_product_summary', 'wrp_carousel_end_container_div', 30 );


        /* add wrp_card div/class wrapper around each item for carousel - note they
        will only be wrapped if no of related posts > 4 */ 
        add_action('woocommerce_before_shop_loop_item','wrp_carousel_start_card_div',5);
        add_action('woocommerce_after_shop_loop_item','wrp_carousel_end_card_div',15);
    
    
    
  }
}






/* Top level function for dynamic woo commerce hook control */
if ( ! function_exists( 'wrp_woocommerce_hook_controls' ) ) {
	
	function wrp_woocommerce_hook_controls()
	{
    if ( is_product() )
    {
			   /* Do the woocommerce hook controls for sinlge product */
			   wrp_single_product_hook_controls();	  
			
    }
	}
}




/* This hooks in a function to control hooks dynamically - only do on frontend */
add_action( 'wp', 'wrp_woocommerce_hook_controls' ,100);



/* Return a list of posts for a specific category */
if ( ! function_exists( 'wrp_get_posts' ) ) {
	
	function wrp_get_posts()
	{
    
     global $product;
    
     $restricted_posts = array();
    
    
     /* Determine which sub category the current product is in */ 
    
     $range = get_term_by('name', 'Range', 'product_cat');
    
     $product_cat_ids = $product->get_category_ids();
    
     //var_dump($product_cat_ids);
    
     foreach( $product_cat_ids as $cat_id ) {
       
        $term = get_term_by( 'id', $cat_id, 'product_cat' );

        /* If the current terms parent is the range id then we have our sub category to restrict by */
        if ($term->parent == $range->term_id){
          $restrict_cat = $term;
        }
      
        
      }
    
     //echo "Restricted term id = ".var_dump($restrict_cat);
       // echo "<br><br>";
    
    
    /* Pull back a list of post/product ids in the restricted category */
    
    $args = array('post__not_in' => array( $product->get_id() ),'post_type'=>'product','posts_per_page' => 20, 'post_status'=>'publish', 'tax_query' => array(
       			array(
         			'taxonomy' => 'product_cat',
         				'field' => 'id',
         			'terms' => array( $restrict_cat->term_id ) )));
    
    
     $wbs_all_query = new WP_Query($args); 
     
     while ( $wbs_all_query->have_posts() ) : $wbs_all_query->the_post();    
     
        $restricted_posts[] = get_the_ID();
    
     endwhile;
    
     wp_reset_postdata();

     //echo "Restricted posts =" .var_dump($restricted_posts);
     return($restricted_posts);
    
  }
}    
    
    


/* This function returns a specific category term id */
if ( ! function_exists( 'wrp_woo_restrict_posts_by_category' ) ) {
	
	function wrp_woo_restrict_posts_by_category($related_posts, $product_id,$arg_array)
	{
	  
    global $related_post_count;
    
    $new_related_posts = wrp_get_posts();
    
    $related_post_count = sizeof($new_related_posts);
    
    return ($new_related_posts);
    
  }
}


if ( ! function_exists( 'wrp_carousel_start_container_div' ) ) {
	
	function wrp_carousel_start_container_div()
	{
     
    /* Had to call here as global $related_post_count 
    not established at this point - and no issue with inner loop */
    
    $new_related_posts = wrp_get_posts();
    
    $post_count = sizeof($new_related_posts);
    
     if ($post_count > 4)
     {
        echo '<div class="wrp-container">';
        echo '<div class="wrp-carousel">';
     } 
    
  }
}

if ( ! function_exists( 'wrp_carousel_end_container_div' ) ) {
	
  
	function wrp_carousel_end_container_div()
	{
    
    /* Had to call here as global $related_post_count 
    not established at this point - and no issue with inner loop */
    $new_related_posts = wrp_get_posts();
    
    $post_count = sizeof($new_related_posts);
    
    if ($post_count > 4)
      { 
        echo '</div>';
        echo '</div>';
      }
  }
}

if ( ! function_exists( 'wrp_carousel_start_card_div' ) ) {
	
	function wrp_carousel_start_card_div()
	{
    
    /* Had to use the stored global here as it seemed to be causing 
     an issue with an inner wordpress loop on call to wrp_get_posts(); */
    
    global $related_post_count;
    
    if ($related_post_count > 4)
    { 
        echo '<div class="wrp-card">';
    } 
    
  }
}

if ( ! function_exists( 'wrp_carousel_end_card_div' ) ) {
	
	function wrp_carousel_end_card_div()
	{
    
    /* Had to use the stored global here as it seemed to be causing 
     an issue with an inner wordpress loop on call to wrp_get_posts(); */
    
    global $related_post_count;
    
    if ($related_post_count > 4)
    { 
       echo '</div>';
    }
    
  }
}




  function wrp_related_products_args( $args ) {
	$args['posts_per_page'] = 20; // 20 related products
	//$args['columns'] = 2; // arranged in 2 columns
	return $args;
}










       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       