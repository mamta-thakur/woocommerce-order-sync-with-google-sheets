<?php
// Add custom Theme Functions here


function woosuite_add_custom_content_after_order_table($order) {
    // Replace 'YOUR_IMAGE_URL' with the actual URL of the image
$image_url =  'https://waterfallsinks.com.au/wp-content/uploads/2023/06/Customer-service-whatsapp.png';
$whatsapp_link = 'https://api.whatsapp.com/send/?phone=61430008230&text=Hello+How+Can+Our+Team+Assist+You+Today';
$back_link = 'https://waterfallsinks.com.au/';
echo '<a href="' . esc_url($whatsapp_link) . '" target="_blank"><img src="' . esc_url($image_url) . '" alt="Custom Image" class="custom-content-image"></a>';

echo '<a href="' . esc_url($back_link) . '">Back</a>';
}

// Enqueue custom JavaScript file
function enqueue_custom_js() {
    wp_enqueue_script( 'custom-theme-js', get_stylesheet_directory_uri() . '/js/custom_theme.js', array('jquery'), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_js' );

// Add the woosuite_add_custom_content_after_order_table function to the action hook
// 'woocommerce_order_details_after_order_table' with a priority of 10 and accepting 1 parameter
add_action('woocommerce_order_details_after_order_table', 'woosuite_add_custom_content_after_order_table', 10, 1);
// Removes the decoding attribute from images added inside post content.
add_filter( 'wp_img_tag_add_decoding_attr', '__return_false' );

// Remove the decoding attribute from featured images and the Post Image block.
add_filter( 'wp_get_attachment_image_attributes', function( $attributes ) {
    unset( $attributes['decoding'] );
    return $attributes;
} );
function ti_custom_javascript() {
    ?>
        <script>
                  function clearBackgroundImages() {
           
            var elements = document.querySelectorAll('.slider-wrapper, .bgload');

      
            elements.forEach(function(element) {
                element.style.backgroundImage = 'none';
            });
        }

        setTimeout(clearBackgroundImages, 3000); 
        </script>
    <?php
}
add_action('wp_head', 'ti_custom_javascript');
add_filter('perfmatters_delay_js', function($delay) {
      if(is_front_page()) {
        return true;
    }
    return $delay;
});
add_filter('perfmatters_delay_js_timeout', function($timeout) {
    return '7';
});

// Add custom column to order admin list
add_filter( 'manage_edit-shop_order_columns', 'bbloomer_add_new_order_admin_list_column' );

function bbloomer_add_new_order_admin_list_column( $columns ) {
    $columns['order_sku'] = 'SKU';
    return $columns;
}

// Display content for the custom column
add_action( 'manage_shop_order_posts_custom_column', 'bbloomer_add_new_order_admin_list_column_content' );

function bbloomer_add_new_order_admin_list_column_content( $column ) {
    global $post;

    if ( 'order_sku' === $column ) {
        $order = wc_get_order( $post->ID );
        $items = $order->get_items();

        foreach ( $items as $item ) {
            $product = $item->get_product();
            $sku = $product->get_sku();
            echo $sku . '<br>';
        }
    }
}

/* New meta box in Order page (Upload in WP folder) Starts */
	
	/*// Step 1: Define Meta Box
	function custom_order_files_meta_box() {
	    add_meta_box(
	        'custom_order_files_meta_box',
	        __('Order Files', 'textdomain'),
	        'custom_order_files_meta_box_content',
	        'shop_order',
	        'normal',
	        'default'
	    );
	}
	add_action('add_meta_boxes', 'custom_order_files_meta_box');

	// Step 2: Add Fields
	function custom_order_files_meta_box_content($post) {
	    echo '<p>' . __('Upload files for this order:', 'textdomain') . '</p>';
	    
	    // Get already uploaded files
	    $uploaded_files = array(
	        'order_request' => get_post_meta($post->ID, 'order_request', true),
	        'order_invoice_from_supplier' => get_post_meta($post->ID, 'order_invoice_from_supplier', true),
	        'invoice_payment_receipt_to_supplier' => get_post_meta($post->ID, 'invoice_payment_receipt_to_supplier', true)
	    );
	    
    	foreach ($uploaded_files as $file_field => $file_info) {
            $label_text = str_replace('_', ' ', $file_field); // Replace underscores with spaces
    		echo '<label for="' . $file_field . '">' . __($label_text . ':', 'textdomain') . '</label>';
            if (!empty($file_info)) {
                // Display file link if file is already uploaded
                $file_url = wp_get_attachment_url($file_info['attachment_id']);
                echo ' <a href="' . esc_url($file_url) . '">' . basename($file_info['file']) . '</a><br/>';
                echo '<input type="file" id="' . $file_field . '" name="' . $file_field . '" /><br/><br/>';
            } else {
                echo '<input type="file" id="' . $file_field . '" name="' . $file_field . '" /><br/>';
            }
        }
        
        // Add the button inside the metabox
        echo '<div id="custom-order-files-button"><button class="button" id="save-custom-order-files">' . __('Save Order Files', 'textdomain') . '</button></div>';
	    // You can add more styling or instructions here as needed
	}

	// Step 3: Handle File Uploads
	function save_custom_order_files_meta($post_id) {
	    
	    $upload_dir = wp_upload_dir(); // Get default upload directory
	    
	    // Define custom folder path
	    $upload_path = $upload_dir['basedir'] . '/order_files/';

	    // Create the folder if it doesn't exist
	    if (!file_exists($upload_path)) {
	        wp_mkdir_p($upload_path);
	    }

	    // Check if the upload path exists and is writable
	    if (!is_writable($upload_path)) {
	        error_log('Upload path is not writable: ' . $upload_path);
	        return; // Abort if the upload path is not writable
	    }
	    
	    $uploaded_files = ['order_request', 'order_invoice_from_supplier', 'invoice_payment_receipt_to_supplier'];

	    // Initialize array to store uploaded file names
	    $uploaded_files_names = array();

	    // Loop through each file field
	    foreach ($uploaded_files as $file_field) {
	        
	        if (!empty($_FILES[$file_field]['name'])) {
	            $file = $_FILES[$file_field];
	            $file_name = sanitize_file_name($file['name']);
	            $file_path = $upload_path . $file_name;

	            // Move the uploaded file to the destination directory
	            if (move_uploaded_file($file['tmp_name'], $file_path)) {
	                // File uploaded successfully, save file information to database
	                $attachment = array(
	                    'post_mime_type' => $file['type'],
	                    'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
	                    'post_content' => '',
	                    'post_status' => 'inherit'
	                );
	                $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
	                if (!is_wp_error($attachment_id)) {
	                    // Update post meta with attachment ID
	                    update_post_meta($post_id, $file_field, array('attachment_id' => $attachment_id, 'file' => $file_path));
	                    $uploaded_files_names[] = $file_name; // Store file name in the array
	                }
	            } else {
	                error_log('Error moving uploaded file: ' . $file_path);
	            }
	        }
	    }

	    // Return uploaded file names
	    return $uploaded_files_names;
	}
	add_action('save_post', 'save_custom_order_files_meta');

	// JavaScript to handle button click event and trigger file saving
	function custom_order_files_button_script() {
	    global $post; // Add this line to access the $post variable
	    ?>
	    <script>
	        document.addEventListener('DOMContentLoaded', function() {
	            var mainForm = document.getElementById('post');
	            if (mainForm) {
	                mainForm.setAttribute('enctype', 'multipart/form-data');
	            }
	        });
	    </script>
	    <script>
	        jQuery(function($) {
	            $('#save-custom-order-files').click(function(e) {
	                e.preventDefault();
	                var formData = new FormData($('form[name="post"]')[0]); // Serialize data from the form with name "order_files"
	                formData.append('action', 'save_custom_order_files');
	                formData.append('post_id', <?php echo json_encode($post->ID); ?>);
	                formData.append('security', '<?php echo wp_create_nonce("save_custom_order_files_nonce"); ?>');

	                $.ajax({
	                    url: ajaxurl,
	                    type: 'POST',
	                    data: formData,
	                    processData: false,
	                    contentType: false,
	                    success: function(response) {
	                        if (response.success) {
	                            alert(response.message); // Display success message
	                           // location.reload(); // Reload the page
	                        } else {
	                            alert('Error: ' + response.message); // Display error message
	                        }
	                    }
	                });
	            });
	        });
	    </script>
	    <?php
	}
	add_action('admin_footer', 'custom_order_files_button_script');

	// Callback function to handle AJAX request and save files
	function save_custom_order_files_ajax_handler() {
	    check_ajax_referer('save_custom_order_files_nonce', 'security');

	    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	    if ($post_id > 0) {
	        save_custom_order_files_meta($post_id); // Call the function to save files
	        $response = array(
	            'success' => true,
	            'message' => __('Order files saved successfully.', 'textdomain')
	        );
	    } else {
	        $response = array(
	            'success' => false,
	            'message' => __('Invalid order.', 'textdomain')
	        );
	    }

	    // Send JSON response
	    wp_send_json($response);
	}
	add_action('wp_ajax_save_custom_order_files', 'save_custom_order_files_ajax_handler');*/


/* New meta box in Order page (Upload in WP folder) Ends */


/*Upload files on drive using Account Service Starts*/
	
	/*error_reporting(E_ALL);
    ini_set('display_errors', 1);*/
    // Step 1: Set up Google Drive API access
    
    // Define the path to the secure directory
    $secure_directory = get_stylesheet_directory() . '/secure/';

    // Include the Google API Client Library
    require_once get_stylesheet_directory() . '/google-api-php-client/autoload.php';
    
    // Replace 'service account key' with the path to your client secret JSON file
    $serviceAccountFilePath  = $secure_directory . 'service-account-key.json';
	
	use Google\Client;
	use Google\Service\Drive;
	use Google\Service\Drive\DriveFile;
	
	// Create Google_Client instance
	$client = new Client();
	$client->setAuthConfig($serviceAccountFilePath);
	$client->addScope(\Google_Service_Drive::DRIVE); // Set scope to Google Drive

	// Authenticate
	$client->fetchAccessTokenWithAssertion();

	// Authorize the client
	if (!$client->getAccessToken()) {
	    echo "Failed to authenticate service account.";
	}

	// Step 2: Create custom metabox
	function custom_metabox_callback($post) {
	    // Retrieve file IDs from post meta
	    $file_ids = get_post_meta($post->ID, 'custom_file_ids', true);
	    
		// Get already uploaded files
	    $uploaded_files = array(
	        'order_request' => get_post_meta($post->ID, 'order_request', true),
	        'order_invoice_from_supplier' => get_post_meta($post->ID, 'order_invoice_from_supplier', true),
	        'invoice_payment_receipt_to_supplier' => get_post_meta($post->ID, 'invoice_payment_receipt_to_supplier', true)
	    );
	    
	    foreach ($uploaded_files as $file_field => $file_info) {
	        $label_text = str_replace('_', ' ', $file_field); // Replace underscores with spaces
	        echo '<label for="' . $file_field . '">' . __(ucwords($label_text) . ':', 'textdomain') . '</label>';
	        if (!empty($file_info)) {
	            /*// Display file link if file is already uploaded
	            $file_url = wp_get_attachment_url($file_info['attachment_id']);*/
	            
	            // Use Google Drive API to generate download link
            	$download_link = get_download_link_from_google_drive($file_info['attachment_id']);
	            echo ' <a href="' . esc_url($download_link) . '">' . basename($file_info['file_name']) . '</a><br/>';
	            echo '<input type="file" id="' . $file_field . '" name="' . $file_field . '" /><br/><br/>';
	        } else {
	            echo '<input type="file" id="' . $file_field . '" name="' . $file_field . '" /><br/><br/>';
	        }
	    }
	    
	    // Add the button inside the metabox
	    echo '<div id="custom-order-files-button"><button class="button" id="save-custom-order-files">' . __('Save Order Files', 'textdomain') . '</button></div>';
	}

	function save_custom_files_meta($post_id) {
	    // Check if files are being uploaded
	    if (!empty($_FILES)) {
	        // Upload files to Google Drive and get file IDs
	        $file_ids = upload_files_to_google_drive($_FILES, $post_id);
	        
	        /*// Save file IDs in post meta
	        update_post_meta($post_id, 'custom_file_ids', $file_ids);*/
	    }
	}

	// Step 3: Implement functions to interact with Google Drive API
	function upload_files_to_google_drive($files, $post_id) {
	    global $client;
	    
	    $driveService = new Drive($client);
	    $file_ids = [];
	    
	    $uploaded_files = ['order_request', 'order_invoice_from_supplier', 'invoice_payment_receipt_to_supplier'];
	    foreach ($uploaded_files as $file_field) {
	    //foreach ($files as $key => $file) {
	    	if (!empty($files[$file_field]['name'])) {
	    		
		    	$file = $files[$file_field];
		        // Check if file was uploaded successfully
		        if ($file['error'] === UPLOAD_ERR_OK) {
		        	// Modify file name if needed
		        	//$new_file_name = "order_".$post_id."_".$file['name'];
		        	$new_file_name = "order_".$post_id.time()."_".$file['name'];
		        	
		            // Prepare file metadata
		            $fileMetadata = new DriveFile([
		                'name' => $new_file_name,
		                'parents' => ['14_Hb9V6c8_xAJ7XpgYgwVji_yVVr-if3'] // Specify the folder ID as the parent
		            ]);
		            
		            // Upload file to Google Drive
		            $content = file_get_contents($file['tmp_name']);
		            $driveFile = $driveService->files->create($fileMetadata, [
		                'data' => $content,
		                'mimeType' => $file['type'],
		                'uploadType' => 'multipart'
		            ]);
		            
		            // Store file ID
		            $file_ids[] = $driveFile->id;
		            
		            // Save file IDs in post meta
		        	//update_post_meta($post_id, $file_field, $driveFile->id);
		        	
		        	// Update post meta with attachment ID
		           	update_post_meta($post_id, $file_field, array('attachment_id' => $driveFile->id, 'file_name' => $new_file_name));
		        
		            
		            
		           // die(' testing ');
		        }
			}
	    }
	    
	    return $file_ids;
	}

	function get_download_link_from_google_drive($file_id) {
	    // Return the download link for the file using the file ID
	    return "https://drive.google.com/uc?id=$file_id";
	    //return "https://drive.google.com/uc?export=download&id=$file_id";
	}

	// Step 4: Hook into WordPress save_post action
	add_action('save_post', 'save_custom_files_meta');

	// Step 5: Display custom metabox on edit page
	add_action('add_meta_boxes', function() {
	   // add_meta_box('custom_metabox', 'Custom Files', 'custom_metabox_callback', 'page', 'normal', 'default');
	    add_meta_box('custom_metabox', 'Upload files for this order:', 'custom_metabox_callback', 'shop_order', 'normal', 'default');
	});
	
	// JavaScript to handle button click event and trigger file saving
	function custom_add_multipart_in_form_for_order_files_script() {
	    global $post; // Add this line to access the $post variable
	    ?>
	    <script>
	        document.addEventListener('DOMContentLoaded', function() {
	            var mainForm = document.getElementById('post');
	            if (mainForm) {
	                mainForm.setAttribute('enctype', 'multipart/form-data');
	            }
	        });
	    </script>
	    <?php
	}
	add_action('admin_footer', 'custom_add_multipart_in_form_for_order_files_script');

/*Upload files on drive using Account Service Ends*/
add_action( 'woocommerce_single_product_summary', 'customizing_add_cart', 1 );
function customizing_add_cart() {

    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15 );
}

function display_image_before_add_to_cart() {
    echo '<img src="https://waterfallsinks.com.au/wp-content/uploads/2024/08/stripe-cart-area.png" alt="PayPal Image" style="width: 100%; height: auto;">';
}
add_action('woocommerce_before_add_to_cart_button', 'display_image_before_add_to_cart');

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

function display_whatsapp_link_after_add_to_cart() {
    echo '<a href="https://api.whatsapp.com/send?phone=61430008230&text=Contact%20our%20customer%20service%20team%20by%20WhatsApp%20chat%20for%20help%20with%WaterfallSinks">
            <img src="https://waterfallsinks.wpengine.com/wp-content/uploads/2023/06/Customer-service-whatsapp.png" alt="Customer Service Image" style="width: 100%; height: auto;">
          </a><br>'; 
	if ( function_exists( 'is_woocommerce' ) ) {
        // Locate the template file and include it
        wc_get_template( 'single-product/share.php' );
    }
}
add_action('woocommerce_after_add_to_cart_button', 'display_whatsapp_link_after_add_to_cart');
