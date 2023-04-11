<?php

/*
Plugin Name: Contact Form 
Plugin URI: https://contactform.com/
Description: Just another contact form plugin. Simple but flexible.
Author: Amine Lamchatab
Author URI: https://ideasilo.wordpress.com/
Text Domain: contact-form
Domain Path: /languages/
Version: 0.1
*/


require_once(ABSPATH . 'wp-admin/includes/upgrade.php');



class ContactForm
{



    static function Plugin_Activation_Hook()
    {
        /**
         * Creation table for ContactForm
         */
        $sql = "CREATE TABLE `wp_contact_form` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `FirstName` varchar(100) NOT NULL,
            `LastName` varchar(100) NOT NULL,
            `Email` varchar(255) NOT NULL,
            `Subject` varchar(255) NOT NULL,
            `Message` text NOT NULL,
            `DateSent` timestamp NOT NULL DEFAULT current_timestamp()
          );
          ";
        dbDelta($sql);
    }

    static function Plugin_Deactivation_Hook()
    {
        /**
         * Delete table for ContactForm
         */
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_form';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }


    // ADD PLUGIN TO WORDPRESS MENU
    public static function contact_form_admin_menu()
    {
        function admin_index()
        {
            // reduire template
            include(dirname(__FILE__) . '/template/' . 'index' . '.php');
        }
        add_menu_page('Contact Form', 'Contact Form',  'manage_options',  'Contact-Form', 'admin_index',  'dashicons-email',  110);
    }
}

/**
 * On activate or uninstall plugin
 */
register_activation_hook(__FILE__, array('ContactForm', 'Plugin_Activation_Hook'));
register_deactivation_hook(__FILE__, array('ContactForm', 'Plugin_Deactivation_Hook'));


add_action('admin_menu', 'ContactForm::contact_form_admin_menu');



// add  Bootstrap_CDN_Scripts to contact form;
function Bootstrap_CDN_Scripts()
{
    // all styles
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');
}
add_action('wp_enqueue_scripts', 'Bootstrap_CDN_Scripts');


// HTML_FORM function will display the form into desired pages :D
function HTML_FORM()
{
    $form = ' <form class="row g-3" method="POST">
<div class="col-md-6">
  <label for="FirstName" class="form-label">FirstName</label>
  <input type="text" class="form-control" name="FirstName" placeholder="FirstName">
</div>
<div class="col-md-6">
  <label for="LastName" class="form-label">LastName</label>
  <input type="text" class="form-control" name="LastName" placeholder="LastName">
</div>
<div class="col-12">
  <label for="Email" class="form-label">Email</label>
  <input type="eamil" class="form-control" name="Email" placeholder="your email">
</div>
<div class="col-12">
  <label for="Subject" class="form-label">Subject</label>
  <input type="text" class="form-control" name="Subject" placeholder="Subject">
</div>
<div class="col-md-12">
  <label for="Message" class="form-label">Message</label>
  <textarea type="text" class="form-control" name="Message" placeholder="Message"></textarea>
</div>
<div class="col-12 gap-2 d-grid">
  <button type="submit" name="ContactForm8" class="btn btn-primary">Contact</button>
</div>
</form> ';

    echo $form;
}

// STORE MAIL FUNCTION will sanitize the inputs and STORE DATA INTO wp_contact_form TABLE
function STORE_MAIL()
{
    if (isset($_POST['ContactForm8'])) {
        // if the submit button is clicked, send the email
        if (
            isset($_POST['FirstName']) && !empty($_POST['FirstName'])
            && isset($_POST['LastName']) && !empty($_POST['LastName'])
            && isset($_POST['Email']) && !empty($_POST['Email'])
            && isset($_POST['Subject']) && !empty($_POST['Subject'])
            && isset($_POST['Message']) && !empty($_POST['Message'])
        ) {
            // sanitize form values
            $FirstName   = sanitize_text_field($_POST["FirstName"]);
            $LastName    = sanitize_text_field($_POST["LastName"]);
            $Email   = sanitize_email($_POST["Email"]);
            $Subject = sanitize_text_field($_POST["Subject"]);
            $Message = esc_textarea($_POST["Message"]);
            // insert message into table 
            global $wpdb;
            $sql = "
                    INSERT INTO `wp_contact_form` (`id`, `FirstName`, `LastName`, `Email`, `Subject`, `Message`) 
                    VALUES (NULL, 
                    '$FirstName', 
                    '$LastName',
                     '$Email', 
                    '$Subject', 
                    '$Message'
              )";
            if ($wpdb->query($sql) == true) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>message sent! </strong> Your message has been recieved thanks for contacting us .
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>message failed! </strong> Your message has not recieved please try again .
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>missing input fields </strong> please fill all the required inputs .
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        }
    }
}



function ShortcodeFunctions()
{
    ob_start();
    STORE_MAIL();
    HTML_FORM();
    return ob_get_clean();
}
add_shortcode('Contact_Form', 'ShortcodeFunctions');
