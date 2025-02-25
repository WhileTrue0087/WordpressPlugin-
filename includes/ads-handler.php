<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// اقدام پس از ارسال فرم ثبت آگهی
add_action( 'gform_after_submission', 'grd_submit_ad', 10, 2 );

function grd_submit_ad( $entry, $form ) {
    if ( isset( $form['title'] ) && $form['title'] === 'Form_After_signup' ) {
        
        $current_user = wp_get_current_user();
        if ( ! $current_user->ID ) {
            return; // بررسی اینکه کاربر لاگین کرده است
        }

        $ad_title   = sanitize_text_field( rgar( $entry, '1' ) ); // عنوان آگهی
        $ad_content = sanitize_textarea_field( rgar( $entry, '2' ) ); // توضیحات آگهی

        // بررسی اینکه آگهی مشابه قبلاً ثبت نشده باشد
        $existing_post = get_posts(array(
            'post_type'   => 'post',
            'post_status' => 'publish',
            'title'       => $ad_title,
            'author'      => $current_user->ID,
            'numberposts' => 1,
        ));

        if ( ! empty( $existing_post ) ) {
            return; // اگر آگهی با همین عنوان قبلاً ثبت شده باشد، از ارسال مجدد جلوگیری می‌شود
        }

        // ایجاد آگهی جدید
        $post_id = wp_insert_post(array(
            'post_title'   => $ad_title,
            'post_content' => $ad_content,
            'post_status'  => 'publish',
            'post_author'  => $current_user->ID,
            'post_type'    => 'post'
        ));

        if ( is_wp_error( $post_id ) ) {
            return;
        }

        // ذخیره یک مقدار متا برای جلوگیری از ارسال مجدد
        update_post_meta( $post_id, '_submitted_by_gravity_form', true );

        // هدایت کاربر به داشبورد برای مشاهده آگهی جدید
        wp_redirect( site_url('/dashboard?tab=my_ads') );
        exit;
    }
}
?>
