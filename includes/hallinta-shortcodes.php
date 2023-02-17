<?php

//SHORTCODE FOR PRODUCTS
function wphallinta_tuotteet() {
    global $wpdb;
    $table_name = $wpdb->prefix . "tuotteet";
    $tuotteet = $wpdb->get_results( "SELECT * FROM $table_name" );
    $tuotteet_data = array();
    foreach ( $tuotteet as $tuote ) {
        $tuotteet_data[] = array(
            'tuote_id' => $tuote->tuote_id,
            'tuote' => $tuote->tuote,
            'varasto' => $tuote->varasto,
            'hinta' => $tuote->hinta,
            'kuvaus' => $tuote->kuvaus,
            'satokausi' => date("d/m", strtotime($tuote->satokausi_alku)) . ' - ' . date("d/m", strtotime($tuote->satokausi_loppu))
        );
    }
    return json_encode($tuotteet_data);
}

add_shortcode( 'wph_tuotteet_table', 'wphallinta_tuotteet_table_shortcode' );

function wphallinta_tuotteet_table_shortcode() {
    wp_enqueue_style( 'wphallinta-style', plugin_dir_url( __FILE__ ) . '../styles/wphallinta-products.css' );
    $tuotteet_data = json_decode(wphallinta_tuotteet(), true);
    $output = '<div class="products-container alignleft"><h2>Tuotteet</h2>';
    foreach ($tuotteet_data as $data) {
        $hinnat_display = null;
        $hinnat_data = json_decode($data['hinta']);
            for($i = 0; $i < count($hinnat_data); $i++) {
                $hinnat_display .= $hinnat_data[$i]->nimi . ': ' . $hinnat_data[$i]->arvo . '€ | ';
        }
        $output .= '<div class="product-display">
            <div><h3 class="product-name">'.$data['tuote'].'</h3>
            <div class="product-date"><span class="dashicons dashicons-calendar-alt"></span><p>Satokausi: '.$data['satokausi'].'</p></div></div>
            <div>'.$data['kuvaus'].'</div>
            <div><p>| '. $hinnat_display .'</p></div>
        </div>';
    }
    $output .= '</div>';
    return $output;
}

//SHORTCODE FOR RESERVATIONS

add_shortcode( 'wph_varaukset_form', 'wphallinta_varaukset_form_shortcode' );

function wphallinta_varaukset_form_shortcode() {
    wp_enqueue_style( 'wphallinta-style', plugin_dir_url( __FILE__ ) . '../styles/wphallinta-reservations.css' );
    $tuotteet_data = json_decode(wphallinta_tuotteet(), true);
    $output = '<form id="reservation_form" method="POST">
    <script>var product_array = ' . json_encode($tuotteet_data) . ';</script>
    <select id="selected_id" onchange="show_prices(product_array)"><option value="" disabled selected>Valitse tuote</option>'; 
    foreach ($tuotteet_data as $data) {
        $output .= '<option value="'. $data['tuote_id'] .'">'.$data['tuote'].'</option>';
    }
    $output .='</select>
    <div class="form-group"><label>Etu- ja sukunimi * </label><input type="text" name="nimi" placeholder="Matti Meikäläinen" ></div>
    <div class="form-group"><label>Puhelinnumero * </label><input type="text" name="puhelin" placeholder="0401234567" ></div>
    <div class="form-group"><label>Sähköposti * </label><input type="text" name="email" placeholder="matti.meikalainen@gmail.com" ></div>
    <div class="form-group"><label>Toimitusosoite  </label><input type="text" name="osoite" placeholder="Katuosoite 1, 12345 Kaupunki"></div>
    <div class="form-group"><label>Toimituksen aika * </label><input type="date" name="paiva" ><input type="time" name="aika"></div>
    <div class="form-group"><label>Toimitustapa * </label><select name="toimitustapa" >
        <option value="nouto">Nouto</option>
        <option value="toimitus">Toimitus</option>
        </select></div>
    <div class="form-group"><input type="submit" name="submit_reservation" value="Varaa"></div>
    </form>';

    if(isset($_POST['submit_reservation'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . "varaukset";
        $tilaaja = sanitize_text_field( $_POST['nimi'] );
        $puhelinnro = sanitize_text_field( $_POST['puhelin'] );
        $email = sanitize_text_field( $_POST['email'] );
        $osoite = sanitize_text_field( $_POST['osoite'] );
        $toimituspvm = sanitize_text_field( $_POST['paiva'] );
        $toimitusaika = sanitize_text_field( $_POST['aika'] );
        $toimitustapa = sanitize_text_field( $_POST['toimitustapa'] );
        $maarat = array_map('sanitize_text_field', $_POST['maara'] );
        $laadut = array_map('sanitize_text_field', $_POST['laatu'] );
        $varatut_id = array_map('sanitize_text_field', $_POST['tuote_id'] );

        $output .= '<script>console.log("' . $toimitusaika . '");</script>';

        $varatut_tuotteet = array();

        for($i = 0; $i < count($varatut_id); $i++) {
            $varatut_tuotteet[] = array(
                'tuote_id' => $varatut_id[$i],
                'maara' => $maarat[$i],
                'laatu' => $laadut[$i]
            );
        }

        $varatut_tuotteet_json = json_encode($varatut_tuotteet);
        $url_param = substr(md5(uniqid(rand(), true)), 0, 25);
        $toimituspvm_aika = new DateTime($toimituspvm);
        
        // if toimitusaika is set, add it to the date
        if($toimitusaika) {
            $toimitusaika = new DateTime($toimitusaika);
            $toimituspvm_aika->setTime($toimitusaika->format('H'), $toimitusaika->format('i'));
        }
        $toimituspvm_aika = $toimituspvm_aika->format('Y-m-d H:i:s');


        $wpdb->insert(
            $table_name,
            array(
                'varaus_url_param' => $url_param,
                'tilaajan_nimi' => $tilaaja,
                'puhelinnumero' => $puhelinnro,
                'email' => $email,
                'osoite' => $osoite,
                'toimituspvm' => $toimituspvm_aika,
                'toimitustapa' => $toimitustapa,
                'varatut_tuotteet' => $varatut_tuotteet_json
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
    }
    return $output;
}

/*
CREATE TABLE `wp_varaukset` (
  `varaus_id` int(11) NOT NULL AUTO_INCREMENT,
  `varaus_url_param` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `tilaajan_nimi` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `puhelinnumero` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `osoite` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `tilauspvm` date DEFAULT current_timestamp(),
  `toimituspvm` date DEFAULT NULL,
  `toimitustapa` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `varatut_tuotteet` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`varatut_tuotteet`)),
  `vahvistettu` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`varaus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci
*/