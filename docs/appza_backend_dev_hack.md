classDiagram
direction BT
class activity_log {
   varchar(255) log_name
   text description
   varchar(255) subject_type
   varchar(255) event
   bigint unsigned subject_id
   varchar(255) causer_type
   bigint unsigned causer_id
   json properties
   char(36) batch_uuid
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_apk_build_history {
   bigint unsigned version_id
   bigint unsigned build_domain_id
   int fluent_id
   varchar(255) app_name
   varchar(255) app_logo
   varchar(255) app_splash_screen_image
   varchar(255) build_version
   timestamp created_at
   timestamp updated_at
   varchar(255) ios_issuer_id
   varchar(255) ios_key_id
   varchar(255) ios_p8_file_content
   varchar(255) ios_team_id
   varchar(255) ios_app_name
   bigint unsigned id
}
class appfiy_app_versions {
   varchar(255) version
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_build_domain {
   varchar(255) site_url
   varchar(255) package_name
   varchar(255) email
   timestamp created_at
   timestamp updated_at
   varchar(255) plugin_name
   varchar(255) license_key
   bigint unsigned version_id
   int fluent_id
   varchar(255) app_name
   varchar(255) app_logo
   varchar(255) app_splash_screen_image
   varchar(255) build_version
   varchar(255) ios_issuer_id
   varchar(255) ios_key_id
   varchar(255) ios_p8_file_content
   varchar(255) team_id
   tinyint(1) is_android
   tinyint(1) is_ios
   varchar(255) confirm_email
   int unsigned fluent_item_id
   varchar(255) build_plugin_slug
   varchar(255) ios_app_name
   tinyint(1) is_app_license_check
   tinyint(1) is_deactivated
   bigint unsigned id
}
class appfiy_component {
   bigint unsigned parent_id
   bigint unsigned layout_type_id
   varchar(255) name
   varchar(255) slug
   varchar(255) label
   varchar(100) icon_code
   varchar(255) event
   json scope
   varchar(255) class_type
   longtext app_icon
   longtext web_icon
   varchar(100) image
   tinyint(1) is_multiple
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   varchar(100) product_type
   varchar(255) display_name
   int clone_component
   int selected_id
   tinyint selected_design
   tinyint details_page
   varchar(20) transparent
   varchar(100) image_url
   bigint unsigned component_type_id
   tinyint(1) is_upcoming
   varchar(255) plugin_slug
   json items
   json dev_data
   json pagination
   json filters
   tinyint(1) show_no_data_view
   bigint unsigned id
}
class appfiy_component_parent_child {
   bigint unsigned parent_id
   bigint unsigned child_id
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_component_style_group {
   bigint unsigned component_id
   bigint unsigned style_group_id
   tinyint(1) is_checked
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_component_style_group_properties {
   bigint unsigned component_id
   bigint unsigned style_group_id
   varchar(255) name
   varchar(255) input_type
   varchar(255) value
   longtext default_value
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_component_type {
   varchar(255) name
   varchar(255) slug
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   varchar(255) group_name
   varchar(255) icon
   bigint unsigned id
}
class appfiy_customer_leads {
   bigint unsigned customer_id
   bigint unsigned license_id
   varchar(255) first_name
   varchar(255) last_name
   varchar(255) email
   varchar(255) domain
   varchar(255) note
   tinyint(1) is_active
   tinyint(1) is_close
   timestamp created_at
   timestamp updated_at
   varchar(255) appza_hash
   varchar(255) plugin_name
   bigint unsigned id
}
class appfiy_customer_license_activations {
   bigint unsigned license_id
   varchar(255) domain
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_customer_licenses {
   bigint unsigned customer_id
   int limit
   int activation_count
   varchar(255) license_key
   date expiration_date
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_customers {
   varchar(255) first_name
   varchar(255) last_name
   varchar(255) email
   varchar(255) mobile
   datetime first_purchase_date
   datetime last_purchase_date
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_domain {
   varchar(255) name
   varchar(100) licence_key
   longtext description
   date expire_date
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_global_config {
   varchar(255) mode
   varchar(255) name
   varchar(255) slug
   varchar(20) selected_color
   varchar(20) unselected_color
   varchar(255) background_color
   varchar(255) layout
   double icon_theme_size
   varchar(255) icon_theme_color
   double(10,2) shadow
   varchar(255) icon
   tinyint(1) automatically_imply_leading
   tinyint(1) center_title
   varchar(255) flexible_space
   varchar(255) bottom
   varchar(255) shape_type
   double shape_border_radius
   double(10,2) toolbar_opacity
   varchar(255) actions_icon_theme_color
   double actions_icon_theme_size
   double title_spacing
   varchar(100) image
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   tinyint(1) is_transparent_background
   varchar(255) text_properties_color
   varchar(255) icon_properties_size
   varchar(255) icon_properties_color
   varchar(255) icon_properties_shape_radius
   varchar(255) icon_properties_background_color
   varchar(255) padding_x
   varchar(255) padding_y
   varchar(255) margin_x
   varchar(255) margin_y
   varchar(255) image_properties_height
   varchar(255) image_properties_width
   varchar(255) image_properties_shape_radius
   varchar(255) image_properties_padding_x
   varchar(255) image_properties_padding_y
   varchar(255) image_properties_margin_x
   varchar(255) image_properties_margin_y
   varchar(255) icon_properties_padding_x
   varchar(255) icon_properties_padding_y
   varchar(255) icon_properties_margin_x
   varchar(255) icon_properties_margin_y
   tinyint(1) is_upcoming
   tinyint(1) float
   int unsigned currency_id
   varchar(255) plugin_slug
   bigint unsigned id
}
class appfiy_global_config_component {
   bigint unsigned global_config_id
   bigint unsigned component_id
   varchar(255) component_position
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_layout_type {
   varchar(255) name
   varchar(255) slug
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_layout_type_group_style {
   bigint unsigned layout_type_id
   bigint unsigned property_id
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_layout_type_style_properties {
   varchar(255) name
   varchar(255) input_type
   varchar(255) value
   longtext default_value
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_license {
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_page {
   varchar(255) name
   varchar(255) slug
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   varchar(255) persistent_footer_buttons
   varchar(20) background_color
   varchar(20) border_color
   varchar(20) border_radius
   int component_limit
   varchar(255) plugin_slug
   bigint unsigned id
}
class appfiy_scope {
   varchar(255) name
   varchar(255) slug
   tinyint(1) is_global
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   varchar(255) plugin_slug
   bigint unsigned page_id
   bigint unsigned id
}
class appfiy_style_group {
   varchar(255) name
   varchar(255) slug
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   json plugin_slug
   bigint unsigned id
}
class appfiy_style_group_properties {
   bigint unsigned style_group_id
   bigint unsigned style_property_id
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_style_properties {
   varchar(255) name
   varchar(255) input_type
   varchar(255) value
   longtext default_value
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_theme {
   varchar(255) name
   varchar(255) slug
   longtext image
   bigint unsigned appbar_id
   bigint unsigned navbar_id
   bigint unsigned drawer_id
   json appbar_navbar_drawer
   tinyint(1) is_default
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   varchar(20) background_color
   varchar(20) font_family
   varchar(20) text_color
   decimal(10,2) font_size
   varchar(20) transparent
   varchar(255) dashboard_page
   varchar(255) login_page
   varchar(255) login_modal
   int sort_order
   varchar(255) plugin_slug
   varchar(255) default_page
   bigint unsigned id
}
class appfiy_theme_component {
   bigint unsigned theme_id
   bigint unsigned parent_id
   bigint unsigned component_parent_id
   bigint unsigned component_id
   bigint unsigned theme_config_id
   bigint unsigned theme_page_id
   timestamp created_at
   timestamp updated_at
   varchar(255) display_name
   int clone_component
   int selected_id
   int sort_ordering
   bigint unsigned id
}
class appfiy_theme_component_style {
   bigint unsigned theme_id
   bigint unsigned theme_component_id
   varchar(255) name
   varchar(255) input_type
   varchar(255) value
   longtext default_value
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned style_group_id
   bigint unsigned id
}
class appfiy_theme_config {
   bigint unsigned theme_id
   bigint unsigned global_config_id
   varchar(255) mode
   varchar(255) name
   varchar(255) slug
   varchar(255) background_color
   varchar(255) layout
   int icon_theme_size
   varchar(255) icon_theme_color
   double(10,2) shadow
   varchar(255) icon
   tinyint(1) automatically_imply_leading
   tinyint(1) center_title
   varchar(255) flexible_space
   varchar(255) bottom
   varchar(255) shape_type
   int shape_border_radius
   double(10,2) toolbar_opacity
   varchar(255) actions_icon_theme_color
   int actions_icon_theme_size
   int title_spacing
   tinyint(1) is_active
   timestamp deleted_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appfiy_theme_page {
   bigint unsigned theme_id
   bigint unsigned page_id
   timestamp created_at
   timestamp updated_at
   varchar(255) persistent_footer_buttons
   varchar(20) background_color
   varchar(20) border_color
   varchar(20) border_radius
   int sort_order
   bigint unsigned id
}
class appfiy_theme_photo_gallery {
   bigint unsigned theme_id
   varchar(255) caption
   varchar(255) image
   tinyint(1) status
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_class_type {
   varchar(255) name
   varchar(255) slug
   json plugin
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_fluent_informations {
   varchar(100) product_name
   varchar(50) product_slug
   varchar(255) api_url
   int item_id
   varchar(255) item_name
   varchar(255) item_description
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_fluent_license_info {
   bigint unsigned build_domain_id
   varchar(255) site_url
   int product_id
   int variation_id
   varchar(255) license_key
   varchar(255) activation_hash
   varchar(255) product_title
   varchar(255) variation_title
   int activation_limit
   int activations_count
   varchar(255) expiration_date
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_free_trial_request {
   varchar(50) product_slug
   varchar(255) site_url
   varchar(255) name
   varchar(255) email
   int product_id
   int variation_id
   varchar(50) license_key
   varchar(50) activation_hash
   varchar(255) product_title
   varchar(255) variation_title
   int activation_limit
   int activations_count
   datetime expiration_date
   datetime grace_period_date
   varchar(20) status
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   tinyint(1) is_fluent_license_check
   bigint unsigned premium_license_id
   bigint unsigned id
}
class appza_popup_messages_android {
   varchar(255) message_type
   longtext message
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned product_id
   bigint unsigned id
}
class appza_product_addons {
   bigint unsigned product_id
   varchar(100) addon_name
   varchar(100) addon_slug
   json addon_json_info
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_product_addons_versions {
   bigint unsigned addon_id
   varchar(255) version
   varchar(255) addon_file
   json version_json_info
   tinyint(1) is_active
   tinyint(1) is_edited
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_request_logs {
   varchar(255) method
   varchar(500) url
   json headers
   json request_data
   int response_status
   longtext response_data
   varchar(255) ip_address
   varchar(500) user_agent
   bigint unsigned user_id
   double execution_time
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_setup {
   varchar(255) key
   varchar(255) value
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class appza_supports_plugin {
   varchar(255) name
   varchar(255) slug
   varchar(255) prefix
   tinyint(1) status
   timestamp created_at
   timestamp updated_at
   varchar(255) title
   longtext description
   longtext others
   tinyint(1) is_disable
   varchar(255) image
   int sort_order
   bigint unsigned id
}
class build_orders {
   varchar(255) package_name
   varchar(255) app_name
   varchar(255) domain
   varchar(255) base_suffix
   varchar(255) base_url
   varchar(255) build_number
   varchar(255) icon_url
   char(10) build_target
   varchar(255) jks_url
   varchar(255) key_properties_url
   varchar(255) issuer_id
   varchar(255) key_id
   varchar(255) api_key_url
   varchar(255) team_id
   varchar(255) app_identifier
   char(10) status
   timestamp created_at
   timestamp updated_at
   varchar(255) apk_url
   varchar(255) build_message
   varchar(255) build_plugin_slug
   varchar(255) aab_url
   varchar(255) android_output_url
   varchar(255) ios_output_url
   varchar(255) build_domain_id
   varchar(255) license_key
   varchar(255) runner_url
   varchar(255) history_id
   datetime process_start
   tinyint(1) is_build_dir_delete
   varchar(255) build_dir
   bigint unsigned id
}
class currency {
   varchar(100) country
   varchar(100) currency
   varchar(100) code
   varchar(100) symbol
   tinyint is_active
   int unsigned id
}
class failed_jobs {
   varchar(255) uuid
   text connection
   text queue
   longtext payload
   longtext exception
   timestamp failed_at
   bigint unsigned id
}
class jobs {
   varchar(191) queue
   longtext payload
   tinyint unsigned attempts
   int unsigned reserved_at
   int unsigned available_at
   int unsigned created_at
   bigint unsigned id
}
class jobs_backup {
   varchar(191) queue
   longtext payload
   tinyint unsigned attempts
   int unsigned reserved_at
   int unsigned available_at
   int unsigned created_at
   bigint unsigned id
}
class license_logics {
   varchar(100) name
   varchar(100) slug
   varchar(255) event_combination
   enum('expiration', 'grace', 'invalid') event
   enum('before', 'equal', 'after') direction
   int unsigned from_days
   int unsigned to_days
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class license_message_details {
   bigint unsigned message_id
   varchar(255) type
   text message
   tinyint(1) is_active
   tinyint(1) is_show
   tinyint(1) is_feature
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class license_messages {
   bigint unsigned product_id
   bigint unsigned addon_id
   bigint unsigned license_logic_id
   varchar(255) license_type
   tinyint(1) is_active
   tinyint(1) is_show
   tinyint(1) is_feature
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class migrations {
   varchar(255) migration
   int batch
   int unsigned id
}
class password_reset_tokens {
   varchar(255) token
   timestamp created_at
   varchar(255) email
}
class password_resets {
   varchar(255) email
   varchar(255) token
   timestamp created_at
}
class personal_access_tokens {
   varchar(255) tokenable_type
   bigint unsigned tokenable_id
   varchar(255) name
   varchar(64) token
   text abilities
   timestamp last_used_at
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class search_field_common {
   varchar(20) fill_color
   varchar(255) page_title_text
   varchar(20) page_title_color
   double(10,2) page_title_font_size
   tinyint(1) is_active
   timestamp created_at
   timestamp updated_at
   bigint unsigned id
}
class users {
   varchar(255) name
   varchar(255) email
   timestamp email_verified_at
   varchar(255) password
   varchar(100) remember_token
   timestamp created_at
   timestamp updated_at
   varchar(255) user_type
   bigint unsigned id
}

appfiy_apk_build_history  -->  appfiy_app_versions : version_id:id
appfiy_apk_build_history  -->  appfiy_build_domain : build_domain_id:id
appfiy_build_domain  -->  appfiy_app_versions : version_id:id
appfiy_component  -->  appfiy_component : parent_id:id
appfiy_component  -->  appfiy_component_type : component_type_id:id
appfiy_component  -->  appfiy_layout_type : layout_type_id:id
appfiy_component_parent_child  -->  appfiy_component : parent_id:id
appfiy_component_parent_child  -->  appfiy_component : child_id:id
appfiy_component_style_group  -->  appfiy_component : component_id:id
appfiy_component_style_group  -->  appfiy_style_group : style_group_id:id
appfiy_component_style_group_properties  -->  appfiy_component : component_id:id
appfiy_component_style_group_properties  -->  appfiy_style_group : style_group_id:id
appfiy_customer_leads  -->  appfiy_customer_license_activations : license_id:id
appfiy_customer_leads  -->  appfiy_customers : customer_id:id
appfiy_customer_license_activations  -->  appfiy_customer_licenses : license_id:id
appfiy_customer_licenses  -->  appfiy_customers : customer_id:id
appfiy_global_config  -->  currency : currency_id:id
appfiy_global_config_component  -->  appfiy_component : component_id:id
appfiy_global_config_component  -->  appfiy_global_config : global_config_id:id
appfiy_layout_type_group_style  -->  appfiy_layout_type : layout_type_id:id
appfiy_layout_type_group_style  -->  appfiy_layout_type_style_properties : property_id:id
appfiy_scope  -->  appfiy_page : page_id:id
appfiy_style_group_properties  -->  appfiy_style_group : style_group_id:id
appfiy_style_group_properties  -->  appfiy_style_properties : style_property_id:id
appfiy_theme  -->  appfiy_global_config : appbar_id:id
appfiy_theme  -->  appfiy_global_config : navbar_id:id
appfiy_theme  -->  appfiy_global_config : drawer_id:id
appfiy_theme_component  -->  appfiy_component : component_parent_id:id
appfiy_theme_component  -->  appfiy_component : component_id:id
appfiy_theme_component  -->  appfiy_theme : theme_id:id
appfiy_theme_component  -->  appfiy_theme_component : parent_id:id
appfiy_theme_component  -->  appfiy_theme_config : theme_config_id:id
appfiy_theme_component  -->  appfiy_theme_page : theme_page_id:id
appfiy_theme_component_style  -->  appfiy_style_group : style_group_id:id
appfiy_theme_component_style  -->  appfiy_theme : theme_id:id
appfiy_theme_component_style  -->  appfiy_theme_component : theme_component_id:id
appfiy_theme_config  -->  appfiy_global_config : global_config_id:id
appfiy_theme_config  -->  appfiy_theme : theme_id:id
appfiy_theme_page  -->  appfiy_page : page_id:id
appfiy_theme_page  -->  appfiy_theme : theme_id:id
appfiy_theme_photo_gallery  -->  appfiy_theme : theme_id:id
appza_fluent_license_info  -->  appfiy_build_domain : build_domain_id:id
appza_free_trial_request  -->  appza_fluent_license_info : premium_license_id:id
appza_popup_messages_android  -->  appza_fluent_informations : product_id:id
appza_product_addons  -->  appza_fluent_informations : product_id:id
appza_product_addons_versions  -->  appza_product_addons : addon_id:id
appza_request_logs  -->  users : user_id:id
license_message_details  -->  license_messages : message_id:id
license_messages  -->  appza_fluent_informations : product_id:id
license_messages  -->  appza_product_addons : addon_id:id
license_messages  -->  license_logics : license_logic_id:id
