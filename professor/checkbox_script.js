
    // Wait for the DOM to load
document.addEventListener("DOMContentLoaded", function () {
    // Get references to the "Select All" checkbox and individual checkboxes
    const selectAllCheckbox = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll("tbody input[type='checkbox']");

    // Store selected IDs
    let selectedIds = new Set();

    // Update selected IDs based on the checkbox state
    const updateSelectedIds = () => {
        selectedIds.clear();
        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                selectedIds.add(checkbox.dataset.id); // Use data-id attribute
            }
        });
        console.log(Array.from(selectedIds)); // Log selected IDs for debugging
    };

    // Event listener for "Select All" checkbox
    selectAllCheckbox.addEventListener("change", () => {
        const isChecked = selectAllCheckbox.checked;
        checkboxes.forEach((checkbox) => {
            checkbox.checked = isChecked;
        });
        updateSelectedIds();
    });

    // Event listeners for individual checkboxes
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", () => {
            updateSelectedIds();
        });
    });
});

