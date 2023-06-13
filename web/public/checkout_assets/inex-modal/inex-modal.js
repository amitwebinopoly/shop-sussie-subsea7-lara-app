var modal = document.getElementById('inexModal');
if(document.getElementById("inexModalBtn")){
    document.getElementById("inexModalBtn").onclick = function() {
        modal.style.display = "block";
    };
}
if(document.getElementsByClassName("inex_modal_close")){
    document.getElementsByClassName("inex_modal_close")[0].onclick = function() {
        modal.style.display = "none";
    };
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};