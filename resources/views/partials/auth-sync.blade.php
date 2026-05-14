{{-- resources/views/partials/auth-sync.blade.php --}}
{{-- Include this in every page's <head> AFTER the CSRF meta tag --}}
{{-- It silently calls /api/me on load and syncs localStorage with the server session --}}
<script>
(async function syncAuth() {
    try {
        const res  = await fetch('/api/me', { credentials: 'same-origin' });
        const data = await res.json();

        if (data.success) {
            // Server session is alive — keep localStorage in sync
            localStorage.setItem('userLoggedIn', 'true');
            localStorage.setItem('userId',   data.user.id);
            localStorage.setItem('userName', data.user.name);
            localStorage.setItem('userTipo', data.user.tipo);
            localStorage.setItem('vendorId', data.user.vendor_id ?? '');
        } else {
            // Session expired or not logged in — clear stale localStorage
            localStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userId');
            localStorage.removeItem('userName');
            localStorage.removeItem('userTipo');
            localStorage.removeItem('vendorId');
        }
    } catch {
        // Network error — leave localStorage as-is, app can still work offline
    }
})();
</script>
