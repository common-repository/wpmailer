<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wewp.dev
 * @since      1.0.0
 *
 * @package    Wpmailer
 * @subpackage Wpmailer/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wpmailer
 * @subpackage Wpmailer/includes
 * @author     wewp.dev <contact@wewp.dev>
 */
class Wpmailer_api
{
    protected $elementor_email_content = null;
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Wpmailer_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    public function __construct($loader)
    {
        $this->loader = $loader;
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }


    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $this->loader->add_action('wp_ajax_nopriv_get_templates', $this, 'get_templates');
        $this->loader->add_action('wp_ajax_nopriv_create_or_update_template', $this, 'create_or_update_template');
        $this->loader->add_action('wp_ajax_nopriv_get_template', $this, 'get_template');
        $this->loader->add_action('wp_ajax_nopriv_get_cf7_list_forms', $this, 'get_cf7_list_forms');
        $this->loader->add_action('wp_ajax_nopriv_get_cf7_integrations', $this, 'get_cf7_integrations');
        $this->loader->add_action('wp_ajax_nopriv_create_or_update_integration', $this, 'create_or_update_integration');
        $this->loader->add_action('wp_ajax_nopriv_wpmailer_file_upload', $this, 'file_upload');
        $this->loader->add_action('wp_ajax_nopriv_get_all_images', $this, 'get_all_images');
        $this->loader->add_action('wp_ajax_nopriv_delete_CF7_integration', $this, 'delete_CF7_integration');
        $this->loader->add_action('wp_ajax_nopriv_delete_template', $this, 'delete_template');
        $this->loader->add_action('wp_ajax_nopriv_need_to_upgrade', $this, 'is_limit_to_one_template');

        $this->loader->add_action('wp_ajax_delete_template', $this, 'delete_template');
        $this->loader->add_action('wp_ajax_delete_CF7_integration', $this, 'delete_CF7_integration');
        $this->loader->add_action('wp_ajax_get_templates', $this, 'get_templates');
        $this->loader->add_action('wp_ajax_create_or_update_template', $this, 'create_or_update_template');
        $this->loader->add_action('wp_ajax_get_template', $this, 'get_template');
        $this->loader->add_action('wp_ajax_get_cf7_list_forms', $this, 'get_cf7_list_forms');
        $this->loader->add_action('wp_ajax_get_cf7_integrations', $this, 'get_cf7_integrations');
        $this->loader->add_action('wp_ajax_create_or_update_integration', $this, 'create_or_update_integration');
        $this->loader->add_action('wp_ajax_wpmailer_file_upload', $this, 'file_upload');
        $this->loader->add_action('wp_ajax_get_all_images', $this, 'get_all_images');
        $this->loader->add_action('wp_ajax_need_to_upgrade', $this, 'is_limit_to_one_template');


        $this->loader->add_action('elementor_pro/forms/wp_mail_message', $this, 'elementor_wp_mail_message', 10, 1);
        $this->loader->add_action('elementor_pro/forms/process', $this, 'elementor_forms_process', 10, 2);
        $this->loader->add_action('wpcf7_before_send_mail', $this, 'wpcf7_change_text_to_mail_body', 10, 1);
        $this->loader->add_action('wpcf7_mail_sent', $this, 'wpcf7_mail_sent', 10, 1);
        // $this->loader->add_action('http_api_curl', $this , 'custom_curl_resolve', 10, 3);

        add_shortcode('wpmailer', [$this, 'template_shortcode']);
    }

    public function is_limit_to_one_template($returnValue = false)
    {
        global $wpm_fs;
        global $wpdb;

        $response = new stdClass();
        $results = $wpdb->get_results("SELECT COUNT(*) as count FROM wpmailer_templates");
        $response->need_to_upgrade = ($results[0]->count > 0) && $wpm_fs->is_not_paying();
        if ($returnValue) {
            return  $response->need_to_upgrade;
        }
        wp_die(wp_json_encode($response));
    }

    public function check_if_payed_user($returnValue = false)
    {
        global $wpm_fs;
        $response = new stdClass();
        $response->is_premium = $wpm_fs->is_not_paying();
        if ($returnValue) {
            return  $response->is_premium;
        }
        wp_die(wp_json_encode($response));
    }

    public function elementor_forms_process($record, $ajax_handler)
    {
        $settings = $record->get('form_settings');

        if (false !== strpos($settings['email_content'], '[wpmailer')) {
            $settings['email_content'] = do_shortcode($settings['email_content']);
            foreach ($record->get('sent_data') as $key => $value) {
                $pattern = "/(\[field[^]]*id=[\"|']{$key}[\"|'][^]]*\])/";
                $settings['email_content'] = preg_replace($pattern, $value, $settings['email_content']);
            }
        }

        if (false !== strpos($settings['email_content_2'], '[wpmailer')) {
            $settings['email_content_2'] = do_shortcode($settings['email_content_2']);
            foreach ($record->get('sent_data') as $key => $value) {
                $pattern = "/(\[field[^]]*id=[\"|']{$key}[\"|'][^]]*\])/";
                $settings['email_content_2'] = preg_replace($pattern, $value, $settings['email_content_2']);
            }
        }

        $record->set('form_settings', $settings);
    }

    public function elementor_wp_mail_message($email_text)
    {
        return $this->elementor_email_content !== null ? $this->elementor_email_content : $email_text;
    }

    public function delete_CF7_integration()
    {
        global $wpdb;

        $response = new stdClass();
        $POST      = array_map('stripslashes_deep', $_POST);

        if (empty($POST['id'])) {
            $response->success = false;
            wp_die(wp_json_encode($response));
        }

        $result = $wpdb->update(
            'wpmailer_plugin_integrations_cf7',
            array(
                'deletedAt'   => date("Y-m-d H:i:s"),
            ),
            array(
                'id' => $POST['id'],
            )
        );


        $response->success = $result === 1;

        wp_die(wp_json_encode($response));
    }

    public function delete_template()
    {
        global $wpdb;

        $response = new stdClass();
        $POST      = array_map('stripslashes_deep', $_POST);

        if (empty($POST['id'])) {
            $response->success = false;
            wp_die(wp_json_encode($response));
        }

        $result = $wpdb->update(
            'wpmailer_templates',
            array(
                'deletedAt'   => date("Y-m-d H:i:s"),
            ),
            array(
                'id' => $POST['id'],
            )
        );


        $response->success = $result === 1;

        wp_die(wp_json_encode($response));
    }
    public function get_all_images()
    {
        $query_images_args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        );

        $query_images = new WP_Query($query_images_args);

        $images = array();
        foreach ($query_images->posts as $image) {
            $images[] = wp_get_attachment_url($image->ID);
        }

        $response = new stdClass();
        $response->images = $images;

        wp_die(wp_json_encode($response));
    }
    public function file_upload()
    {

        $usingUploader = 2;

        $fileErrors = array(
            0 => "There is no error, the file uploaded with success",
            1 => "The uploaded file exceeds the upload_max_files in server settings",
            2 => "The uploaded file exceeds the MAX_FILE_SIZE from html form",
            3 => "The uploaded file uploaded only partially",
            4 => "No file was uploaded",
            6 => "Missing a temporary folder",
            7 => "Failed to write file to disk",
            8 => "A PHP extension stoped file to upload"
        );

        $posted_data =  isset($_POST) ? $_POST : array();
        $file_data = isset($_FILES) ? $_FILES : array();

        $data = array_merge($posted_data, $file_data);

        $response = array();

        if ($usingUploader == 1) {
            $uploaded_file = wp_handle_upload($data['file'], array('test_form' => false));

            if ($uploaded_file && !isset($uploaded_file['error'])) {
                $response['response'] = "SUCCESS";
                $response['filename'] = basename($uploaded_file['url']);
                $response['url'] = $uploaded_file['url'];
                $response['type'] = $uploaded_file['type'];
            } else {
                $response['response'] = "ERROR";
                $response['error'] = $uploaded_file['error'];
            }
        } elseif ($usingUploader == 2) {
            $attachment_id = media_handle_upload('file', 0);

            if (is_wp_error($attachment_id)) {
                $response['response'] = "ERROR";
                $response['error'] = $fileErrors[$data['file']['error']];
            } else {
                $fullsize_path = get_attached_file($attachment_id);
                $pathinfo = pathinfo($fullsize_path);
                $url = wp_get_attachment_url($attachment_id);
                $response['response'] = "SUCCESS";
                $response['filename'] = $pathinfo['filename'];
                $response['url'] = $url;
                $type = $pathinfo['extension'];
                if (
                    $type == "jpeg"
                    || $type == "jpg"
                    || $type == "png"
                    || $type == "gif"
                ) {
                    $type = "image/" . $type;
                }
                $response['type'] = $type;
            }
        }

        echo json_encode($response);
        die();
    }
    public function template_shortcode($atts)
    {
        if (empty($atts['template_id'])) {
            return '';
        }

        $id = sanitize_text_field($atts['template_id']);
        $template = $this->get_template_row($id);
        if ($template === null) {
            return '';
        }

        return $template->html;
    }

    // public function custom_curl_resolve( $handle, $r, $url ) {
    //     curl_setopt($handle, CURLOPT_RESOLVE, array(
    //         "api.wordpress.org:80:66.155.40.187", 
    //         "api.wordpress.org:443:66.155.40.187", 
    //         "downloads.wordpress.org:80:66.155.40.203", 
    //         "downloads.wordpress.org:443:66.155.40.203")
    //     );
    // }

    private function get_template_row($value, $field = 'id')
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM wpmailer_templates WHERE `{$field}`=%s", $value));
        if (empty($results)) {
            return null;
        }
        return $results[0];
    }

    public function wpcf7_change_text_to_mail_body($contact_form)
    {
        global $wpdb;

        $mail = $contact_form->prop('mail');
        $mail_2 = $contact_form->prop('mail_2');

        $integration = $this->get_cf7_integration_by_cf7_id($contact_form->id);
        if ($integration === null) {
            return;
        }

        if ($mail_2['active']) {
            $user_template = $this->get_template_row($integration->user_template_id);
            if ($user_template !== null && !empty($user_template->html)) {
                $html = $user_template->html;
                $mail_2['body'] = $html;
                $mail_2['use_html'] = true;
            }
        }

        if ($mail['active']) {
            $admin_template = $this->get_template_row($integration->admin_template_id);
            if ($admin_template !== null && !empty($admin_template->html)) {
                $html = $admin_template->html;
                $mail['body'] = $html;
                $mail['use_html'] = true;
            }
        }

        $contact_form->set_properties(array('mail' => $mail, 'mail_2' => $mail_2));
    }

    private function get_cf7_integration_by_cf7_id($id)
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM wpmailer_plugin_integrations_cf7 WHERE `cf7_post_id`=%s AND `deletedAt` is null", $id));
        if (empty($results)) {
            return null;
        }
        return $results[0];
    }

    public function wpcf7_mail_sent($contact_form)
    {
    }

    public function get_cf7_list_forms()
    {
        global $wpdb;
        $posts = get_posts(array(
            'post_type'     => 'wpcf7_contact_form',
            'numberposts'   => -1
        ));
        $response = new stdClass();
        $response->posts = $posts;

        wp_die(wp_json_encode($response));
    }

    public function get_template()
    {
        global $wpdb;

        if (empty($_POST['id'])) {
            wp_die(wp_json_encode(['error_code' => 2]));
        }

        $id = sanitize_text_field($_POST['id']);
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM wpmailer_templates WHERE `id`=%s", $id));
        $response = new stdClass();
        $response->template = !empty($results) ? $results['0'] : null;

        wp_die(wp_json_encode($response));
    }

    public function get_cf7_integrations()
    {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM wpmailer_plugin_integrations_cf7 INNER JOIN wp_posts ON wpmailer_plugin_integrations_cf7.cf7_post_id=wp_posts.ID WHERE wpmailer_plugin_integrations_cf7.deletedAt is null  ORDER BY updatedAt DESC ");
        $response = new stdClass();
        $response->integrations = $results;
        wp_die(wp_json_encode($response));
    }

    public function get_templates()
    {
        global $wpdb;
        $limit1 = $this->is_limit_to_one_template(true) ? 'LIMIT 1' : '';
        $results = $wpdb->get_results("SELECT * FROM wpmailer_templates ORDER BY updatedAt DESC {$limit1}");
        $response = new stdClass();
        $response->templates = $results;
        wp_die(wp_json_encode($response));
    }

    private function saveTemplateScreenshot($data)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }

            $data = base64_decode($data);

            if ($data === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $filename = md5($data) . '.' . $type;
        $dir = wp_get_upload_dir()['basedir'] . "/wpmailer";
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $filepath = "{$dir}/{$filename}";

        file_put_contents($filepath, $data);

        return $filename;
    }
    public function create_or_update_template()
    {
        global $wpdb;

        // validation inputs by pattern

        $response = new stdClass();
        $POST      = array_map('stripslashes_deep', $_POST);
        $name = !empty($POST['template']['name']) ? $POST['template']['name'] : '#' . rand(100000, 999999);
        $updatedAt = date("Y-m-d H:i:s");
        $filename = null;
        try {
            $filename = $this->saveTemplateScreenshot($POST['template']['templateDataUrl']);
        } catch (Exception $err) {
        }

        $results = $wpdb->replace(
            'wpmailer_templates',
            array(
                'id' => !empty($POST['template']['id']) ? $POST['template']['id'] : null,
                'name'        => $name,
                'json'              => json_encode($POST['template']['json']),
                'html'              => $POST['html'],
                'screenshot_path'            => $filename,
                //'screenshot'            => serialize($POST['screenshot']),
                // 'updatedAt'   => $updatedAt,
                //'createdAt'   => date("Y-m-d H:i:s"),
            ),
            array(
                '%d', '%s', '%s', '%s', '%s'
            )
        );


        $response->results = $results;
        $template = $POST['template'];
        $template['json'] = json_encode($POST['template']['json']);
        $template['name'] = $name;
        $template['updatedAt'] = $updatedAt;
        $template['id'] = !empty($template['id']) ? $template['id'] : $wpdb->insert_id;
        $response->template = $template;

        wp_die(wp_json_encode($response));
    }

    public function create_or_update_integration()
    {
        global $wpdb;

        // validation inputs by pattern

        $response = new stdClass();
        $POST      = array_map('stripslashes_deep', $_POST);
        $results = $wpdb->replace(
            'wpmailer_plugin_integrations_cf7',
            array(
                'id' => !empty($POST['integration']['id']) ? (int)$POST['integration']['id'] : null,
                'cf7_post_id'        => !empty($POST['integration']['cf7_post_id']) ? (int)$POST['integration']['cf7_post_id'] : null,
                'user_template_id'            => !empty($POST['integration']['user_template_id']) ? (int)$POST['integration']['user_template_id'] : null,
                'admin_template_id'            => !empty($POST['integration']['admin_template_id']) ? (int)$POST['integration']['admin_template_id'] : null,
                //'screenshot'            => serialize($POST['screenshot']),
                //'createdAt'   => date("Y-m-d H:i:s"),
            ),
            array(
                '%d', '%d', '%d', '%d'
            )
        );

        $response->results = $results;
        $integration = $POST['integration'];
        if (!isset($integration['post_title'])) {
            $results = $wpdb->get_results("SELECT * FROM wp_posts  WHERE `id`={$POST['integration']['cf7_post_id']}  LIMIT 1");
            if (!empty($results)) {
                $integration['post_title']  = $results[0]->post_title;
            }
        }
        $integration['id'] = !empty($integration['id']) ? $integration['id'] : $wpdb->insert_id;
        $response->integration = $integration;

        wp_die(wp_json_encode($response));
    }


    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
    }
}
