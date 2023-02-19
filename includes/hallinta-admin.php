<?php
// MENU PAGES

//TUOTEHALLINTA
function wphallinta_admin_page(){
	?>
    <h1>Tuotteet</h1>
	<div class="wrap flex-row gap-25">
        <div class="flex-1">
        <h2>Lisää tuote</h2>
        <form id="add_product_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <input type="hidden" name="action" value="wphallinta_add_product">
            <input type="text" name="tuote" placeholder="Tuotteen nimi"><br><h3>Hinnat</h3>
            <input type="text" name="hinnat_nimi[]" placeholder="Hinnan 1 nimi"><input type="text" name="hinnat_arvo[]" placeholder="Hinnan 1 arvo"><br>
            <button type="button" id="add_price_button" onclick="add_price()">Lisää hinta</button>
            <h3>Muut tiedot</h3>
            <textarea type="text" name="kuvaus" rows="5" placeholder="Tuotteen kuvaus" style="width:100%;"></textarea><br>
            <label>Satokausi: </label>
            <input type="date" name="satokausi_alku">-
            <input type="date" name="satokausi_loppu">
            <input type="text" name="varasto" placeholder="Tuotteen määrä"><br>
            <input type="submit" value="Lisää tuote">
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
                    <th class="manage-column">Tunniste</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . "tuotteet";
                    $tuotteet = $wpdb->get_results( "SELECT * FROM $table_name" );

                    foreach ( $tuotteet as $tuote ) {
                        $hinnat_display = null;
                        $hinnat_data = json_decode($tuote->hinta);
                        for($i = 0; $i < count($hinnat_data); $i++) {
                            $hinnat_display .= $hinnat_data[$i]->nimi . ': ' . $hinnat_data[$i]->arvo . '€<br>';
                        }
                        echo '<tr>';
                        echo '<td>' . $tuote->tuote . '</td>';
                        echo '<td>' . $tuote->varasto . '</td>';
                        echo '<td>' . $hinnat_display . '</td>';
                        echo '<td>' . $tuote->kuvaus . '</td>';
                        echo '<td>' . date("d/m", strtotime($tuote->satokausi_alku)) . ' - ' . date("d/m", strtotime($tuote->satokausi_loppu)) . '</td>';
                        echo '<td>' . $tuote->tuote_id . '</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
        </div>
	<?php
}

add_action( 'admin_post_wphallinta_add_product', 'wphallinta_add_product_callback' );
add_action ( 'admin_post_nopriv_wphallinta_add_product', 'wphallinta_add_product_callback' );

function wphallinta_add_product_callback() {
    $tuote = sanitize_text_field( $_POST['tuote'] );
    $hinnat_nimi = array_map('sanitize_text_field', $_POST['hinnat_nimi'] );
    $hinnat_arvo = array_map('sanitize_text_field', $_POST['hinnat_arvo'] );
    $kuvaus = sanitize_text_field( $_POST['kuvaus'] );
    $varasto = sanitize_text_field( $_POST['varasto'] );
    $satokausi_alku = sanitize_text_field( $_POST['satokausi_alku'] );
    $satokausi_loppu = sanitize_text_field( $_POST['satokausi_loppu'] );
    $hinnat_json = array();

    for($i = 0; $i < count($hinnat_nimi); $i++) {
        $hinnat_json[] = array(
            'nimi' => $hinnat_nimi[$i],
            'arvo' => $hinnat_arvo[$i]
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
            'varasto' => $varasto,
            'satokausi_alku' => $satokausi_alku,
            'satokausi_loppu' => $satokausi_loppu
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s'
        )
    );
    wp_redirect( wp_get_referer() );
    exit;
}

add_action( 'admin_post_wphallinta_edit_product', 'wphallinta_edit_product_callback' );
add_action ( 'admin_post_nopriv_wphallinta_edit_product', 'wphallinta_edit_product_callback' );

function wphallinta_edit_product_callback() {
    if( !isset( $_POST['wphallinta_edit_product_nonce'] ) || !wp_verify_nonce( $_POST['wphallinta_edit_product_nonce'], 'wphallinta_edit_product_nonce' ) ) {
        die( 'Invalid Nonce' );
    }
    $product_id = sanitize_text_field( $_POST['product_id'] );
    global $wpdb;
    $table_name = $wpdb->prefix . "tuotteet";
    $valittu_tuote = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $product_id" );


    wp_redirect( admin_url('admin.php?page=wphallinta_edit_product&product_id=' . $product_id) );
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
                    <th class="manage-column">Toivottu toimitusaika</th>
                    <th class="manage-column">Toimitustapa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ( $varaukset as $varaus ) {
                        echo '<tr>';
                        echo '<td>' . $varaus->varatut_tuotteet . '</td>';
                        echo '<td>' . $varaus->tilaajan_nimi . '</td>';
                        echo '<td>' . $varaus->puhelinnumero . '</td>';
                        echo '<td>' . $varaus->tila . '</td>';
                        echo '<td>' . $varaus->toimituspvm . '</td>';
                        echo '<td>' . $varaus->toimitustapa . '</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
    </div>
    <?php
}