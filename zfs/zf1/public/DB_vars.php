<?php

define ("DB_PREFIX", "parcel_");
//Users
define ("USERS", DB_PREFIX."users");
define ("USERS_DETAILS", DB_PREFIX."users_detail");
define ("USERS_SETTINGS", DB_PREFIX."users_setting");
define ("USERS_LEVEL", DB_PREFIX."user_levels");
define ("USERS_SCHEDULE_PICKUP", DB_PREFIX."users_schedulepickup");
define ("USERS_PRIVILLAGE", DB_PREFIX."user_privileges");

//Modules
define ("MODULES", DB_PREFIX."modules");
//Routings
define ("ROUTING", DB_PREFIX."routing");
define ("ROUTING_PRICE", DB_PREFIX."routing_price");
define ("ROUTING_FORWARDER_COUNTRY", DB_PREFIX."routing_forwarder_country");
define ("ROUTING_WEIGHT_CLASS", DB_PREFIX."routing_weightclass");
define ("ROUTING_SPECIAL_PRICE", DB_PREFIX."routing_special_price");
define ("ROUTING_EDITED", DB_PREFIX."routing_edited");
define ("ROUTING_DELETED", DB_PREFIX."routing_deleted");
define ("ROUTING_POSTCODE", DB_PREFIX."routing_postcode");
//Shipments
define ("SHIPMENT", DB_PREFIX."shipments");
define ("SHIPMENT_BARCODE", DB_PREFIX."shipment_barcodes");
define ("SHIPMENT_BARCODE_DETAIL", DB_PREFIX."shipment_barcode_detail");
define ("SHIPMENT_BARCODE_LOG", DB_PREFIX."shipment_checkin_log");
define ("SHIPMENT_BARCODE_REROUTE", DB_PREFIX."shipment_reroute");
define ("SHIPMENT_HUB", DB_PREFIX."shipment_hub");
define ("ADDRESS_BOOK", DB_PREFIX."address_book");
define ("SHIPMENT_TYPE", DB_PREFIX."shipment_types");	
define ("SHIPMENT_EDI", DB_PREFIX."shipment_edi");	
define ("SHIPMENT_EVENT_HISTORIES", DB_PREFIX."shipment_event_histories");
define ("SHIPMENT_EVENT_ACTIONS", DB_PREFIX."shipment_event_actions");
define ("SHIPMENT_MEDIATOR_FORWARDER", DB_PREFIX."shipment_mediator_forwarder");
define ("SHIPMENT_BARCODE_EDITED", DB_PREFIX."shipment_barcode_edited");
//Delected
define ("SHIPMENT_DELETED", DB_PREFIX."shipments_deleted");
//Edited
define ("SHIPMENT_EDITED", DB_PREFIX."shipments_edited");
define ("EMERGENCY_CHECKIN", DB_PREFIX."shipment_emergency_checkin");
define ("SHIPMENT_SCHEDULE_TIME", DB_PREFIX."shipment_schedule_time");
define ("SHIPMENT_MANUAL_PICKUP", DB_PREFIX."shipment_manual_pickup");
define ("SHIPMENT_PARCELPOINT", DB_PREFIX."shipment_parcelpoint");

//Mail/Post Shipments
define ("MAIL_POST", DB_PREFIX."mailshipment");
define ("MAIL_POST_COUNTRY", DB_PREFIX."mailshipment_country");
define ("MAILPOST_WEIGHTCLASS", DB_PREFIX."mailshipment_weightclass");
define ("MAILPOST_ROUTINGS", DB_PREFIX."mailshipment_routings");
define ("MAIL_POST_DELETED", DB_PREFIX."mailshipment_deleted");
define ("MAIL_POST_SERVICES", DB_PREFIX."mailshipment_service");
//Invoices
define ("INVOICE", DB_PREFIX."invoice");
define ("INVOICE_COD", DB_PREFIX."invoice_cod_detail");
define ("INVOICE_FINANCIAL", DB_PREFIX."invoice_financial");
define ("INVOICE_EXTRA_HEAD", DB_PREFIX."invoice_extraheads");
define ("INVOICE_BTW_CLASS", DB_PREFIX."invoice_btwclasses");
define ("INVOICE_BANK_DETAIL", DB_PREFIX."invoice_bank_detail");
define ("INVOICE_SETTING", DB_PREFIX."invoice_setting");
//Settings
define ("FORWARDERS", DB_PREFIX."forwarders");
define ("FORWARDERS_CUST", DB_PREFIX."forwarder_cust");
define ("FORWARDERS_FTP_DETAIL", DB_PREFIX."forwarder_ftp_detail");
define ("COUNTRIES", DB_PREFIX."countries");
define ("CONTINENTS", DB_PREFIX."continents");
define ("COUNTRYPORT", DB_PREFIX."country_port");
define ("SERVICES", DB_PREFIX."services");
define ("CITIES", DB_PREFIX."world_cities");
define ("CITY_LAT_LNG", DB_PREFIX."city_lat_long");
define ("DPDROUTEINFO", DB_PREFIX."dpdroutesinfo");
define ("GOODS_CATEGORY", DB_PREFIX."goods_category");
define ("LANGUAGE", DB_PREFIX."languages");
define ("PARCELSHOP", DB_PREFIX."parcelshop");
define ("WEIGHT_SCALER_INFO", DB_PREFIX."weightscaler_info");
define ("MAIL_DYNAMIC_FIELD", DB_PREFIX."mail_dynamicfield");
define ("MAIL_MANAGER", DB_PREFIX."mail_templates");
define ("MAIL_TEMPLATE_DEFAULT", DB_PREFIX."mail_templates_default");
define ("MAIL_NOTIFY_TYPES", DB_PREFIX."mail_notification_types");
define ("VEHICLE_SETTINGS", DB_PREFIX."vehicle_settings");
define ("EMAIL_CONFIG", DB_PREFIX."emailconfig");
define ("EMAIL_LOG", DB_PREFIX."mail_log");
define ("TRANSLATION", DB_PREFIX."translations");
define ("BLOCKIP", DB_PREFIX."blockip");
define ("TRANSIT_DETAIL", DB_PREFIX."transit_detail");
define ("COUNTRY_SETTING", DB_PREFIX."countries_setting");
define ("FORWARDER_SEPARATE_FTP", DB_PREFIX."forwarder_separate_ftp");
//====================  Shipment and barcods ====================//
define ("SHIPMENT_ID", "shipment_id");
define ("WEIGHT", "weight");
define ("FORWARDER_ID", "forwarder_id");
define ("COUNTRY_ID", "country_id");
define ("SERVICE_ID", "service_id");
define ("ADDSERVICE_ID", "addservice_id");
define ("QUANTITY", "quantity");
define ("RECEIVER", "rec_name");
define ("STREET", "rec_street");
define ("STREETNR", "rec_streetnr"); 
define ("ADDRESS", "rec_address"); 
define ("STREET2", "rec_street2"); 
define ("CITY", "rec_city"); 
define ("ZIPCODE", "rec_zipcode"); 
define ("CONTACT", "rec_contact");
define ("PHONE", "rec_phone");
define ("EMAIL", "rec_email");
define ("REFERENCE", "rec_reference");
define ("BARCODE_ID", "barcode_id");
define ("TRACENR","tracenr");
define ("BARCODE","barcode");
define ("REROUTE_BARCODE","reroute_barcode");
define ("TRACENR_BARCODE","tracenr_barcode");
define ("LOCAL_BARCODE","local_barcode");
define ("DEPOT_PRICE", "depot_price");
define ("CUSTOMER_PRICE", "customer_price");
define ("LABEL_DATE", "label_date");
define ("LABEL_STATUS", "label_status");
define ("CHECKIN_STATUS", "checkin_status");
define ("CHECKIN_DATE", "checkin_date");
define ("REFERENCE_BARCODE", "reference_barcode");
define ("CREATE_DATE", "create_date");
define ("CHECKIN_TYPE", "checkin_type");
define ("COD_PRICE", "cod_price");
//========================UsersTable=================//
define ("ADMIN_ID", "user_id");
define ("PARENT_ID", "parent_id");
define ("COMPANY_NAME", "company_name");

//==================Routing==============
define ("ROUTING_ID", "routing_id");
//=============FOrwarders================
define ("START_TRACKING", "tracking_start");
define ("END_TRACKING", "tracking_end");
define ("LAST_TRACKING", "last_tracking");

//=============================== mailmanager Table ================================
define ("NOTIFICATION_ID", "notification_id");
define ("MAIL_SUBJECT","subject");
define ("MAIL_TEXT","email_text");

//===========================Forwarder Configs Table=========================
define ("SYSTEMATIC_PICKUP", DB_PREFIX."forwarder_systematic_pickup");
//=============================== translation_text Table ===============================
define ("TRANSLATION_FOR","translation_for");

//===================================Route  Tables=========================================
//DPD
define ("DPD_COUNTRIES", DB_PREFIX."dpd_countries");
define ("DPD_DEPOTS", DB_PREFIX."dpd_depots");
define ("DPD_LOCATIONS", DB_PREFIX."dpd_locations");
define ("DPD_ROUTES", DB_PREFIX."dpd_routes");
define ("DPD_SERVICEINFO", DB_PREFIX."dpd_serviceinfo");
define ("DPD_SERVICE", DB_PREFIX."dpd_services");
//DPDAT
define ("DPDAT_COUNTRIES", DB_PREFIX."dpdat_countries");
define ("DPDAT_DEPOTS", DB_PREFIX."dpdat_depots");
define ("DPDAT_LOCATIONS", DB_PREFIX."dpdat_locations");
define ("DPDAT_ROUTES", DB_PREFIX."dpdat_routes");
define ("DPDAT_SERVICEINFO", DB_PREFIX."dpdat_serviceinfo");
define ("DPDAT_SERVICE", DB_PREFIX."dpdat_services");
//DPDDE
define ("DPDDE_COUNTRIES", DB_PREFIX."dpdde_countries");
define ("DPDDE_DEPOTS", DB_PREFIX."dpdde_depots");
define ("DPDDE_LOCATIONS", DB_PREFIX."dpdde_locations");
define ("DPDDE_ROUTES", DB_PREFIX."dpdde_routes");
define ("DPDDE_SERVICEINFO", DB_PREFIX."dpdde_serviceinfo");
define ("DPDDE_SERVICE", DB_PREFIX."dpdde_services");
//DPDHR
define ("DPDHR_COUNTRIES", DB_PREFIX."dpdhr_countries");
define ("DPDHR_DEPOTS", DB_PREFIX."dpdhr_depots");
define ("DPDHR_LOCATIONS", DB_PREFIX."dpdhr_locations");
define ("DPDHR_ROUTES", DB_PREFIX."dpdhr_routes");
define ("DPDHR_SERVICEINFO", DB_PREFIX."dpdhr_serviceinfo");
define ("DPDHR_SERVICE", DB_PREFIX."dpdhr_services");

//===========================Import Header================
define ("IMPORT_HEADER", DB_PREFIX."import_header");
define ("SHIPMENT_TEMP", DB_PREFIX."shipments_temp");
//Country
define ("COUNTRY_NAME", "country_name");

//language
define ("LANGUAGE_ID","language_id");
define ("LANGUAGE_NAME","language_name");
// Driver
define ("DRIVER_DETAIL_TABLE",DB_PREFIX."driver_details");
define ("DRIVER_HISTORY",DB_PREFIX."driver_history");
define ("DRIVER_PICKEDUP_HISTORY", DB_PREFIX."driver_pickedup_list");
define ("DRIVER_GPS_LOCATION", DB_PREFIX."driver_gps_location");
define ("DRIVER_DAILY_REPORT", DB_PREFIX."driver_daily_report");
define ("DRIVER_VAHICLE_REPORT", DB_PREFIX."driver_vehicle_report");
define ("DRIVER_MESSAGE", DB_PREFIX."driver_messages");
define ("DRIVER_MESSAGE_DETAIL", DB_PREFIX."driver_message_details");
define ("DRIVER_PICKUP_INFO", DB_PREFIX."drver_pickupinfo");

//========================Post AT ========================
define ("POSTAT_PRODUCTS",DB_PREFIX."postat_products");
define ("POSTAT_SERVICES",DB_PREFIX."postat_services");
define ("POSTAT_SUB_SERVICES",DB_PREFIX."postat_sub_services");

//=======================Postnl=======================
define ("POSTNL_SUB_SERVICES",DB_PREFIX."postnl_sub_service");
//=======================UPS=======================
define ("UPS_BILLINGS",DB_PREFIX."ups_billings");
define ("UPS_ROUTECODES",DB_PREFIX."ups_routecodes");
define ("UPS_SUB_SERVICES",DB_PREFIX."ups_sub_services");

//===================Claims==============================
define ("CLAIM_QUESTIONS",DB_PREFIX."claim_questions");
define ("CLAIM_STATUS",DB_PREFIX."claim_status");
define ("CLAIM_TICKET",DB_PREFIX."claim_ticket");
define ("CLAIM_TICKET_DOCUMENT",DB_PREFIX."claim_ticket_documents");
define ("CLAIM_TICKET_REPLY",DB_PREFIX."claim_ticket_reply");
define ("CLAIM_TICKET_STATUS",DB_PREFIX."claim_ticket_status");
//===================HELPdesk==============================
define ("HELPDESK_ACTIVITY",DB_PREFIX."helpdesk_activity");
define ("HELPDESK_QUESTION_DETAILS",DB_PREFIX."helpdesk_question_details");
define ("HELPDESK_QUESTION",DB_PREFIX."helpdesk_questions");
define ("HELPDESK_STATUS",DB_PREFIX."helpdesk_status");
define ("HELPDESK_TICKET",DB_PREFIX."helpdesk_ticket");
define ("HELPDESK_TICKET_DETAIL",DB_PREFIX."helpdesk_ticket_details");
define ("CLAIM_STATUS_ID", "claim_status_id");
define ("HELPDESK_FAQ_DETAILS",DB_PREFIX."helpdesk_faq");
define ("HELPDESK_STEPS",DB_PREFIX."helpdek_step");
define ("HELPDESK_QUESTION_LANGUAGE",DB_PREFIX."helpdesk_question_language");
//=================DHL Street COde========================================
define ("DHL_STREETCODE",DB_PREFIX."dhl_streetcode");
//=================Forwarder Mondial Relay=============================
define ("MR_AGENCY",DB_PREFIX."mr_agencynumber");
define ("MR_NEARESTPR",DB_PREFIX."mr_nearestpr");
define ("MR_ROUTING",DB_PREFIX."mr_routing");
define ("MR_SORTING",DB_PREFIX."mr_sorting");
//=================YOdel Label Record=============================
define ("YODEL_PDF",DB_PREFIX."yodel_pdf");
//=================Forwarder BRT=============================
define ("BRT_CITY",DB_PREFIX."brt_city");
define ("BRT_CITYANYNOMUS",DB_PREFIX."brt_citysynonyms");
define ("BRT_DEPARTURE",DB_PREFIX."brt_departure_term");
define ("BRT_ROUTING",DB_PREFIX."brt_routing");
define ("BRT_TERMINAL",DB_PREFIX."brt_terminal");
//=================Parcel Status=============================
define ("STATUS_MASTER", DB_PREFIX."status_master");
define ("STATUS_LIST", DB_PREFIX."status_lists");
define ("WRONG_ADDRESS_MODIFICATION", DB_PREFIX."address_modification");
define ("STATUS_SMS_SETTING", DB_PREFIX."status_sms_setting");
define ("STATUS_SMSCODE", DB_PREFIX."status_smscode");
define ("STATUS_SMSLOG", DB_PREFIX."status_smslog");
define ("SMS_TEXT", DB_PREFIX."sms_text");
//=================Webshop=============================
define ("WEBSHOP_ORDER_PRODUCTS", DB_PREFIX."webshop_order_products");
define ("WEBSHOP_ORDER", DB_PREFIX."webshop_orders");
define ("WEBSHOP_PRODUCTS", DB_PREFIX."webshop_products");
define ("WEBSHOP_PRODUCT_DESC", DB_PREFIX."webshop_product_desc");
define ("WEBSHOPS", DB_PREFIX."webshops");
//=====================Sepcial Contract Settings================//
define ("DHL_SETTINGS", DB_PREFIX."forwarders_dhl");
define ("DPD_SETTINGS", DB_PREFIX."forwarders_dpd");
define ("GLSDE_SETTINGS", DB_PREFIX."forwarders_glsde");
define ("GLSNL_SETTINGS", DB_PREFIX."forwarders_glsnl");
define ("BPOST_SETTINGS", DB_PREFIX."forwarders_bpost");
define ("RUSSIANPOST_SETTINGS", DB_PREFIX."forwarders_russianpost");
define ("RSWISSPOST_SETTINGS", DB_PREFIX."forwarders_rswisspost");
// TermsCondition
define ("TERMS_CONDITION",DB_PREFIX."termcondition");
define ("TERM_ID",'term_id');
define ("MESSAGE",'message');

// Depot Notification
define ('DEPOT_NOTIFICATION',DB_PREFIX."depot_notification");
define ('NOTIFICATION','notification');

//pARCEL tRACKING lOGS
define ('PARCEL_TRACKING',DB_PREFIX.'tracking_logs');
//Freight Sub_service
define ('FREIGHT_SUBSERVICE',DB_PREFIX.'glsfreight_sub_service');
//forwarder Settings
define ('FORWARDER_SETTINGS',DB_PREFIX.'forwarders_settings');
//API Shop Settings
define ('SHOP_SETTINGS',DB_PREFIX.'shop_api_setting');
define ('SHOPTYPE_LIST',DB_PREFIX.'shop_types');
define ('SHOP_API_SHIPMENT',DB_PREFIX.'shop_api_shipments');

define ('CC_BCC_EMAIL',DB_PREFIX."cc_bcc_email");
//  User Sender Address
define ('USER_SENDER_ADDRESS',DB_PREFIX.'sender_address');
define ('SENDER_ADDRESS_COUNTRIES',DB_PREFIX.'sender_address_country');
//Planner
define ("PLANNER_ROUTELIST", DB_PREFIX."planner_routelist");
define ("PLANNER_SCHEDULE_ROUTE", DB_PREFIX."planner_scheduleroute");
define ("PLANNER_SCHEDULE_DELIVERY", DB_PREFIX."planner_scheduledelivery");
//Correos
define ("CORREOS_PROVINCE", DB_PREFIX."correos_province");
//Russianpost Tarif
define ("RUSSIANPOST_TARIF", DB_PREFIX."russianpost_tarif");
//Systematic Dimention
define ("SYSTEMATIC_DIMENSION", DB_PREFIX."systematic_dimensions");
// news letter templates
define ('NEWSLETTER_TEMPLATES',DB_PREFIX.'newsletter_templates');
define ('NEWSLETTER_RECEIBERS',DB_PREFIX.'newsletter_receivers');
define ("DEFAULT_PRIVILLAGE", DB_PREFIX."user_default_privileges");
//PLanner Nonlisted Schedule
define ("PLANNER_NONLISTED_SCHEDULE", DB_PREFIX."planner_nonlisted_schedule");
//Systsem Log
define ("SYSTEM_ACTIVITY_LOG", DB_PREFIX."system_activity_log");
//Customer Scan Log
define ("CUSTOMER_SCAN_HISTORY", DB_PREFIX."customer_scan_history");
//Customer Routing
define ("CUSTOMER_ROUTING", DB_PREFIX."customer_routing");
//Delivery Tracker
define ("DELIVERY_TRACKER", DB_PREFIX."delivery_tracker");

//Sea Freight
define ("SEAFREIGHT_ROUTING", DB_PREFIX."seafreight_routing");
define ("SEAFREIGHT_DIMENSION", DB_PREFIX."seafreight_dimensions");

//Weight CHnage
define ("WEIGHT_CHANGE", DB_PREFIX."weight_change");

//oldtracking_encrypt
define ("OLDTRACKING_ENCRYPT", DB_PREFIX."oldtracking_encrypt");

define ("FORWARDER_GBDHL", DB_PREFIX."forwarders_gbdhl");

define ("COLISPRIVE_ROUTE", DB_PREFIX."colisprive_route");
define ("FORWARDER_COLISPRIVE", DB_PREFIX."forwarders_colisprive");


//Accounting Models
define ("AccountingHead", DB_PREFIX."accounting_heads");
define ("AccountingFunction", DB_PREFIX."accounting_function");
define ("AccountingClass", DB_PREFIX."accounting_class");
define ("AccountingGroup", DB_PREFIX."accounting_groups");
define ("AccountingBtwRates", DB_PREFIX."accounting_btw_rates");
define ("AccountingBtwRateTypes", DB_PREFIX."accounting_btw_rate_types");
define ("AccountingSuppliers", DB_PREFIX."accounting_suppliers");
define ("AccountingInvoice", DB_PREFIX."accounting_invoice");
define ("AccountingInvoiceDetails", DB_PREFIX."accounting_invoicedetails");
define ("DepotNetworkRouting", DB_PREFIX."depot_network_routing");
define ("DepotNetworkRoutingDetails", DB_PREFIX."depot_network_routing_details");
define ("ADDITIONAL_DOCUMENTS", DB_PREFIX."additional_documents");
define ("DRIVER_DELIVERY_LIST", DB_PREFIX."driver_delivery_list");
define ("MANIFEST_API_REQUESTS", DB_PREFIX."manifest_api_requests");