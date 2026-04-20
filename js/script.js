//Sidebar Open/Close
let arrow = document.querySelectorAll(".arrow");
for (var i = 0; i < arrow.length; i++) {
  arrow[i].addEventListener("click", (e) => {
    let arrowParent = e.target.parentElement.parentElement; //selecting main parent of arrow
    arrowParent.classList.toggle("showMenu");
  });
}
let sidebar = document.querySelector(".sidebar");
let sidebarBtn = document.querySelector(".bx-menu");
sidebarBtn.addEventListener("click", () => {
  sidebar.classList.toggle("close");
});

//Update User Image
async function saveImg() {
  let formData = new FormData();
  formData.append("file", getFile.files[0]);
  await fetch("../php/img.php", {
    method: "POST",
    body: formData,
  });
  alert("Image has been Uploaded");
}

function showTable() {
  document.querySelector("#tbody").hidden = "";
}

function hideTable() {
  document.querySelector("#tbody").hidden = "hidden";
}

function alert_dialog() {
  Swal.fire("Database Backed Up and Downloaded Successfully", "success").then(
    (ok) => {}
  );
}
