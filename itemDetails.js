document.addEventListener('DOMContentLoaded', function() {
  // Function to close update form
  function closeUpdateForm() {
    const formContainer = document.getElementById('updateFormContainer');
    const overlay = document.getElementById('cardOverlay');
    
    overlay.classList.remove('show');
    formContainer.classList.remove('show');
    
    // Remove the form content after hiding
    setTimeout(() => {
      formContainer.innerHTML = '';
    }, 300);
  }

  // Close form when clicking outside
  document.addEventListener('click', function(e) {
    const formContainer = document.getElementById('updateFormContainer');
    const overlay = document.getElementById('cardOverlay');
    
    // Check if form is open
    if (formContainer && formContainer.classList.contains('show')) {
      // Check if click is outside the form
      if (!formContainer.contains(e.target) && !e.target.classList.contains('update-btn')) {
        closeUpdateForm();
      }
    }
  });

  // Prevent form clicks from closing the form
  const updateFormContainer = document.getElementById('updateFormContainer');
  if (updateFormContainer) {
    updateFormContainer.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  // Handle form submission
  document.addEventListener('submit', function(e) {
    if (e.target.id === 'updateForm') {
      e.preventDefault();
      
      const formData = new FormData(e.target);
      
      fetch('update_item.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update the card content in the UI
          const itemId = formData.get('item_id');
          const itemType = formData.get('item_type');
          const card = document.querySelector(`.item-card[data-item-id="${itemId}"][data-status="${itemType}"]`);
          
          if (card) {
            card.querySelector('h3').textContent = formData.get('item_name');
            card.querySelector('p:nth-of-type(1)').textContent = 'Color: ' + formData.get('color');
            card.querySelector('p:nth-of-type(2)').textContent = 
              (itemType === 'lost' ? 'Lost in ' : 'Found in ') + formData.get('location');
            card.querySelector('p:nth-of-type(3)').textContent = formData.get('description');
          }
          
          // Close the form
          closeUpdateForm();
          
          // Show success message
          const successMsg = document.createElement('div');
          successMsg.className = 'success-message';
          successMsg.textContent = 'Item updated successfully!';
          document.body.appendChild(successMsg);
          
          setTimeout(() => {
            successMsg.remove();
          }, 5000);
        } else {
          // Show error message
          const errorMsg = document.createElement('div');
          errorMsg.className = 'error-message';
          errorMsg.textContent = data.message || 'Error updating item';
          document.body.appendChild(errorMsg);
          
          setTimeout(() => {
            errorMsg.remove();
          }, 5000);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        // Show error message
        const errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        errorMsg.textContent = 'Error updating item. Please try again.';
        document.body.appendChild(errorMsg);
        
        setTimeout(() => {
          errorMsg.remove();
        }, 5000);
      });
    }
  });

  // Handle item card clicks
  document.querySelectorAll('.item-card').forEach(card => {
    card.addEventListener('click', function(e) {
      // Don't show modal if clicking on menu button or its dropdown
      if (e.target.closest('.card-menu') || e.target.closest('.card-dropdown')) {
        return;
      }

      const status = this.dataset.status;
      const itemName = this.querySelector('h3').textContent;
      const color = this.querySelector('p:nth-of-type(1)').textContent.replace('Color:', '').trim();
      const location = this.querySelector('p:nth-of-type(2)').textContent.replace('Location:', '').trim();
      const description = this.querySelector('p:nth-of-type(3)').textContent.replace('Description:', '').trim();
      
      // Get additional data from data attributes
      const date = this.dataset.date || 'Not specified';
      const time = this.dataset.time || 'Not specified';
      const specificLocation = this.dataset.specificLocation || 'Not specified';
      const additionalInfo = this.dataset.additionalInfo || 'Not specified';

      const modalContent = `
        <div class="item-details-section">
          <div class="item-details-row">
            <span class="item-details-label">Status:</span>
            <span class="item-details-value">
              <span class="item-status status-${status}">${status.toUpperCase()}</span>
            </span>
          </div>
        </div>

        <div class="item-details-section">
          <div class="item-details-section-title">Item Information</div>
          <div class="item-details-row">
            <span class="item-details-label">Item Name:</span>
            <span class="item-details-value">${itemName}</span>
          </div>
          <div class="item-details-row">
            <span class="item-details-label">Color:</span>
            <span class="item-details-value">${color}</span>
          </div>
        </div>

        <div class="item-details-section">
          <div class="item-details-section-title">Time & Location Details</div>
          <div class="time-location-grid">
            <div class="item-details-row">
              <span class="item-details-label">Date ${status}:</span>
              <span class="item-details-value">${date}</span>
            </div>
            <div class="item-details-row">
              <span class="item-details-label">Time ${status}:</span>
              <span class="item-details-value">${time}</span>
            </div>
          </div>
          <div class="item-details-row">
            <span class="item-details-label">General Location:</span>
            <span class="item-details-value">${location}</span>
          </div>
          <div class="item-details-row">
            <span class="item-details-label">Specific Location:</span>
            <span class="item-details-value">${specificLocation}</span>
          </div>
        </div>

        <div class="item-details-section">
          <div class="item-details-section-title">Additional Information</div>
          <div class="item-details-row">
            <span class="item-details-label">Description:</span>
            <span class="item-details-value">${description}</span>
          </div>
          <div class="item-details-row">
            <span class="item-details-label">Additional Details:</span>
            <span class="item-details-value">${additionalInfo}</span>
          </div>
        </div>
      `;

      // Update modal content and show it
      const modal = document.getElementById('itemDetailsModal');
      modal.querySelector('.item-details-content').innerHTML = modalContent;
      modal.classList.add('show');
      document.getElementById('cardOverlay').classList.add('show');
    });
  });

  // Close modal when clicking the close button
  const closeModalBtn = document.querySelector('.close-modal-btn');
  if (closeModalBtn) {
    closeModalBtn.addEventListener('click', function() {
      document.getElementById('itemDetailsModal').classList.remove('show');
      document.getElementById('cardOverlay').classList.remove('show');
    });
  }

  // Close modal when clicking outside
  const cardOverlay = document.getElementById('cardOverlay');
  if (cardOverlay) {
    cardOverlay.addEventListener('click', function() {
      document.getElementById('itemDetailsModal').classList.remove('show');
      this.classList.remove('show');
    });
  }

  // Prevent modal from closing when clicking inside it
  const itemDetailsModal = document.getElementById('itemDetailsModal');
  if (itemDetailsModal) {
    itemDetailsModal.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
}); 