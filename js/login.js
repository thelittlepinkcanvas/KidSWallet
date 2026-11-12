
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

    const emailForm = document.getElementById("email-form");
    const pswdForm = document.getElementById("pswd-form");
    const pinForm = document.getElementById("pin-form");
    const mfaForm = document.getElementById("mfa-form");

    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const pinInput = document.getElementById("pin");
    const mfaInput = document.getElementById("mfa-code");

    const emailError = document.getElementById("email-error");
    const passwordError = document.getElementById("password-error");
    const pinError = document.getElementById("pin-error");
    const mfaError = document.getElementById("mfa-error");

    const parentNameSpan = document.getElementById("user-name-parent");
    const childNameSpan = document.getElementById("user-name-child");

    let pendingAccount = null;

    function showStep(stepKey, progressIndex) {
        Object.values(steps).forEach(sec => {
            if (sec) {
                sec.hidden = true;
                sec.classList.remove("active");
            }
        });

        if (steps[stepKey]) {
            steps[stepKey].hidden = false;
            steps[stepKey].classList.add("active");
        }

        progressDots.forEach((dot, idx) => {
            dot.classList.toggle("active", idx <= progressIndex);
        });
    }

    // Back buttons reset to email step
    backButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            passwordInput.value = "";
            pinInput.value = "";
            mfaInput.value = "";
            emailError.textContent = "";
            passwordError.textContent = "";
            pinError.textContent = "";
            mfaError.textContent = "";
            showStep("email", 0);
        });
    });

    // Restrict MFA & PIN inputs to digits
    mfaInput.addEventListener("input", () => {
        mfaInput.value = mfaInput.value.replace(/\D/g, "").slice(0, 6);
    });
    pinInput.addEventListener("input", () => {
        pinInput.value = pinInput.value.replace(/\D/g, "").slice(0, 6);
    });

    // STEP 1: Identify email & route to Parent (password) or Child (PIN)
    emailForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        emailError.textContent = "";
        const email = emailInput.value.trim().toLowerCase();

        try {
            const response = await fetch("/api/auth/lookup", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email })
            });

            if (response.ok) {
                const data = await response.json();
                pendingAccount = { email, name: data.name, role: data.role };
                if (data.role === "PARENT") {
                    parentNameSpan.textContent = data.name;
                    showStep("pwsd", 1);
                } else {
                    childNameSpan.textContent = data.name;
                    showStep("pin", 1);
                }
                return;
            } else if (response.status !== 404 && response.status !== 405) {
                emailError.textContent = "Account not found.";
                return;
            }
        } catch (_) {
            // Fallback to localStorage
        }

        const users = JSON.parse(localStorage.getItem("kw_users") || "[]");
        const found = users.find(u => u.email === email);

        if (!found) {
            emailError.textContent = "No account found with that email. Please sign up first.";
            return;
        }

        pendingAccount = found;
        if (found.role === "PARENT") {
            parentNameSpan.textContent = found.name;
            showStep("pwsd", 1);
        } else {
            childNameSpan.textContent = found.name;
            showStep("pin", 1);
        }
    });

    // STEP 2A: Parent Password -> MFA Step
    pswdForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        passwordError.textContent = "";
        const password = passwordInput.value;

        try {
            const response = await fetch("/api/auth/login/parent", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email: pendingAccount.email, password })
            });

            if (response.ok) {
                showStep("mfa", 2);
                return;
            } else if (response.status !== 404 && response.status !== 405) {
                passwordError.textContent = "Incorrect password.";
                return;
            }
        } catch (_) {
            // Fallback to localStorage
        }

        if (!pendingAccount || pendingAccount.password !== password) {
            passwordError.textContent = "Incorrect password. Please try again.";
            return;
        }

        // Demo MFA code hint in console for easy testing
        console.info("KidSWallet Demo MFA Code: 123456 (any 6 digits accepted in demo mode)");
        showStep("mfa", 2);
    });

    // STEP 3A: Parent MFA Verification -> Parent Dashboard
    mfaForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        mfaError.textContent = "";
        const code = mfaInput.value.trim();

        if (!/^\d{6}$/.test(code)) {
            mfaError.textContent = "Please enter the 6-digit verification code.";
            return;
        }

        try {
            const response = await fetch("/api/auth/verify-mfa", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email: pendingAccount.email, code })
            });

            if (response.ok) {
                const data = await response.json();
                localStorage.setItem("kw_currentUser", JSON.stringify({ ...pendingAccount, token: data.token }));
                window.location.href = "parent-dashboard.html";
                return;
            } else if (response.status !== 404 && response.status !== 405) {
                mfaError.textContent = "Invalid or expired verification code.";
                return;
            }
        } catch (_) {
            // Fallback
        }

        localStorage.setItem("kw_currentUser", JSON.stringify(pendingAccount));
        window.location.href = "parent-dashboard.html";
    });

    // STEP 2B: Child PIN -> Parent Approval Step -> Child Dashboard
    pinForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        pinError.textContent = "";
        const pin = pinInput.value.trim();

        try {
            const response = await fetch("/api/auth/login/child", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email: pendingAccount.email, pin })
            });

            if (response.ok) {
                showStep("approval", 2);
                pollParentApproval(pendingAccount.email);
                return;
            } else if (response.status !== 404 && response.status !== 405) {
                pinError.textContent = "Incorrect PIN.";
                return;
            }
        } catch (_) {
            // Fallback
        }

        if (!pendingAccount || pendingAccount.pin !== pin) {
            pinError.textContent = "Incorrect PIN. Please try again.";
            return;
        }

        showStep("approval", 2);

        // Simulate parent approval after 2.5 seconds in standalone demo mode
        setTimeout(() => {
            localStorage.setItem("kw_currentUser", JSON.stringify(pendingAccount));
            window.location.href = "child-dashboard.html";
        }, 2500);
    });

    function pollParentApproval(email) {
        const interval = setInterval(async () => {
            try {
                const res = await fetch(`/api/auth/approval-status?email=${encodeURIComponent(email)}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.approved) {
                        clearInterval(interval);
                        localStorage.setItem("kw_currentUser", JSON.stringify({ ...pendingAccount, token: data.token }));
                        window.location.href = "child-dashboard.html";
                    }
                }
            } catch (_) {
                clearInterval(interval);
            }
        }, 2000);
    }
});