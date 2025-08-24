// Toggle the visibility of a dropdown menu
const toggleDropdown = (dropdown, menu, isOpen) => {
    dropdown.classList.toggle("open", isOpen);
    menu.style.height = isOpen ? `${menu.scrollHeight}px` : 0;
  };
  
  // Close all open dropdowns
  const closeAllDropdowns = () => {
    document.querySelectorAll(".dropdown-container.open").forEach((openDropdown) => {
      toggleDropdown(openDropdown, openDropdown.querySelector(".dropdown-menu"), false);
    });
  };
  
  // Attach click event to all dropdown toggles
  document.querySelectorAll(".dropdown-toggle").forEach((dropdownToggle) => {
    dropdownToggle.addEventListener("click", (e) => {
      e.preventDefault();
      const dropdown = dropdownToggle.closest(".dropdown-container");
      const menu = dropdown.querySelector(".dropdown-menu");
      const isOpen = dropdown.classList.contains("open");
      closeAllDropdowns(); // Close all open dropdowns
      toggleDropdown(dropdown, menu, !isOpen); // Toggle current dropdown visibility
    });
  });
  
  // Attach click event to sidebar toggle buttons
  document.querySelectorAll(".sidebar-toggler, .sidebar-menu-button").forEach((button) => {
    button.addEventListener("click", () => {
      closeAllDropdowns(); // Close all open dropdowns
      document.querySelector(".sidebar").classList.toggle("collapsed"); // Toggle collapsed class on sidebar
    });
  });
  
  document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar");
    const body = document.body;

    // Ensure the sidebar is collapsed by default
    sidebar.classList.add("collapsed");
    body.classList.add("sidebar-collapsed");

    const signOutLink = document.getElementById("signOutLink");
    const signOutModal = document.getElementById("signOutModal");
    const confirmSignOut = document.getElementById("confirmSignOut");
    const cancelSignOut = document.getElementById("cancelSignOut");

    // Show the modal when the "Sign Out" link is clicked
    signOutLink.addEventListener("click", (e) => {
        e.preventDefault(); // Prevent default link behavior
        signOutModal.style.display = "block"; // Show the modal
    });

    // Handle the "Yes, Sign Out" button
    confirmSignOut.addEventListener("click", () => {
        window.location.href = "logout.php"; // Redirect to logout.php
    });

    // Handle the "Cancel" button
    cancelSignOut.addEventListener("click", () => {
        signOutModal.style.display = "none"; // Hide the modal
    });

    // Close the modal if the user clicks outside of it
    window.addEventListener("click", (e) => {
        if (e.target === signOutModal) {
            signOutModal.style.display = "none";
        }
    });
});

document.getElementById('signOutLink').addEventListener('click', function (e) {
  e.preventDefault(); // Prevent default link behavior
  document.getElementById('signOutModal').style.display = 'block'; // Show the modal
});

document.getElementById('cancelSignOut').addEventListener('click', function () {
  document.getElementById('signOutModal').style.display = 'none'; // Hide the modal
});

