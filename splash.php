<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Street2Screen ZA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }

        /* Animated Gradient Background */
        .splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, 
                #0B1F3A 0%, 
                #1a3a6b 25%,
                #2a5a9b 50%,
                #1a3a6b 75%,
                #0B1F3A 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating particles background */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: rgba(255, 193, 7, 0.2);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 100px; height: 100px; left: 35%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 50px; height: 50px; left: 50%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 70px; height: 70px; left: 65%; animation-delay: 8s; }
        .particle:nth-child(6) { width: 90px; height: 90px; left: 80%; animation-delay: 10s; }
        .particle:nth-child(7) { width: 55px; height: 55px; left: 90%; animation-delay: 12s; }

        @keyframes float {
            0%, 100% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-100vh) scale(1);
            }
        }

        /* Content Container */
        .splash-content {
            position: relative;
            z-index: 10;
            text-align: center;
            animation: fadeInUp 1.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo Container */
        .logo-container {
            margin-bottom: 30px;
            animation: zoomIn 1.2s ease-out;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .logo-container img {
            max-width: 500px;
            width: 90vw;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.5));
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Slogan */
        .slogan {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, #FFC107 0%, #FFD54F 50%, #FFC107 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 20px rgba(255, 193, 7, 0.5);
            margin-bottom: 20px;
            animation: slideInLeft 1.8s ease-out;
            letter-spacing: 2px;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Tagline */
        .tagline {
            font-size: 1.5rem;
            color: #ffffff;
            font-weight: 400;
            margin-bottom: 40px;
            animation: slideInRight 2s ease-out;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Loading Animation */
        .loading-container {
            margin-top: 30px;
            animation: fadeIn 2.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .loading-bar {
            width: 300px;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .loading-progress {
            height: 100%;
            background: linear-gradient(90deg, #FFC107 0%, #FFD54F 50%, #FFC107 100%);
            background-size: 200% 100%;
            animation: loadProgress 3s ease-out forwards, shimmer 2s infinite;
            border-radius: 10px;
        }

        @keyframes loadProgress {
            from { width: 0%; }
            to { width: 100%; }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .loading-text {
            margin-top: 15px;
            color: #FFD54F;
            font-size: 1rem;
            font-weight: 600;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* South African Flag Colors Accent */
        .flag-accent {
            position: absolute;
            bottom: 50px;
            display: flex;
            gap: 15px;
            animation: fadeIn 3s ease-out;
        }

        .flag-dot {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            animation: bounce 2s infinite;
        }

        .flag-dot:nth-child(1) { background: #007749; animation-delay: 0s; }
        .flag-dot:nth-child(2) { background: #FFC107; animation-delay: 0.2s; }
        .flag-dot:nth-child(3) { background: #000000; animation-delay: 0.4s; }
        .flag-dot:nth-child(4) { background: #DE3831; animation-delay: 0.6s; }
        .flag-dot:nth-child(5) { background: #002395; animation-delay: 0.8s; }
        .flag-dot:nth-child(6) { background: #FFFFFF; animation-delay: 1s; }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .slogan {
                font-size: 2rem;
            }
            .tagline {
                font-size: 1.1rem;
            }
            .logo-container img {
                max-width: 350px;
            }
            .loading-bar {
                width: 250px;
            }
        }

        @media (max-width: 480px) {
            .slogan {
                font-size: 1.5rem;
            }
            .tagline {
                font-size: 0.9rem;
            }
            .logo-container img {
                max-width: 280px;
            }
        }
    </style>
</head>
<body>
    <div class="splash-screen" id="splash">
        <!-- Floating Particles -->
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <!-- Main Content -->
        <div class="splash-content">
            <!-- Logo -->
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Street2Screen ZA Logo">
            </div>

            <!-- Slogan -->
            <h1 class="slogan">BRINGING KASI TO YOUR SCREEN</h1>

            <!-- Tagline -->
            <p class="tagline">South Africa's Premier Township Marketplace</p>

            <!-- Loading Animation -->
            <div class="loading-container">
                <div class="loading-bar">
                    <div class="loading-progress"></div>
                </div>
                <p class="loading-text">Loading your marketplace...</p>
            </div>
        </div>

        <!-- SA Flag Accent -->
        <div class="flag-accent">
            <div class="flag-dot"></div>
            <div class="flag-dot"></div>
            <div class="flag-dot"></div>
            <div class="flag-dot"></div>
            <div class="flag-dot"></div>
            <div class="flag-dot"></div>
        </div>
    </div>

    <script>
        // Auto-redirect after 3.5 seconds
        setTimeout(function() {
            document.getElementById('splash').style.transition = 'opacity 0.8s ease-out';
            document.getElementById('splash').style.opacity = '0';
            
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 800);
        }, 3500);
    </script>
</body>
</html>
