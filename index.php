<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merit System - Landing Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #600000;
            --maroon-light: #aa0000;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #f8f8f8 100%);
        }
        
        .navbar {
            background-color: var(--maroon) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: scale(1.05);
        }
        
        .btn-primary {
            background-color: white !important;
            color: var(--maroon) !important;
            border: 2px solid white !important;
        }
        
        .btn-outline-primary {
            color: white !important;
            border: 2px solid white !important;
            background: transparent !important;
        }
        
        .btn-outline-primary:hover {
            background: white !important;
            color: var(--maroon) !important;
        }
        
        .btn {
            border-radius: 25px;
            padding: 8px 25px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .hero-section {
            padding: 160px 0 100px 0;
            background: radial-gradient(circle at top right, rgba(128,0,0,0.1) 0%, transparent 60%);
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--maroon);
            animation: fadeIn 1s ease-out;
        }
        
        .hero-text {
            color: #4a4a4a;
            font-size: 1.25rem;
            margin-bottom: 2rem;
            animation: slideUp 1s ease-out;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-bottom: 3px solid var(--maroon);
        }
        
        .feature-icon {
            font-size: 2rem;
            color: var(--maroon);
            margin-bottom: 1rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-maroon {
            background-color: var(--maroon);
            color: white;
            border: 2px solid var(--maroon);
        }

        .btn-maroon:hover {
            background-color: var(--maroon-dark);
            border-color: var(--maroon-dark);
            color: white;
        }

        .btn-outline-maroon {
            color: var(--maroon);
            border: 2px solid var(--maroon);
            background: transparent;
        }

        .btn-outline-maroon:hover {
            background: var(--maroon);
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-award me-2"></i>Merit System
            </a>
            <div>
                <a href="logreg_site.php?show=login" class="btn btn-primary me-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start">
                    <h1 class="hero-title">Track Performance,<br>Promote Cooperation</h1>
                    <p class="hero-text">A modern platform for managing merits and demerits with ease and efficiency.</p>
                    <a href="logreg_site.php?show=register" class="btn btn-maroon btn-lg me-3">
                        Get Started
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="features">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <h3 class="h4 mb-3">Track Progress</h3>
                        <p class="text-muted">Monitor performance and growth with intuitive tracking tools.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-medal feature-icon"></i>
                        <h3 class="h4 mb-3">Award Merits</h3>
                        <p class="text-muted">Recognize and reward positive achievements instantly.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-clipboard-list feature-icon"></i>
                        <h3 class="h4 mb-3">Generate Reports</h3>
                        <p class="text-muted">Get detailed insights with comprehensive reporting tools.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>