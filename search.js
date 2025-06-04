document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusSelect = document.querySelector('.status');
    const locationSelect = document.querySelector('.location');
    const categorySelect = document.querySelector('.items-dropdown2');
    const itemCards = document.querySelectorAll('.item-card');

    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = statusSelect.value;
        const locationFilter = locationSelect.value;
        const categoryFilter = categorySelect.value;

        itemCards.forEach(card => {
            const itemName = card.querySelector('h3').textContent.toLowerCase();
            const itemDescription = card.querySelector('p:last-child').textContent.toLowerCase();
            const itemStatus = card.dataset.status;
            const itemLocation = card.dataset.location.toLowerCase();
            const itemType = card.dataset.type.toLowerCase();

            const matchesSearch = itemName.includes(searchTerm) || 
                                itemDescription.includes(searchTerm);
            const matchesStatus = statusFilter === 'all' || itemStatus === statusFilter;
            const matchesLocation = locationFilter === 'all' || itemLocation === locationFilter;
            const matchesCategory = categoryFilter === 'all' || itemType === categoryFilter;

            if (matchesSearch && matchesStatus && matchesLocation && matchesCategory) {
                card.style.display = 'block';
                
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            } else {
                card.style.display = 'none';
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
            }
        });
    }

    searchInput.addEventListener('input', filterItems);
    statusSelect.addEventListener('change', filterItems);
    locationSelect.addEventListener('change', filterItems);
    categorySelect.addEventListener('change', filterItems);

    itemCards.forEach(card => {
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    });
}); 