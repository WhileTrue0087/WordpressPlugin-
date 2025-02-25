<?php
/*
Plugin Name: Gravity Registration Dashboard
Plugin URI: https://example.com/
Description: ایجاد داشبورد کاربری سفارشی برای کاربران ثبت‌نام شده با استفاده از Gravity Forms.
Version: 1.5
Author: MohammadTaha
License: GPL2
*/

// جلوگیری از دسترسی مستقیم به فایل
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// بارگذاری فایل‌های مورد نیاز
require_once plugin_dir_path(__FILE__) . 'includes/register-user.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/ads-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';
?>
