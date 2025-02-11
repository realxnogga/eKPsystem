

function hideToastFunc() {
    setTimeout(() => {
        const alertBox = document.getElementById("alertMessage");
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 400); // Remove from DOM after fade-out
        }
    }, 1500);
}
hideToastFunc();


function turnToDefaultMessageInURLFunc() {
    if (window.history.replaceState) {
        const url = new URL(window.location);

        if (url.searchParams.has("login_message")) {
            url.searchParams.set("login_message", "default");
        }
        if (url.searchParams.has("update_info_message")) {
            url.searchParams.set("update_info_message", "default");
        }  
        if (url.searchParams.has("update_muni_message")) {
            url.searchParams.set("update_muni_message", "default");
        }
        if (url.searchParams.has("update_account_message")) {
            url.searchParams.set("update_account_message", "default");
        }
        if (url.searchParams.has("update_securityquestion_message")) {
            url.searchParams.set("update_securityquestion_message", "default");
        }
        if (url.searchParams.has("manage_brgy_message")) {
            url.searchParams.set("manage_brgy_message", "default");
        }
        if (url.searchParams.has("manage_assessor_message")) {
            url.searchParams.set("manage_assessor_message", "default");
        }

        if (url.searchParams.has("add_userreport_message")) {
            url.searchParams.set("add_userreport_message", "default");
        }
        if (url.searchParams.has("delete_userreport_message")) {
            url.searchParams.set("delete_userreport_message", "default");
        }
        if (url.searchParams.has("edit_userreport_message")) {
            url.searchParams.set("edit_userreport_message", "default");
        }
        

        

        window.history.replaceState({}, document.title, url);
    }
}

turnToDefaultMessageInURLFunc();


