<div class="files-upload-wrap" id="files-upload-wrap">
    <div id="files-upload-form">
        <div class="input-group mb-3">
            <input type="file" class="form-control" onchange="addInputGroup()">
            <input type="text" class="form-control" placeholder="新しいファイル名">
        </div>
    </div>
    <div class="files-upload-action">
        <button type="button" class="btn btn-primary" onclick="submitFiles()">UPLOAD</button>
    </div>
</div>
<script>

    function addInputGroup() {
        let empty = false;
        let inputGroups = document.querySelectorAll("#files-upload-form .input-group");
        for (let i = 0; i < inputGroups.length; i++) {
            let fileinput = inputGroups[i].querySelector("input");
            if (fileinput.value === "") {
                empty = true;
            }
        }
        if (empty === false) {
            let formElement = document.createElement("div");
            formElement.className = "input-group mb-3";
            formElement.innerHTML = `
                <input type="file" class="form-control" onchange="addInputGroup()">
                <input type="text" class="form-control" placeholder="新しいファイル名">
`;
            document.getElementById("files-upload-form").appendChild(formElement);
        }
    }

    function submitFiles() {
        let files = document.querySelectorAll("#files-upload-form .input-group");
        let formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            let inputs = files[i].querySelectorAll("input");
            let file = inputs[0].files;
            let name = inputs[1].value;
            if (file !== undefined) {
                formData.append("file_" + i, file[0]);
                formData.append("file_" + i + "_name", name);
            }
        }
        let res = fetch_core("FileUpload", formData).then(response => {
            if (response["status"] && response["status"] === "success") {
                if (response["html"]) {
                    document.getElementById("files-list-container").innerHTML = response["html"];
                }
            }
        });

    }
</script>
