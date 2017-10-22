<?php
/**
* @package Equitel For WooCommerce
* @version 1.6
* @author Mauko Maunde
**/
/*
Plugin Name: Equitel For WooCommerce
Plugin URI: http://wordpress.org/plugins/wc-equitel/
Description: This plugin extends WooCommerce functionality to integrate Equitel for making payments, checking account balance transaction status and reversals. It also adds Kenyan Counties to the WooCommerce states list.
Author: Mauko Maunde
Version: 0.1
Author URI: https://mauko.co.ke/
*/

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'wc_mpesa_gateway_init', 11 );

add_filter( 'woocommerce_states', 'custom_woocommerce_states' );

function custom_woocommerce_states( $states ) {

  $states['KE'] = array(
	'BAR' => 'BARINGO',
	'BMT' => 'BOMET',
	'BGM' => 'BUNGOMA',
	'BSA' => 'BUSIA',
	'EGM' => 'ELGEYO/MARAKWET',
	'EBU' => 'EMBU',
	'GSA' => 'GARISSA',
	'HMA' => 'HOMA BAY',
	'ISL' => 'ISIOLO',
	'KAJ' => 'KAJIADO',
	'KAK' => 'KAKAMEGA',
	'KCO' => 'KERICHO',
	'KBU' => 'KIAMBU',
	'KLF' => 'KILIFI',
	'KIR' => 'KIRINYAGA',
	'KSI' => 'KISII',
	'KIS' => 'KISUMU',
	'KTU' => 'KITUI',
	'KLE' => 'KWALE',
	'LKP' => 'LAIKIPIA',
	'LAU' => 'LAMU',
	'MCS' => 'MACHAKOS',
	'MUE' => 'MAKUENI',
	'MDA' => 'MANDERA',
	'MAR' => 'MARSABIT',
	'MRU' => 'MERU',
	'MIG' => 'MIGORI',
	'MBA' => 'MOMBASA',
	'MRA' => 'MURANGA',
	'NBO' => 'NAIROBI',
	'NKU' => 'NAKURU',
	'NDI' => 'NANDI',
	'NRK' => 'NAROK',
	'NYI' => 'NYAMIRA',
	'NDR' => 'NYANDARUA',
	'NER' => 'NYERI',
	'SMB' => 'SAMBURU',
	'SYA' => 'SIAYA',
	'TVT' => 'TAITA TAVETA',
	'TAN' => 'TANA RIVER',
	'TNT' => 'THARAKA - NITHI',
	'TRN' => 'TRANS NZOIA',
	'TUR' => 'TURKANA',
	'USG' => 'UASIN GISHU',
	'VHG' => 'VIHIGA',
	'WJR' => 'WAJIR',
	'PKT' => 'WEST POKOT'
  );

  return $states;
}