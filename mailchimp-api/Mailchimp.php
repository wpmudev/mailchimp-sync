<?php

require_once 'Mailchimp/Folders.php';
require_once 'Mailchimp/Templates.php';
require_once 'Mailchimp/Users.php';
require_once 'Mailchimp/Helper.php';
require_once 'Mailchimp/Mobile.php';
require_once 'Mailchimp/Ecomm.php';
require_once 'Mailchimp/Neapolitan.php';
require_once 'Mailchimp/Lists.php';
require_once 'Mailchimp/Campaigns.php';
require_once 'Mailchimp/Vip.php';
require_once 'Mailchimp/Reports.php';
require_once 'Mailchimp/Gallery.php';
require_once 'Mailchimp/Exceptions.php';

class WPMUDEV_Mailchimp_Sync_API {

    /**
     * Placeholder attribute for Mailchimp_Folders class
     *
     * @var Mailchimp_Folders
     * @access public
     */
    var $folders;
    /**
     * Placeholder attribute for Mailchimp_Templates class
     *
     * @var Mailchimp_Templates
     * @access public
     */
    var $templates;
    /**
     * Placeholder attribute for Mailchimp_Users class
     *
     * @var Mailchimp_Users
     * @access public
     */
    var $users;
    /**
     * Placeholder attribute for Mailchimp_Helper class
     *
     * @var Mailchimp_Helper
     * @access public
     */
    var $helper;
    /**
     * Placeholder attribute for Mailchimp_Mobile class
     *
     * @var Mailchimp_Mobile
     * @access public
     */
    var $mobile;
    /**
     * Placeholder attribute for Mailchimp_Ecomm class
     *
     * @var Mailchimp_Ecomm
     * @access public
     */
    var $ecomm;
    /**
     * Placeholder attribute for Mailchimp_Neapolitan class
     *
     * @var Mailchimp_Neapolitan
     * @access public
     */
    var $neapolitan;
    /**
     * Placeholder attribute for Mailchimp_Lists class
     *
     * @var Mailchimp_Lists
     * @access public
     */
    var $lists;
    /**
     * Placeholder attribute for Mailchimp_Campaigns class
     *
     * @var Mailchimp_Campaigns
     * @access public
     */
    var $campaigns;
    /**
     * Placeholder attribute for Mailchimp_Vip class
     *
     * @var Mailchimp_Vip
     * @access public
     */
    var $vip;
    /**
     * Placeholder attribute for Mailchimp_Reports class
     *
     * @var Mailchimp_Reports
     * @access public
     */
    var $reports;
    /**
     * Placeholder attribute for Mailchimp_Gallery class
     *
     * @var Mailchimp_Gallery
     * @access public
     */
    var $gallery;

    /**
     * CURLOPT_SSL_VERIFYPEER setting
     * @var  bool
     */
    public $ssl_verifypeer = true;
    /**
     * CURLOPT_SSL_VERIFYHOST setting
     * @var  bool
     */
    public $ssl_verifyhost = 2;
    /**
     * CURLOPT_CAINFO
     * @var  string
     */
    public $ssl_cainfo = null;

    /**
     * Timeout setting
     * @var  bool
     */
    public $timeout = 600;

    /**
     * the api key in use
     * @var  string
     */
    public $apikey;

    public $root = 'https://api.mailchimp.com/2.0';
    /**
     * whether debug mode is enabled
     * @var  bool
     */
    public $debug = false;

    public static $error_map = array(
        "ValidationError" => "WPMUDEV_Mailchimp_ValidationError_API",
        "ServerError_MethodUnknown" => "WPMUDEV_Mailchimp_ServerError_MethodUnknown_API",
        "ServerError_InvalidParameters" => "WPMUDEV_Mailchimp_ServerError_InvalidParameters_API",
        "Unknown_Exception" => "WPMUDEV_Mailchimp_Unknown_Exception_API",
        "Request_TimedOut" => "WPMUDEV_Mailchimp_Request_TimedOut_API",
        "Zend_Uri_Exception" => "WPMUDEV_Mailchimp_Zend_Uri_Exception_API",
        "PDOException" => "WPMUDEV_Mailchimp_PDOException_API",
        "Avesta_Db_Exception" => "WPMUDEV_Mailchimp_Avesta_Db_Exception_API",
        "XML_RPC2_Exception" => "WPMUDEV_Mailchimp_XML_RPC2_Exception_API",
        "XML_RPC2_FaultException" => "WPMUDEV_Mailchimp_XML_RPC2_FaultException_API",
        "Too_Many_Connections" => "WPMUDEV_Mailchimp_Too_Many_Connections_API",
        "Parse_Exception" => "WPMUDEV_Mailchimp_Parse_Exception_API",
        "User_Unknown" => "WPMUDEV_Mailchimp_User_Unknown_API",
        "User_Disabled" => "WPMUDEV_Mailchimp_User_Disabled_API",
        "User_DoesNotExist" => "WPMUDEV_Mailchimp_User_DoesNotExist_API",
        "User_NotApproved" => "WPMUDEV_Mailchimp_User_NotApproved_API",
        "Invalid_ApiKey" => "WPMUDEV_Mailchimp_Invalid_ApiKey_API",
        "User_UnderMaintenance" => "WPMUDEV_Mailchimp_User_UnderMaintenance_API",
        "Invalid_AppKey" => "WPMUDEV_Mailchimp_Invalid_AppKey_API",
        "Invalid_IP" => "WPMUDEV_Mailchimp_Invalid_IP_API",
        "User_DoesExist" => "WPMUDEV_Mailchimp_User_DoesExist_API",
        "User_InvalidRole" => "WPMUDEV_Mailchimp_User_InvalidRole_API",
        "User_InvalidAction" => "WPMUDEV_Mailchimp_User_InvalidAction_API",
        "User_MissingEmail" => "WPMUDEV_Mailchimp_User_MissingEmail_API",
        "User_CannotSendCampaign" => "WPMUDEV_Mailchimp_User_CannotSendCampaign_API",
        "User_MissingModuleOutbox" => "WPMUDEV_Mailchimp_User_MissingModuleOutbox_API",
        "User_ModuleAlreadyPurchased" => "WPMUDEV_Mailchimp_User_ModuleAlreadyPurchased_API",
        "User_ModuleNotPurchased" => "WPMUDEV_Mailchimp_User_ModuleNotPurchased_API",
        "User_NotEnoughCredit" => "WPMUDEV_Mailchimp_User_NotEnoughCredit_API",
        "MC_InvalidPayment" => "WPMUDEV_Mailchimp_MC_InvalidPayment_API",
        "List_DoesNotExist" => "WPMUDEV_Mailchimp_List_DoesNotExist_API",
        "List_InvalidInterestFieldType" => "WPMUDEV_Mailchimp_List_InvalidInterestFieldType_API",
        "List_InvalidOption" => "WPMUDEV_Mailchimp_List_InvalidOption_API",
        "List_InvalidUnsubMember" => "WPMUDEV_Mailchimp_List_InvalidUnsubMember_API",
        "List_InvalidBounceMember" => "WPMUDEV_Mailchimp_List_InvalidBounceMember_API",
        "List_AlreadySubscribed" => "WPMUDEV_Mailchimp_List_AlreadySubscribed_API",
        "List_NotSubscribed" => "WPMUDEV_Mailchimp_List_NotSubscribed_API",
        "List_InvalidImport" => "WPMUDEV_Mailchimp_List_InvalidImport_API",
        "MC_PastedList_Duplicate" => "WPMUDEV_Mailchimp_MC_PastedList_Duplicate_API",
        "MC_PastedList_InvalidImport" => "WPMUDEV_Mailchimp_MC_PastedList_InvalidImport_API",
        "Email_AlreadySubscribed" => "WPMUDEV_Mailchimp_Email_AlreadySubscribed_API",
        "Email_AlreadyUnsubscribed" => "WPMUDEV_Mailchimp_Email_AlreadyUnsubscribed_API",
        "Email_NotExists" => "WPMUDEV_Mailchimp_Email_NotExists_API",
        "Email_NotSubscribed" => "WPMUDEV_Mailchimp_Email_NotSubscribed_API",
        "List_MergeFieldRequired" => "WPMUDEV_Mailchimp_List_MergeFieldRequired_API",
        "List_CannotRemoveEmailMerge" => "WPMUDEV_Mailchimp_List_CannotRemoveEmailMerge_API",
        "List_Merge_InvalidMergeID" => "WPMUDEV_Mailchimp_List_Merge_InvalidMergeID_API",
        "List_TooManyMergeFields" => "WPMUDEV_Mailchimp_List_TooManyMergeFields_API",
        "List_InvalidMergeField" => "WPMUDEV_Mailchimp_List_InvalidMergeField_API",
        "List_InvalidInterestGroup" => "WPMUDEV_Mailchimp_List_InvalidInterestGroup_API",
        "List_TooManyInterestGroups" => "WPMUDEV_Mailchimp_List_TooManyInterestGroups_API",
        "Campaign_DoesNotExist" => "WPMUDEV_Mailchimp_Campaign_DoesNotExist_API",
        "Campaign_StatsNotAvailable" => "WPMUDEV_Mailchimp_Campaign_StatsNotAvailable_API",
        "Campaign_InvalidAbsplit" => "WPMUDEV_Mailchimp_Campaign_InvalidAbsplit_API",
        "Campaign_InvalidContent" => "WPMUDEV_Mailchimp_Campaign_InvalidContent_API",
        "Campaign_InvalidOption" => "WPMUDEV_Mailchimp_Campaign_InvalidOption_API",
        "Campaign_InvalidStatus" => "WPMUDEV_Mailchimp_Campaign_InvalidStatus_API",
        "Campaign_NotSaved" => "WPMUDEV_Mailchimp_Campaign_NotSaved_API",
        "Campaign_InvalidSegment" => "WPMUDEV_Mailchimp_Campaign_InvalidSegment_API",
        "Campaign_InvalidRss" => "WPMUDEV_Mailchimp_Campaign_InvalidRss_API",
        "Campaign_InvalidAuto" => "WPMUDEV_Mailchimp_Campaign_InvalidAuto_API",
        "MC_ContentImport_InvalidArchive" => "WPMUDEV_Mailchimp_MC_ContentImport_InvalidArchive_API",
        "Campaign_BounceMissing" => "WPMUDEV_Mailchimp_Campaign_BounceMissing_API",
        "Campaign_InvalidTemplate" => "WPMUDEV_Mailchimp_Campaign_InvalidTemplate_API",
        "Invalid_EcommOrder" => "WPMUDEV_Mailchimp_Invalid_EcommOrder_API",
        "Absplit_UnknownError" => "WPMUDEV_Mailchimp_Absplit_UnknownError_API",
        "Absplit_UnknownSplitTest" => "WPMUDEV_Mailchimp_Absplit_UnknownSplitTest_API",
        "Absplit_UnknownTestType" => "WPMUDEV_Mailchimp_Absplit_UnknownTestType_API",
        "Absplit_UnknownWaitUnit" => "WPMUDEV_Mailchimp_Absplit_UnknownWaitUnit_API",
        "Absplit_UnknownWinnerType" => "WPMUDEV_Mailchimp_Absplit_UnknownWinnerType_API",
        "Absplit_WinnerNotSelected" => "WPMUDEV_Mailchimp_Absplit_WinnerNotSelected_API",
        "Invalid_Analytics" => "WPMUDEV_Mailchimp_Invalid_Analytics_API",
        "Invalid_DateTime" => "WPMUDEV_Mailchimp_Invalid_DateTime_API",
        "Invalid_Email" => "WPMUDEV_Mailchimp_Invalid_Email_API",
        "Invalid_SendType" => "WPMUDEV_Mailchimp_Invalid_SendType_API",
        "Invalid_Template" => "WPMUDEV_Mailchimp_Invalid_Template_API",
        "Invalid_TrackingOptions" => "WPMUDEV_Mailchimp_Invalid_TrackingOptions_API",
        "Invalid_Options" => "WPMUDEV_Mailchimp_Invalid_Options_API",
        "Invalid_Folder" => "WPMUDEV_Mailchimp_Invalid_Folder_API",
        "Invalid_URL" => "WPMUDEV_Mailchimp_Invalid_URL_API",
        "Module_Unknown" => "WPMUDEV_Mailchimp_Module_Unknown_API",
        "MonthlyPlan_Unknown" => "WPMUDEV_Mailchimp_MonthlyPlan_Unknown_API",
        "Order_TypeUnknown" => "WPMUDEV_Mailchimp_Order_TypeUnknown_API",
        "Invalid_PagingLimit" => "WPMUDEV_Mailchimp_Invalid_PagingLimit_API",
        "Invalid_PagingStart" => "WPMUDEV_Mailchimp_Invalid_PagingStart_API",
        "Max_Size_Reached" => "WPMUDEV_Mailchimp_Max_Size_Reached_API",
        "MC_SearchException" => "WPMUDEV_Mailchimp_MC_SearchException_API"
    );

    public function __construct($apikey=null, $opts=array()) {
        if(!$apikey) $apikey = getenv('MAILCHIMP_APIKEY');
        //if(!$apikey) $apikey = $this->readConfigs();
        if(!$apikey) throw new WPMUDEV_Mailchimp_Error_API('You must provide a MailChimp API key');
        $this->apikey = $apikey;
        $dc = "us1";
        if (strstr($this->apikey,"-")){
            list($key, $dc) = explode("-",$this->apikey,2);
            if (!$dc) $dc = "us1";
        }
        $this->root = str_replace('https://api', 'https://'.$dc.'.api', $this->root);
        $this->root = rtrim($this->root, '/') . '/';

        if ( isset( $opts['timeout'] ) && is_int( $opts['timeout'] ) )
            $this->timeout = absint( $opts['timeout'] );

        if ( isset( $opts['debug'] ) )
            $this->debug = true;

        if ( isset( $opts['ssl_verifypeer'] ) )
            $this->ssl_verifypeer = $opts['ssl_verifypeer'];

        if ( isset( $opts['ssl_verifyhost'] ) )
            $this->ssl_verifyhost = $opts['ssl_verifyhost'];

        if ( isset( $opts['ssl_cainfo'] ) )
            $this->ssl_cainfo = $opts['ssl_cainfo'];


        $this->folders = new WPMUDEV_Mailchimp_Folders_API($this);
        $this->templates = new WPMUDEV_Mailchimp_Templates_API($this);
        $this->users = new WPMUDEV_Mailchimp_Users_API($this);
        $this->helper = new WPMUDEV_Mailchimp_Helper_API($this);
        $this->mobile = new WPMUDEV_Mailchimp_Mobile_API($this);
        $this->ecomm = new WPMUDEV_Mailchimp_Ecomm_API($this);
        $this->neapolitan = new WPMUDEV_Mailchimp_Neapolitan_API($this);
        $this->lists = new WPMUDEV_Mailchimp_Lists_API($this);
        $this->campaigns = new WPMUDEV_Mailchimp_Campaigns_API($this);
        $this->vip = new WPMUDEV_Mailchimp_Vip_API($this);
        $this->reports = new WPMUDEV_Mailchimp_Reports_API($this);
        $this->gallery = new WPMUDEV_Mailchimp_Gallery_API($this);
    }

    public function call($url, $params) {

        $params['apikey'] = $this->apikey;
        $params = json_encode($params);

        $start = microtime(true);
        $this->log('Call to ' . $this->root . $url . '.json: ' . $params);

        $args = array(
            'timeout'     => $this->timeout,
            'user-agent'  => 'MailChimp-PHP/2.0.4',
            'blocking'    => true,
            'headers'     => array( 'Content-Type' => 'application/json' ),
            'body'        => $params,
            'sslverify'   => $this->ssl_verifypeer,
            'filename'    => null
        );

        $args = apply_filters( 'mailchimp_request_args', $args );

        if ( $this->ssl_cainfo )
            $args['sslcertificates'] = $this->ssl_cainfo;

        $response = wp_remote_post( $this->root . $url . '.json', $args );

        if ( is_wp_error( $response ) ) {
            $this->log_errors(
                array( array(  
                    'code' => $response->get_error_code(),
                    'message' => $response->get_error_message()
                ) )
            );
            return $response;
        }

        $response_body = wp_remote_retrieve_body( $response );

        $time = microtime(true) - $start;

        $this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
        $this->log('Got response: ' . $response_body);

        $result = json_decode($response_body, true);
        
        if( floor( $response['response']['code'] / 100 ) >= 4 ) {
            $error = $this->castError($result);
            $this->log_errors(
                array( array(  
                    'code' => $error->getCode(),
                    'message' => $error->getMessage()
                ) )
            );
            return new WP_Error( $error->getCode(), $error->getMessage() );
        }

        return $result;
    }

    public function readConfigs() {
        $paths = array('~/.mailchimp.key', '/etc/mailchimp.key');
        foreach($paths as $path) {
            if(file_exists($path)) {
                $apikey = trim(file_get_contents($path));
                if($apikey) return $apikey;
            }
        }
        return false;
    }

    public function castError($result) {
        if($result['status'] !== 'error' || !$result['name']) return new WPMUDEV_Mailchimp_Error_API('We received an unexpected error: ' . json_encode($result));

        $class = (isset(self::$error_map[$result['name']])) ? self::$error_map[$result['name']] : 'WPMUDEV_Mailchimp_Error_API';
        return new $class($result['error'], $result['code']);
    }

    public function log($msg) {
        if($this->debug) error_log($msg);
    }

    public function log_errors( $errors ) {
        if ( ! is_array( $errors ) )
            $errors = array( $errors );

        $current_log = get_site_option( 'mailchimp_error_log' );
        $new_log = array();


        foreach ( $errors as $error ) {

            $code = isset( $error['code'] ) ? $error['code'] : 0;
            $message = isset( $error['message'] ) ? $error['message'] : '';
            $email = isset( $error['email'] ) ? $error['email'] : '';
            $date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp' ) );

            $new_log[] = compact( 'code', 'message', 'email', 'date' );
            
        }


        if ( $current_log ) {

            $new_log = array_merge( $current_log, $new_log );

            // We'll only saved the last X lines of the log
            $count = count( $new_log );
            if ( $count > MAILCHIMP_MAX_LOG_LINES ) {
                $new_log = array_slice( $new_log, $count - $offset - 1 );
            }
            
        }

        update_site_option( 'mailchimp_error_log', $new_log );
    }
}


