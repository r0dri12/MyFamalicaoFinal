# MyFamalicão 🗺️

**MyFamalicão** is a Progressive Web App (PWA) developed as part of the **Professional Aptitude Project (PAP)** for the **Computer Systems Management and Programming Technician** course (2025/2026).

The project aims to promote **Vila Nova de Famalicão**, allowing users to discover points of interest, create personalized tourist routes, and explore the city through an interactive map.

> Developed by **Rodrigo Afonso Loureiro de Frutuoso** — Agrupamento de Escolas Camilo Castelo Branco.

## ✨ Features

- 🗺️ **Interactive map** with points of interest in Vila Nova de Famalicão
- 📍 **Points of Interest (POIs)** with descriptions, images, and locations
- 🧭 **Personalized route creation** by selecting places to visit
- 🚗 **Google Maps integration** to open created routes directly in GPS navigation
- 📏 **Route distance and duration calculation**
- 💾 **Save and manage routes** linked to the user's account
- 📄 **Export routes to PDF**
- 🔊 **Audio guide** using text-to-speech for location descriptions
- ⭐ **Custom locations**, allowing users to create their own places
- 👥 **Community features** for sharing and discovering user-created locations
- 🏆 **Achievements and gamification** to encourage exploration
- 🌦️ **Weather information** for Famalicão
- 🌍 **Multi-language support**
- 👤 **User registration, login, and profile management**
- ⚙️ **User settings** for account customization
- 🛡️ **Administrator area** for platform management
- 📱 **Responsive design** for desktop, tablet, and mobile devices
- 📲 **Progressive Web App (PWA)** support

## 🛠️ Technologies

### Frontend

- **HTML5**
- **CSS3**
- **JavaScript (ES6)**
- **Leaflet.js** — interactive maps
- **Leaflet Routing Machine** — route calculation and visualization
- **Phosphor Icons** — interface icons
- **Google Fonts — Inter**

### Backend

- **PHP**
- **MySQL**
- **PDO** for database connections
- **PHPMailer** for email functionality

### PWA

- Web App Manifest (`manifest.json`)
- Service Worker (`sw.js`)
- Responsive mobile-first interface

### External Services

The project uses several external services and resources, including:

- **OpenStreetMap / CARTO** for map tiles
- **Leaflet** for map functionality
- **Google Maps** for navigation
- **Google Fonts** for typography
- **Phosphor Icons** for interface icons

## 📁 Project Structure

```text
MyFamalicaoFinal/
├── index.php                    # Homepage
├── map.php                      # Interactive map
├── login.php                    # User login
├── register.php                 # User registration
├── logout.php                   # Logout
├── comunidade.php               # Community area
├── meus_locais.php              # User-created/saved locations
├── destaques.php                # Featured locations and content
├── sobre.php                    # PAP information
├── settings.php                 # User settings
├── admin.php                    # Administration area
│
├── api_admin.php                # Administration API
├── api_custom_pois.php          # Custom POI management
├── api_gamification.php         # Gamification system
├── api_manage_poi.php           # Point of interest management
├── api_routes.php               # Route management
├── api_social.php               # Community/social features
├── api_update_profile.php       # Profile updates
├── api_upload_avatar.php        # Avatar uploads
│
├── db_connect.php               # MySQL database connection
├── migrate_gamification.php     # Gamification database migration
├── migrate_language.php         # Language database migration
│
├── script.js                    # Main map and application logic
├── theme_handler.js             # Theme management
├── sw.js                        # PWA Service Worker
├── manifest.json                # PWA configuration
│
├── style.css                    # Map/application styles
├── main_style.css               # Main interface styles
├── auth_style.css               # Authentication styles
├── favicon.png                  # Application icon
│
└── includes/
    ├── mailer.php               # Email configuration
    └── PHPMailer/               # PHPMailer library
```

## 🚀 Installation

### Requirements

To run the project locally, you will need:

- **PHP 8.x or later**
- **MySQL / MariaDB**
- **Apache**
- **XAMPP** or an equivalent local server environment
- A modern web browser with JavaScript enabled

### 1. Clone the repository

```bash
git clone https://github.com/r0dri12/MyFamalicaoFinal.git
cd MyFamalicaoFinal
```

### 2. Configure the local server

Place the project inside the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\MyFamalicaoFinal
```

Start the following services in XAMPP:

- Apache
- MySQL

### 3. Configure the database

The application uses a MySQL database named `myfamalicao`. The connection is configured in `db_connect.php`.

The default local configuration is:

```php
$host = "localhost";
$db_name = "myfamalicao";
$username = "root";
$password = "";
```

Create the database through **phpMyAdmin**:

```sql
CREATE DATABASE myfamalicao;
```

> **Note:** The repository currently does not include a complete SQL schema/seed file. The required database tables must be created before using features that depend on the database.

### 4. Run the application

With Apache and MySQL running, open:

```text
http://localhost/MyFamalicaoFinal/
```

## 🗺️ How to Use

### Explore the city

1. Open the application.
2. Access the **Map** section.
3. Explore the points of interest displayed on the map.
4. Select a location to view its information.

### Create a route

1. Select the locations you want to visit.
2. Add them to your route.
3. The application calculates the route, including distance and estimated duration.
4. Save the route for future use if desired.
5. Open the route directly in **Google Maps** for navigation.

### Create a custom location

Users can click on an empty location on the map and create a custom place by providing its name and description. Custom locations can then be added to routes.

### Audio Guide

Points of interest can provide their descriptions through text-to-speech. Users can listen to individual descriptions or activate the audio guide for the complete route.

## 📱 Progressive Web App

MyFamalicão is designed as a **Progressive Web App**, allowing it to be accessed through a browser and installed as an application on compatible devices.

The PWA configuration is defined in `manifest.json`, while `sw.js` provides Service Worker functionality.

## ♿ Accessibility

Accessibility is an important part of the project. The application includes features such as:

- Audio descriptions for points of interest
- Responsive interface for different screen sizes
- Icons and visual elements to improve navigation
- Audio-based access to information that would otherwise rely mainly on visual content

## 🔐 Accounts and Permissions

The application provides different levels of access:

- **Visitor:** can access the public content of the platform.
- **Registered user:** can use features such as routes, custom locations, community features, and profile settings.
- **Administrator:** has access to the administration area and management tools.

## 🎯 Project Goals

The main goal of MyFamalicão is to provide a digital platform that supports local tourism and makes discovering Vila Nova de Famalicão easier and more interactive.

The main objectives are to:

- Promote the city's heritage and points of interest
- Make personalized tourist routes easier to create
- Encourage exploration of the city
- Integrate accessibility features
- Provide a modern experience adapted to mobile devices
- Encourage user participation through community features

## 🔮 Future Improvements

Possible future improvements include:

- Personalized recommendations based on user preferences
- Additional points of interest and multimedia content
- Improved offline support
- Further accessibility improvements
- Integration with additional local tourism services
- More advanced ratings and review functionality
- Further backend security and infrastructure improvements

## 👨‍💻 Author

**Rodrigo Afonso Loureiro de Frutuoso**  
Computer Systems Management and Programming Technician  
Agrupamento de Escolas Camilo Castelo Branco  
Professional Aptitude Project — 2025/2026

## 📄 License

This project was developed as part of a **Professional Aptitude Project (PAP)**. No specific open-source license has been defined for this repository.

---

⭐ If you find this project interesting or useful, consider giving the repository a star.