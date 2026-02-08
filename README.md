
<div align="center">

# 🌟 Luminous AI Curator
### The Premium AI Tools Directory & Management System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
![Status](https://img.shields.io/badge/Status-Active-success)
![Version](https://img.shields.io/badge/Version-9.0.2-blue)
![Python](https://img.shields.io/badge/Python-3.10+-blue?logo=python&logoColor=white)
![Firebase](https://img.shields.io/badge/Firebase-Realtime_Database-orange?logo=firebase&logoColor=white)

<p align="center">
  <img src="https://via.placeholder.com/800x400?text=Luminous+AI+Curator+Preview" alt="Project Banner">
</p>

[Features](#-key-features) • [Tech Stack](#-tech-stack) • [Installation](#-installation) • [Admin Panel](#-admin-control-center)

</div>

---

## 🚀 Overview

**Luminous AI Curator** is a modern, high-performance web application designed to curate and display the best Artificial Intelligence tools available. Built with a focus on **Glassmorphism design**, responsiveness, and user experience, it features a robust backend powered by **Firebase** and a custom **Python/PyQt5 Admin Control Center** for real-time management.

## ✨ Key Features

### 🌐 Web Platform (Frontend)
- **💎 Premium UI/UX:** Glassmorphism design system with neon accents and smooth animations.
- **📱 Fully Responsive:** "App-like" mobile navigation bar and touch-optimized interface.
- **🔍 Smart Search:** Instant, real-time filtering of AI tools by name, category, or tags.
- **🐛 Bug Reporting System:** Integrated modal for users to report issues directly to the admin.
- **🌑 Eco Mode:** Toggle between rich visual effects and a cleaner, low-power mode.
- **💰 Support System:** Integrated donation flow via PayPal.

### 🛠️ Admin Control Center (Backend Tool)
- **🖥️ Desktop Dashboard:** A powerful PyQt5 application to manage the entire platform.
- **⚡ Real-time Sync:** Updates made in the admin panel reflect instantly on the website.
- **📊 Analytics:** View search trends, popular tools, and user access logs.
- **📝 Content Management:** Add, edit, or remove AI tools with ease.
- **🛡️ Security:** Protected routes and Firebase token authentication.

---

## 💻 Tech Stack

| Component | Technologies |
|-----------|--------------|
| **Frontend** | HTML5, CSS3 (Variables, Flexbox/Grid), Vanilla JavaScript (ES6+) |
| **Backend** | Firebase Realtime Database (NoSQL) |
| **Admin Tool** | Python 3.10+, PyQt5 (GUI), Requests (API) |
| **Hosting** | Any static host (Netlify, Vercel, Apache/Nginx) |

---

## 📦 Installation

### 1. Web Application
Clone the repository and host it on your local server or deploy to Netlify/Vercel.

```bash
git clone https://github.com/moisesvvanti-dev/ia-curadoria.git
cd ia-curadoria
# Serve with Live Server or any static server
```

### 2. Admin Control Center
The admin panel is a Python application. Ensure you have Python 3.10+ installed.

```bash
# Install dependencies
pip install PyQt5 requests

# Run the Admin Panel
python changelog_admin.py
```

### 3. Firebase Setup
1. Create a project at [Firebase Console](https://console.firebase.google.com/).
2. Enable **Realtime Database**.
3. Copy your configuration to `index.html` (inside `FIREBASE_CONFIG`).
4. Update `firebase_rules.json` in the console with the file provided in this repo.

---

## 📸 Screenshots

| Home Page | Admin Dashboard |
|:---:|:---:|
| <img src="https://via.placeholder.com/400x200?text=Home+Page" width="400"> | <img src="https://via.placeholder.com/400x200?text=Admin+Panel" width="400"> |

| Mobile View | Bug Report Modal |
|:---:|:---:|
| <img src="https://via.placeholder.com/400x200?text=Mobile+App" width="400"> | <img src="https://via.placeholder.com/400x200?text=Bug+Report" width="400"> |

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---
<div align="center">
  <sub>Built with ❤️ by <b>moises vianna vanti</b></sub>
</div>
