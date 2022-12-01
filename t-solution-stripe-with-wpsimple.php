<?php
/*
 * Plugin Name: Stripe Payment Gateway By Syed Tirmizi
 * Plugin URI: http://tirmizi.net/
 * Description: Custom Order manage from other Woo Website. 
 * Version: 1.0.0
 * Author: Syed Muzaffar Tirmizi
 * Author URI: https://www.upwork.com/freelancers/syedtirmizi
 * License: GNU Public License v3
 */
 
 
 
  add_action('init', 'mjt_sstripe_order_post_init');
function mjt_sstripe_order_post_init() {
	global $data;
	register_post_type(
		'sstripe_order',
		array(
			'labels' => array(
				'name' => 'Stripe Orders',
				'singular_name' => 'Stripe Order'
			),
			'public' => true,
			'has_archive' => 'sst_order',
			'rewrite' => array('slug' => 'sst_order', 'with_front' => false),
			'supports' => array('title', '', '', 'author', '', '', 'revisions', 'custom-fields', '', ''),
			'can_export' => true,
		)
	);
	/* register_taxonomy('product_category', 'product', array('hierarchical' => true, 'label' => 'Categories', 'query_var' => true, 'rewrite' => true)); */

}
add_action( 'add_meta_boxes', 'mjt_order_meta_box_add' );
function mjt_order_meta_box_add()
{
    add_meta_box( 'mjt-order-meta-box-id', 'Order Details', 'mjt_order_meta_box_stripe', 'sstripe_order', 'normal', 'high' );
}
function mjt_order_meta_box_stripe()
{
	global $post;
	$post_id = $post->ID ;
	$order_id = get_post_meta( $post_id, 'order_id', true);
	$ordertotal = get_post_meta( $post_id, 'ordertotal', true) ; 
	$ctmsrid =  get_post_meta($post_id,'customer_id', true ); ?>
	 <p>
        <label for="order_id_meta_box_text">Order ID</label>
        <input type="text" name="order_id" id="order_id_meta_box_text" value="<?php echo $order_id; ?>" />
    </p>
	<p>
        <label for="order_id_meta_box_text">Order Total</label>
        <input type="text" name="ordertotal" id="ordertotal_meta_box_text" value="<?php echo $ordertotal; ?>" />
    </p>  
	
	<?php 
	if($order_id){
		$orderstausm = 'orderstaus_'.$order_id ;
		$orderstausv = get_post_meta( $post_id, $orderstausm, true); ?>
	<p>
        <label for="orderstaus_meta_box_text">Order Staus</label>
        <input type="text" name="<?php echo $orderstausm ;?>" id="orderstaus_meta_box_text" value="<?php echo $orderstausv; ?>" />
    </p> 
	
	<p>
        <label for="customer_id_meta_box_text">Customer ID</label>
        <input type="text" name="customer_id" id="customer_id_meta_box_text" value="<?php echo $ctmsrid ; ?>" />
    </p>

	<?php }
	
}
add_action( 'save_post', 'mjt_order_meta_box_save' );
function mjt_order_meta_box_save( $post_id )
{
	if( isset( $_POST['order_id'] ) ) 		
        update_post_meta( $post_id, 'order_id',  $_POST['order_id']) ;
	
	if( isset( $_POST['ordertotal'] ) )
        update_post_meta( $post_id, 'ordertotal', $_POST['ordertotal']) ; 
		
	if( isset( $_POST['customer_id'] ) )
        update_post_meta( $post_id, 'customer_id', $_POST['customer_id']) ;
	
	if( isset( $_POST['order_id'] ) ){
		$order_id = $_POST['order_id'] ;
		$orderstaus = 'orderstaus_'.$order_id ;
        update_post_meta( $post_id, $orderstaus, $_POST[$orderstaus] );
	}
}

function mjt_get_post_id_by_meta_key_orderid($order_id){
	 global $wpdb;
	 $orderstaus = 'orderstaus_'.$order_id ;
	// $wpdb->get_results ("SELECT 'option_value' FROM  $wpdb->options WHERE option_name='siteurl'" );
   $meta = $wpdb->get_results("SELECT * FROM ".$wpdb->postmeta." WHERE meta_key='".$orderstaus."'");
   if (is_array($meta) && !empty($meta) && isset($meta[0])) {
      $meta = $meta[0];
      }   
   if (is_object($meta)) {
      return $meta->post_id;
      }
   else {
      return false;
      }
}
 function mjt_order_data_init_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
// Start session on init hook.
add_action( 'init', 'mjt_order_data_init_session' );

/* use form from Plugin wp simple pay : https://wordpress.org/plugins/stripe/ */
 add_action('wp_footer','mjt_get_order_parameter_script');
 function mjt_get_order_parameter_script(){
	  if(is_home() || Is_front_page() ){
		 if( isset($_GET['order_id']) && isset($_GET['ordertotal']) && isset($_GET['orderemailid']) ){
			$order_id = $_GET['order_id'] ;
			$ordertotal = $_GET['ordertotal'] ;
			$orderemailid = $_GET['orderemailid'] ;
			$_SESSION['order_id'] = $order_id;
			?>
		<script type="text/javascript" id="mjt_payment_stripe">
			jQuery(document).ready(function() {
				var $order_id  = '<?php echo $order_id ; ?>';
				var $ordertotal  = '<?php echo $ordertotal ; ?>';
				var $orderemailid  = '<?php echo $orderemailid ; ?>';
				var curncystrng = jQuery(".simpay-currency-symbol").text(); 		
				var amntwithcrncy = curncystrng +''+  $ordertotal;
				console.log(amntwithcrncy); 
				jQuery("input[name='simpay_field[orderId]']").val($order_id);
				jQuery("input[name='simpay_field[orderId]']").prop('disabled', true);
				jQuery("input[name='simpay_custom_price_amount']").val($ordertotal); 
				jQuery(".simpay-checkout-btn").text('Pay '+amntwithcrncy);
				jQuery("input[name='simpay_amount']").val($ordertotal);
				jQuery("input[name='simpay_email']").val($orderemailid);
			//	jQuery("input[name='simpay_field[orderId]']").prop('readonly', true);		
				// jQuery("input[name='simpay_custom_price_amount']").trigger( "focus" );		
				jQuery("input[name='simpay_custom_price_amount']").prop('disabled', true);
				jQuery("input[name='simpay_email']").trigger( "focus" );
			});
		</script> 
		<?php 
		$type = 'sstripe_order'; $orderstaus = 'orderstaus_'.$order_id ;
		$postID = mjt_get_post_id_by_meta_key_orderid($order_id);
		if($postID == false){
			$new = array(
					'post_title' => 'New Order from '.$orderemailid,
					'post_content' => '',
					'post_status' => 'publish',
					'post_type'    => $type,
				);
			$post_id = wp_insert_post( $new );
			if($post_id){
				add_post_meta($post_id,'order_id',$order_id);
				add_post_meta($post_id,'ordertotal',$ordertotal);
				add_post_meta($post_id, $orderstaus, 'Pending');
			}

		}
	}
   }
 }
 
 /* script change page id at line number 168 , 173 */
add_action( 'init', 'mjt_order_payment_confirmation' );
function mjt_order_payment_confirmation(){
	if( is_page(2580) || is_page(2581) ){
		if(isset($_SESSION['order_id'])){
			$order_id = $_SESSION['order_id'];
			$orderstaus = 'orderstaus_'.$order_id ; 
			$post_id = mjt_get_post_id_by_meta_key_orderid($order_id);
			if( is_page(2580)){ $orderstatus = 'Completed'; }elseif(is_page(2581)){ $orderstatus = 'Failed'; }else{  $orderstatus = 'Pending'; }
			update_post_meta($post_id, $orderstaus, $orderstatus);
			 if( isset($_GET['customer_id'])){
				 $ctmsrid = $_GET['customer_id'] ;
				 add_post_meta($post_id,'customer_id', $ctmsrid );
			 }
		}
	}
}

// Add the custom columns to the sstripe_order post type:
add_filter( 'manage_sstripe_order_posts_columns', 'mjt_set_custom_edit_sstripe_order_columns' );
function mjt_set_custom_edit_sstripe_order_columns($columns) {
    unset( $columns['author'] );
    $columns['order_id'] = __( 'Order ID', 'pay_tirmizi' );
    $columns['ordertotal'] = __( 'Order Total', 'pay_tirmizi' );
	$columns['orderstatus'] = __( 'Order Status', 'pay_tirmizi' );
	$columns['customer_id'] = __( 'Customer ID', 'pay_tirmizi' );
    return $columns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_sstripe_order_posts_custom_column' , 'mjt_custom_sstripe_order_column', 10, 2 );
function mjt_custom_sstripe_order_column( $column, $post_id ) {
    switch ( $column ) {

        case 'order_id' :
            echo get_post_meta( $post_id , 'order_id' , true ); 
            break;

        case 'ordertotal' :
            echo get_post_meta( $post_id , 'ordertotal' , true ); 
            break;
			
		case 'orderstatus' :
			$order_id = get_post_meta( $post_id , 'order_id' , true );
			$orderstaus = 'orderstaus_'.$order_id ;
            echo get_post_meta( $post_id , $orderstaus , true ); 
            break;
			
		 case 'customer_id' :
            echo get_post_meta( $post_id , 'customer_id' , true ); 
            break;
    }
}


add_filter( 'manage_sstripe_order_posts_columns', 'mjt_sstripe_order_columns' );
function mjt_sstripe_order_columns( $columns ) {
	$columns = array(
      'cb' => $columns['cb'],      
      'title' => __( 'Title' ),
      'order_id' => __( 'Order ID', 'pay_tirmizi' ),
      'ordertotal' => __( 'Order Total', 'pay_tirmizi' ),
	  'orderstatus' => __( 'Order Status', 'pay_tirmizi' ),
	  'customer_id' => __( 'Customer ID', 'pay_tirmizi' ),
	  'date' => __( 'Date', 'pay_tirmizi' ),
    );
	return $columns;
}