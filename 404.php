<?php
declare(strict_types=1);
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Waqt Finance</title>
    
    <!-- Meta tags for SEO & Security -->
    <meta name="description" content="The page you are looking for does not exist on Waqt Finance. Use our helper links or contact support to find what you need.">
    <link rel="canonical" href="https://waqtfinance.com/404">
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: radial-gradient(120% 120% at 50% 10%, #f0f7ff 30%, #e0f2fe 70%, #ffffff 100%);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px 20px;
        }

        .wf-404-wrapper {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 32px;
            padding: 56px;
            box-shadow: 0 30px 60px -15px rgba(15, 23, 42, 0.05);
        }

        /* Main Content Container */
        .wf-404-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            margin-bottom: 50px;
        }

        .wf-404-left {
            flex: 1.1;
            max-width: 580px;
        }

        .wf-404-right {
            flex: 0.9;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .wf-404-right img {
            max-width: 100%;
            height: auto;
            border-radius: 24px;
            filter: drop-shadow(0 20px 40px rgba(11, 92, 173, 0.12));
            animation: float 6s ease-in-out infinite;
        }

        /* Tagline */
        .wf-404-tagline {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .wf-tag-line {
            width: 45px;
            height: 2px;
            background: #0b5cad;
            border-radius: 2px;
        }

        .wf-tag-line.line-red {
            background: #dc2626;
        }

        .wf-tag-text {
            font-size: 13px;
            font-weight: 800;
            color: #0b5cad;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Title & Headings */
        .wf-404-title {
            font-size: 140px;
            font-weight: 800;
            line-height: 0.9;
            margin: 0 0 20px 0;
            letter-spacing: -6px;
            background: linear-gradient(135deg, #0b5cad 0%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .wf-404-heading {
            font-size: 40px;
            font-weight: 800;
            color: #0f2742;
            line-height: 1.25;
            margin-bottom: 18px;
        }

        .wf-404-desc {
            font-size: 16px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        /* Action Buttons */
        .wf-404-actions {
            display: flex;
            gap: 16px;
        }

        .btn-home, .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 30px;
            font-size: 15px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-home {
            background: #0b5cad;
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 14px rgba(11, 92, 173, 0.2);
        }

        .btn-home:hover {
            background: #094a8c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(11, 92, 173, 0.3);
        }

        .btn-back {
            background: #ffffff;
            color: #0b5cad;
            border: 2px solid #0b5cad;
        }

        .btn-back:hover {
            background: #eff6ff;
            transform: translateY(-2px);
        }

        /* Section: You may be looking for */
        .wf-looking-section {
            margin-bottom: 50px;
        }

        .wf-section-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 35px;
        }

        .wf-divider-line {
            flex: 1;
            height: 1px;
            background: #cbd5e1;
            opacity: 0.6;
        }

        .wf-divider-text {
            font-size: 15px;
            font-weight: 800;
            color: #0f2742;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .wf-cards-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 18px;
        }

        .wf-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.01);
        }

        .wf-card:hover {
            border-color: #0b5cad;
            transform: translateY(-4px);
            box-shadow: 0 12px 20px rgba(11, 92, 173, 0.06);
        }

        .wf-card-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .wf-card:hover .wf-card-icon {
            transform: scale(1.05);
        }

        .icon-blue { background-color: #eff6ff; color: #2563eb; }
        .icon-red { background-color: #fef2f2; color: #ef4444; }
        .icon-green { background-color: #f0fdf4; color: #22c55e; }
        .icon-purple { background-color: #faf5ff; color: #a855f7; }
        .icon-orange { background-color: #fff7ed; color: #f97316; }
        .icon-teal { background-color: #f0fdfa; color: #0d9488; }

        .wf-card-text {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .wf-card-text i {
            font-size: 10px;
            transition: transform 0.2s ease;
        }

        .wf-card:hover .wf-card-text i {
            transform: translateX(4px);
            color: #0b5cad;
        }

        /* Section: Need Help Bar */
        .wf-help-bar {
            background: #eff6ff;
            border: 1px solid rgba(11, 92, 173, 0.08);
            border-radius: 24px;
            padding: 24px 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            margin-bottom: 45px;
            box-shadow: 0 4px 20px rgba(11, 92, 173, 0.03);
        }

        .wf-help-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .wf-help-icon {
            width: 48px;
            height: 48px;
            background: #0b5cad;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .wf-help-text h3 {
            font-size: 18px;
            font-weight: 800;
            color: #0f2742;
            margin-bottom: 2px;
        }

        .wf-help-text p {
            font-size: 14px;
            color: #475569;
        }

        .wf-help-channels {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .wf-channel-item {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #1e293b;
            transition: opacity 0.2s;
        }

        .wf-channel-item:hover {
            opacity: 0.85;
        }

        .wf-channel-item i {
            font-size: 22px;
        }

        .wf-channel-item .fa-phone { color: #0b5cad; }
        .wf-channel-item .fa-whatsapp { color: #22c55e; }
        .wf-channel-item .fa-envelope { color: #2563eb; }

        .wf-channel-item div {
            display: flex;
            flex-direction: column;
        }

        .wf-channel-item strong {
            font-size: 15px;
            font-weight: 800;
            color: #0f2742;
        }

        .wf-channel-item span {
            font-size: 12px;
            color: #64748b;
        }

        .btn-expert {
            display: inline-block;
            background: #0b5cad;
            color: #ffffff;
            padding: 14px 28px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(11, 92, 173, 0.15);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }

        .btn-expert:hover {
            background: #094a8c;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11, 92, 173, 0.25);
        }

        /* Trust Footer */
        .wf-trust-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .wf-trust-line {
            flex: 1;
            max-width: 120px;
            height: 1px;
            background: #cbd5e1;
            opacity: 0.6;
        }

        .wf-trust-content {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .wf-trust-content i {
            color: #22c55e;
            font-size: 14px;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .wf-404-wrapper {
                padding: 40px 30px;
                border-radius: 24px;
            }
            .wf-404-container {
                flex-direction: column-reverse;
                text-align: center;
                gap: 40px;
            }
            .wf-404-right {
                max-width: 320px;
                margin: 0 auto;
            }
            .wf-404-tagline {
                justify-content: center;
            }
            .wf-404-left {
                max-width: 100%;
            }
            .wf-404-actions {
                justify-content: center;
            }
            .wf-cards-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }
            .wf-help-bar {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 28px 20px;
                gap: 24px;
            }
            .wf-help-left {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            .wf-help-channels {
                flex-direction: column;
                align-items: center;
                gap: 20px;
                width: 100%;
            }
            .wf-channel-item {
                flex-direction: column;
                text-align: center;
                gap: 6px;
            }
            .wf-help-action {
                width: 100%;
            }
            .btn-expert {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .wf-404-title {
                font-size: 110px;
            }
            .wf-404-heading {
                font-size: 32px;
            }
            .wf-section-divider {
                margin-bottom: 25px;
            }
        }

        @media (max-width: 580px) {
            body {
                padding: 20px 15px;
            }
            .wf-404-wrapper {
                padding: 24px 12px;
                background: transparent;
                border: none;
                box-shadow: none;
            }
            .wf-cards-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .wf-404-actions {
                flex-direction: column;
                width: 100%;
            }
            .btn-home, .btn-back {
                width: 100%;
            }
        }

        @media (max-width: 400px) {
            .wf-cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wf-404-wrapper">
        <!-- Main Layout Section -->
        <div class="wf-404-container">
            <!-- Left Side: Content Box -->
            <div class="wf-404-left">
                <div class="wf-404-tagline">
                    <span class="wf-tag-line"></span>
                    <span class="wf-tag-text">Oops! Page Not Found</span>
                    <span class="wf-tag-line line-red"></span>
                </div>
                
                <h1 class="wf-404-title">404</h1>
                <h2 class="wf-404-heading">Looks like you've taken<br>a wrong turn.</h2>
                <p class="wf-404-desc">The page you're looking for doesn't exist or may have been moved.</p>
                
                <div class="wf-404-actions">
                    <a href="/" class="btn-home">
                        <i class="fa-solid fa-house"></i> Go to Homepage
                    </a>
                    <button onclick="window.history.back();" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Go Back
                    </button>
                </div>
            </div>

            <!-- Right Side: Clay 3D Signpost Illustration -->
            <div class="wf-404-right">
                <img src="assets/images/illustration_404.webp" alt="Waqt Finance 404 Illustration">
            </div>
        </div>

        <!-- Middle Section: Alternate Routes -->
        <div class="wf-looking-section">
            <div class="wf-section-divider">
                <span class="wf-divider-line"></span>
                <span class="wf-divider-text">You may be looking for</span>
                <span class="wf-divider-line"></span>
            </div>

            <div class="wf-cards-grid">
                <a href="personal-loan" class="wf-card">
                    <div class="wf-card-icon icon-blue">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <span class="wf-card-text">Personal Loan <i class="fa-solid fa-chevron-right"></i></span>
                </a>
                
                <a href="business-loan" class="wf-card">
                    <div class="wf-card-icon icon-red">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <span class="wf-card-text">Business Loan <i class="fa-solid fa-chevron-right"></i></span>
                </a>
                
                <a href="vehicle-loan" class="wf-card">
                    <div class="wf-card-icon icon-green">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <span class="wf-card-text">Vehicle Loan <i class="fa-solid fa-chevron-right"></i></span>
                </a>
                
                <a href="emi-calculator" class="wf-card">
                    <div class="wf-card-icon icon-purple">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <span class="wf-card-text">EMI Calculator <i class="fa-solid fa-chevron-right"></i></span>
                </a>
                
                <a href="free-credit-score" class="wf-card">
                    <div class="wf-card-icon icon-orange">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <span class="wf-card-text">CIBIL Score <i class="fa-solid fa-chevron-right"></i></span>
                </a>
                
                <a href="contact-us" class="wf-card">
                    <div class="wf-card-icon icon-teal">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <span class="wf-card-text">Contact Us <i class="fa-solid fa-chevron-right"></i></span>
                </a>
            </div>
        </div>

        <!-- Helpdesk Footer Alert Banner -->
        <div class="wf-help-bar">
            <div class="wf-help-left">
                <div class="wf-help-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="wf-help-text">
                    <h3>Need Help?</h3>
                    <p>Our support team is here to assist you.</p>
                </div>
            </div>
            
            <div class="wf-help-channels">
                <a href="tel:+919560260240" class="wf-channel-item">
                    <i class="fa-solid fa-phone"></i>
                    <div>
                        <strong>+91 95602 60240</strong>
                        <span>Call Us</span>
                    </div>
                </a>
                
                <a href="https://wa.me/919560260240" target="_blank" class="wf-channel-item">
                    <i class="fa-brands fa-whatsapp"></i>
                    <div>
                        <strong>WhatsApp Chat</strong>
                        <span>Quick Support</span>
                    </div>
                </a>
                
                <a href="mailto:customercare@waqtfinance.com" class="wf-channel-item">
                    <i class="fa-solid fa-envelope"></i>
                    <div>
                        <strong>customercare@waqtfinance.com</strong>
                        <span>Email Us</span>
                    </div>
                </a>
            </div>
            
            <div class="wf-help-action">
                <a href="contact-us" class="btn-expert">Talk to Expert</a>
            </div>
        </div>

        <!-- Security Credentials Bar -->
        <div class="wf-trust-footer">
            <span class="wf-trust-line"></span>
            <div class="wf-trust-content">
                <i class="fa-solid fa-circle-check"></i>
                <span>Secure • Trusted • RBI Registered NBFC</span>
            </div>
            <span class="wf-trust-line"></span>
        </div>
    </div>
</body>
</html>
