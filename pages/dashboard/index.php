<script>
    //Redirect to the first available page
    window.addEventListener('load', async () => {
        const allPages = [ 'cashbook', 'clients', 'orders', 'inventory', 'reminders', 'users']
        for (let i = 0; i < allPages.length; i++) {
            const thisPage = allPages[i];
            const permissionTest = hasPermission(perms, thisPage);
            if (permissionTest > 0) return window.location.href = '?page=' + thisPage;
        };

        return window.location.href = '?page=profile';
    });
</script>