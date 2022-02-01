<?php

// HTML template for admin notice
function ts_yte_admin_notice_html( $message = '', $priority = 'updated', $screen = '' ) {
	// Display admin notice on specific screen
	if ( ! empty( $screen ) ) {
		global $pagenow;

		if ( is_array( $screen ) ) {
			if ( false === in_array( $pagenow, $screen ) )
				return;
		} else {
			if ( $pagenow !== $screen )
				return;
		}

	} ?>
<div id="message" class="<?php echo esc_attr( $priority ); ?>">
	<p><?php echo esc_html( $message ); ?></p>
</div>
<?php

}

function ts_yte_template_header( $title = '', $icon = 'woocommerce' ) { ?>
<div id="woo-pi" class="wrap">
	<div id="icon-<?php echo esc_attr( $icon ); ?>" class="icon32 icon32-woocommerce-importer"><br /></div>
	<h2><?php echo esc_html( $title ); ?></h2>
<?php

}

function ts_yte_template_footer() { ?>
</div>
<!-- .wrap -->
<?php

}

// Add Product Import to WordPress Administration menu
function ts_yte_admin_menu() {
	$page = add_submenu_page( 'woocommerce', __( 'TicketSonic', 'ts_yte' ), __( 'TicketSonic', 'ts_yte' ), 'manage_woocommerce', 'ts_yte', 'ts_yte_html_page' );
	add_action( 'admin_print_styles-' . $page, 'ts_yte_enqueue_scripts' );
}
add_action( 'admin_menu', 'ts_yte_admin_menu', 11 );

function ts_yte_enqueue_scripts( $hook ) {
	// Simple check that WooCommerce is activated
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
	}

	wp_enqueue_style( 'ts_yte_styles', plugins_url( '/templates/admin/import.css', TS_YTE_RELPATH ) );
	wp_enqueue_script( 'ts_yte_scripts', plugins_url( '/templates/admin/import.js', TS_YTE_RELPATH ), array( 'jquery' ) );
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'woo_vm_styles', plugins_url( '/templates/admin/woocommerce-admin_dashboard_vm-plugins.css', TS_YTE_RELPATH ) );
}

function ts_yte_admin_active_tab( $tab_name = null, $tab = null ) {
	if ( isset( $_GET['tab'] ) && ! $tab )
		$tab = $_GET['tab'];
	else if ( ! isset( $_GET['tab'] ) && ts_yte_get_option( 'skip_overview', false ) )
		$tab = 'import';
	else
		$tab = 'overview';

	$output = '';
	if ( isset( $tab_name ) && $tab_name ) {
		if ( $tab_name === $tab )
			$output = ' nav-tab-active';
	}
	echo $output;
}

function ts_yte_tab_template( $tab = '' ) {
	global $import;

	if ( ! $tab)
		$tab = 'overview';

	switch ( $tab ) {
		case 'overview':
			$skip_overview = ts_yte_get_option( 'skip_overview', false );
			break;

		case 'import':
			if ( isset( $_GET['import'] ) && TS_YTE_PREFIX === $_GET['import'] )
				$url = 'import';
			if ( isset( $_GET['page'] ) && TS_YTE_PREFIX === $_GET['page'] )
				$url = 'page';
			break;

		case 'settings':
			$api_key                 = ts_yte_get_option( 'api_key', '' );
			$api_userid              = ts_yte_get_option( 'api_userid', '' );
			$ticket_info_endpoint    = ts_yte_get_option( 'ticket_info_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/list' );
			$event_info_endpoint     = ts_yte_get_option( 'event_info_endpoint', 'https://www.ticketsonic.com:9507/v1/event/list' );
			$new_event_endpoint      = ts_yte_get_option( 'new_event_endpoint', 'https://www.ticketsonic.com:9507/v1/event/new' );
			$new_ticket_endpoint     = ts_yte_get_option( 'new_ticket_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/new' );
			$change_ticket_endpoint  = ts_yte_get_option( 'change_ticket_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/edit' );
			$change_event_endpoint   = ts_yte_get_option( 'change_event_endpoint', 'https://www.ticketsonic.com:9507/v1/event/edit' );
			$external_order_endpoint = ts_yte_get_option( 'external_order_endpoint', 'https://www.ticketsonic.com:9507/v1/order/new' );
			$event_id                = ts_yte_get_option( 'event_id', '' );
			$email_subject           = ts_yte_get_option( 'email_subject', 'Ticket #[ticket_number] - [ticket_title] for the Your Event is ready' );
			$email_body              = ts_yte_get_option(
				'email_body',
				'
				<html lang="en">
					<head>
						<style type="text/css">
						table {
								border-spacing: 0;
						}
						td.black-square {
							background-color: black;
							width: 2px;
							height: 4px;
						}

						td.white-square {
							background-color: white;
							width: 2px;
							height: 4px;
						}
						</style>
					</head>
					<body style="width: 100%;margin: 50px;padding: 0px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #dbe5ea;">
						<div style="width: 500px; margin: auto;">
							<div id="header">
								<div style="background-color: 537895; border-top-left-radius: 5px;border-top-right-radius: 5px; height: 50px; text-align: center;">
								</div>
							</div>
							<div id="body" style="background: white; padding: 10px;">
								<div style="float: left; height: 140px; margin-right: 20px;;">
									[ticket_qr]
								</div>
								<div style="height: 140px">
									<div style="margin: 10px">
										[ticket_title]
									</div>
									<div style="margin: 10px">
										[ticket_description]
									</div>
									<div style="margin: 10px">
										[ticket_price]
									</div>
								</div>
							</div>
							<div id="footer" style="border-top: 1px solid lightgray;">
								<div style="background-color: white; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px; color: gray; font-family: Helvetica, Arial, sans-serif; font-size: 12px; height: 50px; line-height: 50px; text-align: center;">©2022 Demo Conference</div>
							</div>
						</div>
					</body>
				</html>'
			);

			break;
	}

	if ( $tab ) {
		if ( file_exists( TS_YTE_PATH . 'templates/admin/tabs-' . $tab . '.php' ) )
			include_once TS_YTE_PATH . 'templates/admin/tabs-' . $tab . '.php';
	}
}
