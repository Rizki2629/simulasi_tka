<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    function toggleSubmenu(event) {
        const menuItem = event.currentTarget;
        const submenu = menuItem.nextElementSibling;
        
        menuItem.classList.toggle('expanded');
        submenu.classList.toggle('expanded');
    }
</script>
