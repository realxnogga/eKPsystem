const barangayOptions = {
    "Sta Rosa": ["Aplaya", "Balibago", "Caingin", "Dila", "Dita", "Don Jose", "Ibaba", "Kanluran (Poblacion Uno)", "Labas", "Macabling", "Malitlit", "Malusak (Poblacion Dos)", "Market Area (Poblacion Tres)", "Pooc (Pook)", "Pulong Santa Cruz", "Santo Domingo", "Sinalhan", "Tagapo"],
    "San Pablo": ["I-A (Sambat)", "I-B (City+Riverside)", "I-C (Bagong Bayan)", "II-A (Triangulo)", "II-B (Guadalupe)", "II-C (Unson)", "II-D (Bulante)", "II-E (San Anton)", "II-F (Villa Rey)", "III-A (Hermanos Belen)", "III-B", "III-C (Labak/De Roma)", "III-D (Villongco)", "III-E", "III-F (Balagtas)", "IV-A", "IV-B", "IV-C", "V-A", "V-B", "V-C", "V-D", "VI-A (Mavenida)", "VI-B", "VI-C (Bagong Pook)", "VI-D (Lparkers)", "VI-E (YMCA)", "VII-A (P.Alcantara)", "VII-B", "VII-C", "VII-D", "VII-E", "Atisan", "Bautista", "Concepcion (Bunot)", "Del Remedio (Wawa)", "Dolores", "San Antonio 1 (Balanga)", "San Antonio 2 (Sapa)", "San Bartolome (Matang-ag)", "San Buenaventura (Palakpakin)", "San Crispin (Lumbangan)", "San Cristobal", "San Diego (Tiim)", "San Francisco (Calihan)", "San Gabriel (Butucan)", "San Gregorio", "San Ignacio", "San Isidro (Balagbag)", "San Joaquin", "San Jose (Malamig)", "San Juan", "San Lorenzo (Saluyan)", "San Lucas 1 (Malinaw)", "San Lucas 2", "San Marcos (Tikew)", "San Mateo", "San Miguel", "San Nicolas", "San Pedro", "San Rafael (Magampon)", "San Roque (Buluburan)", "San Vicente", "Santa Ana", "Santa Catalina (Sandig)", "Santa Cruz (Putol)", "Santa Elena", "Santa Filomena (Banlagin)", "Santa Isabel", "Santa Maria", "Santa Maria Magdalena (Boe)", "Santa Monica", "Santa Veronica (Bae)", "Santiago I (Bulaho)", "Santiago II", "Santisimo Rosario", "Santo Angel (Ilog)", "Santo Cristo", "Santo Niño (Arsum)", "Soledad (Macopa)"],
    "San Pedro": ["Bagong Silang", "Chrysanthemum", "Cuyab", "Estrella", "Fatima", "G.S.I.S.", "Landayan", "Langgam", "Laram", "Magsaysay", "Maharlika", "Nueva", "Pacita I", "Pacita II", "Población", "Riverside", "Rosario", "San Antonio", "San Lorenzo", "San Roque", "San Vicente", "Santo Niño", "United Bayanihan", "United Better Living", "Sampaguita Village", "Calendola", "Narra"],
    "Los Baños": ["Anos", "Batong Malake", "Bayog", "Bagong Silang", "Baybayin", "Bambang", "Lalakay", "Malinta", "Mayondon", "Maahas", "San Antonio", "Tuntungin Putho", "Timugan", "Tadlac"],
    "Calamba": ["Bagong Kalsada", "Bañadero", "Banlic", "Barandal", "Batino", "Bubuyan", "Bucal", "Bunggo", "Burol", "Camaligan", "Canlubang", "Halang", "Hornalan", "Kay-Anlog", "Laguerta", "La Mesa", "Lawa", "Lecheria", "Lingga", "Looc", "Mabato", "Majada-Labas", "Makiling", "Mapagong", "Masili", "Maunong", "Mayapa", "Milagrosa", "Paciano Rizal", "Palingon", "Palo-Alto", "Pansol", "Parian", "Barangay 1 (Pob.)", "Barangay 2 (Pob.)", "Barangay 3 (Pob.)", "Barangay 4 (Pob.)", "Barangay 5 (Pob.)", "Barangay 6 (Pob.)", "Barangay 7 (Pob.)", "Prinza", "Punta", "Puting Lupa", "Real", "Saimsim", "Sampiruhan", "San Cristobal", "San Jose", "San Juan", "Sirang Lupa", "Sucol", "Turbina", "Ulango", "Uwisan"],
    "Calauan": ["BALAYHANGIN", "BANGYAS", "DAYAP", "HANGGAN", "IMOK", "LAMOT 1", "LAMOT 2", "LIMAO", "MABACAN", "MASIIT", "PALIPARAN", "PEREZ", "POB. KANLURAN", "POB. SILANGAN", "PRINZA", "SAN ISIDRO", "SANTO TOMAS"],
    "Cabuyao": ["Baclaran", "Banaybanay", "Banlic", "Bigaa", "Butong", "Casile", "Gulod", "Mamatid", "Marinig", "Niugan", "Pittland", "Pulo", "Sala", "San Isidro", "Diezmo", "Barangay Uno (Pob.)", "Barangay Dos (Pob.)", "Barangay Tres (Pob.)"],
    "Biñan": ["Biñan", "Bungahan", "Santo Tomas (Calabuso)", "Canlalay", "Casile", "De La Paz", "Ganado", "San Francisco (Halang)", "Langkiwa", "Loma", "Malaban", "Malamig", "Mampalasan (Mamplasan)", "Platero", "Poblacion", "Santo Niño", "San Antonio", "San Jose", "San Vicente", "Soro-Soro", "Santo Domingo", "Timbao", "Tubigan", "Zapote"],
    "Bay": ["San Nicolas", "San Agustin", "Bitin", "Calo", "Dila", "Masaya", "Maitim", "Paciano Rizal", "Puypuy", "San Antonio", "Santa Cruz", "San Isidro", "Santo Domingo", "Tagumpay", "Tranca"],
    "Alaminos": ["Barangay 1 (Poblacion)", "Barangay 2 (Poblacion)", "Barangay 3 (Poblacion)", "Barangay 4 (Poblacion)", "Del Carmen", "Palma", "San Agustin (Antipolo)", "San Andres", "San Benito (Palita)", "San Gregorio", "San Ildefonso", "San Juan", "San Miguel", "San Roque", "Santa Rosa"],
    // Add barangays for other municipalities here
};

const municipalitySelect = document.getElementById("municipality");
const barangaySelect = document.getElementById("barangay");
const form = document.getElementById("registrationForm");
const passwordInput = document.getElementById("password");
const confirmPasswordInput = document.getElementById("confirmpassword");
const errorMessage = document.getElementById("error-message");

municipalitySelect.addEventListener("change", function () {
    const selectedMunicipality = this.value;
    barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';

    if (selectedMunicipality && barangayOptions[selectedMunicipality]) {
        barangayOptions[selectedMunicipality].forEach(function (barangay) {
            const option = document.createElement("option");
            option.value = barangay;
            option.textContent = barangay;
            barangaySelect.appendChild(option);
        });
    }
});

form.addEventListener("submit", function (event) {
    if (passwordInput.value !== confirmPasswordInput.value) {
        event.preventDefault(); // Prevent form submission
        errorMessage.textContent = "Passwords do not match.";
    } else {
        errorMessage.textContent = ""; // Clear error message
    }
});