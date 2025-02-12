<?php
/*
Plugin Name: Gravity Registration Dashboard
Plugin URI: https://example.com/
Description: ایجاد داشبورد کاربری سفارشی برای کاربران ثبت‌نام شده با استفاده از Gravity Forms و Gravity Registration.
Version: 1.2
Author: نام شما
Author URI: https://example.com/
License: GPL2
*/

// جلوگیری از دسترسی مستقیم به فایل
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * هدایت کاربر به داشبورد پس از ثبت‌نام
 */
add_action( 'gform_user_registered', 'grd_post_user_registration_setup', 10, 4 );
function grd_post_user_registration_setup( $user_id, $config, $entry, $user_pass ) {
    // هدایت کاربر به صفحه داشبورد (فرض بر این است که صفحه داشبورد با آدرس /dashboard/ ایجاد شده است)
    wp_redirect( site_url( '/dashboard/' ) );
    exit;
}

/**
 * تعریف شورت‌کد داشبورد کاربری [user_dashboard]
 */
function grd_user_dashboard_shortcode() {
    // بررسی ورود کاربر
    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url() );
        exit;
    }

    $current_user = wp_get_current_user();
    ob_start();
    ?>
    <div class="grd-dashboard-container">
        <h2>سلام، <?php echo esc_html( $current_user->display_name ); ?>!</h2>
        <div class="grd-dashboard-menu">
            <ul>
                <li><a href="<?php echo esc_url( remove_query_arg( array('tab', 'edit_id') ) ); ?>?tab=my_ads">آگهی‌های ثبت شده من</a></li>
                <li><a href="<?php echo esc_url( remove_query_arg( array('tab', 'edit_id') ) ); ?>?tab=new_ad">ثبت آگهی جدید</a></li>
                <li><a href="<?php echo esc_url( remove_query_arg( array('tab', 'edit_id') ) ); ?>?tab=personal_info">اطلاعات شخصی</a></li>
            </ul>
        </div>
        <div class="grd-dashboard-content">
            <?php
            // بررسی زبانه انتخاب شده (از طریق پارامتر GET)
            $tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'my_ads';

            switch ( $tab ) {
                case 'personal_info':
                    ?>
                    <h3>ویرایش اطلاعات شخصی</h3>
                    <p>در این بخش می‌توانید اطلاعات شخصی خود را مشاهده و ویرایش کنید.</p>
                    <!-- اینجا می‌توانید فرم یا امکانات ویرایش اطلاعات شخصی را اضافه کنید -->
                    <?php
                    break;

                case 'new_ad':
                    ?>
                    <h3>ثبت آگهی جدید</h3>
                    <p>در این بخش می‌توانید آگهی جدید ثبت کنید.</p>
                    <?php
                    // نمایش فرم ثبت آگهی جدید (شناسه فرم ثبت آگهی را جایگزین کنید)
                    echo do_shortcode('[gravityform id="YOUR_FORM_ID" title="false" description="false" ajax="true"]');
                    break;

                case 'my_ads':
                default:
                    // در صورتیکه پارامتر edit_id موجود باشد، فرم ویرایش آگهی نمایش داده می‌شود
                    if ( isset( $_GET['edit_id'] ) ) {
                        $edit_id = intval( $_GET['edit_id'] );
                        $ad_post = get_post( $edit_id );

                        // بررسی صحت آگهی و تعلق آن به کاربر فعلی
                        if ( $ad_post && $ad_post->post_type === 'ads' && $ad_post->post_author == $current_user->ID ) {
                            
                            // پردازش ارسال فرم ویرایش آگهی
                            if ( isset( $_POST['grd_edit_ad_nonce'] ) && wp_verify_nonce( $_POST['grd_edit_ad_nonce'], 'grd_edit_ad_' . $edit_id ) ) {
                                $ad_title   = sanitize_text_field( $_POST['ad_title'] );
                                $ad_content = wp_kses_post( $_POST['ad_content'] );

                                $update_args = array(
                                    'ID'           => $edit_id,
                                    'post_title'   => $ad_title,
                                    'post_content' => $ad_content,
                                );

                                $result = wp_update_post( $update_args );

                                if ( ! is_wp_error( $result ) ) {
                                    echo '<div class="notice notice-success"><p>آگهی با موفقیت به روز شد.</p></div>';
                                    // بازیابی اطلاعات جدید آگهی
                                    $ad_post = get_post( $edit_id );
                                } else {
                                    echo '<div class="notice notice-error"><p>خطا در به روز رسانی آگهی.</p></div>';
                                }
                            }
                            ?>
                            <h3>ویرایش آگهی: <?php echo esc_html( $ad_post->post_title ); ?></h3>
                            <form method="post">
                                <?php wp_nonce_field( 'grd_edit_ad_' . $edit_id, 'grd_edit_ad_nonce' ); ?>
                                <p>
                                    <label for="ad_title">عنوان آگهی</label><br>
                                    <input type="text" id="ad_title" name="ad_title" value="<?php echo esc_attr( $ad_post->post_title ); ?>" required>
                                </p>
                                <p>
                                    <label for="ad_content">متن آگهی</label><br>
                                    <textarea id="ad_content" name="ad_content" rows="5" required><?php echo esc_textarea( $ad_post->post_content ); ?></textarea>
                                </p>
                                <p>
                                    <input type="submit" value="به روز رسانی آگهی">
                                    <a href="<?php echo esc_url( remove_query_arg( 'edit_id' ) ); ?>">بازگشت به لیست آگهی‌ها</a>
                                </p>
                            </form>
                            <?php
                        } else {
                            echo '<p>آگهی مورد نظر معتبر نیست یا شما اجازه ویرایش آن را ندارید.</p>';
                        }
                    } else {
                        // نمایش لیست آگهی‌های کاربر به همراه لینک ویرایش
                        ?>
                        <h3>آگهی‌های ثبت شده من</h3>
                        <?php
                        $args = array(
                            'post_type'      => 'ads',  // نام پست تایپ آگهی‌ها
                            'posts_per_page' => -1,
                            'author'         => $current_user->ID,
                        );
                        $user_ads = new WP_Query( $args );
                        
                        if ( $user_ads->have_posts() ) {
                            echo '<ul>';
                            while ( $user_ads->have_posts() ) {
                                $user_ads->the_post();
                                // لینک ویرایش آگهی: با اضافه کردن پارامتر edit_id
                                echo '<li>';
                                echo '<a href="' . esc_url( add_query_arg( 'edit_id', get_the_ID() ) ) . '">';
                                echo get_the_title();
                                echo '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                            wp_reset_postdata();
                        } else {
                            echo '<p>آگهی ثبت شده‌ای یافت نشد.</p>';
                        }
                    }
                    break;
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'user_dashboard', 'grd_user_dashboard_shortcode' );

/**
 * افزودن استایل‌های افزونه
 */
function grd_enqueue_styles() {
    wp_enqueue_style( 'grd-styles', plugins_url( '/assets/css/style.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'grd_enqueue_styles' );
