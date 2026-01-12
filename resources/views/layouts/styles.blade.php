<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Roboto', sans-serif;
        background: #F5F5F7;
        overflow-x: hidden;
    }

    .dashboard-container {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar Styles */
    .sidebar {
        width: 260px;
        background: #702637;
        color: white;
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-menu::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-menu::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar-menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .sidebar-header {
        padding: 24px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logo-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-icon .material-symbols-outlined {
        font-size: 24px;
        color: white;
        font-variation-settings: 'FILL' 1, 'wght' 500;
    }

    .logo-text {
        font-size: 16px;
        font-weight: 600;
        letter-spacing: -0.5px;
        line-height: 1.3;
    }

    .sidebar-menu {
        padding: 20px 0;
        flex: 1;
        overflow-y: auto;
    }

    .menu-section {
        margin-bottom: 24px;
    }

    .menu-section-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.5);
        padding: 0 20px;
        margin-bottom: 12px;
    }

    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
    }

    .menu-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .menu-item.active {
        background: rgba(255, 255, 255, 0.15);
        color: white;
    }

    .menu-item .material-symbols-outlined {
        font-size: 22px;
        margin-right: 12px;
    }

    .menu-item-text {
        font-size: 14px;
        font-weight: 500;
    }

    .menu-item-arrow {
        margin-left: auto;
        transition: transform 0.2s ease;
    }

    .menu-item.expanded .menu-item-arrow {
        transform: rotate(180deg);
    }

    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .submenu.expanded {
        max-height: 300px;
    }

    .submenu-item {
        display: block;
        padding: 10px 20px 10px 54px;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    /* Ensure submenu styling is consistent across pages that still carry old CSS overrides */
    .submenu-item::before {
        content: none !important;
        display: none !important;
    }

    .submenu-item:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
    }

    .submenu-item.active {
        background: rgba(255, 255, 255, 0.15) !important;
        color: white !important;
        font-weight: 500 !important;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: auto;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .user-role {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
    }

    /* Main Content Styles */
    .main-content {
        flex: 1;
        margin-left: 260px;
        transition: margin-left 0.3s ease;
    }

    .header {
        background: white;
        padding: 20px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .menu-toggle {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        transition: background 0.2s ease;
    }

    .menu-toggle:hover {
        background: #F3F4F6;
    }

    .search-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #F5F5F7;
        padding: 10px 16px;
        border-radius: 12px;
        min-width: 300px;
    }

    .search-bar .material-symbols-outlined {
        font-size: 20px;
        color: #999;
    }

    .search-bar input {
        border: none;
        background: none;
        outline: none;
        font-size: 14px;
        width: 100%;
        color: #333;
    }

    .search-bar input::placeholder {
        color: #999;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F5F5F7;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .header-icon:hover {
        background: #E8E8EA;
    }

    .header-icon .material-symbols-outlined {
        font-size: 20px;
        color: #666;
    }

    .header-icon .badge {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #702637;
        border-radius: 50%;
        border: 2px solid white;
    }

    .user-avatar-header {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #FFB6B6, #FFA0A0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        color: #702637;
        cursor: pointer;
    }

    .content {
        padding: 32px;
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .page-subtitle {
        font-size: 14px;
        color: #6B7280;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }

        .menu-toggle {
            display: block;
        }

        .content {
            padding: 20px;
        }
    }
</style>
