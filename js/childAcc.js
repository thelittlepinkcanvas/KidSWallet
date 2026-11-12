const laterBtn=document.getElementById("later-btn");

laterBtn.addEventListener("click", function(){
    window.location.href="parent-dashboard.html";
});

document.addEventListener("DOMContentLoaded", () => {
    const currentUser = JSON.parse(localStorage.getItem("kw_currentUser") || "null");

    // Populate child name in welcome header
    const nameEls = document.querySelectorAll(".user-name-child");
    nameEls.forEach(el => {
        el.textContent = currentUser?.name || "Alice Rosebloom";
    });

    // If a newly created child account is logged in, update cards with their values
    if (currentUser && currentUser.role === "CHILD") {
        const balanceEl = document.querySelector(".balance h3");
        const spendingEl = document.querySelector(".spending h2");
        const budgetEl = document.querySelector(".budget h2");
        const spendingLeftEl = document.querySelector(".spending-left");

        const balance = Number(currentUser.balance ?? 0);
        const spending = Number(currentUser.totalSpending ?? 0);
        const budget = Number(currentUser.monthlyBudget ?? 150);
        const left = Math.max(0, budget - spending);

        if (balanceEl) balanceEl.textContent = `RM ${balance.toFixed(2)}`;
        if (spendingEl) spendingEl.textContent = `RM ${spending.toFixed(2)}`;
        if (budgetEl) budgetEl.textContent = `RM ${budget.toFixed(2)}`;
        if (spendingLeftEl) spendingLeftEl.textContent = `RM ${left.toFixed(2)} left`;
    }

    // Logout handler
    const logoutBtn = document.getElementById("logout");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            localStorage.removeItem("kw_currentUser");
            window.location.href = "login.html";
        });
    }
});