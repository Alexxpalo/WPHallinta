var hinnat_count = 1;

function add_price() {
    hinnat_count++;
    var input = document.createElement("input");
    input.type = "text";
    input.name = "hinnat_nimi[]";
    input.placeholder = "Hinnan " + hinnat_count + " nimi";

    var input2 = document.createElement("input");
    input2.type = "text";
    input2.name = "hinnat_arvo[]";
    input2.placeholder = "Hinnan " + hinnat_count + " arvo";

    var br = document.createElement("br");

    var form = document.getElementById("add_product_form");

    var button = document.getElementById("add_price_button");

    form.insertBefore(input, button);
    form.insertBefore(input2, button);
    form.insertBefore(br, button);
}