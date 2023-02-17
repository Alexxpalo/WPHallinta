<?php
/*
Plugin Name:  WPHallinta
Description:  Hallinnoi WP-sivustosi uutisia, tuotteita sekä varauksia.
Version:      BETA
Author:       Alexander Palosaari
Author URI:   https://www.linkedin.com/in/alexander-palosaari-23a9b8237/  
*/


// SETUP
function wphallinta_activate() {
    global $wpdb;
    $table_name1 = $wpdb->prefix . "tuotteet";
    $table_name2 = $wpdb->prefix . "varaukset";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name1 (
        tuote_id mediumint(9) NOT NULL AUTO_INCREMENT,
        tuote varchar(255) NOT NULL,
        hinta json NOT NULL,
        kuvaus TEXT NOT NULL,
        varasto int NOT NULL DEFAULT 0,
        satokausi_alku date NOT NULL,
        satokausi_loppu date NOT NULL,
        PRIMARY KEY (tuote_id)
    ) $charset_collate;";

    $sql .= "CREATE TABLE $table_name2 (
        varaus_id INT(11) NOT NULL AUTO_INCREMENT,
        varaus_url_param VARCHAR(255),
        tilaajan_nimi VARCHAR(255),
        puhelinnumero VARCHAR(255),
        email VARCHAR(255),
        osoite VARCHAR(255),
        tilauspvm DATE DEFAULT CURRENT_TIMESTAMP,
        toimituspvm DATETIME,
        toimitustapa VARCHAR(255),
        varatut_tuotteet JSON,
        vahvistettu BOOLEAN DEFAULT 0,
        PRIMARY KEY (varaus_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

register_activation_hook( __FILE__, 'wphallinta_activate' );

// ADMIN MENU
add_action('admin_menu', 'hallinta_admin_menu');

function hallinta_admin_menu() {
    add_menu_page(
        'Hallinta',
        'Hallinta',
        'manage_options',
        'hallinta/hallinta-admin.php',
        'wphallinta_admin_page',
        'dashicons-edit',
        2
    );
    add_submenu_page(
        'hallinta/hallinta-admin.php',
        'Varaukset',
        'Varaukset',
        'manage_options',
        'hallinta/hallinta-admin-varaukset.php',
        'wphallinta_admin_varaukset_page'
    );
}

function wphallinta_enqueue_styles() {
    wp_enqueue_style( 'wphallinta-style', plugin_dir_url( __FILE__ ) . 'styles/wphallinta-admin.css' );
}

function wphallinta_enqueue_admin_scripts() {
    wp_enqueue_script( 'wphallinta-script', plugin_dir_url( __FILE__ ) . 'js/wphallinta-admin.js', array('jquery'), '1.0.0', true );
}

function wphallinta_enqueue_frontend_scripts() {
    wp_enqueue_script( 'wphallinta-script', plugin_dir_url( __FILE__ ) . 'js/wphallinta-frontend.js', array('jquery'), '1.0.0', true );
    $tuotteet_data = "test";
    wp_localize_script( 'wphallinta-script', 'tuotteetData', $tuotteet_data);
}


add_action( 'admin_enqueue_scripts', 'wphallinta_enqueue_styles' );
add_action( 'admin_enqueue_scripts', 'wphallinta_enqueue_admin_scripts' );

add_action( 'wp_enqueue_scripts', 'wphallinta_enqueue_frontend_scripts' );

// MENU PAGES
require 'includes/hallinta-admin.php';

//SHORTCODE

require 'includes/hallinta-shortcodes.php';