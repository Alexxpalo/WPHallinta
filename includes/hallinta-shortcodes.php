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
            //'varasto' => $tuote->varasto,
            'hinta' => $tuote->hinta,
            'kuvaus' => $tuote->kuvaus,
            'satokausi' => date("d/m", strtotime($tuote->satokausi_alku)) . ' - ' . date("d/m", strtotime($tuote->satokausi_loppu)),
            'kuva_path' => $tuote->kuva_path
        );
    }
    return json_encode($tuotteet_data);
}

add_shortcode( 'wph_tuotteet_table', 'wphallinta_tuotteet_table_shortcode' );

function wphallinta_tuotteet_table_shortcode() {
    wp_enqueue_style( 'wphallinta-style', plugin_dir_url( __FILE__ ) . '../styles/wphallinta-products.css' );
    $tuotteet_data = json_decode(wphallinta_tuotteet(), true);
    $upload_dir = wp_upload_dir();
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
            <img src="'. $upload_dir['baseurl'] . $data['kuva_path'] .'" alt="'.$data['tuote'].'">
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

    global $wpdb;
    $table_name = $wpdb->prefix . "asetukset";
    $tilaukset_tila = $wpdb->get_results( "SELECT * FROM $table_name WHERE asetus = 'tilaukset_tila' LIMIT 1" );
    $tilaukset_tila = $tilaukset_tila[0]->arvo;
    $tilaukset_aika_alku = $wpdb->get_results( "SELECT * FROM $table_name WHERE asetus = 'tilaukset_aika_alku' LIMIT 1" );
    $tilaukset_aika_alku = $tilaukset_aika_alku[0]->arvo;
    $tilaukset_aika_loppu = $wpdb->get_results( "SELECT * FROM $table_name WHERE asetus = 'tilaukset_aika_loppu' LIMIT 1" );
    $tilaukset_aika_loppu = $tilaukset_aika_loppu[0]->arvo;

    $tuotteet_data = json_decode(wphallinta_tuotteet(), true);
    $output = '<form id="reservation_form" method="POST">
    <script>var product_array = ' . json_encode($tuotteet_data) . ';</script>
    <select id="selected_id" onchange="show_prices(product_array)"><option value="" selected>Valitse tuote</option>'; 
    foreach ($tuotteet_data as $data) {
        $output .= '<option value="'. $data['tuote_id'] .'">'.$data['tuote'].'</option>';
    }
    $output .='</select>
    <div class="form-group"><label>Etu- ja sukunimi * </label><input type="text" name="nimi" placeholder="Matti Meikäläinen"></div>
    <div class="form-group"><label>Puhelinnumero * </label><input type="text" name="puhelin" placeholder="0401234567" ></div>
    <div class="form-group"><label>Sähköposti * </label><input type="text" name="email" placeholder="matti.meikalainen@gmail.com" ></div>
    <div class="form-group"><label>Toimitusosoite  </label><input type="text" name="osoite" placeholder="Katuosoite 1, 12345 Kaupunki"></div>
    <div class="form-group"><label>Toimituksen aika * </label><input type="date" name="paiva" ><input type="time" name="aika" min="' . $tilaukset_aika_alku . '" max="' . $tilaukset_aika_loppu . '"></div>
    <div class="form-group"><label>Toimitustapa * </label><select name="toimitustapa" >
        <option value="nouto_tori">Nouto torilta</option>
        <option value="nouto_tila">Nouto tilalta</option>
        <option value="toimitus">Toimitus (5€)</option>
        </select></div>
    <p>Valitse haluamasi tuote ja täytä lomake. Valitse aika ' . $tilaukset_aika_alku . ' ja ' . $tilaukset_aika_loppu . ' väliltä.</p>    
    <div class="form-group"><input type="submit" name="submit_reservation" value="Varaa"></div>
    </form>';

    if($tilaukset_tila == 0) {
        $output = '<p>Tilaukset ovat tällä hetkellä suljettu. Tarkista myöhemmin uudelleen.</p>
        <form id="reservation_form" method="POST" disabled>
    <select id="selected_id" onchange="show_prices(product_array)" disabled><option value="" selected>Valitse tuote</option>'; 
    $output .='</select>
    <div class="form-group"><label>Etu- ja sukunimi * </label><input type="text" name="nimi" placeholder="Matti Meikäläinen" disabled></div>
    <div class="form-group"><label>Puhelinnumero * </label><input type="text" name="puhelin" placeholder="0401234567" disabled></div>
    <div class="form-group"><label>Sähköposti * </label><input type="text" name="email" placeholder="matti.meikalainen@gmail.com" disabled></div>
    <div class="form-group"><label>Toimitusosoite  </label><input type="text" name="osoite" placeholder="Katuosoite 1, 12345 Kaupunki" disabled></div>
    <div class="form-group"><label>Toimituksen aika * </label><input type="date" name="paiva" disabled><input type="time" name="aika" disabled></div>
    <div class="form-group"><label>Toimitustapa * </label><select name="toimitustapa" disabled>
        <option value="nouto">Nouto</option>
        <option value="toimitus">Toimitus</option>
        </select></div>
    <div class="form-group"><input type="submit" name="" value="Varaa" disabled></div>
    </form>';
    }

    if(isset($_POST['submit_reservation'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . "varaukset";
        $table_tuotteet = $wpdb->prefix . "tuotteet";
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
        $varatut_name = array_map('sanitize_text_field', $_POST['tuote_name'] );

        $varatut_tuotteet = array();

        for($i = 0; $i < count($varatut_id); $i++) {
            $varatut_tuotteet[] = array(
                'tuote_id' => $varatut_id[$i],
                'maara' => $maarat[$i],
                'laatu' => $laadut[$i],
                'tuote_nimi' => $varatut_name[$i]
            );

            $sql = "SELECT hinta FROM $table_tuotteet WHERE tuote_id = $varatut_id[$i]";
            $db_maara = $wpdb->get_var($sql);
            $db_maara = json_decode($db_maara, true);
            $db_maara = $db_maara[0]['maara'];

            if ($db_maara < $maarat[$i]) {
                $output = '<p>Varauksen teko epäonnistui. Tarkistathan määrän.</p>';
                return $output;
            }
        }

        $varatut_tuotteet_json = json_encode($varatut_tuotteet);
        $url_param = substr(md5(uniqid(rand(), true)), 0, 25);
        $toimituspvm_aika = new DateTime($toimituspvm);
        
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

        $subject = 'Varausvahvistus';
        $message = 'Hei ' . $tilaaja . '! Olet tehnyt varauksen.<br><br>
        Varauksen tiedot:<br>
        Tilaajan nimi: ' . $tilaaja . '<br>
        Puhelinnumero: ' . $puhelinnro . '<br>
        Sähköposti: ' . $email . '<br>
        Toimitustapa: ' . $toimitustapa . '<br>
        Toimituspäivä: ' . $toimituspvm_aika . '<br><br>
        Vahvista varaus tästä linkistä: <a href="' . get_permalink() . '&varaus=' . $url_param . '">Vahvista varaus</a>';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = wp_mail( $email, $subject, $message, $headers );
        if($sent) {
            $output .= '<script>alert("Varauksesi on lähetetty. Vahvista varaus sähköpostilla.");</script>';
        } else {
            $output .= '<script>alert("Varauksesi lähetys epäonnistui.");</script>';
        }
    }

    if ( get_query_var('varaus') ) {

        global $wpdb;
        $table_name = $wpdb->prefix . "varaukset";
        $table_name2 = $wpdb->prefix . "tuotteet";
        $varaus = get_query_var('varaus', 1);

        $varaus_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE varaus_url_param = '$varaus'" );

        if($varaus_data) {
            if($varaus_data->tila == 0){
            $sql = "UPDATE $table_name SET tila = 1 WHERE varaus_url_param = '$varaus'";
            $json_arr = json_decode($varaus_data->varatut_tuotteet);

            foreach($json_arr as $tuote) {
                $tuote_id = $tuote->tuote_id;
                $maara = $tuote->maara;
                $laatu = $tuote->laatu;
                $hinnat_json = $wpdb->get_var( "SELECT hinta FROM $table_name2 WHERE tuote_id = '$tuote_id'" );
                $hinnat_json = json_decode($hinnat_json);
                
                foreach($hinnat_json as $db_data) {
                    if($db_data->nimi == $laatu) {
                    $db_data->maara -= $maara;
                    }
                }
            }

            $hinnat_json = json_encode($hinnat_json);

            $sql2 = "UPDATE $table_name2 SET hinta = '$hinnat_json' WHERE tuote_id = '$tuote_id'";
            $wpdb->query($sql2);
            $wpdb->query($sql);

            $output .= '<script>alert("Tilauksesi on vahvistettu!");</script>';
        } else {
            $output .= '<script>alert("Tilauksesi vahvistamisessa tapahtui virhe.");</script>';
        }
        }
    
    }
    return $output;
}