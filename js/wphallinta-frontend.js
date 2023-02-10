var products_count = 1;

function add_to_reservation() {
    products_count++;
    var input = document.createElement("input");
    input.type = "text";
    input.name = "hinnat_nimi[]";
    input.placeholder = "Hinnan " + products_count + " nimi";

    var input2 = document.createElement("input");
    input2.type = "text";
    input2.name = "hinnat_arvo[]";
    input2.placeholder = "Hinnan " + products_count + " arvo";

    var br = document.createElement("br");

    var form = document.getElementById("reservation_form");

    var button = document.getElementById("btn_add_product");

    form.insertBefore(input, button);
    form.insertBefore(input2, button);
    form.insertBefore(br, button);
}

function show_prices(data) {
    var form = document.getElementById("reservation_form");
    var selected_id = document.getElementById("selected_id").value;
    var selected = data.filter( element => element.tuote_id == selected_id)[0];
    selected_price = JSON.parse(selected.hinta);

    var laatu = document.createElement("select");
    laatu.name = "laatu";

    var maara = document.createElement("input");
    maara.type = "text";
    maara.name = "maara";
    maara.placeholder = "Määrä";

    for (let x = 0; x < selected_price.length; x++) {
        console.log(selected_price[x].nimi + " " + selected_price[x].arvo);
        
        laatu.value = selected_price[x].nimi;
        laatu.innerHTML += "<option>Laatu: " + selected_price[x].nimi + " Hinta: " + selected_price[x].arvo +"€/kg</option>";

    }
    form.insertBefore(laatu, document.getElementById("btn_add_product"));
    form.insertBefore(maara, document.getElementById("btn_add_product"));
}