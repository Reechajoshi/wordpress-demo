<?php
/**
 * Cart
 * @package by Theme Record
 * @auther: MattMao
*/

add_shortcode('shopping_cart', 'shortcode_shopping_cart');

#
#Shopping Cart
#
function shortcode_shopping_cart( $atts, $content = null)
{
	if(isset($_POST['product_id'])) {
		$product_id = $_POST['product_id'];
		$qty = $_POST['product_qty']; 
		$action = $_POST['product_action']; 
		
		switch($action) 
		{ 		
			case "add" :			
				$_SESSION['cart'][$product_id] = $_SESSION['cart'][$product_id] + $qty;			
			break;			
			case "empty" :
				unset($_SESSION['cart']); 
			break;
			case "remove" :
				unset($_SESSION['cart'][$product_id]); 
			break;		
		}
	}


	#Options
	global $tr_config;

	$currency = $tr_config['currency'];
	$paypal_email = $tr_config['paypal_email'];
	$cart_empty_text = $tr_config['cart_empty_text'];
	$shopping_methods = $tr_config['shopping_methods'];
	$shopping_fee = $tr_config['shopping_fee'];
	$shop_page_id = $tr_config['shop_page_id'];
	$shop_cart_page_id = $tr_config['shop_cart_page_id'];
	$shop_thank_you_page_id = $tr_config['shop_thank_you_page_id'];
	$paypal_sandbox = $tr_config['paypal_sandbox'];

	if($paypal_sandbox == 'yes')
	{
		$url = 'www.sandbox.paypal.com';
	}
	else
	{
		$url = 'www.paypal.com';
	}


	#Cart List
	if(isset($_SESSION['cart'])) {

		$output = '<div class="shopping-cart-list">'."\n";
		$output .= '<table>'."\n";
		$output .= '<thead>'."\n";
		$output .= '<th>'.__('Product', 'TR').'</th>'."\n";
		$output .= '<th>'.__('Price', 'TR').'</th>'."\n";
		$output .= '<th class="textcenter">'.__('Quantity', 'TR').'</th>'."\n";
		$output .= '<th>'.__('Total', 'TR').'</th>'."\n";
		$output .= '<th class="last">'.__('Remove', 'TR').'</th>'."\n";
		$output .= '</thead>'."\n";
		$output .= '<tbody>'."\n";

		foreach($_SESSION['cart'] as $product => $qty)
		{	
			$product_price = get_meta_option('product_price', $product);
			$title = get_the_title($product);
			$permalink = get_permalink($product);

			$output .= '<tr>'."\n";
			$output .= '<td class="title"><a href="'.$permalink.'">'.$title.'</a>'."\n";
			$output .= '<td>'.price_currency_symbol($currency).$product_price.'</td>'."\n";
			$output .= '<td class="textcenter">'.$qty.'</td>'."\n";
			$output .= '<td>'.price_currency_symbol($currency).number_format($product_price * $qty, 2).'</td>'."\n";
			$output .= '<td class="last">'."\n";
			$output .= '<form action="" method="post">'."\n";
			$output .= '<input type="hidden" name="product_id" value="'.$product.'" />'."\n";
			$output .= '<input type="hidden" name="product_action" value="remove" />'."\n";
			$output .= '<input type="submit" class="button" value="'.__('Remove', 'TR').'" />'."\n";
			$output .= '</form>'."\n";
			$output .= '</td>'."\n";
			$output .= '</tr>'."\n";

			$subtotal += $product_price * $qty;

			if($shopping_methods == '2')
			{
				$shopping_fee_total += $shopping_fee * $qty;
			}
			elseif($shopping_methods == '3')
			{
				$shopping_fee_total = $shopping_fee;
			}
			else
			{
				$shopping_fee_total = 0;
			}
		}

		$output .= '</tbody>'."\n";
		$output .= '<tfoot>'."\n";
		$output .= '<tr>'."\n";
		$output .= '<td colspan="4" class="textright title">'.__('Cart Subtotal:', 'TR').'</td>'."\n";
		$output .= '<td>'.price_currency_symbol($currency).number_format($subtotal, 2).'</td>'."\n";
		$output .= '</tr>'."\n";
		$output .= '<tr>'."\n";
		$output .= '<td colspan="4" class="textright title">'.__('Shopping:', 'TR').'</td>'."\n";

		if($shopping_methods == '1') {
			$output .= '<td>'.__('Free Shopping', 'TR').'</td>'."\n";
		}else{
			$output .= '<td>'.price_currency_symbol($currency).number_format($shopping_fee_total, 2).'</td>'."\n";
		}

		$output .= '</tr>'."\n";

		$output .= '<tr>'."\n";
		$output .= '<td colspan="4" class="textright title">'.__('Order Total:', 'TR').'</td>'."\n";
		$output .= '<td>'.price_currency_symbol($currency).number_format($subtotal + $shopping_fee_total, 2).'</td>'."\n";
		$output .= '</tr>'."\n";

		$output .= '<tr>'."\n";
		$output .= '<td class="shopping-cart-return"><a href="'.get_page_link($shop_page_id).'">'.__('Return to shop', 'TR').'</a></td>'."\n";
		$output .= '<td colspan="3" class="textright">'."\n";
		$output .= '<form action="" method="post">'."\n";
		$output .= '<input type="hidden" name="product_id" value="null" />'."\n";
		$output .= '<input type="hidden" name="product_action" value="empty" />'."\n";
		$output .= '<input type="submit" class="button" value="'.__('Empty cart', 'TR').'" />'."\n";
		$output .= '</form>'."\n";
		$output .= '</td>'."\n";

		$output .= '<td>'."\n";
		$output .= '<form  action="https://'.$url.'/cgi-bin/webscr" method="post">'."\n";
		$output .= '<input type="hidden" name="cmd" value="_cart" />'."\n";
		$output .= '<input type="hidden" name="upload" value="1" />'."\n";
		$output .= '<input type="hidden" name="business" value="'.$paypal_email.'" />'."\n";
		$output .= '<input type="hidden" name="currency_code" value="'.$currency.'" />'."\n";
		$output .= '<input type="hidden" name="rm" value="2" />'."\n";
		$output .= '<input type="hidden" name="return" value="'.get_page_link($shop_thank_you_page_id).'" />'."\n";
		$output .= '<input type="hidden" name="cancel_return" value="'.get_page_link($shop_cart_page_id).'" />'."\n";
		$output .= '<input type="hidden" name="notify_url" value="'.FUNCTIONS_URI.'/shop/ipn.php" />'."\n";
		
		$i = 1;
		foreach($_SESSION['cart'] as $product => $qty)
		{
			$title = get_the_title($product);
			$product_price = get_meta_option('product_price', $product);

			$output .= '<input type="hidden" name="item_name_'.$i.'" value="'.$title.'" />'."\n";
			$output .= '<input type="hidden" name="quantity_'.$i.'" value="'.$qty.'" />'."\n";
			$output .= '<input type="hidden" name="amount_'.$i.'" value="'.$product_price.'" />'."\n";

			if($shopping_methods == '2') {
				$output .= '<input type="hidden" name="shipping_'.$i.'" value="'.number_format($shopping_fee * $qty, 2).'">'."\n";
			}

			$i++;
		}

		if($shopping_methods == '3') {
			$output .= '<input type="hidden" name="shipping_1" value="'.number_format($shopping_fee, 2).'">'."\n";
		}

		$output .= '<input type="submit" class="submit-button button" value="'.__('Proceed to PayPal', 'TR').'" />'."\n";

		$output .= '</form>'."\n";
		$output .= '</td>'."\n";
		$output .= '</tr>'."\n";

		$output .= '</tfoot>'."\n";
		$output .= '</table>'."\n";
		$output .= '</div>'."\n";

	}else{

		$output = '<div class="shopping-cart-empty"><span>'.$cart_empty_text.'</span><b><a href="'.get_page_link($shop_page_id).'">'.__('Return to shop', 'TR').'</a></b></div>'."\n";
	
	}

	return $output;
}
?>