<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * نمایش داشبورد کاربری
 */
function grd_user_dashboard_shortcode() {
    ob_start();
    if ( ! is_user_logged_in() ) {
        echo '<div class="grd-plugin-register">';
        echo do_shortcode( '[gravityform id="Plugin_regester_gravity" title="false" description="false" ajax="false"]' );
        echo '</div>';
        return ob_get_clean();
    }
    
    $current_user = wp_get_current_user();
    ?>
    <div class="grd-dashboard-container">
        <!-- بخش بالا: header با پس‌زمینه زیبا -->
        <div class="grd-dashboard-header">
            <h2>سلام، <?php echo esc_html( $current_user->display_name ); ?>!</h2>
            <p>خوش آمدید به داشبورد شما</p>
        </div>
        
        <!-- بدنه داشبورد: منو به صورت عمودی و محتوای تب -->
        <div class="grd-dashboard-body">
            <div class="grd-dashboard-menu">
                <ul class="grd-tabs">
                    <li class="grd-tab-link active" data-tab="my_ads">آگهی‌های من</li>
                    <li class="grd-tab-link" data-tab="new_ad">ثبت آگهی جدید</li>
                    <li class="grd-tab-link" data-tab="personal_info">اطلاعات شخصی</li>
                </ul>
            </div>
            <div class="grd-dashboard-content">
                <div id="my_ads" class="grd-tab-content active">
                    <?php grd_display_user_ads($current_user->ID); ?>
                </div>
                <div id="new_ad" class="grd-tab-content">
                    <?php echo do_shortcode('[gravityform id="Form_After_signup" title="false" description="false" ajax="false"]'); ?>
                </div>
                <div id="personal_info" class="grd-tab-content">
                    <p>اطلاعات شخصی شما در اینجا نمایش داده خواهد شد.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'user_dashboard', 'grd_user_dashboard_shortcode' );

/**
 * نمایش آگهی‌های ثبت شده توسط کاربر
 */
function grd_display_user_ads($user_id) {
    $args = array(
        'post_type'      => 'post',
        'author'         => $user_id,
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    if ( $query->have_posts() ) {
        echo '<ul class="user-ads-list">';
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>شما هیچ آگهی‌ای ثبت نکرده‌اید.</p>';
    }
    wp_reset_postdata();
}
?>
