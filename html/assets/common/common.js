function fetch_core(moduleName, formData) {
    return fetch("/post--connect/" + moduleName, {
        method: "POST",
        mode: "same-origin",
        cache: "no-cache",
        referrerPolicy: "strict-origin-when-cross-origin",
        credentials: "same-origin",
        body: formData
    }).then(response => {
        if (!response.ok) {
            switch (response.status) {
                case 404:
                    console.error("Module not found: " + moduleName);
                    break;
                case 403:
                    console.error("Forbidden: " + moduleName);
                    break;
                case 500:
                    console.error("ISE: " + moduleName);
                    break;
                default:
                    console.error(response.status + ": " + moduleName);
            }
        }
        return response.json();
    });
}