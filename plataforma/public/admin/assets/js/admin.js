"use strict";

const sidebarToggle =
    document.getElementById(
        "sidebar-toggle"
    );

const overlay =
    document.getElementById(
        "admin-overlay"
    );

function abrirSidebar() {
    document.body.classList.add(
        "sidebar-open"
    );
}

function cerrarSidebar() {
    document.body.classList.remove(
        "sidebar-open"
    );
}

if (sidebarToggle) {

    sidebarToggle.addEventListener(
        "click",
        () => {
            if (
                document.body
                    .classList
                    .contains(
                        "sidebar-open"
                    )
            ) {
                cerrarSidebar();
            } else {
                abrirSidebar();
            }
        }
    );
}

if (overlay) {

    overlay.addEventListener(
        "click",
        cerrarSidebar
    );
}

window.addEventListener(
    "resize",
    () => {
        if (
            window.innerWidth
            > 820
        ) {
            cerrarSidebar();
        }
    }
);

const adminToast =
    document.getElementById(
        "admin-toast"
    );

if (adminToast) {
    window.setTimeout(
        () => {
            adminToast.style.opacity = "0";
            adminToast.style.transform =
                "translateY(-8px)";

            window.setTimeout(
                () => {
                    adminToast.remove();
                },
                180
            );
        },
        3500
    );
}

const selectAllCategories =
    document.getElementById(
        "select-all-categories"
    );

const clearAllCategories =
    document.getElementById(
        "clear-all-categories"
    );

const printCategoryInputs =
    document.querySelectorAll(
        '.print-category-card input[type="checkbox"]'
    );

if (selectAllCategories) {
    selectAllCategories.addEventListener(
        "click",
        () => {
            printCategoryInputs.forEach(
                input => {
                    input.checked = true;
                }
            );
        }
    );
}

if (clearAllCategories) {
    clearAllCategories.addEventListener(
        "click",
        () => {
            printCategoryInputs.forEach(
                input => {
                    input.checked = false;
                }
            );
        }
    );
}