document.addEventListener("DOMContentLoaded", function () {
    const panel = document.getElementById('sidePanel');
    const closeBtn = document.getElementById('closePanel');
    const panelContent = document.getElementById('panelContent');
    const links = document.querySelectorAll('.sidebar a');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            const section = this.getAttribute('data-section');
            
            // Only prevent default for profile section
            if (section === 'profile') {
                e.preventDefault();
                panel.classList.remove('hidden');
            }
        });
    });

    closeBtn.addEventListener('click', function () {
        panel.classList.add('hidden');
    });
});

document.addEventListener("DOMContentLoaded", function () {
  const statusFilter = document.querySelector(".status");
  const locationFilter = document.querySelector(".location");
  const typeFilter = document.querySelector(".items-dropdown2");
  const cards = document.querySelectorAll(".item-card");

  function filterCards() {
    const statusValue = statusFilter.value.toLowerCase();
    const locationValue = locationFilter.value.toLowerCase();
    const typeValue = typeFilter.value.toLowerCase();

    cards.forEach(card => {
      const cardStatus = card.dataset.status.toLowerCase();
      const cardLocation = card.dataset.location.toLowerCase();
      const cardType = card.dataset.type.toLowerCase();

      const matchesStatus = cardStatus === statusValue;
      const matchesLocation = cardLocation === locationValue;
      const matchesType = cardType === typeValue;

      if (matchesStatus && matchesLocation && matchesType) {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });
  }

  statusFilter.addEventListener("change", filterCards);
  locationFilter.addEventListener("change", filterCards);
  typeFilter.addEventListener("change", filterCards);
});

const sidebarLinks = document.querySelectorAll('.sidebar a');

sidebarLinks.forEach(link => {
  link.addEventListener('click', function () {
    sidebarLinks.forEach(l => l.classList.remove('active')); // remove from all
    this.classList.add('active'); // add to clicked one
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const menuButtons = document.querySelectorAll(".card-menu");

  menuButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      // Close all other open menus
      document.querySelectorAll(".card-dropdown").forEach(drop => drop.classList.add("hidden"));

      // Toggle dropdown visibility
      const dropdown = btn.nextElementSibling;
      dropdown.classList.toggle("hidden");

      // Prevent click from affecting other elements
      e.stopPropagation();
    });
  });

  // Hide dropdown if clicked outside
  document.addEventListener("click", function () {
    document.querySelectorAll(".card-dropdown").forEach(drop => drop.classList.add("hidden"));
  });
});

// Handle card menu clicks
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('card-menu')) {
        const dropdown = e.target.nextElementSibling;
        dropdown.classList.toggle('hidden');
    }
});

// Add update panel styles
const updatePanelStyles = document.createElement('style');
updatePanelStyles.textContent = `
    .update-panel {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        width: 90%;
        max-width: 500px;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    }

    .update-panel.popup-success {
        border-left: 4px solid #4CAF50;
    }

    .update-panel.popup-error {
        border-left: 4px solid #f44336;
    }

    .update-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .update-form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .update-form-group label {
        color: #1a237e;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .update-form-group input {
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .update-form-group input:focus {
        outline: none;
        border-color: #1a237e;
        box-shadow: 0 0 0 2px rgba(26,35,126,0.1);
    }

    .update-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 1rem;
    }

    .update-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .update-btn.confirm {
        background: #1a237e;
        color: white;
    }

    .update-btn.cancel {
        background: #e0e0e0;
        color: #333;
    }

    .update-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(updatePanelStyles);

// Handle Update button clicks
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('update-btn')) {
        const card = e.target.closest('.item-card');
        const itemId = card.dataset.itemId;
        const itemType = card.dataset.status; // 'lost' or 'found'
        
        // Get current values
        const itemName = card.querySelector('h3').textContent;
        const color = card.querySelector('p:nth-of-type(1)').textContent.replace('Color:', '').trim();
        const location = card.querySelector('p:nth-of-type(2)').textContent.replace('Location:', '').trim();
        const description = card.querySelector('p:nth-of-type(3)').textContent.replace('Description:', '').trim();

        const updateForm = `
            <div class="update-panel">
                <div class="popup-title">Update Item</div>
                <form class="update-form">
                    <div class="update-form-group">
                        <label>Item Name:</label>
                        <input type="text" name="item_name" value="${itemName}" required>
                    </div>
                    <div class="update-form-group">
                        <label>Color:</label>
                        <input type="text" name="color" value="${color}" required>
                    </div>
                    <div class="update-form-group">
                        <label>Location:</label>
                        <input type="text" name="location" value="${location}" required>
                    </div>
                    <div class="update-form-group">
                        <label>Description:</label>
                        <input type="text" name="description" value="${description}">
                    </div>
                    <div class="update-buttons">
                        <button type="button" class="update-btn cancel">Cancel</button>
                        <button type="submit" class="update-btn confirm">Update</button>
                    </div>
                </form>
            </div>
        `;

        // Show the update panel
        popupOverlay.innerHTML = updateForm;
        popupOverlay.classList.add('show');

        // Handle form submission
        const form = popupOverlay.querySelector('form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            formData.append('item_id', itemId);
            formData.append('item_type', itemType);

            fetch('reports/update_item.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                popupOverlay.classList.remove('show');
                showPopup({
                    title: data.title,
                    message: data.message,
                    type: data.type,
                    onConfirm: () => {
                        if (data.success && data.reload) {
                            window.location.reload();
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
                popupOverlay.classList.remove('show');
                showPopup({
                    title: 'Error',
                    message: 'An error occurred while updating the item. Please try again.',
                    type: 'error'
                });
            });
        });

        // Handle cancel button
        const cancelBtn = popupOverlay.querySelector('.update-btn.cancel');
        cancelBtn.addEventListener('click', () => {
            popupOverlay.classList.remove('show');
        });
    }
});

// Add popup panel styles
const popupStyles = document.createElement('style');
popupStyles.textContent = `
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .popup-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    .popup-panel {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        width: 90%;
        max-width: 400px;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    }

    .popup-overlay.show .popup-panel {
        transform: translateY(0);
    }

    .popup-title {
        color: #1a237e;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .popup-message {
        color: #555;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .popup-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .popup-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .popup-btn.confirm {
        background: #f44336;
        color: white;
    }

    .popup-btn.cancel {
        background: #e0e0e0;
        color: #333;
    }

    .popup-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .popup-success {
        border-left: 4px solid #4CAF50;
    }

    .popup-error {
        border-left: 4px solid #f44336;
    }
`;
document.head.appendChild(popupStyles);

// Create popup overlay
const popupOverlay = document.createElement('div');
popupOverlay.className = 'popup-overlay';
document.body.appendChild(popupOverlay);

// Function to show popup
function showPopup(options) {
    const { title, message, type = 'confirm', onConfirm, onCancel } = options;

    const popupContent = `
        <div class="popup-panel ${type === 'success' ? 'popup-success' : type === 'error' ? 'popup-error' : ''}">
            <div class="popup-title">${title}</div>
            <div class="popup-message">${message}</div>
            <div class="popup-buttons">
                ${type === 'confirm' ? `
                    <button class="popup-btn cancel">Cancel</button>
                    <button class="popup-btn confirm">Delete</button>
                ` : `
                    <button class="popup-btn confirm">OK</button>
                `}
            </div>
        </div>
    `;

    popupOverlay.innerHTML = popupContent;
    popupOverlay.classList.add('show');

    const buttons = popupOverlay.querySelectorAll('.popup-btn');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            popupOverlay.classList.remove('show');
            if (button.classList.contains('confirm')) {
                onConfirm && onConfirm();
            } else {
                onCancel && onCancel();
            }
        });
    });
}

// Handle Delete button clicks
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete-btn')) {
        const card = e.target.closest('.item-card');
        const itemId = card.dataset.itemId;
        const itemType = card.dataset.status; // 'lost' or 'found'

        showPopup({
            title: 'Confirm Deletion',
            message: 'Are you sure you want to delete this item? This action cannot be undone.',
            type: 'confirm',
            onConfirm: () => {
                const formData = new FormData();
                formData.append('item_id', itemId);
                formData.append('item_type', itemType);

                fetch('reports/delete_item.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showPopup({
                        title: data.title,
                        message: data.message,
                        type: data.type,
                        onConfirm: () => {
                            if (data.success && data.reload) {
                                window.location.reload();
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    showPopup({
                        title: 'Error',
                        message: 'An error occurred while deleting the item. Please try again.',
                        type: 'error'
                    });
                });
            }
        });
    }
});

// Add event listeners for approve/reject claim buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('approve-claim-btn') || e.target.classList.contains('reject-claim-btn')) {
        const itemId = e.target.dataset.itemId;
        const action = e.target.classList.contains('approve-claim-btn') ? 'approve' : 'reject';
        
        if (confirm(`Are you sure you want to ${action} this claim?`)) {
            fetch('handle_claim.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `found_id=${encodeURIComponent(itemId)}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    // Refresh the page to update the display
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing claim. Please try again.');
            });
        }
    }
});

// Handle view claim details button clicks
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('view-claim-btn')) {
        const itemId = e.target.dataset.itemId;
        window.location.href = `ClaimedItems.php?item_id=${itemId}`;
    }
});

// Add CSS for the claim popup
const claimStyle = document.createElement('style');
claimStyle.textContent = `
    .claim-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        width: 300px;
    }
    .claim-popup h3 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    .claim-popup input {
        width: 100%;
        padding: 8px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .claim-popup .buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .claim-popup button {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .claim-popup .submit-btn {
        background: #4CAF50;
        color: white;
    }
    .claim-popup .cancel-btn {
        background: #f44336;
        color: white;
    }
`;
document.head.appendChild(claimStyle);

// Handle Claim button clicks
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('staff-claim-btn')) {
        const card = e.target.closest('.item-card');
        const itemId = card.dataset.itemId;
        
        // Create claim popup
        const popup = document.createElement('div');
        popup.className = 'claim-popup';
        popup.innerHTML = `
            <h3>Enter Student ID</h3>
            <input type="text" id="studentIdInput" placeholder="Enter student ID" required>
            <div class="buttons">
                <button class="cancel-btn">Cancel</button>
                <button class="submit-btn">Submit</button>
            </div>
        `;
        
        document.body.appendChild(popup);
        
        // Focus the input
        popup.querySelector('#studentIdInput').focus();
        
        // Handle cancel button
        popup.querySelector('.cancel-btn').addEventListener('click', function() {
            popup.remove();
        });
        
        // Handle submit button
        popup.querySelector('.submit-btn').addEventListener('click', function() {
            const studentId = popup.querySelector('#studentIdInput').value.trim();
            
            if (!studentId) {
                alert('Please enter a student ID');
                return;
            }
            
            const formData = new FormData();
            formData.append('found_id', itemId);
            formData.append('student_id', studentId);
            
            fetch('process_claim.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing claim. Please try again.');
            });
            
            popup.remove();
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Profile panel functionality
    const profileBtn = document.querySelector('.profile-btn');
    const sidePanel = document.getElementById('sidePanel');
    const closePanel = document.getElementById('closePanel');

    profileBtn.addEventListener('click', function() {
        sidePanel.classList.remove('hidden');
        setTimeout(() => {
            sidePanel.classList.add('active');
        }, 10);
    });

    closePanel.addEventListener('click', function() {
        sidePanel.classList.remove('active');
        setTimeout(() => {
            sidePanel.classList.add('hidden');
        }, 300);
    });

    // Close panel when clicking outside
    document.addEventListener('click', function(event) {
        if (!sidePanel.contains(event.target) && 
            !profileBtn.contains(event.target) && 
            !sidePanel.classList.contains('hidden')) {
            closePanel.click();
        }
    });

    // Handle escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !sidePanel.classList.contains('hidden')) {
            closePanel.click();
        }
    });
});

// Profile Panel Functionality
document.addEventListener('DOMContentLoaded', function() {
  const profilePanel = document.getElementById('profilePanel');
  const profileLink = document.querySelector('.profile-link');
  const closeProfileBtn = document.getElementById('closeProfilePanel');

  if (profileLink && profilePanel && closeProfileBtn) {
    // Open profile panel when clicking the profile link
    profileLink.addEventListener('click', function(e) {
      e.preventDefault();
      profilePanel.classList.add('active');
    });

    // Close profile panel when clicking the close button
    closeProfileBtn.addEventListener('click', function() {
      profilePanel.classList.remove('active');
    });

    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
      if (!profilePanel.contains(e.target) && 
          !profileLink.contains(e.target) && 
          profilePanel.classList.contains('active')) {
        profilePanel.classList.remove('active');
      }
    });
  }

  // Handle three-dot menu functionality
  const menuButtons = document.querySelectorAll('.card-menu');
  menuButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.stopPropagation(); // Prevent event from bubbling up
      
      // Find the dropdown for this button
      const dropdown = this.nextElementSibling;
      
      // Close all other dropdowns first
      document.querySelectorAll('.card-dropdown').forEach(d => {
        if (d !== dropdown) {
          d.classList.remove('show');
        }
      });
      
      // Toggle the dropdown
      dropdown.classList.toggle('show');
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.matches('.card-menu')) {
      document.querySelectorAll('.card-dropdown').forEach(dropdown => {
        dropdown.classList.remove('show');
      });
    }
  });

  // Auto-hide messages after 5 seconds
  const messages = document.querySelectorAll('.success-message, .error-message');
  messages.forEach(message => {
    setTimeout(() => {
      message.remove();
    }, 5000);
  });
});
