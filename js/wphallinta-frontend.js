let product_count = 0;

function show_prices(data) {
    product_count++;
    const form = document.getElementById("reservation_form");
    let selected_id = document.getElementById("selected_id").value;
    let selected = data.filter( element => element.tuote_id == selected_id)[0];
    let selected_price = JSON.parse(selected.hinta);

    let containerDiv = document.createElement("div");
    containerDiv.className = "form-group";

    let otsikko = document.createElement("label");
    otsikko.innerHTML = selected.tuote;

    let laatu = document.createElement("select");
    laatu.name = "laatu[]";

    let maara = document.createElement("input");
    maara.type = "text";
    maara.name = "maara[]";
    maara.placeholder = "Määrä";

    let tuote_id = document.createElement("input");
    tuote_id.type = "hidden";
    tuote_id.name = "tuote_id[]";
    tuote_id.value = selected.tuote_id;

    let tuote_name = document.createElement("input");
    tuote_name.type = "hidden";
    tuote_name.name = "tuote_name[]";
    tuote_name.value = selected.tuote;

    for (let x = 0; x < selected_price.length; x++) {
        console.log(selected_price[x].nimi + " " + selected_price[x].arvo);
        laatu.value = selected_price[x].nimi;
        laatu.innerHTML += "<option value="+ selected_price[x].nimi +">Laatu: " + selected_price[x].nimi + " Hinta: " + selected_price[x].arvo +"€/kg</option>";
    }

    containerDiv.appendChild(otsikko);
    containerDiv.appendChild(laatu);
    containerDiv.appendChild(maara);
    containerDiv.appendChild(tuote_id);
    containerDiv.appendChild(tuote_name);
    document.getElementById("selected_id").insertAdjacentElement("afterend", containerDiv);
}