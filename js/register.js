const signupForm=document.getElementById("signup-form");
signupForm.addEventListener("submit", function(event){
    event.preventDefault();
    window.location.href = "child-account-create.html";
});