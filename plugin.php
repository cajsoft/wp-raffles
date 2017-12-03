<?php


/*
Plugin Name: CaJSoft Raffle Ticket Plugin
Plugin URI: http://www.cajsoft.co.uk/wordpress
Description: Raffle Ticket Plugin.  Lets users select raffle tickets and add to cart for purchase.  Fully customizable.
Version: 1.0.0
Author: CaJSoft WP
Author URI: http://www.cajsoft.co.uk

*/ 



//add_action('admin_init','process_bulk_action');

if (!defined('RAFFLE_TICKET_PLUGIN_VERSION'))
    define('RAFFLE_TICKET_PLUGIN_VERSION', '1.0.0');

define('WP_CART_LIVE_PAYPAL_URL', 'https://www.paypal.com/cgi-bin/webscr');
define('WP_CART_SANDBOX_PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');

/**
 * register_activation_hook implementation
 *
 * will be called when user activates plugin first time
 * must create needed database tables
 */

function activate_cj_raffle() {
   $role = get_role( 'editor' );
   $role->add_cap( 'manage_options' ); // capability
//   $role = get_role ('administrator');
//   $role->add_cap( 'manage_options' );
}
// Register our activation hook
register_activation_hook( __FILE__, 'activate_cj_raffle' );

function deactivate_cj_raffle() {
  $role = get_role( 'editor' );
  $role->remove_cap( 'manage_options' ); // capability
}

// Register our de-activation hook
register_deactivation_hook( __FILE__, 'deactivate_cj_raffle' );

	 
 function cj_raffle_install(){
	  global $wpdb;  
	 global $jal_db_version;  
	 update_option('raffle_ticket_plugin_version', RAFFLE_TICKET_PLUGIN_VERSION);

	 $table_name = $wpdb->prefix . "cj_raffle_tbl"; 
	 if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
	{
		$sql = "CREATE TABLE IF NOT EXISTS ". $table_name . " (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`pre_text` varchar(200) NOT NULL,
		`post_text` varchar(200) NOT NULL,
		`total_tickets_req` int(6) DEFAULT NULL,
		`tickets_sold` int(6) NOT NULL DEFAULT '0',
		`ticket_price` float NOT NULL DEFAULT '0',
		`raffle_desc` text,
		`raffle_img` varchar(255) DEFAULT NULL,
		`run_date` date DEFAULT NULL,
		`winning_no` int(6) DEFAULT NULL,
		`completed` tinyint(1) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$wpdb->query($sql);
		
		$sql = "ALTER TABLE ". $table_name ." AUTO_INCREMENT=1;";
		$wpdb->query($sql);
	}
	
	$table_name = $wpdb->prefix . "cj_raffle_payments"; 
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
	{
		$sql = "CREATE TABLE IF NOT EXISTS ". $table_name ." (
			`payment_id` int(11) NOT NULL AUTO_INCREMENT,
			`txn_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			`payment_gross` float(10,2) NOT NULL,
			`currency_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
			`payment_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			`payer_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`payment_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$wpdb->query($sql);
	}
	
	$table_name = $wpdb->prefix . "cj_raffle_tickets"; 
	 if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
	{
		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name ." (
			`ticketid` int(11) NOT NULL,
			`raffleid` int(11) NOT NULL,
			`txnid` varchar(50) NOT NULL,
			`ticket` varchar(100) NOT NULL,
			`name` varchar(60),
			`address` varchar(200),
			`email` varchar(25) NOT NULL,
			`purchase_date` date NOT NULL,
			UNIQUE KEY `raffle_ticket` (`ticketid`,`raffleid`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$wpdb->query($sql);
	}
		
}

register_activation_hook(__FILE__,'cj_raffle_install');


function raffle_ticket_plugin_check_version() {
	if (RAFFLE_TICKET_PLUGIN_VERSION !== get_option('raffle_ticket_plugin_version'))
		cj_raffle_install();
}

add_action('plugins_loaded', 'raffle_ticket_plugin_check_version');



function cj_raffle_uninstall(){
	  global $wpdb;  
	 global $jal_db_version;  
	 $table_name = $wpdb->prefix . "cj_raffle_tbl"; 
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
	 $table_name = $wpdb->prefix . "cj_raffle_tickets"; 
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);	
	 $table_name = $wpdb->prefix . "cj_raffle_payments"; 
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
}
//register_deactivation_hook( __FILE__, 'cj_raffle_uninstall' );	 
register_uninstall_hook(__FILE__, 'cj_raffle_uninstall');


function raffle_raffle_script() {
	 
	// wp_enqueue_script( 'custom-script', plugin_dir_url( __FILE__ ) . 'includes/js/jquery-ui.js' );
	
	// wp_enqueue_script( 'custom-script-3', plugin_dir_url( __FILE__ ) . 'includes/js/jcarousellite_1.0.1c4.js' );
		wp_register_style( 'raffle_raffle_style', plugin_dir_url( __FILE__ ) . 'includes/css/rafflestyles.css', false, '1.0.0' );
	// wp_register_style( 'raffle_raffle_style_pages', plugin_dir_url( __FILE__ ) . 'includes/css/style.css', false, '1.0.0' );
	//require plugin_dir_path(__FILE__)  . 'includes/gateways/payments.php';

    //	add_action( 'wp_enqueue_scripts', 'register_my_script' );
//	wp_register_script( 'raffle-selector', plugin_dir_url(__FILE__). 'includes/js/raffle-selector.js' , false, '1.0.0' );
		wp_register_script ( 'raffle-selector', plugins_url ( 'includes/js/raffle-selector.js', __FILE__ ) );
	//wp_register_script( 'raffle-selector', plugin_dir_url( 'includes/js/raffle-selector.js' , __FILE__ ), array(), '1.0.0', true );
	//wp_register_style( 'prefix-style', plugins_url('mystyle.css', __FILE__) );
	//wp_enqueue_style( 'prefix-style' ); 
	
		wp_enqueue_style( 'raffle_raffle_style' );
	// wp_enqueue_style( 'raffle_raffle_style_pages' );
}


add_action( 'wp_enqueue_scripts', 'raffle_raffle_script');

add_shortcode('raffle-tickets', 'my_shortcode_function');


	
function my_shortcode_function( $atts ) { 

		global $wpdb;  
		//global $options;
		global $ticket_price;
	
	
		$atts = shortcode_atts( array(
        'raffleno' => 1,
		), $atts, 'raffle-tickets' );
	
		$options = get_option( 'settings' );
		$table_name = $wpdb->prefix . "cj_raffle_tbl"; 
	 
	//print_r($atts['raffleno']) ;
		$today = date("Y-m-d", strtotime("now"));
		//2017-12-03
		$retrieve_data = $wpdb->get_results ("Select run_date from " . $table_name ." where id = " .$atts['raffleno'] ." and (run_date > '".$today . "' or run_date is null) and completed=0");
		//error_log(var_dump($wpdb));
		if (!$retrieve_data) {
			//error_log(var_dump($wpdb));
			$output = "<h4>Sorry, this raffle has either passed its end date or has completed...</h4>";
			return $output;
		}
		$output .= "<div class='raffle'>";
		$retrieve_data = $wpdb->get_results( "select main.id, main.total_tickets_req, main.raffle_desc, main.raffle_img, main.post_text, main.pre_text,main.ticket_price, main.total_tickets_req from " . $table_name ." main where main.id = " .$atts['raffleno']);	
		if ($retrieve_data) {
	
			$output .= "<ul>";
	
			foreach ($retrieve_data as $retrieved_data){
				$raffleid =  $retrieved_data->id;
				$output .= "<p><img class='raffleimage' src='" . $retrieved_data->raffle_img . "' width='50%' height='50%'/></p>";
				$output .= "<p><span class='raffledesc'>" . $retrieved_data->raffle_desc ."</span></p>";
	
				$total_tickets_req = $retrieved_data->total_tickets_req;
				$pre_text = $retrieved_data->pre_text;
				$post_text = $retrieved_data->post_text;
				$ticket_price = $retrieved_data->ticket_price;
			}
		}
		$output .= "</ul><div class='rafflerow'>";
		$table_name = $wpdb->prefix . "cj_raffle_tickets"; 
	
		$retrieved_data = $wpdb->get_results( "select tickets.ticketid, tickets.email from " . $table_name . " tickets where raffleid=" .$atts['raffleno'] );	
		//if ($retrieve_data) {
		for ($i = 1; $i <= $total_tickets_req; $i++) {
			$found = False;
			for ($ii = 0; $ii <= sizeof($retrieved_data)-1;$ii++) {
				if ($i == $retrieved_data[$ii]->ticketid) {
					$output .= "<div class='raffleticket-sold' id='$i' >" . $pre_text . "-" . $i . "-" . $post_text . "</div>";
					$found = True;
					break;
				}
			}
			if ($found == False) {
				$output .= "<div class='raffleticket' id='$i'>" . $pre_text . "-" . $i . "-" . $post_text . "</div>";
			}
		}
	
		$paypal_checkout_url = WP_CART_LIVE_PAYPAL_URL;
		if ($options[checkbox_field_0]) {
			$paypal_checkout_url = WP_CART_SANDBOX_PAYPAL_URL;
		}
	

		$output .= <<<EOT
		</div><div class="raffletextblock">
			</br>Ticket Price = £<span class="ticketprice">$ticket_price</span>
			</br>Total Tickets Selected = <span class="ticketsselected">0</span>
			</br>Total Cost = £<span class="totalcost">0</span></div>
		</br>
		</br>
		<form class="paypal" action='$paypal_checkout_url' method="post" id="paypal_form" target="_self">
		<input type="hidden" name="business" value="$options[text_field_1]">
		<input type="hidden" name="cmd" value="_cart">
		 <input type="hidden" name="upload" value="1">
        <input type="hidden" name="lc" value="UK" />
		<input type="hidden" name="currency_code" value="GBP" />
		<input type="hidden" name="custom" value='$raffleid' />
		<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
		<input type='hidden' name='notify_url' value='$options[text_field_3]'>
		<!-- <input type="hidden" name="no_shipping" value="1"> -->
		<input type='hidden' name='cancel_return' value='$options[text_field_5]'>
        <input type='hidden' name='return' value='$options[text_field_4]'>
		<input type="submit" name="submit" value="Submit Payment"/>
		</form>
		</div>
EOT;
		$tickettext = array('pre_text'=> $pre_text,'post_text'=> $post_text);
	
		wp_localize_script( 'raffle-selector', 'tickettext', $tickettext );

		wp_enqueue_script('raffle-selector');

		return $output;
	
} 





/**
 * Trick to update plugin database, see docs
 

 function cltd_example_update_db_check()
{
    global $cltd_example_db_version;
    if (get_site_option('cltd_example_db_version') != $cltd_example_db_version) {
        cltd_example_install();
    }
}
add_action('plugins_loaded', 'cltd_example_update_db_check');
*/

/**
 * PART 2. Defining Custom Table List
 * ============================================================================
 *
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 *
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wordpress.org/extend/plugins/custom-list-table-example/
 */
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
* Raffle Tickets Class based on WP_List_Table*
*/

class Raffle_Tickets_Sold_List extends WP_List_Table
{
	function __construct()
    {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'raffleticket-sold',
            'plural' => 'raffletickets-sold',
        ));
    }
	
	 function column_default($item, $column_name)
    {
        switch ( $column_name ) {
			case 'ticketid':
			case 'raffleid':
			case 'txnid':
			case 'ticket':
			case 'name':
			case 'address':
			case 'email':
			case 'purchase_date':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
    }
	
	
	 function column_id($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &raffle=2
        $actions = array(
            //'edit' => sprintf('<a href="?page=raffleticketssold_form&id=%s">%s</a>', $item['id'], __('Edit', 'cltd_example')),
            'delete' => sprintf('<a href="?page=%s&action=delete&ticketid=%s">%s</a>', $_REQUEST['page'], $item['ticketid'], __('Delete', 'cltd_example')),
        );
        return sprintf('%s %s',
            $item['ticketid'],
            $this->row_actions($actions)
        );
    }
	
	function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="ticketid[]" value="%s" />',
            $item['ticketid']
        );
    }
	
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'ticketid'	  => __( 'Ticket Id', 'sp'),
		        'raffleid' => __('Raffle Id', 'sp'),
			'txnid' => __('Transaction','sp'),
			'ticket' => __('Ticket','sp'),
			'name'  => __('Name','sp'),
			'address'  => __('Address','sp'),
			'email' => __('email','sp'),
		        'purchase_date' => __('Purchase Date','sp')
		];

		return $columns;
	}
	
	public function get_sortable_columns() {
		$sortable_columns = array(
			'ticketid' => array( 'ticketid', true ),
			'raffleid' => array( 'raffleid', true ),
			'email' =>  array( 'email',true ),
			'purchase_date' => array('purchase_date', true )
		);

		return $sortable_columns;
	}
    
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete',
			'export' => 'Export'
        );
        return $actions;
    }
	
	 function process_bulk_action()
    {
		error_log('GOT HERE');
        global $wpdb;
        $table_name = $wpdb->prefix . 'cj_raffle_tickets'; // do not forget about tables prefix
	
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['ticketid']) ? $_REQUEST['ticketid'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);
            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE ticketid IN($ids)");
            }
        }
		if ('export' === $this->current_action()) {
				//csv_export();
				//ob_start();

			$csv_headers = array();
			//$csv_headers[] = 'Date';
			//$csv_headers[] = 'Name';
			//$csv_headers[] = 'Email';
	
				
				
			
			//Assigning the variable to store all future CSV file's data
			$result = $wpdb->get_results("SHOW COLUMNS FROM " . $table_name . "");   //Displays all COLUMN NAMES under 'Field' column in records	 returned

			if (count($result) > 0) {
				foreach($result as $row) {
					$csv_headers[] =  $row->Field;
				}
				$output2 = substr($output2, 0, -1);               //Removing the last separator, because thats how CSVs work
			}
			
			//ob_start();
			//echo "test";
			//flush();
			$output2 = fopen('php://output', 'w');	
			ob_end_clean();
			fputcsv($output2, $csv_headers);

			
			$ids = isset($_REQUEST['ticketid']) ? $_REQUEST['ticketid'] : array();
		    if (is_array($ids)) $ids = implode(',', $ids);
		    if (!empty($ids)) {
				$values = $wpdb->get_results("SELECT * FROM $table_name where ticketid IN ($ids)");       //This here
			}

			foreach ($values as $rowr) {
				//Getting rid of the keys and using numeric array to get values
				fputcsv($output2, array_values((array) $rowr));

			}
			// Download the file	
			error_log("Got this far:)");
				
			//error_log($output);	
				
			$file = "cj_raffle_tickets";
			$filename = $file."_".date("Y-m-d");
			header("Content-Type: text/csv");
			header("Content-Disposition: attachment; filename=\"report.csv\";" );
			header("Pragma: public");
			header("Expires: 0");
			
			fclose($output2);

			//ob_end_flush();
			//ob_end_flush();
			
			exit;

        }
		
    }
		
	
	function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cj_raffle_tickets'; // do not forget about tables prefix
        $per_page = 20; // constant, how much records will be shown per page
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);
        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();
        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(ticketid) FROM $table_name");
        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'ticketid';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
	
	
}


/**
 * Custom_Table_Example_List_Table class that will display our custom table
 * records in nice table
 */
class Custom_Table_Example_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'raffle',
            'plural' => 'raffles',
        ));
    }
	
	
    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        switch ( $column_name ) {
			case 'id':
			case 'pre_text':
			case 'post_text':
			case 'total_tickets_req':
			case 'ticket_price':
			case 'raffle_desc':
			case 'raffle_img':
			case 'run_date':
			case 'winning_no':
			case 'completed':			
				return $item[ $column_name ];
			case 'tickets_sold':
				return get_sold_tickets ($item);
			case 'shortcode' :
				return "[raffle-tickets raffleno=" . $item['id']  . "]";
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
    }
    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_id($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &raffle=2
        $actions = array(
            'edit' => sprintf('<a href="?page=raffles_form&id=%s">%s</a>', $item['id'], __('Edit', 'cltd_example')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'cltd_example')),
        );
        return sprintf('%s %s',
            $item['id'],
            $this->row_actions($actions)
        );
    }
    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'id'	  => __( 'id', 'sp'),
           'pre_text' => __('Pre Text', 'sp'),
			'post_text' => __('Post Text','sp'),
			'total_tickets_req' => __('Tickets Required','sp'),
                        'tickets_sold' => __('Sold','sp'),
                        'ticket_price' => __('Price','sp'),
                        'raffle_desc' => __('Description','sp'),
						'raffle_img' => __('Image URL','sp'),
                        'run_date' => __('Run Date','sp'),
                        'winning_no' => __('Winning No.','sp'),
                        'completed' => __('Completed','sp'),
						'shortcode' => __('Shortcode','sp')
		];

		return $columns;
	}
    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    public function get_sortable_columns() {
		$sortable_columns = array(
			'id' => array( 'id', true ),
			'run_date' => array( 'run_date', true ),
			'completed' =>  array( 'completed',true )
		);

		return $sortable_columns;
	}
    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }
    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cj_raffle_tbl'; // do not forget about tables prefix
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);
            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }
    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cj_raffle_tbl'; // do not forget about tables prefix
        $per_page = 5; // constant, how much records will be shown per page
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);
        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();
        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}



function csv_export() {
    // Check for current user privileges 
    if( !current_user_can( 'manage_options' ) ){ return false; }
    // Check if we are in WP-Admin
    if( !is_admin() ){ return false; }
    // Nonce Check
    $nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
  //  if ( ! wp_verify_nonce( $nonce, 'download_csv' ) ) {
   //     die( 'Security check error' );
   // }
    
    $domain = $_SERVER['SERVER_NAME'];
    $filename = 'users-' . $domain . '-' . time() . '.csv';
    
    $header_row = array(
        'Email',
        'Name'
    );
    $data_rows = array();
    global $wpdb;
	$table_name = $wpdb->prefix . 'cj_raffle_tickets';
    $sql = 'SELECT * FROM ' . $table_name;
    $users = $wpdb->get_results( $sql, 'ARRAY_A' );
    foreach ( $users as $user ) {
        $row = array(
            $user['ticketid'],
            $user['raffleid']
        );
        $data_rows[] = $row;
    }
    $fh = @fopen( 'php://output', 'w' );
    fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
	ob_start();
	echo 'test';
	flush();
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Content-Description: File Transfer' );
    header( 'Content-type: text/csv' );
    header( "Content-Disposition: attachment; filename={$filename}" );
    header( 'Expires: 0' );
    header( 'Pragma: public' );
    fputcsv( $fh, $header_row );
    foreach ( $data_rows as $data_row ) {
        fputcsv( $fh, $data_row );
    }
    fclose( $fh );
    
    ob_end_flush();
    
    wp_die();
}

/**
 * PART 3. Admin page
 * ============================================================================
 *
 * In this part you are going to add admin page for custom table
 *
 * http://codex.wordpress.org/Administration_Menus
 */
/**
 * admin_menu hook implementation, will add pages to list raffles and to add new one
 */
function cltd_example_admin_menu()
{
	if (is_admin()) {
		add_menu_page(__('Raffle Tickets', 'cltd_example'), __('Raffle Tickets', 'cltd_example'), 'edit_pages', 'raffles', 'cltd_example_raffles_page_handler');
	
		add_submenu_page('raffles', __('All Raffles', 'cltd_example'), __('All Raffles', 'cltd_example'), 'edit_pages', 'raffles', 'cltd_example_raffles_page_handler');
	
    // add new will be described in next part
		add_submenu_page('raffles', __('Add/Edit Raffle', 'cltd_example'), __('Add new', 'cltd_example'), 'edit_pages', 'raffles_form', 'cltd_example_raffles_form_page_handler');
	
		add_submenu_page(
        'raffles',
        'Raffle Settings',
        'Raffle Settings',
        'manage_options',
        'raffle-settings',
        'options_page'
		);
	
		add_submenu_page('raffles', __('Sold Tickets', 'raffleticketssold'), __('Sold Tickets', 'raffleticketssold'), 'activate_plugins', 'raffle_tickets_sold_page_handler', 'raffle_tickets_sold_page_handler');
	}
}


add_action('admin_menu', 'cltd_example_admin_menu');


function settings_init(  ) { 
	

	register_setting( 'pluginPage', 'settings' );

	add_settings_section(
		'pluginPage_section', 
		__( 'Raffle Tickets Settings', 'wordpress' ), 
		'settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'chkPaypal_sandbox', 
		__( 'use Paypal Sandbox', 'wordpress' ), 
		'checkbox_field_0_render', 
		'pluginPage', 
		'pluginPage_section' 
	);

	add_settings_field( 
		'txtPaypal_email', 
		__( 'Paypal email address', 'wordpress' ), 
		'text_field_1_render', 
		'pluginPage', 
		'pluginPage_section' 
	);

	add_settings_field( 
		'chkPaypal_IPN', 
		__( 'Use Paypal IPN', 'wordpress' ), 
		'checkbox_field_2_render', 
		'pluginPage', 
		'pluginPage_section' 
	);

	add_settings_field( 
		'txtNotify_URL', 
		__( 'Paypal IPN URL', 'wordpress' ), 
		'text_field_3_render', 
		'pluginPage', 
		'pluginPage_section' 
	);

	add_settings_field( 
		'txtReturn_URL', 
		__( 'Paypal Success Return URL', 'wordpress' ), 
		'text_field_4_render', 
		'pluginPage', 
		'pluginPage_section' 
	);

	add_settings_field( 
		'txtCancel_URL', 
		__( 'Paypal Cancel URL', 'wordpress' ), 
		'text_field_5_render', 
		'pluginPage', 
		'pluginPage_section' 
	);

}

add_action( 'admin_init', 'settings_init' );

function checkbox_field_0_render(  ) { 

	$options = get_option( 'settings' );
	?>
	<input type='checkbox' name='settings[checkbox_field_0]' <?php checked( $options['checkbox_field_0'], 1 ); ?> value='1'>
	<?php

}


function text_field_1_render(  ) { 

	$options = get_option( 'settings' );
	?>
	<input type='text' name='settings[text_field_1]' value='<?php echo $options['text_field_1']; ?>'> <b>(Business account required for IPN)</b>
	<?php

}


function checkbox_field_2_render(  ) { 

	$options = get_option( 'settings' );
	?>
	<input type='checkbox' name='settings[checkbox_field_2]' <?php checked( $options['checkbox_field_2'], 1 ); ?> value='1'>
	<?php

}

function text_field_3_render(  ) { 

	$options = get_option( 'settings' );
	?>
	<input type='text' name='settings[text_field_3]' value='<?php echo plugin_dir_url( __FILE__ ) . "includes/gateways/ipn.php"; ?>'>
	<?php

}

function text_field_4_render(  ) { 

	$options = get_option( 'settings' );
	?>
	<input type='text' name='settings[text_field_4]' value='<?php echo $options['text_field_4']; ?>'>
	<?php

}

function text_field_5_render(  ) { 

	$options = get_option( 'settings' );
	?>
	<input type='text' name='settings[text_field_5]' value='<?php echo $options['text_field_5']; ?>'>
	<?php

}


function settings_section_callback(  ) { 

	echo __( 'Complete the following options for use with Paypal', 'wordpress' );

}


function options_page(  ) { 

	?>
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

	add_action( 'admin_menu', 'options_page' );
}



/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function cltd_example_raffles_page_handler()
{
    global $wpdb;
    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();
    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'cltd_example'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Raffle Tickets', 'cltd_example')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=raffles_form');?>"><?php _e('Add new', 'cltd_example')?></a>
    </h2>
    <?php echo $message; ?>
	
    <form id="raffles-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}


function raffle_tickets_sold_page_handler()
{
    global $wpdb;
    $table = new Raffle_Tickets_Sold_List();
    $table->prepare_items();
    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'cltd_example'), count($_REQUEST['ticketid'])) . '</p></div>';
    }
    ?>
<div class="wrap">


    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Sold Tickets', 'cltd_example')?></br></br>
<!--<a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=raffles_form');?>"><?php _e('Add new', 'cltd_example')?></a>
    </h2>
-->
    <?php echo $message; ?>

    <form id="raffletickssold-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 *
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/selected
 */
/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
 
 
function get_sold_tickets ($item)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'cj_raffle_tickets'; // do not forget about tables prefix
    $results = $wpdb->get_var("SELECT count(*) FROM $table_name WHERE raffleid =" . $item['id']);
	return $results;
	//return $item['id'];
      
}
	
 
function cltd_example_raffles_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'cj_raffle_tbl'; // do not forget about tables prefix
    $message = '';
    $notice = '';
    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'raffle_desc' => '',
        'raffle_img' => '',
		'pre_text' => '',
        'post_text' => '',
        'ticket_price' => 1,
		'total_tickets_req' => 10,
		'winning_no' => '',
		'run_date' => '',
		'completed' => '0'
    );
	
	
    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
		//var_dump($default);
		//var_dump($_REQUEST);
		//var_dump($item);
		//die();
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = cltd_example_validate_raffle($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'cltd_example');
                } else {
                    $notice = __('There was an error while saving item', 'cltd_example');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'cltd_example');
                } else {
                    $notice = __('There was an error while updating item', 'cltd_example');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'cltd_example');
            }
        }
    }
    // here we adding our custom meta box
    add_meta_box('raffles_form_meta_box', 'Raffle Details', 'cltd_example_raffles_form_meta_box_handler', 'raffle', 'normal', 'default');
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Raffle Form', 'cltd_example')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=raffles');?>"><?php _e('back to list', 'cltd_example')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('raffle', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'cltd_example')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}
/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function cltd_example_raffles_form_meta_box_handler($item)
{
	//var_dump($item);
    ?>
<script>
        jQuery(document).ready(function($){
             $("#random").click(function(){
			// var today = moment().format('DD-MM-YYYY');
             var number = 1 + Math.floor(Math.random()*(<?php echo esc_attr($item['total_tickets_req'])?>-1+1));//Change the 6 to be the number of random numbers you want to generate. So if you want 100 numbers, change to 100
                 $("#winning_no").val(number); 
				// $("#run_date")

        });

        });
</script>
<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Raffle Description', 'cltd_example')?></label>
        </th>
        <td>
            <textarea id="raffle_desc" name="raffle_desc" rows="5" 
                   size="50" class="code" placeholder="<?php _e('Raffle Description', 'cltd_example')?>" required><?php echo esc_attr($item['raffle_desc'])?></textarea>
        </td>
    </tr>
	<tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Raffle Image', 'cltd_example')?></label>
        </th>
        <td>
            <input id="raffle_img" name="raffle_img" type="text" style="width: 50%" value="<?php echo esc_attr($item['raffle_img'])?>"
                   size="50" class="code" placeholder="<?php _e('Raffle Image', 'cltd_example')?>">
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="pretext"><?php _e('Pre Text', 'cltd_example')?></label>
        </th>
        <td>
            <input id="pre_text" name="pre_text" type="text" style="width: 25%" value="<?php echo esc_attr($item['pre_text'])?>"
                   size="50" class="code" placeholder="<?php _e('Pre Ticket Text', 'cltd_example')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="age"><?php _e('Post Text', 'cltd_example')?></label>
        </th>
        <td>
            <input id="post_text" name="post_text" type="text" style="width: 25%" value="<?php echo esc_attr($item['post_text'])?>"
                   size="50" class="code" placeholder="<?php _e('Post Ticket Text', 'cltd_example')?>" required>
        </td>
    </tr>
	 <tr class="form-field">
        <th valign="top" scope="row">
            <label for="ticketprice"><?php _e('Ticket Price', 'cltd_example')?></label>
        </th>
        <td>
            <input id="ticket_price" name="ticket_price" type="number" style="width: 10%" value="<?php echo esc_attr($item['ticket_price'])?>"
                   size="50" class="code" placeholder="<?php _e('Ticket Price', 'cltd_example')?>" required>
        </td>
    </tr>
	 <tr class="form-field">
        <th valign="top" scope="row">
            <label for="ticketsreq"><?php _e('Tickets Required', 'cltd_example')?></label>
        </th>
        <td>
            <input id="total_tickets_req" name="total_tickets_req" type="number" style="width: 10%" value="<?php echo esc_attr($item['total_tickets_req'])?>"
                   size="50" class="code" placeholder="<?php _e('Total Tickets Required', 'cltd_example')?>">
        </td>
    </tr>
	<tr class="form-field">
        <th valign="top" scope="row">
            <label for="winning_no"><?php _e('Winning No.', 'cltd_example')?></label>
        </th>
        <td>
            <input id="winning_no" name="winning_no" type="text" style="width: 10%" value="<?php echo esc_attr($item['winning_no'])?>"
                   size="50" class="code" placeholder="<?php _e('Winning No.', 'cltd_example')?>">   <button class="button" type="button" name="buttonpassvalue" id="random">Get Random Number</button> 
        </td>
    </tr>
	<tr class="form-field">
		<th valign="top" scope="row">
            <label for="run_date"><?php _e('Run Date', 'cltd_example')?></label>
        </th>
        <td>
            <input id="run_date" name="run_date" type="text" style="width: 10%" value="<?php echo esc_attr($item['run_date'])?>"
			size="20" class="code" placeholder="<?php _e('Run Date', 'cltd_example')?>"> (YYYY-MM-DD)
        </td>
    </tr>
	<tr class="form-field">
		<th valign="top" scope="row">
            <label for="completed"><?php _e('Completed?', 'cltd_example')?></label>
        </th>
        <td>
            <input id="completed" name="completed" type="checkbox" value=1 <?php checked( $item['completed'], 1 ); ?> size="20" class="code" placeholder="<?php _e('Completed?', 'cltd_example')?>">
        </td>
    </tr>
    </tbody>
</table>
<?php 
}


/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function cltd_example_validate_raffle($item)
{
    $messages = array();
    if (empty($item['ticket_price'])) $messages[] = __('Ticket Price is required', 'cltd_example');
	if (empty($item['raffle_desc'])) $messages[] = __('Raffle Description is required', 'cltd_example');
	if (empty($item['pre_text'])) $messages[] = __('Raffle Prex Text is required', 'cltd_example');
	if (empty($item['post_text'])) $messages[] = __('Raffle Post Text is required', 'cltd_example');
    //if (!ctype_digit($item['age'])) $messages[] = __('Age in wrong format', 'cltd_example');
    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...
    if (empty($messages)) return true;
    return implode('<br />', $messages);
}
/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == cltd_example
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function cltd_example_languages()
{
    load_plugin_textdomain('cltd_example', false, dirname(plugin_basename(__FILE__)));
}
add_action('init', 'cltd_example_languages');

?>
