<?php
// custom plugin by yusuf eko n.
/**
 * Plugin Name: Malika Shipping
 * Plugin URI: http://malika.id/
 // Ori URI: http://code.tutsplus.com/tutorials/create-a-custom-shipping-method-for-woocommerce--cms-26098
 * Description: Custom Shipping Method for WooCommerce
 * Version: 0.0.6
 * Author: Yusuf Eko N.
 * Author URI: http://malika.id/
 // License: GPL-3.0+
 // License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: tutsplus
 */
 
if ( ! defined( 'ABSPATH' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function malika_shipping_method() {
        if ( ! class_exists( 'Malika_Shipping_Method' ) ) {
            class Malika_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'malika'; 
                    $this->method_title       = __( 'Malika Shipping', 'malika' );  
                    $this->method_description = __( 'Custom Shipping Method for malika', 'malika' ); 
 
                    // Availability & Countries
/*                    $this->availability = 'including';
					  $this->countries = array(
						'US', // Unites States of America
						'CA', // Canada
						'DE', // Germany
						'GB', // United Kingdom
						'IT', // Italy
						'ES', // Spain
						'HR' // Croatia
					);
 */
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'malika Shipping', 'malika' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', 'malika' ),
                          'type' => 'checkbox',
                          'description' => __( 'Aktifkan pengiriman.', 'malika' ),
                          'default' => 'yes'
                          ),
 
                     'title' => array(
                        'title' => __( 'Title', 'malika' ),
                          'type' => 'text',
                          'description' => __( 'judul yang di tampilkan', 'malika' ),
                          'default' => __( 'Malika Shipping', 'malika' )
                          ),
 
                     'weight' => array(
                        'title' => __( 'Weight (kg)', 'malika' ),
                          'type' => 'number',
                          'description' => __( 'Maximum allowed weight', 'malika' ),
                          'default' => 100
                          ),
						  
					 'kurir' => array(
                        'title' => __( 'Kurir yang tersedia', 'malika' ),
                          'type' => 'text',
                          'description' => __( 'jne, tiki, pos, wahana dan j&t', 'malika' ),
                          'default' => __( 'jne:tiki:pos:wahana:jnt', 'malika' )
                          ),
					 'kurir_inter' => array(
                        'title' => __( 'Kurir yang tersedia untuk International', 'malika' ),
                          'type' => 'text',
                          'description' => __( 'jne, tiki & pos', 'malika' ),
                          'default' => __( 'jne:pos', 'malika' )
                          ),
						  
					 'free_ship' => array(
                          'title' => __( 'Gratis pengiriman', 'malika' ),
                          'type' => 'checkbox',
                          'description' => __( 'Aktifkan pengiriman gratis untuk kupon.', 'malika' ),
                          'default' => 'yes'
                          ),
 
                     );
 
                }
 
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
				public function calculate_shipping( $package = array() ){
					$package = WC()->cart->get_shipping_packages();
					$this->malika_calculate_shipping($package[0]);
				}
				
				private function malika_calculate_shipping( $package ) {
					$data = $this->malika_shipp_different();
				
					// 445 = Solo
					if($data['kabupaten'] == '445'){
						$this->malika_free_ongkir();
					}else{
						$this->malika_check_ongkir($data,$package);
					}
				}
				
				private function malika_shipp_different(){
					$user_id = get_current_user_id();
					$ship_address = get_user_meta($user_id,'ship_to_different_address',true);
					
					// check shipping address
					if($ship_address==1){
						$type = 'shipping';
						add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true' );
					}else{
						$type = 'billing';
						add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
					}
					
					$local = get_user_meta($user_id,$type.'_country',true);
					$local = empty($local)?'ID':$local;
					if($local == 'ID'){
						$tujuan = get_user_meta($user_id,$type.'_district',true);
						$destinasi = get_user_meta($user_id,$type.'_kabupaten',true);
					}else{
						$tujuan = get_user_meta($user_id,$type.'_negara',true);
						$destinasi = '0';
					}
					
					$data = array(
						'negara'	=> $local,
						'tujuan'	=> $tujuan,
						'kabupaten'	=> $destinasi
					);
					
					return $data;
				}
				
				// Kurir yang digunakan
				private function malika_free_ongkir(){
					$this->malika_add_rate_kurir('local pickup',0);
					$this->malika_add_rate_kurir('free ongkir',0);
				}
				
				private function malika_check_ongkir($data,$package){
					$coupon = WC()->session->applied_coupons;
					$check = array_filter($coupon);
					
					if(! empty($check)){
						$coupon = $coupon[0];
						$coupon = new WC_Coupon($coupon);
					
						if($coupon->get_free_shipping()==1){
							$this->malika_free_ongkir();
						}else{
							$this->malika_get_kurir($data,$package);
						}
					}else{
						$this->malika_get_kurir($data,$package);
					}
				}
				
				private function malika_get_kurir($data,$package){
					$locale = $data['negara'];
					
					if($locale=='ID'){
						$kurir = $this->settings['kurir'];
					}else{
						$kurir = $this->settings['kurir_inter'];
					}
					
					$weight = 0;
					
					foreach ( $package['contents'] as $item_id => $values ){	
						$_product = $values['data'];
						$weight = $weight + $_product->get_weight() * $values['quantity']; 
					}

					// array data kurir
					$args = array(
						'berat'		=> $weight * 1000,
						'kurir'		=> $kurir
					);
					
					$args = $this->malika_get_request($data,$args);
					$this->malika_set_request($data['negara'],$args);	
				}
				
				private function malika_data_kurir($rates){
					$check = array_filter($rates);
					if(empty($check)){
						return false;
					}

					foreach($rates['hasil'] as $key => $val){
						$kurir = $val['code'];
						$kurirs = $val['costs'];
						foreach($kurirs as $key_a => $val_a){
							$hari = '';
											
							if($kurir != 'pos'){
								$hari = ' hari';
							}
											
							if($kurir == 'J&T'){
								$hari = $rates['hasil'][0]['costs'][0]['cost'][0]['etd'].' hari';
							}
							
							$est = strtolower('('.$val_a['cost'][0]['etd'].$hari.')');
							$nama = $kurir.' '.$val_a['service'].' '.$est;
							$cost = $val_a['cost'][0]['value'];
							$this->malika_add_rate_kurir($nama,$cost);
						}
					}
				}
				
				private function malika_data_kurir_inter($rates){
					$check = array_filter($rates);
					if(empty($check)){
						return false;
					}

					foreach($rates['hasil'] as $key => $val){
						$kurir = $val['code'];
						$kurirs = $val['costs'];
						foreach($kurirs as $key_a => $val_a){
							$hari = ' hari';
							
							$est = strtolower('('.$val_a['etd'].$hari.')');
							$nama = $kurir.' '.$val_a['service'].' '.$est;
							$cost = $val_a['cost'];
							$curs = $rates['curs']['value'];

							if(get_locale() == 'en_US'){
								if($val_a['currency'] == 'IDR'){
									$cost = $this->malika_conv_money($cost,'USD',$curs);
								}
							}else{
								if($val_a['currency'] == 'USD'){
									$cost = $this->malika_conv_money($cost,'IDR',$curs);
								}
							}
							
							$this->malika_add_rate_kurir($nama,$cost);
						}
					}
				}
				
				private function malika_add_rate_kurir($nama,$cost){
					$rate = array(
						'id' => $nama,
						'label' => $nama,
						'cost' => $cost
					);
				 
					$this->add_rate( $rate );
				}
				
				// request RajaOngkir ------------------------
				private function malika_get_request($data,$args = array()){
					$local = $data['negara'];
					$url = "https://pro.rajaongkir.com/api/";
					
					$asal = "origin=445";
					$tujuan = "&destination=".$data['tujuan'];
					$berat = "&weight=".$args['berat'];
					$kurir = "&courier=".$args['kurir'];
					
					if($local == 'ID'){
						$url .= "cost";
						$asal .= "&originType=city";
						$tujuan .= "&destinationType=subdistrict";
					}else{
						$url .= "v2/internationalCost";
					}
					
					$field = $asal.$tujuan.$berat.$kurir;
					
					$data = array(
						'url'	=> $url,
						'data'	=> $field
					);
					
					return $data;
				}
				
				private function malika_set_request($local,$args = array()){					
					$data = $this->malika_request_rajaongkir($args['url'],$args['data']);
					$data = json_decode($data, true);
					$data = $data['rajaongkir'];
					
					$hasil = array();
					$check = $data['status']['code'];
					
					if($check == 400){
						//$data = $data['status']['description'];
						return false;
					}else{
						$hasil['hasil'] = $data['results'];
						
						if(isset($data['currency'])){
							$hasil['curs'] = $data['currency'];
						}else{
							$hasil['curs'] = 1;
						}
					}
					
					if($local == 'ID'){
						$this->malika_data_kurir($hasil);
					}else{
						$this->malika_data_kurir_inter($hasil);
					}
				}
				
				private function malika_request_rajaongkir($url,$data){
					$curl = curl_init();

					curl_setopt_array($curl, array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS => $data,
						CURLOPT_HTTPHEADER => array(
							"content-type: application/x-www-form-urlencoded",
							"key: 6e017a3c62af6b88f5a0dea0bfe0bbdd"
						),
					));
									
					$response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

					if ($err) {
						$data = array(
							'rajaongkir'	=> array(
								"query"			=> array(
									"id" 			=> "0"
								),
								"status" 		=> array(
									"code"			=> 400,
									"description"	=> $err
								),
								"results"		=> array(
									
								)
							)
						);
						return json_encode($data);
					} else {
						$data = $response;
						return $data;
					}

				}
				
				private function malika_conv_money($money,$type = 'IDR',$curs){
					global $WOOCS;
					
					switch($type){
						case 'IDR':
							$money = $money * $curs;
							break;
						case 'USD':
							$money = round($money / $curs,2);
							break;
						default:
							break;
					}
					
					return $money;
				}
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'malika_shipping_method' );
 
    function add_malika_shipping_method( $methods ) {
        $methods[] = 'Malika_Shipping_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_malika_shipping_method' );
 
    function malika_validate_order( $posted )   {
 
        $packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( 'malika', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != "malika" ) {
                             
                    continue;
                             
                }
 
                $Malika_Shipping_Method = new Malika_Shipping_Method();
                $weightLimit = (int) $Malika_Shipping_Method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                        $message = sprintf( __( 'Maaf, %d kg exceeds the maximum weight of %d kg for %s', 'malika' ), $weight, $weightLimit, $Malika_Shipping_Method->title );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }
                }
            }       
        } 
    }
 
    add_action( 'woocommerce_review_order_before_cart_contents', 'malika_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'malika_validate_order' , 10 );
	
}