function checkPassword(){
    let pswd1=document.getElementById("pswd1").value;
    let pswd2=document.getElementById("pswd2").value;

    if(pswd1!=pswd2){
        alert("Password mismatch! Please match both fields!");
        return false;
    }else{
        return true;
    }
}