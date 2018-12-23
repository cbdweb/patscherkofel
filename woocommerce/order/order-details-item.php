<?php
/**
 * Order Item Details
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}

global $bed_types;

?>
<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
	<td class="product-name">
		<?php
			// $is_visible = $product && $product->is_visible();
			$is_visible = false;
			
			echo '<h3>' . apply_filters( 'woocommerce_order_item_name', $is_visible ? sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ) : $item['name'], $item, $is_visible ) . '</h3>';
//			echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );

			do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

			if( isset($item['item_meta']) )
			{
				$dl = $dd = $name = '';

				foreach ($item['item_meta'] as $item_key => $it)
				{
					foreach($bed_types as $b => $bed )
					{
						$b = $b.'_';

						if( preg_match('/'.$b.'(\d+)/i', $item_key) )
						{
							$bed_display = ucwords(str_replace( '_', ' ', $item_key));
							foreach( $it as $itd )
							{
								$data = unserialize(unserialize($itd));
								if( isset($data) && !empty($data) )
								{
									$name = $data['name'];
								}
								$name = ( $name != '' ) ? $name : '';
								$dd .= '<dt class="variation-Check-inDate">'.$bed_display.':</dt><dd class="variation-Check-outDate">'.$name.'</dd>';
							}

						}
					}
				};
				if( !empty($dd) )
				{
					$dl .= '<dl class="variation">';
					$dl .= $dd;
					$dl .= '<dl class="variation">';

					echo $dl;
				}
			}

			$order->display_item_meta( $item );

			$order->display_item_downloads( $item );

			do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
		?>
	</td>
	<td class="product-total">
		<?php echo $order->get_formatted_line_subtotal( $item ); ?>
	</td>
</tr>
<?php if ( $order->has_status( array( 'completed', 'processing' ) ) && ( $purchase_note = get_post_meta( $product->id, '_purchase_note', true ) ) ) : ?>
<tr class="product-purchase-note">
	<td colspan="3"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
</tr>
<?php endif; ?>
