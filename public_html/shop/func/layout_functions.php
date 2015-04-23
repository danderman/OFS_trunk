<?
include_once ('config_openfood.php');

// Load templates
$temp = explode("\n\n", file_get_contents(FILE_PATH."/templates.txt"));
$layout_templates = array();
foreach ($temp as $value)
{
	$matches = array();
	preg_match("/(.*?):[ \n]?(.*)/s", $value, $matches);
	$layout_templates[$matches[1]] = $matches[2];
}

function replace_templ ($template, $data)
{
	global $layout_templates;
	$string = $layout_templates[$template];
	$string = preg_replace('/\$\{(.+?)\}/e', '$data[$1]', $string);
	return $string;
}

function order_cycle_info ()
{
	$result = mysql_query("SELECT date_format(date_open, '%b %D') AS date_open,
		date_format(date_closed, '%b %D') AS date_closed,
		date_format(delivery_date, '%b %D') AS delivery_date,
		((now() > date_open) + (now() > date_closed)) AS status
		FROM ". TABLE_CURDEL) or die("Failed to get delivery info: ".mysql_error());
	$data = mysql_fetch_assoc($result);
	$data["selected_".$data["status"]] = ' class="selected"';
	$result = mysql_query("SELECT product_list.product_id
		FROM ".TABLE_PRODUCT.", ".TABLE_PRODUCER." 
		WHERE product_list.donotlist != '1'
		AND product_list.producer_id = product_list.producer_id
		AND producers.donotlist_producer != '1'
		GROUP BY product_id") or die("Failed to get listed product info info: ".mysql_error());
	$data["products_listed"] = mysql_num_rows($result);
	return replace_templ("order_cycle_info", $data);
}

function order_status_bar ()
{
	global $layout_templates;
	if ($_SESSION["valid_m"])
	{
		$result = mysql_query("SELECT first_name, last_name, member_id
			FROM ".TABLE_MEMBER."
			WHERE username_m='".$_SESSION["valid_m"]."'")
			or die("Failed to get name and member id info: ".mysql_error());
		$data = mysql_fetch_assoc($result);
		$result = mysql_query("SELECT basket_id
			FROM ".TABLE_BASKET_ALL.", ".TABLE_CURDEL."
			WHERE member_id=".$data["member_id"]." AND ".TABLE_BASKET_ALL.".delivery_id=".TABLE_CURDEL.".delivery_id")
			or die("Failed to get basket id: ".mysql_error());
		$has_basket = ""; // set to "_basket" if they have an order
		if (mysql_num_rows($result) > 0)
		{
			$has_basket = "_basket";
			$temp = mysql_fetch_assoc($result);
			$result = mysql_query("SELECT ".TABLE_BASKET.".*, ".TABLE_PRODUCT.".random_weight AS rand_weight
				FROM ".TABLE_PRODUCT.", ".TABLE_BASKET."
				WHERE ".TABLE_BASKET.".basket_id = ".$temp["basket_id"]."
				AND ".TABLE_BASKET.".product_id = ".TABLE_PRODUCT.".product_id")
				or die("Failed to get basket item data to make order total: ".mysql_error());
			while ($item = mysql_fetch_assoc($result))
			{
				$data["order_items"] += $item["quantity"];
				$data["order_dollars"] += $item["item_price"] * ($item["rand_weight"] ? $item["total_weight"] : $item["quantity"]);
			}
		} else {
			$data["order_dollars"] = 0;
			$data["order_items"] = 0;
		}
		$data["order_dollars"] = sprintf("%0.2f", $data["order_dollars"]);
		return replace_templ("user_bar$has_basket", $data);
	} else {
		return $layout_templates["login_bar"];
	}
}

?>
