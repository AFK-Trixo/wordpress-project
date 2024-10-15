<?php
/*
Plugin Name: My Custom E-commerce Plugin
Description: A custom plugin for e-commerce functionality.
Version: 1.0
Author: Faris
*/

// Your custom code will go here

function start_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);

function create_product_post_type() {
    register_post_type('product',
        array(
            'labels' => array(
                'name' => __('Products'),
                'singular_name' => __('Product')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'products'),
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        )
    );
}
add_action('init', 'create_product_post_type');

function add_product_meta_boxes() {
    add_meta_box('product_price', 'Product Price', 'product_price_callback', 'product');
}
add_action('add_meta_boxes', 'add_product_meta_boxes');

function product_price_callback($post) {
    $value = get_post_meta($post->ID, '_product_price', true);
    echo '<label for="product_price_field">Price: </label>';
    echo '<input type="text" id="product_price_field" name="product_price_field" value="' . esc_attr($value) . '" />';
}


function save_product_price($post_id) {
    if (array_key_exists('product_price_field', $_POST)) {
        update_post_meta($post_id, '_product_price', $_POST['product_price_field']);
    }
}
add_action('save_post', 'save_product_price');

function display_products_shortcode() {
    // Example array of products (you can replace this with a dynamic product fetching method)
    $products = [
        [
            'name' => 'Lenovo ThinkPad X1 Carbon',
            'price' => '$1600',
            'image' => 'https://m.media-amazon.com/images/I/81fZmxBbQgL.jpg'
        ],
        [
            'name' => 'Dell XPS 13',
            'price' => '$1400',
            'image' => 'https://www.zdnet.com/a/img/resize/a483a468f4b839b1653a01adf5473ceedf1dcbe8/2021/06/08/5167f5ef-3ea7-46bc-bdfc-9d5bf238bd88/dell-xps-13-9310-header.jpg?auto=webp&fit=crop&height=675&width=1200'
        ],
        [
            'name' => 'Asus ROG Strix',
            'price' => '$1200',
            'image' => 'https://m.media-amazon.com/images/I/61exNzohS-L._AC_UF1000,1000_QL80_.jpg'
        ]
    ];

    // Start output buffering to capture the product display
    ob_start();
    ?>
    <div class="product-grid">
        <?php foreach ($products as $index => $product): ?>
            <div class="product-item">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" />
                <h3><?php echo $product['name']; ?></h3>
                <p>Price: <?php echo $product['price']; ?></p>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?php echo $index; ?>" />
                    <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('display_products', 'display_products_shortcode');


function handle_add_to_cart() {
    if (isset($_POST['add_to_cart'])) {
        $product_id = intval($_POST['product_id']);  // This product_id is just the array index

        // Initialize the cart if not already done
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Add product to the cart
        if (!in_array($product_id, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $product_id;
        }

        // Redirect to cart or refresh page to update the cart view
        wp_redirect(home_url('/cart')); // You can also set this to a dedicated cart page
        exit;
    }
}
add_action('wp', 'handle_add_to_cart');

function view_cart_shortcode() {
    // Example array of products
    $products = [
        [
            'name' => 'Lenovo ThinkPad X1 Carbon',
            'price' => '$1600',
            'image' => 'https://m.media-amazon.com/images/I/81fZmxBbQgL.jpg'
        ],
        [
            'name' => 'Dell XPS 13',
            'price' => '$1400',
            'image' => 'https://www.zdnet.com/a/img/resize/a483a468f4b839b1653a01adf5473ceedf1dcbe8/2021/06/08/5167f5ef-3ea7-46bc-bdfc-9d5bf238bd88/dell-xps-13-9310-header.jpg?auto=webp&fit=crop&height=675&width=1200'
        ],
        [
            'name' => 'Asus ROG Strix',
            'price' => '$1200',
            'image' => 'https://m.media-amazon.com/images/I/61exNzohS-L._AC_UF1000,1000_QL80_.jpg'
        ]
    ];

    $output = '<h2>Your Cart</h2>';
    
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        $output .= '<ul>';
        
        foreach ($_SESSION['cart'] as $product_id) {
            $product = $products[$product_id];  // Use the index to get the product
            $output .= '<li>' . $product['name'] . ' – ' . $product['price'] . '</li>';
        }
        
        $output .= '</ul>';
    } else {
        $output .= '<p>Your cart is empty.</p>';
    }
    
    return $output;
}
add_shortcode('view_cart', 'view_cart_shortcode');

function handle_remove_from_cart() {
    if (isset($_POST['remove_from_cart'])) {
        $product_key = intval($_POST['remove_product_id']);

        // Remove the product from the cart array
        if (isset($_SESSION['cart'][$product_key])) {
            unset($_SESSION['cart'][$product_key]);

            // Reindex the cart array to maintain proper order
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
}
add_action('wp', 'handle_remove_from_cart');

function checkout_form_shortcode() {
    // Display the checkout form
    $output = '';
    
    $output .= '<form method="post" action="" class="checkout-form">';
    
    // Full Name Field
    $output .= '<div class="form-group">';
    $output .= '<label for="full_name">Full Name:</label>';
    $output .= '<input type="text" id="full_name" name="full_name" class="form-control" required>';
    $output .= '</div><br>';
    
    // Address Field
    $output .= '<div class="form-group">';
    $output .= '<label for="address">Address:</label>';
    $output .= '<textarea id="address" name="address" class="form-control" required></textarea>';
    $output .= '</div><br>';
    
    // Email Field
    $output .= '<div class="form-group">';
    $output .= '<label for="email">Email:</label>';
    $output .= '<input type="email" id="email" name="email" class="form-control" required>';
    $output .= '</div><br>';
    
    // Phone Number Field
    $output .= '<div class="form-group">';
    $output .= '<label for="phone">Phone Number:</label>';
    $output .= '<input type="text" id="phone" name="phone" class="form-control" required>';
    $output .= '</div><br>';
    
    // Submit Button
    $output .= '<div class="form-group">';
    $output .= '<input type="submit" name="submit_order" value="Place Order" class="btn btn-primary">';
    $output .= '</div>';
    
    $output .= '</form>';
    
    return $output;
}
add_shortcode('checkout_form', 'checkout_form_shortcode');

function handle_checkout_form_submission() {
    if (isset($_POST['submit_order'])) {
        // Get the user-submitted data
        $full_name = sanitize_text_field($_POST['full_name']);
        $address = sanitize_textarea_field($_POST['address']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        
        // Check if cart is empty
        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
            // Display the order summary
            echo '<div class="confirmation-container">';
            echo '<div class="order-summary">';
            echo '<h3 class="thank-you-message">Thank you for your order!</h3>';
            echo '<p><strong>Name:</strong> ' . $full_name . '</p>';
            echo '<p><strong>Address:</strong> ' . $address . '</p>';
            echo '<p><strong>Email:</strong> ' . $email . '</p>';
            echo '<p><strong>Phone:</strong> ' . $phone . '</p>';
            
            echo '<h4>Ordered Products:</h4>';
            echo '<ul>';
            foreach ($_SESSION['cart'] as $product_id) {
                $product = get_post($product_id);
                $price = get_post_meta($product_id, '_product_price', true);
                echo '<li>' . $product->post_title . ' – $' . $price . '</li>';
            }
            echo '</ul>';
            echo '<p>Your order has been successfully placed. You will receive a confirmation email shortly.</p>';
            echo '</div>';
            echo '</div>';
            
            // Clear the cart after order is processed
            $_SESSION['cart'] = array();

            // Set a flag for redirect
            $_SESSION['redirect_to_confirmation'] = true;
        } else {
            echo '<p>Your cart is empty.</p>';
        }
    }
}
add_action('wp', 'handle_checkout_form_submission');

function redirect_after_order() {
    if (isset($_SESSION['redirect_to_confirmation']) && $_SESSION['redirect_to_confirmation']) {
        // Unset the flag
        unset($_SESSION['redirect_to_confirmation']);
        
        // Redirect to the Order Confirmation page
        wp_redirect(home_url('/order-confirmation'));
        exit;
    }
}
add_action('template_redirect', 'redirect_after_order');



//registration stuff
// Registration form shortcode
function custom_registration_form_shortcode() {
    ob_start(); ?>
    
    <h2>Register</h2>
    <form method="post" action="">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>
        
        <input type="submit" name="submit_registration" value="Register">
    </form>
    
    <?php
    return ob_get_clean();
}
add_shortcode('custom_registration_form', 'custom_registration_form_shortcode');

// Handle registration form submission
function handle_registration_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_user'])) {
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        // Check if the email is already registered
        if (email_exists($email)) {
            // Store the error in a session so we can display it below the form
            $_SESSION['registration_error'] = 'Email already exists.';
        } else {
            // Create a new user
            $user_id = wp_create_user($email, $password, $email);

            if (is_wp_error($user_id)) {
                $_SESSION['registration_error'] = 'There was a problem creating your account.';
            } else {
                // Log in the user after successful registration
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                // Redirect to the homepage after successful registration
                wp_redirect(home_url());
                exit;
            }
        }
    }
}
add_action('init', 'handle_registration_form_submission');

// Registration form shortcode
function custom_registration_form() {
    ob_start();

    ?>
    <div class="registration-form">
        <h2>Register</h2>
        <form method="post" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>

            <input type="submit" name="register_user" value="Register">
        </form>

        <?php
        // Display the error message if it exists
        if (isset($_SESSION['registration_error'])) {
            echo '<div class="registration-error" style="color:red; margin-top: 10px;">';
            echo $_SESSION['registration_error'];
            echo '</div>';

            // Unset the session error after displaying it
            unset($_SESSION['registration_error']);
        }
        ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('custom_registration_form', 'custom_registration_form');

// Login form shortcode
function custom_login_form_shortcode() {
    if (!is_user_logged_in()) {
        ob_start(); ?>
        
        <h2>Login</h2>
        <form method="post" action="<?php echo wp_login_url(home_url()); // Redirect to homepage after login ?>">
            <label for="email">Email:</label>
            <input type="email" id="email" name="log" required><br><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="pwd" required><br><br>
            
            <input type="submit" name="submit_login" value="Login">
        </form>
        
        <?php
        return ob_get_clean();
    } else {
        return '<p>You are already logged in. <a href="' . wp_logout_url(home_url()) . '">Logout</a></p>'; // Redirect to homepage after logout
    }
}
add_shortcode('custom_login_form', 'custom_login_form_shortcode');

// Custom profile management form shortcode with password change functionality
function custom_profile_form_shortcode() {
    // Initialize message variable
    $message = '';

    // Handle profile update form submission
    if (isset($_POST['submit_profile_update'])) {
        $user_id = get_current_user_id();
        $current_password = sanitize_text_field($_POST['current_password']);
        $new_password = sanitize_text_field($_POST['new_password']);
        $confirm_password = sanitize_text_field($_POST['confirm_password']);

        // Get submitted values for profile update
        $email = sanitize_email($_POST['email']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);

        // Check if the current password is correct
        $user = get_userdata($user_id);
        if (!empty($current_password) && wp_check_password($current_password, $user->user_pass, $user_id)) {
            // Check if new password and confirmation match
            if (!empty($new_password) && $new_password === $confirm_password) {
                wp_set_password($new_password, $user_id);
                $message = '<p style="color: green;">Password successfully changed!</p>';
            } elseif (!empty($new_password)) {
                $message = '<p style="color: red;">New password and confirmation do not match.</p>';
            }
        } elseif (!empty($current_password)) {
            $message = '<p style="color: red;">Current password is incorrect.</p>';
        }

        // Update user information (if no error in password change)
        if (empty($message)) {
            $update_result = wp_update_user(array(
                'ID' => $user_id,
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ));

            // Check for any errors in the profile update
            if (is_wp_error($update_result)) {
                $message = '<p style="color: red;">There was an error updating your profile. Please try again.</p>';
            } else {
                // Success message
                $message = '<p style="color: green;">Profile successfully updated!</p>';

                // Redirect after 2 seconds to avoid resubmission
                echo '<meta http-equiv="refresh" content="2;url=' . home_url() . '" />';
            }
        }
    }

    // Display the form with current user information
    ob_start();
    $current_user = wp_get_current_user();
    ?>
    <h2>Update Profile</h2>
    <form method="post">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>" required><br><br>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required><br><br>

        <!-- Password Change Section -->
        <h3>Change Password</h3>

        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password"><br><br>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password"><br><br>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password"><br><br>

        <input type="submit" name="submit_profile_update" value="Update Profile">
    </form>

    <!-- Display message under the form -->
    <?php echo $message; ?>

    <?php
    return ob_get_clean();
}
add_shortcode('custom_profile_form', 'custom_profile_form_shortcode');


// Handle profile update
function handle_profile_update() {
    if (isset($_POST['update_profile']) && is_user_logged_in()) {
        $user_id = get_current_user_id();
        
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        
        // Update user information
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_email' => $email,
        ]);
        
        echo '<p>Profile updated successfully.</p>';
    }
}
add_action('wp', 'handle_profile_update');

// Handle password change
function handle_password_change() {
    if (isset($_POST['change_password']) && is_user_logged_in()) {
        $user_id = get_current_user_id();
        $new_password = sanitize_text_field($_POST['new_password']);
        $confirm_new_password = sanitize_text_field($_POST['confirm_new_password']);
        
        if ($new_password === $confirm_new_password) {
            wp_set_password($new_password, $user_id);
            echo '<p>Password changed successfully.</p>';
        } else {
            echo '<p>Passwords do not match.</p>';
        }
    }
}
add_action('wp', 'handle_password_change');
