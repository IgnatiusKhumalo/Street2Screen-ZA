<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Street2Screen ZA - Coming Soon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0B1F3A 0%, #1a3a5c 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            text-align: center;
            max-width: 900px;
            animation: fadeIn 1s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo-container {
            margin-bottom: 2rem;
        }
        
        .logo-container img {
            max-width: 400px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .tagline {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: #FFC107;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }
        
        .status {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #FFC107;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
        }
        
        .status h2 {
            color: #FFC107;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .feature {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 10px;
            border: 1px solid rgba(255, 193, 7, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 193, 7, 0.2);
            border-color: #FFC107;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .feature h3 {
            color: #FFC107;
            margin-bottom: 0.5rem;
        }
        
        .info {
            background: rgba(76, 175, 80, 0.2);
            border-left: 4px solid #4CAF50;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 5px;
            text-align: left;
        }
        
        .info ul {
            margin-left: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .info li {
            margin: 0.5rem 0;
            font-size: 1.05rem;
        }
        
        .tech-stack {
            margin: 2rem 0;
        }
        
        .tech-stack h3 {
            color: #FFC107;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .tech-badge {
            display: inline-block;
            background: rgba(255, 193, 7, 0.2);
            padding: 0.5rem 1rem;
            margin: 0.3rem;
            border-radius: 25px;
            font-size: 0.95rem;
            border: 1px solid #FFC107;
            transition: all 0.3s ease;
        }
        
        .tech-badge:hover {
            background: rgba(255, 193, 7, 0.4);
            transform: scale(1.05);
        }
        
        .footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 193, 7, 0.3);
            color: #999;
            font-size: 0.9rem;
        }
        
        .footer p {
            margin: 0.5rem 0;
        }
        
        .server-info {
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            font-size: 0.85rem;
            font-family: 'Courier New', monospace;
        }
        
        @media (max-width: 768px) {
            .logo-container img {
                max-width: 280px;
            }
            
            .tagline {
                font-size: 1.3rem;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
            
            .status h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo-container">
            <img src="public/images/Street2ScreenZA_Logo.png" alt="Street2Screen ZA Logo">
        </div>
        
        <!-- Tagline -->
        <div class="tagline">Bringing Kasi To Your Screen</div>
        
        <!-- Status -->
        <div class="status">
            <h2>🛠️ Website Under Construction</h2>
            <p style="font-size: 1.1rem; margin: 1rem 0;">
                We're building South Africa's premier C2C marketplace for street vendors and township entrepreneurs.
            </p>
        </div>
        
        <!-- Features -->
        <div class="features">
            <div class="feature">
                <div class="feature-icon">🛒</div>
                <h3>Shop Local</h3>
                <p>Browse products from SA street vendors and township entrepreneurs</p>
            </div>
            <div class="feature">
                <div class="feature-icon">💼</div>
                <h3>Sell Online</h3>
                <p>List your products and reach more customers across South Africa</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🌍</div>
                <h3>Multi-Language</h3>
                <p>Full support for all 11 South African official languages</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🔒</div>
                <h3>Secure Platform</h3>
                <p>Safe transactions with buyer and seller protection</p>
            </div>
        </div>
        
        <!-- Development Status -->
        <div class="info">
            <strong style="font-size: 1.1rem;">✅ Development Progress:</strong>
            <ul>
                <li>✅ Hosting infrastructure deployed (InfinityFree)</li>
                <li>✅ Database configured (MySQL 8.0)</li>
                <li>✅ Email notification system ready (Brevo SMTP)</li>
                <li>✅ Professional branding and logo integrated</li>
                <li>🔄 User authentication system in development</li>
                <li>🔄 Vendor dashboard development in progress</li>
                <li>🔄 Product catalog and search features coming soon</li>
                <li>🔄 Payment integration planning phase</li>
            </ul>
        </div>
        
        <!-- Technology Stack -->
        <div class="tech-stack">
            <h3>🔧 Technology Stack</h3>
            <span class="tech-badge">PHP 8.3</span>
            <span class="tech-badge">MySQL</span>
            <span class="tech-badge">Bootstrap 5</span>
            <span class="tech-badge">JavaScript ES6+</span>
            <span class="tech-badge">Brevo SMTP</span>
            <span class="tech-badge">InfinityFree Hosting</span>
            <span class="tech-badge">GitHub</span>
            <span class="tech-badge">UTF-8 Multi-language</span>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="font-size: 1rem; color: #FFC107;"><strong>&copy; 2026 Street2Screen ZA</strong></p>
            <p>Empowering Township Entrepreneurs & Street Vendors</p>
            <p style="margin-top: 1rem;">
                📍 44 Alsatian Road, Glen Austin, Midrand, 1685<br>
                🇿🇦 South Africa
            </p>
            <p style="margin-top: 1.5rem;">
                <strong>Developer:</strong> Ignatius Mayibongwe Khumalo<br>
                <strong>Academic Project:</strong> ITECA3-12 Initial Project<br>
                <strong>Institution:</strong> Eduvos | Year: 2026
            </p>
            
            <!-- Server Info -->
            <div class="server-info">
                <strong>🖥️ Server Information:</strong><br>
                Domain: <?php echo $_SERVER['SERVER_NAME']; ?><br>
                PHP Version: <?php echo phpversion(); ?><br>
                Server IP: <?php echo $_SERVER['SERVER_ADDR']; ?><br>
                Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
                Status: ✅ Online & Operational
            </div>
        </div>
    </div>
</body>
</html>