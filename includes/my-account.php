<?php

/**
 * format extra bed order item meta
 * 
 * @param  $string $output html of the meta values
 * @param  $obj $class  class WC_Order_Item_Meta
 * @return $string filtered html
 */
function psl_woocommerce_order_items_meta_filter($meta, $class) {
	global $bed_types;

	foreach ($meta as $k => $v) {
		foreach ($bed_types as $bed_key => $bed_val) {
			// if keys matchs 
			if (preg_match('/'.$bed_key.'_/', $v['label'])) {
				$label = explode('_',$v['label']);
				$meta[$k]['label'] = $bed_val.' '.$label[1];
			}
		}
	}
	return $meta;
}


add_filter('woocommerce_order_items_meta_get_formatted', 'psl_woocommerce_order_items_meta_filter', 10, 2);
