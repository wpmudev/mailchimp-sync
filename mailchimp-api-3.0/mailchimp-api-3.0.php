<?php

/**
 * A wrapper method for API calls
 *
 * @param string $method
 * @param string $path
 * @param array $args
 *
 * @return WP_Error|array
 */
function mailchimp_api_30_make_request( $method, $path, $args = array() ) {
	$api = mailchimp_load_api_30();
	if ( is_wp_error( $api ) ) {
		return $api;
	}

	try {
		switch ( $method ) {
			case 'post': {
				$result = $api->post( $path, $args );
				break;
			}
			case 'delete': {
				$result = $api->delete( $path, $args );
				break;
			}
			case 'patch': {
				$result = $api->patch( $path, $args );
				break;
			}
			default: {
				$result = $api->get( $path, $args );
			}
		}
	}
	catch ( Exception $e ) {
		return new WP_Error( $e->getCode(), $e->getMessage() );
	}

	if ( ! $api->success() ) {
		$response = $api->getLastResponse();
		$status = '';
		if ( ! empty( $response['headers']['http_code'] ) ) {
			$status = $response['headers']['http_code'];
		}

		// Log here

		return new WP_Error( $status, $api->getLastError() );
	}

	return $result;
}

/**
 * A wrapper method for Batch API calls
 *
 * @param string $method
 * @param string $path
 * @param array $args
 *
 * @return WP_Error|array
 */
function mailchimp_api_30_make_batch_request( $method, $path, $args = array() ) {
	$api = mailchimp_load_api_30();
	if ( is_wp_error( $api ) ) {
		return $api;
	}

	$batch = $api->new_batch();
	$batch::$operation_number++;

	try {
		switch ( $method ) {
			case 'post': {
				$result = $batch->post( 'op_' . $batch::$operation_number, $path, $args );
				break;
			}
			case 'delete': {
				$result = $batch->delete( 'op_' . $batch::$operation_number, $path, $args );
				break;
			}
			case 'patch': {
				$result = $batch->patch( 'op_' . $batch::$operation_number, $path, $args );
				break;
			}
			default: {
				$result = $batch->get( 'op_' . $batch::$operation_number, $path, $args );
			}
		}
	}
	catch ( Exception $e ) {
		return new WP_Error( $e->getCode(), $e->getMessage() );
	}

	if ( ! $api->success() ) {
		$response = $api->getLastResponse();
		$status = '';
		if ( ! empty( $response['headers']['http_code'] ) ) {
			$status = $response['headers']['http_code'];
		}

		// Log here

		return new WP_Error( $status, $api->getLastError() );
	}

	return $result;
}

/**
 * Load the Mailchimp API
 *
 * @return MailChimp_Sync_Mailchimp|WP_Error Object
 */
function mailchimp_load_api_30() {
	global $mailchimp_sync;

	include_once( 'Mailchimp/Mailchimp.php' );
	include_once( 'Mailchimp/Batch.php' );
	include_once( 'Mailchimp/Webhook.php' );

	if ( is_a( $mailchimp_sync, 'MailChimp_Sync_Mailchimp' ) ) {
		return $mailchimp_sync;
	}

	$mailchimp_apikey = get_site_option('mailchimp_apikey');

	try {
		$api = new MailChimp_Sync_Mailchimp( $mailchimp_apikey );
	}
	catch ( Exception $e ) {
		return new WP_Error( $e->getCode(), $e->getMessage() );
	}

	$mailchimp_sync = $api;

	return $mailchimp_sync;
}

/**
 * Return a list of API options like timeout, ssl verify...
 *
 * @return array
 */
function mailchimp_api_options() {
	$options = array(
		'timeout' => apply_filters( 'mailchimp_sync_api_timeout', false )
	);

	$ssl_verifypeer = apply_filters( 'mailchimp_sync_api_ssl_verifypeer', false );
	if ( $ssl_verifypeer ) {
		$options['ssl_verifypeer'] = $ssl_verifypeer;
	}

	$ssl_verifyhost = apply_filters( 'mailchimp_sync_api_ssl_verifyhost', false );
	if ( $ssl_verifyhost ) {
		$options['ssl_verifyhost'] = $ssl_verifyhost;
	}

	$ssl_cainfo = apply_filters( 'mailchimp_sync_api_ssl_cainfo', false );
	if ( $ssl_cainfo ) {
		$options['ssl_cainfo'] = $ssl_cainfo;
	}

	$debug = apply_filters( 'mailchimp_sync_api_debug', false );
	if ( $debug ) {
		$options['debug'] = $debug;
	}

	return $options;
}

//
//class WPMUDEV_Mailchimp_Sync_API_30 extends Mailchimp {
//
//	/**
//     * CURLOPT_SSL_VERIFYPEER setting
//     * @var  bool
//     */
//    public $ssl_verifypeer = true;
//    /**
//     * CURLOPT_SSL_VERIFYHOST setting
//     * @var  bool
//     */
//    public $ssl_verifyhost = 2;
//    /**
//     * CURLOPT_CAINFO
//     * @var  string
//     */
//    public $ssl_cainfo = null;
//
//    /**
//     * Timeout setting
//     * @var  bool
//     */
//    public $timeout = 600;
//
//	public function __construct( $apikey, $opts ) {
//		if( ! $apikey )
//			$apikey = getenv('MAILCHIMP_APIKEY');
//
//		if( ! $apikey )
//			throw new Mailchimp_Error( 'You must provide a MailChimp API key' );
//
//		$this->apikey = $apikey;
//        $dc           = "us1";
//
//        if ( strstr( $this->apikey, "-" ) ) {
//            list( $key, $dc ) = explode( "-", $this->apikey, 2 );
//            if ( ! $dc )
//                $dc = "us1";
//        }
//
//        $this->root = str_replace('https://api', 'https://' . $dc . '.api', $this->root);
//        $this->root = rtrim($this->root, '/') . '/';
//
//        $defaults = array(
//        	'debug' => false,
//        	'ssl_verifypeer' => true,
//        	'ssl_verifyhost' => 2,
//        	'ssl_cainfo' => null
//        );
//
//        $opts = wp_parse_args( $opts, $defaults );
//
//        $this->debug = $opts['debug'];
//
//        if ( isset( $opts['timeout'] ) )
//        	$this->timeout = absint( $opts['timeout'] );
//
//        $this->ssl_verifypeer = $opts['ssl_verifypeer'];
//        $this->ssl_verifyhost = $opts['ssl_verifyhost'];
//        $this->ssl_cainfo = $opts['ssl_cainfo'];
//
//        $this->folders = new Mailchimp_Folders($this);
//        $this->templates = new Mailchimp_Templates($this);
//        $this->users = new Mailchimp_Users($this);
//        $this->helper = new Mailchimp_Helper($this);
//        $this->mobile = new Mailchimp_Mobile($this);
//        $this->conversations = new Mailchimp_Conversations($this);
//        $this->ecomm = new Mailchimp_Ecomm($this);
//        $this->neapolitan = new Mailchimp_Neapolitan($this);
//        $this->lists = new Mailchimp_Lists($this);
//        $this->campaigns = new Mailchimp_Campaigns($this);
//        $this->vip = new Mailchimp_Vip($this);
//        $this->reports = new Mailchimp_Reports($this);
//        $this->gallery = new Mailchimp_Gallery($this);
//        $this->goal = new Mailchimp_Goal($this);
//	}
//
//	public function call( $url, $params ) {
//		$params['apikey'] = $this->apikey;
//
//		$params = json_encode($params);
//
//        $args = array(
//            'timeout'     => $this->timeout,
//            'user-agent'  => 'MailChimp-PHP/2.0.4',
//            'blocking'    => true,
//            'headers'     => array( 'Content-Type' => 'application/json' ),
//            'body'        => $params,
//            'sslverify'   => $this->ssl_verifypeer,
//            'filename'    => null
//        );
//
//        $args = apply_filters( 'mailchimp_request_args', $args );
//
//        if ( $this->ssl_cainfo )
//            $args['sslcertificates'] = $this->ssl_cainfo;
//
//        $response = wp_remote_post( $this->root . $url . '.json', $args );
//
//        if ( is_wp_error( $response ) ) {
//            $this->log_errors(
//                array( array(
//                    'code' => $response->get_error_code(),
//                    'message' => $response->get_error_message()
//                ) )
//            );
//            return $response;
//        }
//
//        $response_body = wp_remote_retrieve_body( $response );
//
//        $result = json_decode($response_body, true);
//
//        if( floor( $response['response']['code'] / 100 ) >= 4 ) {
//            $error = $this->castError($result);
//            $this->log_errors(
//                array( array(
//                    'code' => $error->getCode(),
//                    'message' => $error->getMessage()
//                ) )
//            );
//            return new WP_Error( $error->getCode(), $error->getMessage() );
//        }
//
//        return $result;
//	}
//
//	public function log_errors( $errors ) {
//        if ( ! is_array( $errors ) )
//            $errors = array( $errors );
//
//        $current_log = get_site_option( 'mailchimp_error_log' );
//        $new_log = array();
//
//
//        foreach ( $errors as $error ) {
//
//            $code = isset( $error['code'] ) ? $error['code'] : 0;
//            $message = isset( $error['message'] ) ? $error['message'] : '';
//            $email = isset( $error['email'] ) ? $error['email'] : '';
//            $date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp' ) );
//
//            $new_log[] = compact( 'code', 'message', 'email', 'date' );
//
//        }
//
//
//        if ( $current_log ) {
//
//            $new_log = array_merge( $current_log, $new_log );
//
//            // We'll only saved the last X lines of the log
//            $count = count( $new_log );
//            if ( $count > MAILCHIMP_MAX_LOG_LINES ) {
//                $new_log = array_slice( $new_log, $count - $offset - 1 );
//            }
//
//        }
//
//        update_site_option( 'mailchimp_error_log', $new_log );
//    }
//}