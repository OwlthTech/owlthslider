

## index.php <a id="index_php"></a>


## owlthslider.php <a id="owlthslider_php"></a>
### Functions
- **activate_owlthslider**
- **deactivate_owlthslider**
- **run_owlthslider**


## uninstall.php <a id="uninstall_php"></a>


## class-owlthslider-admin.php <a id="class-owlthslider-admin_php"></a>
### Classes
- Owlthslider_Admin
### Functions
- **__construct**
  - Parameters: $plugin_name, $version
- **enqueue_styles_scripts**
  - Parameters: $hook
- **enqueue_page_selection**
- **os_register_slider_cpt_and_taxonomy**
- **os_add_shortcode_column**
  - Parameters: $columns
- **os_shortcode_column_content**
  - Parameters: $column, $post_id
- **os_ajax_refresh_reviews**
- **redirect_new_slider_to_type_selection**
- **add_slider_type_selection_page**
- **os_render_slider_type_selection_page**
- **handle_os_slider_creation**


## index.php <a id="index_php"></a>


## fetch.php <a id="fetch_php"></a>
### Functions
- **os_fetch_google_reviews**
  - Parameters:  $google_place_id, $refresh = false 


## index.php <a id="index_php"></a>


## api.php <a id="api_php"></a>
### Functions
- **os_register_slider_rest_routes**
- **os_slider_get_permission**
  - Parameters: $request
- **os_slider_post_permission**
  - Parameters: $request
- **os_get_slider_data**
  - Parameters: $request
- **os_update_slider_data**
  - Parameters: $request


## cpt.php <a id="cpt_php"></a>
### Functions
- **os_register_cpt**
  - Parameters: $cpt_slug, $cpt_taxonomies
- **os_register_taxonomy**
  - Parameters: $cpt_slug, $cpt_taxonomies


## index.php <a id="index_php"></a>


## render-old.php <a id="render-old_php"></a>
### Functions
- **os_slider_render_table**
  - Parameters: $post
- **render_table_rows**
  - Parameters: $index, $data = []
- **render_table_row_template**
- **os_slider_render_reviews_settings**
  - Parameters: $post
- **os_render_reviews_table**
  - Parameters: $post_id, $google_place_id, $refresh = false


## render.php <a id="render_php"></a>
### Functions
- **os_render_slider_data_table**
  - Parameters: $post
- **render_table_row**
  - Parameters: $index, $slide, $schema
- **render_table_row_template**
- **os_render_fieldset**
  - Parameters: $field_key, $field, $value, $index
- **os_render_field**
  - Parameters: $name, $field, $value


## sanitize.php <a id="sanitize_php"></a>
### Functions
- **os_pre_update_slider_meta**
  - Parameters:  $meta_value, $object_id, $meta_key 
- **os_sanitize_and_validate_meta**
  - Parameters:  $type, &$errors 
- **os_slider_sanitize_slide**
  - Parameters:  $slide, $type 
- **os_sanitize_and_validate_options_meta**
  - Parameters:  $post_id, $is_ajax, &$errors 
- **os_validate_field_type**
  - Parameters:  $type, $value 


## save.php <a id="save_php"></a>
### Functions
- **test_os_save_data**
  - Parameters:  $post_id 
- **os_save_data_ajax**


## schema.php <a id="schema_php"></a>
### Functions
- **os_get_slides_schema**
- **os_get_slider_option_schema**


## class-owlthslider-metaboxes.php <a id="class-owlthslider-metaboxes_php"></a>
### Classes
- Class_Owlthslider_Metaboxes
### Functions
- **__construct**
- **os_remove_meta_box**
- **os_add_meta_box**
- **os_slider_render_options**
  - Parameters: $post
- **handleDependencies**
- **os_slider_render_types**
  - Parameters: $post


## class-owlthslider-activator.php <a id="class-owlthslider-activator_php"></a>
### Classes
- Owlthslider_Activator
### Functions
- **activate**


## class-owlthslider-deactivator.php <a id="class-owlthslider-deactivator_php"></a>
### Classes
- Owlthslider_Deactivator
### Functions
- **deactivate**


## class-owlthslider-i18n.php <a id="class-owlthslider-i18n_php"></a>
### Classes
- Owlthslider_i18n
### Functions
- **load_plugin_textdomain**


## class-owlthslider-loader.php <a id="class-owlthslider-loader_php"></a>
### Classes
- Owlthslider_Loader
### Functions
- **__construct**
- **add_action**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add_filter**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add**
  - Parameters:  $hooks, $hook, $component, $callback, $priority, $accepted_args 
- **run**


## class-owlthslider.php <a id="class-owlthslider_php"></a>
### Classes
- Owlthslider
### Functions
- **__construct**
- **load_dependencies**
- **set_locale**
- **define_admin_hooks**
- **define_public_hooks**
- **run**
- **get_plugin_name**
- **get_loader**
- **get_version**


## index.php <a id="index_php"></a>


## class-owlthslider-public.php <a id="class-owlthslider-public_php"></a>
### Classes
- Owlthslider_Public
### Functions
- **__construct**
  - Parameters: $plugin_name, $version
- **enqueue_styles_scripts**
- **os_slider_conditional_enqueue**
  - Parameters: $content
- **os_render_slider_in_preview**
  - Parameters: $content
- **os_slider_shortcode**
  - Parameters: $atts


## index.php <a id="index_php"></a>


## index.php <a id="index_php"></a>


## owlthslider.php <a id="owlthslider_php"></a>
### Functions
- **activate_owlthslider**
  - Parameters: 
- **deactivate_owlthslider**
  - Parameters: 
- **run_owlthslider**
  - Parameters: 


## uninstall.php <a id="uninstall_php"></a>


## class-owlthslider-admin.php <a id="class-owlthslider-admin_php"></a>
### Classes
- Owlthslider_Admin
### Functions
- **__construct**
  - Parameters: $plugin_name, $version
- **enqueue_styles_scripts**
  - Parameters: $hook
- **enqueue_page_selection**
  - Parameters: 
- **os_register_slider_cpt_and_taxonomy**
  - Parameters: 
- **os_add_shortcode_column**
  - Parameters: $columns
- **os_shortcode_column_content**
  - Parameters: $column, $post_id
- **os_ajax_refresh_reviews**
  - Parameters: 
- **redirect_new_slider_to_type_selection**
  - Parameters: 
- **add_slider_type_selection_page**
  - Parameters: 
- **os_render_slider_type_selection_page**
  - Parameters: 
- **handle_os_slider_creation**
  - Parameters: 


## index.php <a id="index_php"></a>


## fetch.php <a id="fetch_php"></a>
### Functions
- **os_fetch_google_reviews**
  - Parameters:  $google_place_id, $refresh = false 


## index.php <a id="index_php"></a>


## api.php <a id="api_php"></a>
### Functions
- **os_register_slider_rest_routes**
  - Parameters: 
- **os_slider_get_permission**
  - Parameters: $request
- **os_slider_post_permission**
  - Parameters: $request
- **os_get_slider_data**
  - Parameters: $request
- **os_update_slider_data**
  - Parameters: $request


## cpt.php <a id="cpt_php"></a>
### Functions
- **os_register_cpt**
  - Parameters: $cpt_slug, $cpt_taxonomies
- **os_register_taxonomy**
  - Parameters: $cpt_slug, $cpt_taxonomies


## index.php <a id="index_php"></a>


## render-old.php <a id="render-old_php"></a>
### Functions
- **os_slider_render_table**
  - Parameters: $post
- **render_table_rows**
  - Parameters: $index, $data = []
- **render_table_row_template**
  - Parameters: 
- **os_slider_render_reviews_settings**
  - Parameters: $post
- **os_render_reviews_table**
  - Parameters: $post_id, $google_place_id, $refresh = false


## render.php <a id="render_php"></a>
### Functions
- **os_render_slider_data_table**
  - Parameters: $post
- **render_table_row**
  - Parameters: $index, $slide, $schema
- **render_table_row_template**
  - Parameters: 
- **os_render_fieldset**
  - Parameters: $field_key, $field, $value, $index
- **os_render_field**
  - Parameters: $name, $field, $value


## sanitize.php <a id="sanitize_php"></a>
### Functions
- **os_pre_update_slider_meta**
  - Parameters:  $meta_value, $object_id, $meta_key 
- **os_sanitize_and_validate_meta**
  - Parameters:  $type, &$errors 
- **os_slider_sanitize_slide**
  - Parameters:  $slide, $type 
- **os_sanitize_and_validate_options_meta**
  - Parameters:  $post_id, $is_ajax, &$errors 
- **os_validate_field_type**
  - Parameters:  $type, $value 


## save.php <a id="save_php"></a>
### Functions
- **test_os_save_data**
  - Parameters:  $post_id 
- **os_save_data_ajax**
  - Parameters: 


## schema.php <a id="schema_php"></a>
### Functions
- **os_get_slides_schema**
  - Parameters: 
- **os_get_slider_option_schema**
  - Parameters: 


## sanitize copy.php <a id="sanitize_copy_php"></a>
### Functions
- **os_slider_sanitize_slide**
  - Parameters: $slide
- **os_slider_sanitize_options**
  - Parameters: $options
- **os_sanitize_boolean**
  - Parameters: $value
- **os_sanitize_datetime**
  - Parameters: $value
- **os_sanitize_date**
  - Parameters: $value


## sanitize-2.php <a id="sanitize-2_php"></a>
### Functions
- **os_slider_sanitize_slide**
  - Parameters: $slide
- **os_slider_sanitize_options**
  - Parameters: $options


## save copy.php <a id="save_copy_php"></a>
### Functions
- **os_sanitize_validate_data_handler**
  - Parameters: $data
- **os_save_data_handler**
  - Parameters: $post_id, $data
- **os_save_data_ajax**
  - Parameters: 
- **os_save_data**
  - Parameters: $post_id
- **os_sanitize_validate_data_using_schema**
  - Parameters: $data, $schema
- **os_slider_sanitize_slide_using_schema**
  - Parameters: $slide
- **os_slider_sanitize_options_using_schema**
  - Parameters: $options


## save-2.php <a id="save-2_php"></a>
### Functions
- **os_sanitize_validate_data_handler**
  - Parameters: $data
- **os_save_data_handler**
  - Parameters: $post_id, $data
- **os_save_data_ajax**
  - Parameters: 
- **os_save_data**
  - Parameters: $post_id
- **os_sanitize_validate_data_using_schema**
  - Parameters: $data, $schema
- **os_slider_sanitize_slide_using_schema**
  - Parameters: $slide
- **os_slider_sanitize_options_using_schema**
  - Parameters: $options


## schema.php <a id="schema_php"></a>
### Functions
- **get_schema_carousel**
  - Parameters: 
- **get_schema_reviews**
  - Parameters: 
- **get_schema_products**
  - Parameters: 
- **get_full_slider_schema**
  - Parameters: 
- **get_schema_slider_options**
  - Parameters: 
- **os_register_meta**
  - Parameters: 
- **sanitize_slider_meta**
  - Parameters:  $meta_value, $meta_key = '', $object_type = '' 
- **sanitize_field**
  - Parameters: $value, $schema
- **validate_slider_meta**
  - Parameters:  $meta_value, $meta_key = '', $object_type = '', $object_subtype = '' 
- **validate_field**
  - Parameters: $value, $schema


## class-owlthslider-metaboxes.php <a id="class-owlthslider-metaboxes_php"></a>
### Classes
- Class_Owlthslider_Metaboxes
### Functions
- **__construct**
  - Parameters: 
- **os_remove_meta_box**
  - Parameters: 
- **os_add_meta_box**
  - Parameters: 
- **os_slider_render_options**
  - Parameters: $post
- **handleDependencies**
  - Parameters: 
- **os_slider_render_types**
  - Parameters: $post


## owlthslider-admin-display.php <a id="owlthslider-admin-display_php"></a>


## class-owlthslider-activator.php <a id="class-owlthslider-activator_php"></a>
### Classes
- Owlthslider_Activator
### Functions
- **activate**
  - Parameters: 


## class-owlthslider-deactivator.php <a id="class-owlthslider-deactivator_php"></a>
### Classes
- Owlthslider_Deactivator
### Functions
- **deactivate**
  - Parameters: 


## class-owlthslider-i18n.php <a id="class-owlthslider-i18n_php"></a>
### Classes
- Owlthslider_i18n
### Functions
- **load_plugin_textdomain**
  - Parameters: 


## class-owlthslider-loader.php <a id="class-owlthslider-loader_php"></a>
### Classes
- Owlthslider_Loader
### Functions
- **__construct**
  - Parameters: 
- **add_action**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add_filter**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add**
  - Parameters:  $hooks, $hook, $component, $callback, $priority, $accepted_args 
- **run**
  - Parameters: 


## class-owlthslider.php <a id="class-owlthslider_php"></a>
### Classes
- Owlthslider
### Functions
- **__construct**
  - Parameters: 
- **load_dependencies**
  - Parameters: 
- **set_locale**
  - Parameters: 
- **define_admin_hooks**
  - Parameters: 
- **define_public_hooks**
  - Parameters: 
- **run**
  - Parameters: 
- **get_plugin_name**
  - Parameters: 
- **get_loader**
  - Parameters: 
- **get_version**
  - Parameters: 


## index.php <a id="index_php"></a>


## class-owlthslider-public.php <a id="class-owlthslider-public_php"></a>
### Classes
- Owlthslider_Public
### Functions
- **__construct**
  - Parameters: $plugin_name, $version
- **enqueue_styles_scripts**
  - Parameters: 
- **os_slider_conditional_enqueue**
  - Parameters: $content
- **os_render_slider_in_preview**
  - Parameters: $content
- **os_slider_shortcode**
  - Parameters: $atts


## index.php <a id="index_php"></a>


## owlthslider-public-display.php <a id="owlthslider-public-display_php"></a>
