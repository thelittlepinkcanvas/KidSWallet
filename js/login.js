
document.addEventListener("DOMContentLoaded", () => {

    const steps = {
        email: document.getElementById("email-step"),
        pwsd: document.getElementById("pwsd-step"),
        pin: document.getElementById("pin-step"),
        mfa: document.getElementById("mfa-step"),
        approval: document.getElementById("approval-step")
    };

    const progressDots = document.querySelectorAll(".progress-step");
    const backButtons = document.querySelectorAll(".back-button");

    const passwordInput = document.getElementById("password");
    const pinInput = document.getElementById("pin");
    const mfaInput = document.getElementById("mfa-code");

    const passwordError = document.getElementById("password-error");
    const pinError = document.getElementById("pin-error");
    const mfaError = document.getElementById("mfa-error");

    function showStep(stepKey, progressIndex) {
        Object.values(steps).forEach(section => {
            if (section) {
                section.hidden = true;
                section.classList.remove("active");
            }
        });
        if (steps[stepKey]) {
            steps[stepKey].hidden = false;
            steps[stepKey].classList.add("active");
        }
        progressDots.forEach((dot, index) => {
            dot.classList.toggle("active", index <= progressIndex);
        });
    }

    backButtons.forEach(button => {
        button.addEventListener("click", () => {
            passwordInput.value = "";
            pinInput.value = "";
            mfaInput.value = "";
            passwordError.textContent = "";
            pinError.textContent = "";
            mfaError.textContent = "";
            showStep("email", 0);
        });
    });
    if (pinInput) {
        pinInput.addEventListener("input", () => {
            pinInput.value = pinInput.value
                .replace(/\D/g, "")
                .slice(0, 6);
        });
    }

    if (mfaInput) {
        mfaInput.addEventListener("input", () => {
            mfaInput.value = mfaInput.value
                .replace(/\D/g, "")
                .slice(0, 6);
        });
    }

    // remove later when implementing MFa, this is just for me to see the mfa screen for testing!!
    const previewMfaButton = document.getElementById("preview-mfa");

    if (previewMfaButton) {
        previewMfaButton.addEventListener("click", () => {
            showStep("mfa", 2);
        });
    }

});
