import './bootstrap';

// Global toggle bookmark function
window.toggleBookmark = function(type, id, element) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/bookmarks/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            bookmarkable_type: type,
            bookmarkable_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        // Update UI
        const icon = element.querySelector('svg');
        if (data.bookmarked) {
            icon.classList.remove('text-gray-400');
            icon.classList.add('text-yellow-400', 'fill-yellow-400');
            showToast('Bookmark saved successfully!');
        } else {
            icon.classList.remove('text-yellow-400', 'fill-yellow-400');
            icon.classList.add('text-gray-400');
            showToast('Bookmark removed.');
        }
    })
    .catch(error => {
        console.error('Error toggling bookmark:', error);
    });
};

// Global Toast System
window.showToast = function(message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'glass-card border border-blue-500/30 text-white px-4 py-3 rounded-lg shadow-xl flex items-center justify-between transition-all duration-300 transform translate-y-2 opacity-0';
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm font-medium">${message}</span>
        </div>
    `;

    container.appendChild(toast);

    // Fade in
    setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    }, 10);

    // Fade out and remove
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};
