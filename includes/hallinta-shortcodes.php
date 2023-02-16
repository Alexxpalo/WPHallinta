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
        $tilaaja = $_POST['nimi'];
        $puhelinnro = $_POST['puhelin'];
        $email = $_POST['email'];
        $osoite = $_POST['osoite'];
        $toimituspvm = $_POST['paiva'];
        $toimitusaika = $_POST['aika'];
        $toimitustapa = $_POST['toimitustapa'];
        $maarat = $_POST['maara'];
        $laadut = $_POST['laatu'];
        $varatut_id = $_POST['tuote_id'];
        
        for($x = 0; $x < count($maarat); $x++){
            $output .= '<script>console.log("'.$varatut_id[$x] . ' : '. $laadut[$x] .' : ' .$maarat[$x].'")</script>';
        }
    }
    return $output;
}

/*
wp_varaukset table containts:
varaus_id int(11) AI PK
varaus_url_param varchar(255)
tilaajan nimi varchar(255)
puhelinnumero varchar(255)
email varchar(255)
osoite varchar(255)
tilauspvm date current_timestamp
toimituspvm date
toimitustapa varchar(255)
varatut tuotteet json
vahvistettu boolean default 0
*/