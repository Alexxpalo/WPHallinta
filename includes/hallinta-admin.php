<?php
// MENU PAGES

//TUOTEHALLINTA
function wphallinta_admin_page(){
	?>
	<div class="wrap form form-txt-14">
    <h1>Tuotteet</h1>
        <div class="flex-1">
        <h2>Lisää tuote:</h2>
        <form id="add_product_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="wphallinta_add_product">
            <input type="text" name="tuote" placeholder="Tuotteen nimi"><br>

            <h2>Hinnat:</h2>
            <input type="text" name="hinnat_nimi[]" placeholder="Hinnan 1 nimi">
            <input type="text" name="hinnat_arvo[]" placeholder="Hinnan 1 arvo">
            <input type="text" name="hinnat_maara[]" placeholder="Hinnan 1 määrä"><br>
            <button class="product-btn" type="button" id="add_price_button" onclick="add_price()">Lisää hinta</button>
            
            <h2>Muut tiedot:</h2>
            <textarea class="form-txtarea" type="text" name="kuvaus" rows="5" placeholder="Tuotteen kuvaus" style="width:100%;"></textarea><br>
            
            <h2 style="margin: 5px;">Satokausi: </h2><br>
            <input style="cursor: pointer;" type="date" name="satokausi_alku"> -
            <input style="cursor: pointer;" type="date" name="satokausi_loppu"><br>
            <h2>Tuotteen kuva</h2>
            <input type="file" name="kuvaupload" id="kuva"><br>
            <input class="product-btn" type="submit" value="Lisää tuote">
        </form>
        </div>

        <div class="flex-3">
        <h2>Tuotteet</h2>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th class="manage-column">Tuote</th>
                    <th class="manage-column">Varasto</th>
                    <th class="manage-column">Hinnat</th>
                    <th class="manage-column">Kuvaus</th>
                    <th class="manage-column">Satokausi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . "tuotteet";
                    $tuotteet = $wpdb->get_results( "SELECT * FROM $table_name" );

                    foreach ( $tuotteet as $tuote ) {
                        $hinnat_value = null;
                        $hinnat_maara = null;
                        $hinnat_data = json_decode($tuote->hinta);
                        for($i = 0; $i < count($hinnat_data); $i++) {
                            $hinnat_value .= $hinnat_data[$i]->nimi . ': ' . $hinnat_data[$i]->arvo . '€<br>';
                            $hinnat_maara .= $hinnat_data[$i]->nimi . ': ' . $hinnat_data[$i]->maara . 'kg<br>';
                        }
                        echo '<tr>';
                        echo '<td><a href="' . wp_nonce_url( admin_url('admin-post.php?action=wphallinta_edit_tuote&tuote_id=' . $tuote->tuote_id), 'wphallinta_edit_tuote_nonce' ) . '">' . $tuote->tuote . '</a></td>';
                        echo '<td>' . $hinnat_maara . '</td>';
                        echo '<td>' . $hinnat_value . '</td>';
                        echo '<td>' . $tuote->kuvaus . '</td>';
                        echo '<td>' . date("d/m", strtotime($tuote->satokausi_alku)) . ' - ' . date("d/m", strtotime($tuote->satokausi_loppu)) . '</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
        </div>
	<?php
}

add_action( 'admin_post_wphallinta_edit_tuote', 'wphallinta_edit_tuote_callback' );
add_action ( 'admin_post_nopriv_wphallinta_edit_tuote', 'wphallinta_edit_tuote_callback' );

function wphallinta_edit_tuote_callback() {
    if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wphallinta_edit_tuote_nonce' ) ) {
        wp_die('Invalid nonce');
    }
    $tuote_id = sanitize_text_field( $_GET['tuote_id'] );
    global $wpdb;
    $table_name = $wpdb->prefix . "tuotteet";
    $tuote = $wpdb->get_row( "SELECT * FROM $table_name WHERE tuote_id = $tuote_id" );

    echo "<style>";
    include plugin_dir_path( __FILE__ ) . '../styles/wphallinta-admin.css';
    echo "</style>";

    echo "<div class='wrap form form-txt-14'>";
    echo "<h1>Muokkaa tuotetta</h1>";
    echo "<h2>Tuote:</h2>";
    echo "<form id='edit_product_form' action='" . esc_url( admin_url('admin-post.php') ) . "' method='post'>";
    echo "<input type='hidden' name='action' value='wphallinta_submit_edit'>";
    echo "<input type='hidden' name='tuote_id' value='" . $tuote->tuote_id . "'>";
    echo "<input type='text' name='tuote' value='" . $tuote->tuote . "'><br><h2>Hinnat:</h2>";
    $hinnat_data = json_decode($tuote->hinta);
    for($i = 0; $i < count($hinnat_data); $i++) {
        echo "<label>Hinta: </label><input type='text' name='hinnat_nimi[]' value='" . $hinnat_data[$i]->nimi . "'>
        <label> Arvo: </label><input type='text' name='hinnat_arvo[]' value='" . $hinnat_data[$i]->arvo . "'>
        <label> Määrä: </label><input type='text' name='maarat_arvo[]' value='" . $hinnat_data[$i]->maara . "'>
        <br class='brs'>";
    }
    echo "<button class='product-btn' type='button' id='add_price_button' onclick='add_price()'>Lisää hinta</button> - ";
    echo "<button class='product-btn' type='button' id='remove_price_button' onclick='remove_price()'>Poista hinta</button>";
    echo "<h2>Muut tiedot:</h2>";
    echo "<textarea type='text' name='kuvaus' rows='5' style='width:100%;font-size:18px;'>" . $tuote->kuvaus . "</textarea><br>";
    echo "<h2>Satokausi: </h2>";
    echo "<input type='date' name='satokausi_alku' value='" . $tuote->satokausi_alku . "'> - ";
    echo "<input type='date' name='satokausi_loppu' value='" . $tuote->satokausi_loppu . "'><br>";
    echo "<input class='product-btn' type='submit' value='Tallenna'>";
    echo "</form>";
    echo "</div>";
    echo "<script>
    function add_price() {
        var form = document.getElementById('edit_product_form');

        var label = document.createElement('label');
        label.innerHTML = 'Hinta: ';
        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'hinnat_nimi[]';
        form.insertBefore(label, document.getElementById('add_price_button'));
        form.insertBefore(input, document.getElementById('add_price_button'));

        var label = document.createElement('label');
        label.innerHTML = ' Arvo: ';
        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'hinnat_arvo[]';
        form.insertBefore(label, document.getElementById('add_price_button'));
        form.insertBefore(input, document.getElementById('add_price_button'));

        var label = document.createElement('label');
        label.innerHTML = ' Määrä: ';
        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'maarat_arvo[]';
        form.insertBefore(label, document.getElementById('add_price_button'));
        form.insertBefore(input, document.getElementById('add_price_button'));

        var br = document.createElement('br');
        br.className = 'brs';
        form.insertBefore(br, document.getElementById('add_price_button'));
    }
    function remove_price() {
        var form = document.getElementById('edit_product_form');
        var inputs = form.querySelectorAll('input[name=\'hinnat_nimi[]\'], input[name=\'hinnat_arvo[]\'], input[name=\'maarat_arvo[]\']');
        var brs = form.querySelectorAll('.brs');
        var last_input = inputs[inputs.length - 1];
        form.removeChild(last_input);
        var last_input = inputs[inputs.length - 2];
        form.removeChild(last_input);
        var last_input = inputs[inputs.length - 3];
        form.removeChild(last_input);
        var last_br = brs[brs.length - 1];
        form.removeChild(last_br);
    }
    </script>";
}

add_action( 'admin_post_wphallinta_submit_edit', 'wphallinta_submit_edit_callback' );
add_action ( 'admin_post_nopriv_wphallinta_submit_edit', 'wphallinta_submit_edit_callback' );

function wphallinta_submit_edit_callback() {
    $tuote_id = $_POST['tuote_id'];
    $tuote = sanitize_text_field($_POST['tuote']);
    $hinnat_nimi = array_map('sanitize_text_field', $_POST['hinnat_nimi'] );
    $hinnat_arvo = array_map('sanitize_text_field', $_POST['hinnat_arvo'] );
    $hinnat_maara = array_map('sanitize_text_field', $_POST['maarat_arvo'] );
    $satokausi_alku = sanitize_text_field($_POST['satokausi_alku']);
    $satokausi_loppu = sanitize_text_field($_POST['satokausi_loppu']);
    $kuvaus = sanitize_text_field($_POST['kuvaus']);

    $hinnat = array();
    for($i = 0; $i < count($hinnat_nimi); $i++) {
        $hinnat[$i] = array(
            'nimi' => $hinnat_nimi[$i],
            'arvo' => $hinnat_arvo[$i],
            'maara' => $hinnat_maara[$i]
        );
    }

    $hinnat = json_encode($hinnat);

    global $wpdb;
    $table_name = $wpdb->prefix . 'tuotteet';

    $wpdb->update(
        $table_name,
        array(
            'tuote' => $tuote,
            'hinta' => $hinnat,
            'satokausi_alku' => $satokausi_alku,
            'satokausi_loppu' => $satokausi_loppu,
            'kuvaus' => $kuvaus
        ),
        array( 'tuote_id' => $tuote_id )
    );

    wp_redirect( wp_get_referer() );
    exit;
}

add_action( 'admin_post_wphallinta_add_product', 'wphallinta_add_product_callback' );
add_action ( 'admin_post_nopriv_wphallinta_add_product', 'wphallinta_add_product_callback' );

function wphallinta_add_product_callback() {
    if ( ! function_exists ( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    $tuote = sanitize_text_field( $_POST['tuote'] );
    $hinnat_nimi = array_map('sanitize_text_field', $_POST['hinnat_nimi'] );
    $hinnat_arvo = array_map('sanitize_text_field', $_POST['hinnat_arvo'] );
    $hinnat_maara = array_map('sanitize_text_field', $_POST['hinnat_maara'] );
    $kuvaus = sanitize_text_field( $_POST['kuvaus'] );
    $satokausi_alku = sanitize_text_field( $_POST['satokausi_alku'] );
    $satokausi_loppu = sanitize_text_field( $_POST['satokausi_loppu'] );
    $hinnat_json = array();
    $upload_kuva = $_FILES['kuvaupload'];
    $upload_overrides = array( 'test_form' => false );
    $movefile = wp_handle_upload( $upload_kuva, $upload_overrides );

    if ( $movefile && ! isset( $movefile['error'] ) ) {
        echo "File is valid, and was successfully uploaded.\n";
        $upload_dir = wp_upload_dir();
        $filename = basename($movefile['file']);
        $filepath = $upload_dir['subdir'] . '/' . $filename;
    } else {
    wp_die( $movefile['error'], 'File Upload Error' );
    }

    

    for($i = 0; $i < count($hinnat_nimi); $i++) {
        $hinnat_json[] = array(
            'nimi' => $hinnat_nimi[$i],
            'arvo' => $hinnat_arvo[$i],
            'maara' => $hinnat_maara[$i]
        );
    }

    $hinnat_json = json_encode($hinnat_json);
    global $wpdb;
    $table_name = $wpdb->prefix . "tuotteet";
    $wpdb->insert(
        $table_name,
        array(
            'tuote' => $tuote,
            'hinta' => $hinnat_json,
            'kuvaus' => $kuvaus,
            'satokausi_alku' => $satokausi_alku,
            'satokausi_loppu' => $satokausi_loppu,
            'kuva_path' => $filepath
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        )
    );
    wp_redirect( wp_get_referer() );
    exit;
}

//VARAUSHALLINTA

function wphallinta_admin_varaukset_page(){
    global $wpdb;

    $table_name = $wpdb->prefix . "varaukset";
    $varaukset = $wpdb->get_results( "SELECT * FROM $table_name" );
    ?>
    <div class="wrap">
        <h2>Varaukset</h2>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th class="manage-column">Tuotteet</th>
                    <th class="manage-column">Varaaja</th>
                    <th class="manage-column">Puhelinnumero</th>
                    <th class="manage-column">Tila</th>
                    <th class="manage-column">Tilauksen päivämäärä</th>
                    <th class="manage-column">Toivottu toimitusaika</th>
                    <th class="manage-column">Toimitustapa</th>
                    <th class="manage-column">Toimitusosoite</th>
                    <th class="manage-column">Toiminnot</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ( $varaukset as $varaus ) {
                        $tuotteet_str = '';
                        $json_arr = json_decode($varaus->varatut_tuotteet);
                        foreach ( $json_arr as $tuote ) {
                            $tuotteet_str .= $tuote->tuote_nimi . ' (' . $tuote->tuote_id . ')<ul><li>Laatu: ' . $tuote->laatu . ' </li><li>Määrä: ' . $tuote->maara . '</li></ul>';
                        }
                        echo '<tr>';
                        echo '<td class="">' . $tuotteet_str . '</td>';
                        echo '<td>' . $varaus->tilaajan_nimi . '</td>';
                        echo '<td>' . $varaus->puhelinnumero . '</td>';
                        echo '<td>' . $varaus->tila . '</td>';
                        echo '<td>' . $varaus->tilauspvm . '</td>';
                        echo '<td>' . $varaus->toimituspvm . '</td>';
                        echo '<td>' . $varaus->toimitustapa . '</td>';
                        echo '<td>' . $varaus->osoite . '</td>';
                        echo '<td><a href="' . wp_nonce_url( admin_url('admin-post.php?action=wphallinta_delete_varaus&varaus_id=' . $varaus->varaus_id), 'wphallinta_delete_varaus_nonce' ) . '">Poista tilaus</a><br>
                        <a href="' . wp_nonce_url( admin_url('admin-post.php?action=wphallinta_cancel_varaus&varaus_id=' . $varaus->varaus_id), 'wphallinta_cancel_varaus_nonce' ) . '">Peruuta tilaus</a></td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
    </div>
    <?php
}

add_action( 'admin_post_wphallinta_cancel_varaus', 'wphallinta_cancel_varaus_callback' );
add_action ( 'admin_post_nopriv_wphallinta_cancel_varaus', 'wphallinta_cancel_varaus_callback' );

function wphallinta_cancel_varaus_callback() {
    if( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'wphallinta_cancel_varaus_nonce' ) ) {
        die( 'Invalid Nonce' );
    }
    $varaus_id = sanitize_text_field( $_GET['varaus_id'] );
    global $wpdb;
    $table_name = $wpdb->prefix . "varaukset";
    $data = $wpdb->get_results( "SELECT * FROM $table_name WHERE varaus_id = $varaus_id" );
    foreach ( $data as $row ) {
        $email = $row->email;
        $name = $row->tilaajan_nimi;
    }

    $to = $email;
    $subject = 'Tilaus peruutettu';
    $message = 'Hei, ' . $name . '<br><br>Tilauksenne on peruutettu.<br><br>Ystävällisin terveisin,<br>Heikkilän tila' ;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $sent = wp_mail( $to, $subject, $message, $headers );

    if($sent) {
        echo '<script>alert("Tilaus peruutettu ja sähköposti lähetetty asiakkaalle.");';
        $wpdb->delete( $table_name, array( 'varaus_id' => $varaus_id ) );
        wp_redirect( wp_get_referer() );
        exit;
    } else {
        echo '<script>alert("Tilauksen peruuttamisessa tapahtui virhe.");';
        wp_redirect( wp_get_referer() );
        exit;
    }
}

add_action( 'admin_post_wphallinta_delete_varaus', 'wphallinta_delete_varaus_callback' );
add_action ( 'admin_post_nopriv_wphallinta_delete_varaus', 'wphallinta_delete_varaus_callback' );

function wphallinta_delete_varaus_callback() {
    if( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'wphallinta_delete_varaus_nonce' ) ) {
        die( 'Invalid Nonce' );
    }
    $varaus_id = sanitize_text_field( $_GET['varaus_id'] );
    global $wpdb;
    $table_name = $wpdb->prefix . "varaukset";
    $wpdb->delete( $table_name, array( 'varaus_id' => $varaus_id ) );
    wp_redirect( wp_get_referer() );
    exit;
}

function wphallinta_admin_asetukset_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "asetukset";
    $tilaukset_tila = $wpdb->get_results( "SELECT * FROM $table_name WHERE asetus = 'tilaukset_tila' LIMIT 1" );
    ?>
    <div class="wrap">
        <div class="setting-container">
            <h1 class="setting-title">Tilaajien tiedot</h1>
            <p class="setting-desc">Täältä voit poistaa tilaajien tiedot tietokannasta.</p><br>
            <a class="setting-button" href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=wphallinta_delete_varaukset'), 'wphallinta_delete_varaukset_nonce' ); ?>">Poista tilaajien tiedot</a>
        </div>

        <div class="setting-container">
        <h1 class="setting-title">Varausasetukset</h1>
        <?php
        if ($tilaukset_tila[0]->arvo == 0) {
            echo '<p class="setting-desc">Tilaukset ovat tällä hetkellä suljettu.</p><br>
            <a class="setting-button" href="' . wp_nonce_url( admin_url('admin-post.php?action=wphallinta_toggle_tilaukset'), 'wphallinta_toggle_tilaukset_nonce' ) . '">Kytke tilaukset päälle</a>';
        } else {
            echo '<p class="setting-desc">Tilaukset ovat tällä hetkellä auki.</p><br>
            <a class="setting-button" href="' . wp_nonce_url( admin_url('admin-post.php?action=wphallinta_toggle_tilaukset'), 'wphallinta_toggle_tilaukset_nonce' ) . '">Kytke tilaukset pois päältä</a>';
        }
        ?>
        </div>

        <div class="setting-container">
        <h1 class="setting-title">Tilausten aika</h1>
        <p class="setting-desc">Tästä voit muokata tilausten aikaikkunaa.</p>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <input type="hidden" name="action" value="wphallinta_time_tilaukset">
            <label class="setting-desc">Alku: </label><input type="time" name="uusi_aika_alku"><br>
            <label class="setting-desc">Loppu: </label><input type="time" name="uusi_aika_loppu"><br>
            <input type="submit" value="Tallenna" class="setting-submit">
        </form>
        </div>
    </div>
    <?php
}

add_action( 'admin_post_wphallinta_time_tilaukset', 'wphallinta_time_tilaukset_callback' );
add_action ( 'admin_post_nopriv_wphallinta_time_tilaukset', 'wphallinta_time_tilaukset_callback' );

function wphallinta_time_tilaukset_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . "asetukset";
    $uusi_aika_alku = sanitize_text_field( $_POST['uusi_aika_alku'] );
    $uusi_aika_loppu = sanitize_text_field( $_POST['uusi_aika_loppu'] );

    $wpdb->update( $table_name, array( 'arvo' => $uusi_aika_alku ), array( 'asetus' => 'tilaukset_aika_alku' ) );
    $wpdb->update( $table_name, array( 'arvo' => $uusi_aika_loppu ), array( 'asetus' => 'tilaukset_aika_loppu' ) );

    wp_redirect( wp_get_referer() );
    exit;
}

add_action( 'admin_post_wphallinta_toggle_tilaukset', 'wphallinta_toggle_tilaukset_callback' );
add_action ( 'admin_post_nopriv_wphallinta_toggle_tilaukset', 'wphallinta_toggle_tilaukset_callback' );

function wphallinta_toggle_tilaukset_callback() {
    if( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'wphallinta_toggle_tilaukset_nonce' ) ) {
        die( 'Invalid Nonce' );
    }
    global $wpdb;
    $table_name = $wpdb->prefix . "asetukset";
    $tilaukset_tila = $wpdb->get_results( "SELECT * FROM $table_name WHERE asetus = 'tilaukset_tila' LIMIT 1" );

    if ($tilaukset_tila[0]->arvo == 0) {
        $update_to = 1;
    } else {
        $update_to = 0;
    }

    $wpdb->update( $table_name, array( 'arvo' => $update_to ), array( 'asetus' => 'tilaukset_tila' ) );
    wp_redirect( wp_get_referer() );
    exit;
}

add_action( 'admin_post_wphallinta_delete_varaukset', 'wphallinta_delete_varaukset_callback' );
add_action ( 'admin_post_nopriv_wphallinta_delete_varaukset', 'wphallinta_delete_varaukset_callback' );

function wphallinta_delete_varaukset_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . "varaukset";
    $wpdb->query( "TRUNCATE TABLE $table_name" );
    wp_redirect( wp_get_referer() );
    exit;
}