# Street2Screen-ZA - C2C E-Commerce Platform

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)

## Overview

Street2Screen-ZA is a Customer-to-Customer (C2C) e-commerce platform designed specifically for South African street vendors. The platform enables informal traders to establish digital storefronts, reach broader customer bases, and conduct secure transactions while providing buyers with access to diverse local products and services.

## Problem Statement

South Africa's informal economy generates approximately R900 billion annually and employs 20% of the workforce. However, street vendors and informal traders face significant barriers to digital commerce participation:
- Lack of secure payment infrastructure
- No verified seller mechanisms  
- Limited access to affordable logistics
- Absence of platforms designed for their specific needs
- Digital exclusion from mainstream e-commerce

## Solution

Street2Screen-ZA addresses these challenges by providing:
- **Secure C2C marketplace** with buyer and seller protection
- **Vendor verification system** building consumer trust
- **Multi-language support** (English, isiZulu, isiXhosa, Afrikaans, and 7 other SA languages)
- **Mobile-first design** optimized for low-data connections
- **Local payment integration** including mobile money options
- **RBAC admin system** for platform management
- **Low-cost hosting** on InfinityFree for sustainability

## Technology Stack

### Frontend
- HTML5
- CSS3 with Bootstrap 5
- JavaScript (ES6+)
- Responsive design (mobile-first)

### Backend
- PHP 8.2+
- MySQL 8.0+
- UTF-8 multi-language support

### Development Environment
- XAMPP (Apache + PHP + MySQL)
- Git & GitHub for version control
- VS Code for development

### Hosting & Deployment
- InfinityFree (free hosting)
- Brevo SMTP (email notifications)
- GitHub for version control

## Features

### Customer Features
- User registration and authentication
- Browse vendors and products
- Advanced search and filtering
- Shopping cart and checkout
- Order tracking
- Rating and review system
- Multi-language interface

### Vendor Features
- Vendor registration and verification
- Product management (add/edit/delete)
- Inventory tracking
- Order management
- Sales analytics dashboard
- Customer communication
- Profile customization

### Admin Features
- Role-Based Access Control (RBAC)
- User and vendor management
- Content moderation
- Platform analytics
- Dispute resolution
- System configuration

## Project Timeline

- **Deliverable 1:** Project Proposal - Due: 27 February 2026
- **Deliverable 2:** Design & Development - Due: 5 June 2026
- **Deliverable 3:** User Manual & Presentation - Due: 12 June 2026

## Installation & Setup

### Prerequisites
- XAMPP (PHP 8.2+, MySQL 8.0+, Apache)
- Git
- Code editor (VS Code recommended)
- Web browser (Chrome/Firefox recommended)

### Local Development Setup

1. **Clone Repository:**
```bash
   git clone https://github.com/IgnatiusKhumalo/Street2Screen-ZA.git
   cd Street2Screen-ZA
```

2. **Database Setup:**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `street2screen_db`
   - Set collation: `utf8mb4_unicode_ci`
   - Import schema: `src/database/schema.sql` (when available)

3. **Configuration:**
   - Copy `src/config/config.example.php` to `src/config/config.php`
   - Update database credentials
   - Configure SMTP settings for Brevo

4. **Access Application:**
   - Main site: `http://localhost/street2screen/src/public/`
   - Admin panel: `http://localhost/street2screen/src/admin/`

## Project Structure
```
street2screen/
├── README.md              # Project documentation
├── .gitignore            # Git ignore rules
├── docs/                 # Project documentation
│   ├── deliverable1/     # Project proposal
│   ├── deliverable2/     # Design & development docs
│   └── deliverable3/     # User manual
├── src/                  # Source code
│   ├── admin/            # Admin panel
│   │   ├── css/          # Admin stylesheets
│   │   ├── js/           # Admin JavaScript
│   │   ├── includes/     # Admin includes
│   │   └── index.php     # Admin dashboard
│   ├── public/           # Public-facing site
│   │   ├── css/          # Public stylesheets
│   │   ├── js/           # Public JavaScript
│   │   ├── images/       # Public images
│   │   ├── includes/     # Public includes
│   │   └── index.php     # Homepage
│   ├── config/           # Configuration files
│   │   ├── database.php  # Database connection
│   │   └── config.php    # App configuration
│   └── database/         # Database scripts
│       ├── schema.sql    # Database schema
│       └── sample-data.sql  # Sample data
├── tests/                # Test files
├── assets/               # Design assets
│   ├── images/           # Image assets
│   ├── mockups/          # UI mockups
│   └── wireframes/       # Wireframes
└── deployment/           # Deployment docs
    └── infinityfree-setup.md
```

## Database Configuration

**Database Name:** `street2screen_db`  
**Collation:** `utf8mb4_unicode_ci` (supports all 11 SA official languages)  
**Tables:** (To be defined in Deliverable 2)

### Supported Languages
- English
- isiZulu
- isiXhosa
- Afrikaans
- Sepedi
- Setswana
- Sesotho
- Xitsonga
- siSwati
- Tshivenda
- isiNdebele

## Development Workflow

### Daily Workflow
1. Start XAMPP (Apache + MySQL)
2. Pull latest changes: `git pull origin main`
3. Make changes in VS Code
4. Test in browser: `http://localhost/street2screen/src/public/`
5. Commit changes: `git add .` → `git commit -m "message"` → `git push`

### Git Workflow
```bash
# Pull latest changes
git pull origin main

# Make changes to code

# Check status
git status

# Stage changes
git add .

# Commit with message
git commit -m "Add vendor registration feature"

# Push to GitHub
git push origin main
```

## Contributing

This is an academic project for ITECA3-12. Contributions are limited to project team members.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Academic Information

- **Course:** ITECA3-12 Initial Project
- **Institution:** Eduvos
- **Academic Year:** 2026
- **Project Type:** Customer-to-Customer E-Commerce Platform
- **Student:** Ignatius Mayibongwe Khumalo
- **Focus:** Empowering South African street vendors through digital commerce

## Contact

For questions or support, please contact:
- **Developer:** Ignatius Mayibongwe Khumalo
- **GitHub:** [@IgnatiusKhumalo](https://github.com/IgnatiusKhumalo)
- **Repository:** [Street2Screen-ZA](https://github.com/IgnatiusKhumalo/Street2Screen-ZA)

## Acknowledgments

This project addresses the digital exclusion of South Africa's informal economy, with reference to:
- World Wide Worx & Mastercard (2025) - Online Retail Report
- Statistics South Africa (2025) - QLFS Report  
- Standard Bank (2025) - Township Informal Economy Report
- Research on digital barriers facing informal traders

## Project Status

🚧 **Currently in Development** - Deliverable 1 Phase

- [x] XAMPP installation and configuration
- [x] Database creation (street2screen_db)
- [x] PHP configuration (extensions + settings)
- [x] GitHub repository setup
- [ ] Project proposal documentation
- [ ] Database schema design
- [ ] Frontend development
- [ ] Backend development
- [ ] Brevo SMTP integration
- [ ] InfinityFree deployment

---

**Note:** This is an academic project developed for educational purposes. The platform demonstrates technical proficiency in web development and addresses real-world social challenges in the South African context.