<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Simulasi TKA</title>
    @include('layouts.styles')
    <style>
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.primary {
            background: rgba(112, 38, 55, 0.1);
        }

        .stat-icon.primary .material-symbols-outlined {
            color: #702637;
            font-size: 24px;
            font-variation-settings: 'FILL' 1;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: #999;
            font-weight: 500;
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

            .search-bar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="main-content">
            @include('layouts.header', ['pageTitle' => 'Dashboard', 'showSearch' => true])

            <!-- Content -->
            <div class="content">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Selamat datang di dashboard Simulasi TKA</p>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Total Soal</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">quiz</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Soal Terjawab</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">task_alt</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0%</div>
                                <div class="stat-label">Progress</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">trending_up</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Nilai Rata-rata</div>
                            </div>
                            <div class="stat-icon primary">
                                <span class="material-symbols-outlined">grade</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @include('layouts.scripts')
</body>
</html>
