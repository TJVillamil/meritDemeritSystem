<?php
session_start();
include 'db_connect.php';
require_once 'session_checker.php';
check_session(false);

if (!isset($_SESSION['user_id'])) {
    die("Error: User is not logged in. Please log in to access the demerit records.");
}

$user_id = $_SESSION['user_id'];

$user_query = "SELECT first_name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

if (!$user_data) {
    die("Error: User not found. Please contact support.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demerit Records | Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); font-family: 'Inter', sans-serif; color: #2c3e50; line-height: 1.6;">
    <nav class="navbar navbar-expand-lg shadow-sm fixed-top" style="background: linear-gradient(135deg, #800000, #a63f3f); padding: 1rem 0; box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
        <div class="container">
            <a class="navbar-brand" href="dashboard_site.php" style="font-size: 1.4rem; font-weight: 700; color: white; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fas fa-award me-2"></i>Student Dashboard
            </a>
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <span class="text-white" style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars($user_data['first_name']) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill" style="border-width: 2px; font-weight: 500;">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 5rem; padding-top: 1.5rem;">
        <div style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 2rem;">
            <div style="background: linear-gradient(135deg, #800000, #a63f3f); color: white; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-minus-circle"></i>
                    Demerit Records
                </h4>
                <button onclick="fetchDemeritRecords(true)" 
                        style="background: rgba(255,255,255,0.15); 
                               color: white; 
                               border: none; 
                               padding: 0.75rem 1.5rem; 
                               border-radius: 50px; 
                               font-weight: 500; 
                               display: flex; 
                               align-items: center; 
                               gap: 0.75rem; 
                               cursor: pointer;
                               transition: all 0.3s ease;">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
            <div style="position: relative;">
                <div class="table-responsive" style="min-height: 300px;">
                    <table class="table" id="demerit-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="background: #800000; color: white; font-weight: 600; padding: 1rem 1.5rem; border: none;">ID</th>
                                <th style="background: #800000; color: white; font-weight: 600; padding: 1rem 1.5rem; border: none;">Amount</th>
                                <th style="background: #800000; color: white; font-weight: 600; padding: 1rem 1.5rem; border: none;">Reason</th>
                                <th style="background: #800000; color: white; font-weight: 600; padding: 1rem 1.5rem; border: none;">Date Given</th>
                                <th style="background: #800000; color: white; font-weight: 600; padding: 1rem 1.5rem; border: none;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div id="user-info" style="font-weight: 500; color: #2c3e50;">0 records found</div>
                </div>
                <div class="col-md-4 text-center">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                        <label style="margin: 0; color: #2c3e50;">Page</label>
                        <input type="number" id="page-input" value="1" min="1" disabled 
                               style="width: 80px; 
                                      text-align: center; 
                                      border: 2px solid #e9ecef; 
                                      border-radius: 8px; 
                                      padding: 0.5rem; 
                                      font-weight: 500;" />
                        <span id="total-pages" style="color: #2c3e50;">/1</span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div>
                        <label style="margin: 0; color: #2c3e50;">Entries</label>
                        <select id="items-per-page" class="form-select form-select-sm d-inline w-auto ms-2" onchange="updateLimit(this.value)">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <script>
    let currentPage = 1;
    let itemsPerPage = 20;
    let isLoading = false;
    let autoFetchInterval;

    // Initialize auto-fetching when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        fetchDemeritRecords(true); // Initial fetch
        // Set up automatic fetching every 5 seconds
        autoFetchInterval = setInterval(() => {
            fetchDemeritRecords(false);
        }, 5000); // 5 seconds interval
    });

    function showLoading() {
        if (!document.querySelector('.loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.style.cssText = `
                position: absolute;
                inset: 0;
                background: rgba(255,255,255,0.95);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
            `;
            const spinner = document.createElement('div');
            spinner.style.cssText = `
                width: 50px;
                height: 50px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #800000;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            `;
            overlay.appendChild(spinner);
            document.querySelector('.table-responsive').appendChild(overlay);
        }
    }

    function hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) overlay.remove();
    }

    function showNoRecords() {
        const tbody = document.querySelector('#demerit-table tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5">
                    <div style="padding: 4rem 2rem; text-align: center;">
                        <i class="fas fa-info-circle" style="font-size: 4rem; color: #6c757d; margin-bottom: 1.5rem; display: block;"></i>
                        <h5 style="color: #2c3e50; font-weight: 600; margin-bottom: 0.75rem;">No Records Found</h5>
                        <p style="color: #6c757d; margin: 0;">Records will appear here automatically when available.</p>
                    </div>
                </td>
            </tr>`;

        document.getElementById('user-info').textContent = '0 records found';
        document.getElementById('total-pages').textContent = '/1';
        document.getElementById('page-input').disabled = true;
        document.getElementById('items-per-page').disabled = true;
    }

    async function fetchDemeritRecords(showLoadingIndicator = false) {
        if (isLoading) return;
        isLoading = true;

        if (showLoadingIndicator) showLoading();

        try {
            const response = await fetch(`fetch_demerit.php?limit=${itemsPerPage}&page=${currentPage}`);
            const data = await response.json();
            
            if (!data || !Array.isArray(data.records) || data.records.length === 0) {
                showNoRecords();
                return;
            }

            updateDemeritTable(data);
        } catch (error) {
            console.error("Error fetching demerit records:", error);
            showNoRecords();
        } finally {
            isLoading = false;
            if (showLoadingIndicator) hideLoading();
        }
    }

    function getStatusBadge(status) {
            const badgeStyles = {
                'Pending': 'background: linear-gradient(135deg, #ffd700, #ffaa00); color: #000;',
                'Approved': 'background: linear-gradient(135deg, #28a745, #20c997); color: white;',
                'Rejected': 'background: linear-gradient(135deg, #dc3545, #c82333); color: white;'
            };

            return `<span style="${badgeStyles[status] || badgeStyles['Pending']}; 
                                padding: 0.6rem 1.2rem; 
                                border-radius: 50px; 
                                font-weight: 500; 
                                font-size: 0.875rem; 
                                display: inline-block; 
                                text-align: center; 
                                min-width: 120px; 
                                box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        ${status}
                    </span>`;
    }

    async function fetchDemeritRecords(showLoadingIndicator = false) {
            if (isLoading) return;
            isLoading = true;

            if (showLoadingIndicator) showLoading();

            try {
                const response = await fetch(`fetch_demerit.php?limit=${itemsPerPage}&page=${currentPage}`);
                const data = await response.json();
                
                if (!data || !Array.isArray(data.records)) {
                    showNoRecords();
                    return;
                }

                updateDemeritTable(data);
            } catch (error) {
                console.error("Error fetching demerit records:", error);
                showNoRecords();
            } finally {
                isLoading = false;
                if (showLoadingIndicator) hideLoading();
            }
        }

        function updateDemeritTable(data) {
            const tbody = document.querySelector('#demerit-table tbody');
            tbody.innerHTML = '';

            if (data.records.length > 0) {
                data.records.forEach((record, index) => {
                    const row = document.createElement('tr');
                    row.style.cssText = 'opacity: 0; transition: opacity 0.3s ease;';
                    row.innerHTML = `
                        <td style="padding: 1.25rem 1.5rem; vertical-align: middle;">${record.id}</td>
                        <td style="padding: 1.25rem 1.5rem; vertical-align: middle; color: #dc3545;">-${record.amount}</td>
                        <td style="padding: 1.25rem 1.5rem; vertical-align: middle;">${record.reason}</td>
                        <td style="padding: 1.25rem 1.5rem; vertical-align: middle;">${formatDate(record.date_given)}</td>
                        <td style="padding: 1.25rem 1.5rem; vertical-align: middle;">${getStatusBadge(record.status)}</td>
                    `;
                    tbody.appendChild(row);
                    
                    setTimeout(() => {
                        row.style.opacity = '1';
                    }, index * 100);
                });

                document.getElementById('user-info').textContent = 
                    `Showing ${(currentPage - 1) * itemsPerPage + 1}-${Math.min(currentPage * itemsPerPage, data.total_records)} of ${data.total_records} records`;
                document.getElementById('total-pages').textContent = `/${data.total_pages}`;
                document.getElementById('page-input').max = data.total_pages;

                document.getElementById('page-input').disabled = false;
                document.getElementById('items-per-page').disabled = false;
            } else {
                showNoRecords();
            }
        }

        document.getElementById('page-input').addEventListener('change', (event) => {
            const page = parseInt(event.target.value);
            if (page >= 1 && page <= parseInt(event.target.max)) {
                currentPage = page;
                fetchDemeritRecords(true);
            }
        });

        document.getElementById('items-per-page').addEventListener('change', (event) => {
            itemsPerPage = parseInt(event.target.value);
            currentPage = 1;
            fetchDemeritRecords(true);
        });

    // Cleanup
    window.addEventListener('unload', () => {
        if (autoFetchInterval) clearInterval(autoFetchInterval);
    });

    // Fetch stats immediately and then every 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
            fetchDashboardStats();
            setInterval(fetchDemeritRecords, 5000); // 5 seconds interval
        });
</script>
<style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</body>
</html>