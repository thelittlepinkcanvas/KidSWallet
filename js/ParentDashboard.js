document.addEventListener("DOMContentLoaded", () => {
    const currentUser = JSON.parse(localStorage.getItem("kw_currentUser") || "null");

    // Populate parent name in welcome header
    const nameEls = document.querySelectorAll(".user-name-parent");
    nameEls.forEach(el => {
        el.textContent = currentUser?.name || "March Rosebloom";
    });

    // Wire "+ Add child account" link
    const addChildLink = document.querySelector(".addChild");
    if (addChildLink) {
        addChildLink.href = "child-account-create.html";
    }

    // Render dynamically added children if present in localStorage
    if (currentUser && Array.isArray(currentUser.children) && currentUser.children.length > 0) {
        const cont1 = document.querySelector(".cont1");
        if (cont1) {
            // Remove hardcoded placeholder rows
            cont1.querySelectorAll(".child").forEach(row => row.remove());

            currentUser.children.forEach(child => {
                const row = document.createElement("div");
                row.className = "child";
                row.innerHTML = `
                    <p class="childName">${escapeHtml(child.name)}</p>
                    <p>Money spent: RM ${Number(child.moneySpent || 0).toFixed(2)}</p>
                `;
                cont1.appendChild(row);
            });
        }
    }

    // Logout handler
    const logoutBtn = document.getElementById("logout");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", (e) => {
            // Inline onclick confirm already runs; if confirmed, clear session and redirect
            localStorage.removeItem("kw_currentUser");
            window.location.href = "login.html";
        });
    }

    function escapeHtml(str) {
        const div = document.createElement("div");
        div.textContent = str;
        return div.innerHTML;
    }
});