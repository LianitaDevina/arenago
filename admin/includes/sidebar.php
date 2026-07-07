<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    
    body { 
        font-family: 'Inter', sans-serif; 
        margin: 0; 
        padding: 0; 
        display: flex; 
        background: #F4F7FE; 
        min-height: 100vh; 
    }
    
    .sidebar { 
        width: 260px; 
        background: #F4F7FE; 
        padding: 30px 20px; 
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #E2E8F0;
    }

    .sidebar-header {
        margin-bottom: 40px;
        padding-left: 10px;
    }
    
    .sidebar-header h2 { 
        color: #174AE4; 
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    
    .sidebar-header p {
        color: #8F9BBA;
        font-size: 11px;
        font-weight: 600;
        margin: 5px 0 0 0;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .sidebar-nav a { 
        display: flex; 
        align-items: center;
        gap: 12px;
        color: #718096; 
        text-decoration: none; 
        padding: 12px 18px; 
        border-radius: 30px; 
        font-weight: 500; 
        font-size: 15px; 
        transition: all 0.2s ease; 
    }
    
    .sidebar-nav a i {
        font-size: 20px;
    }

    .sidebar-nav a:hover { 
        background: #E2E8F0; 
        color: #2D3748;
    }
    
    .sidebar-nav a.active { 
        background: #2563EB; /* Bright Blue */
        color: white; 
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }
    
    .sidebar-bottom {
        margin-top: auto;
    }
    
    .btn-new-booking {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #093CC7;
        color: white;
        text-decoration: none;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        transition: background 0.2s;
        margin-bottom: 20px;
    }
    
    .btn-new-booking:hover {
        background: #062D9A;
    }

    .sidebar-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-top: 15px;
        border-top: 1px solid #E2E8F0;
    }
    
    .profile-img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #CBD5E0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
    
    .profile-info {
        flex: 1;
    }
    
    .profile-name {
        font-size: 13px;
        font-weight: 700;
        color: #1A202C;
        margin: 0;
    }
    
    .profile-role {
        font-size: 10px;
        font-weight: 600;
        color: #8F9BBA;
        margin: 2px 0 0 0;
        text-transform: uppercase;
    }
    
    .logout-btn {
        color: #A0AEC0;
        text-decoration: none;
        font-size: 20px;
        transition: color 0.2s;
    }
    
    .logout-btn:hover {
        color: #E53E3E;
    }

    .content { 
        flex: 1; 
        padding: 30px; 
        box-sizing: border-box; 
        overflow-y: auto; 
    }

    /* Unified Admin Styles (based on Court Management) */
    .admin-container { 
        background:white; padding:30px; border-radius:12px; border:1px solid #EAEAEA; 
    }
    .admin-container-narrow {
        max-width: 800px;
    }
    .admin-title {
        font-family:'Poppins', sans-serif; margin-top:0px; color:#333; margin-bottom: 20px;
    }
    .admin-label {
        font-weight:600; font-size:13px; display:block; margin-bottom:5px; color:#4A5568;
    }
    .admin-input, .admin-select, .admin-textarea {
        width:100%; padding:10px; margin-bottom:15px; border:1px solid #CCC; border-radius:6px; box-sizing:border-box; font-family: 'Inter', sans-serif; font-size:14px;
    }
    .admin-btn {
        background:#004AC6; color:white; border:none; padding:12px 20px; border-radius:6px; font-weight:600; cursor:pointer; font-family: 'Inter', sans-serif; font-size:14px; display: inline-block; text-align: center; text-decoration: none;
    }
    .admin-btn:hover { background: #003794; }
    .admin-btn-block { width: 100%; }

    .admin-table {
        width:100%; border-collapse:collapse; margin-top:10px; font-size: 14px;
    }
    .admin-table th, .admin-table td {
        padding:12px 15px; text-align:left; border-bottom:1px solid #EAEAEA;
    }
    .admin-table th {
        background:#F4F8FF; color:#004AC6; font-weight: 600;
    }
    .admin-badge {
        padding:4px 8px; border-radius:4px; font-size:12px; font-weight:700;
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>ArenaGO</h2>
        <p>Admin Console</p>
    </div>
    
    <div class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
            <i class='bx bx-grid-alt'></i> Dashboard
        </a>
        <a href="courts.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'courts.php') ? 'active' : ''; ?>">
            <i class='bx bx-tennis-ball'></i> Court Management
        </a>
        <a href="venue_info.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'venue_info.php') ? 'active' : ''; ?>">
            <i class='bx bx-building-house'></i> Venue Info
        </a>
        <a href="bookings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'bookings.php') ? 'active' : ''; ?>">
            <i class='bx bx-calendar-check'></i> Bookings
        </a>
        <a href="report.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'report.php') ? 'active' : ''; ?>">
            <i class='bx bx-line-chart'></i> Reports
        </a>
    </div>

    <div class="sidebar-bottom">
        <a href="dashboard.php" class="btn-new-booking">
            <i class='bx bx-plus'></i> New Booking
        </a>
        
        <div class="sidebar-profile">
            <div class="profile-img">
                <i class='bx bx-user'></i>
            </div>
            <div class="profile-info">
                <p class="profile-name"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Mitra Lapangan'; ?></p>
                <p class="profile-role"><?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin' ? 'SUPERADMIN' : 'ARENAGO MANAGEMENT'; ?></p>
            </div>
            <a href="../auth/logout.php" class="logout-btn" title="Logout">
                <i class='bx bx-log-out'></i>
            </a>
        </div>
    </div>
</div>
