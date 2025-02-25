<?php
// جلوگیری از دسترسی مستقیم به فایل
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// پردازش فرم و ثبت کاربر بعد از ارسال فرم Plugin_regester_gravity
add_filter( 'gform_confirmation', 'custom_register_user', 10, 4 );

function custom_register_user( $confirmation, $form, $entry, $ajax ) {
    if ( $form['title'] !== 'Plugin_regester_gravity' ) {
        return $confirmation;
    }

    $phone = rgar( $entry, '10' ); // دریافت شماره تلفن از فرم (شناسه اصلاح شد به 10)
    $title = rgar( $entry, '6' ); // دریافت عنوان آگهی از فرم
    $content = rgar( $entry, '8' ); // دریافت توضیحات آگهی از فرم
    $password = wp_generate_password( 12, false );
    $email = $phone . '@example.com'; // ایجاد ایمیل جعلی بر اساس شماره تلفن
    
    // بررسی اینکه کاربر قبلاً ثبت‌نام کرده است یا نه
    if ( username_exists( $phone ) ) {
        $user = get_user_by( 'login', $phone );
        if ( $user ) {
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID );
            return '<script>window.location.href="https://jointalent.ir/dashboard/";</script>';
        }
    }

    // ایجاد کاربر جدید
    $user_id = wp_create_user( $phone, $password, $email );
    
    if ( is_wp_error( $user_id ) ) {
        error_log( 'خطا در ثبت کاربر: ' . $user_id->get_error_message() );
        return '<p>مشکلی در ثبت نام پیش آمده است. لطفاً دوباره تلاش کنید.</p>';
    }

    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );
    update_user_meta( $user_id, 'grd_plugin_registered', 1 );
    wp_update_user( array( 'ID' => $user_id, 'display_name' => $phone ) ); // نمایش نام بر اساس شماره تلفن
    
    // تنظیم نقش کاربر برای جلوگیری از دسترسی به wp-admin
    wp_update_user( array( 'ID' => $user_id, 'role' => 'subscriber' ) );

    // ذخیره اطلاعات فرم در متا داده‌های آگهی
    $ad_data = array(
        'title' => $title,
        'phone' => $phone,
        'email' => $email,
        'content' => $content,
    );

    // ثبت یک آگهی جدید بعد از ثبت‌نام
    $new_ad = array(
        'post_title'    => $title, // تنظیم عنوان آگهی بر اساس فیلد 6
        'post_content'  => $content, // تنظیم محتوا بر اساس فیلد 8
        'post_status'   => 'publish',
        'post_author'   => $user_id,
        'post_type'     => 'post',
        'meta_input'    => array( 'grd_ad_data' => $ad_data ),
    );
    wp_insert_post( $new_ad );

    // 🔹 هدایت به صفحه تأیید بدون استفاده از wp_safe_redirect
    return '<script>window.location.href="https://jointalent.ir/%d8%aa%d8%a7%db%8c%db%8c%d8%af-%d9%81%d8%b1%d9%85/";</script>';
}

// جلوگیری از دسترسی کاربران عادی به wp-admin
add_action( 'init', 'redirect_non_admin_users' );
function redirect_non_admin_users() {
    if ( is_admin() && ! current_user_can( 'manage_options' ) && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        wp_redirect( site_url( '/dashboard/' ) );
        exit;
    }
}
