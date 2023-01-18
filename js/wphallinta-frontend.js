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