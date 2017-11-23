<?php
//Include DB configuration file
//include 'dbConfig.php';
require_once('../../../../../wp-load.php');


// CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
// Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
// Set this to 0 once you go live or don't require logging.
define("DEBUG", 1);
// Set to 0 once you're ready to go live
define("USE_SANDBOX", 1);
//define("LOG_FILE", "./ipn.log");
// Read POST data
// reading posted data directly from $_POST causes serialization
// issues with array data in POST. Reading raw POST data from input stream instead.
	error_log(print_r($_POST,true));
	
  if ( ! count($_POST)) {
            throw new Exception("Missing POST Data");
        }
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                // Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
                if ($keyval[0] === 'payment_date') {
                    if (substr_count($keyval[1], '+') === 1) {
                        $keyval[1] = str_replace('+', '%2B', $keyval[1]);
                    }
                }
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // Build the body of the verification post request, adding the _notify-validate command.
        $req = 'cmd=_notify-validate';
        $get_magic_quotes_exists = false;
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }
        // Post the data back to PayPal, using curl. Throw exceptions if errors occur.
        $ch = curl_init('https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        // This is often required if the server is missing a global cert bundle, or is using an outdated one.
        //if ($this->use_local_certs) {
        //    curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cert/cacert.pem");
        //}
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        $res = curl_exec($ch);
        if ( ! ($res)) {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: [$errno] $errstr");
        }
        $info = curl_getinfo($ch);
        $http_code = $info['http_code'];
        if ($http_code != 200) {
            throw new Exception("PayPal responded with http code $http_code");
        }
        curl_close($ch);
		error_log($res);
if (strcmp ($res, "VERIFIED") == 0) {
	//Payment data
	global $wpdb;
	$txn_id = $_POST['txn_id'];
	$payment_gross = $_POST['mc_gross'];
	$currency_code = $_POST['mc_currency'];
	$payment_status = $_POST['payment_status'];
	$payer_email = $_POST['payer_email'];

	//Check if payment data exists with the same TXN ID.
	//$prevPayment = $db->query("SELECT payment_id FROM wp_cj_raffle_payments WHERE txn_id = '".$txn_id."'");
	$tablename = $wpdb->prefix . "cj_raffle_payments";
	try {
		$prevPayment = $wpdb->query("SELECT payment_id FROM `$tablename` WHERE txn_id = '".$txn_id."'");	
	}
	catch(Exception $e){
	}
	if($prevPayment->num_rows > 0){
		exit();
	}else{
		//Insert tansaction data into the database
		$insertPayment = $wpdb->query("INSERT INTO `$tablename` (txn_id,payment_gross,currency_code,payment_status,payer_email) VALUES('".$txn_id."','".$payment_gross."','".$currency_code."','".$payment_status."','".$payer_email."')");
	
		
	if($insertPayment){
		//Insert order items into the database
		//$payment_id = $db->insert_id;
		$num_cart_items = $_POST['num_cart_items'];
		error_log(var_export($_POST), true);
		$raffleid = $_POST['custom'];
		$email = $_POST['payer_email'];
		$purchase_datetmp = strtotime($_POST['payment_date']);
		$purchase_date = date('Y-m-d H:i:s', $purchase_datetmp);
		$txnid = $_POST['txn_id'];
		
		$tablename = $wpdb->prefix . "cj_raffle_tickets";
		
		for($i=1;$i<=$num_cart_items;$i++){
			$order_item_number = $_POST['item_name'.$i];
			//$order_item_quantity = $_POST['quantity'.$i];
			$order_item_gross_amount = $_POST['mc_gross_'.$i];
			$order_item_ticket_no = $_POST['item_number'.$i];
			
			$insertOrderItem = $wpdb->query("INSERT INTO `$tablename` (ticketid,raffleid,txnid,email,purchase_date) VALUES('".$order_item_ticket_no."','".$raffleid."','".$txnid."','".$email."','".$purchase_date."')");
		}
	}
	

	}
}
//CONVERT_TZ(STR_TO_DATE(SUBSTRING(col, 1, 21), '%H:%i:%s %b %d, %Y'),
//                  SUBSTRING(col, 23),      -- convert from PDT
//                  '+00:00')  

?>
