
const emailForm=document.getElementById("email-form");
const passwordForm=document.getElementById("pswd-form");
const pinForm=document.getElementById("pin-form");
const mfaForm=document.getElementById("mfa-form");

const userEmail=document.getElementById("email");
const emailError=document.getElementById("email-error");

const allSteps=document.querySelectorAll(".step");
const progressSteps=document.querySelectorAll(".progress-step");

function showStep(stepId, progressIndex){
    allSteps.forEach((step) => {
        step.hidden=step.id!==stepId;
    });
    progressSteps.forEach((step, index) => {
        step.classList.toggle("active", index <= progressIndex);
    });
}