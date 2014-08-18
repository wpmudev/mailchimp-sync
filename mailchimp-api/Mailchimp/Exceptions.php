<?php

class WPMUDEV_Mailchimp_Error_API extends Exception {}
class WPMUDEV_Mailchimp_HttpError_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * The parameters passed to the API call are invalid or not provided when required
 */
class WPMUDEV_Mailchimp_ValidationError_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_ServerError_MethodUnknown_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_ServerError_InvalidParameters_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Unknown_Exception_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Request_TimedOut_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Zend_Uri_Exception_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_PDOException_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Avesta_Db_Exception_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_XML_RPC2_Exception_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_XML_RPC2_FaultException_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Too_Many_Connections_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Parse_Exception_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_Unknown_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_Disabled_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_DoesNotExist_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_NotApproved_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_ApiKey_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_UnderMaintenance_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_AppKey_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_IP_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_DoesExist_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_InvalidRole_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_InvalidAction_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_MissingEmail_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_CannotSendCampaign_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_MissingModuleOutbox_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_ModuleAlreadyPurchased_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_ModuleNotPurchased_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_User_NotEnoughCredit_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_MC_InvalidPayment_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_DoesNotExist_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_InvalidInterestFieldType_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_InvalidOption_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_InvalidUnsubMember_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_InvalidBounceMember_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_AlreadySubscribed_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_NotSubscribed_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_InvalidImport_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_MC_PastedList_Duplicate_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_MC_PastedList_InvalidImport_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Email_AlreadySubscribed_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Email_AlreadyUnsubscribed_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Email_NotExists_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Email_NotSubscribed_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_MergeFieldRequired_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_CannotRemoveEmailMerge_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_Merge_InvalidMergeID_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_TooManyMergeFields_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_InvalidMergeField_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_InvalidInterestGroup_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_List_TooManyInterestGroups_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_DoesNotExist_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_StatsNotAvailable_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidAbsplit_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidContent_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidOption_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidStatus_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_NotSaved_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidSegment_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidRss_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidAuto_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_MC_ContentImport_InvalidArchive_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_BounceMissing_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Campaign_InvalidTemplate_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_EcommOrder_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Absplit_UnknownError_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Absplit_UnknownSplitTest_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Absplit_UnknownTestType_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Absplit_UnknownWaitUnit_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Absplit_UnknownWinnerType_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Absplit_WinnerNotSelected_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_Analytics_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_DateTime_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_Email_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_SendType_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_Template_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_TrackingOptions_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_Options_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_Folder_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_URL_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Module_Unknown_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_MonthlyPlan_Unknown_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Order_TypeUnknown_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_PagingLimit_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Invalid_PagingStart_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_Max_Size_Reached_API extends WPMUDEV_Mailchimp_Error_API {}

/**
 * None
 */
class WPMUDEV_Mailchimp_MC_SearchException_API extends WPMUDEV_Mailchimp_Error_API {}

