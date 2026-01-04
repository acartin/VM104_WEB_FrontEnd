// Global Lucide initialization (runs once per page load)
document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) {
        lucide.createIcons();
    }
});

// Re-scan on Livewire updates
document.addEventListener('livewire:navigated', function () {
    if (window.lucide) {
        lucide.createIcons();
    }
});

// Re-scan after Livewire updates table
Livewire.hook('commit', ({ component, succeed }) => {
    succeed(() => {
        if (window.lucide) {
            setTimeout(() => lucide.createIcons(), 100);
        }
    });
});
