# PHP Adademic Portal Website for UEL

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

The application is built using a native PHP backend and MySQL database, designed for deployment on standard Apache server environment (XAMPP/WAMP).

# Note! 
this repository is a imported project that was created a few weeks before this projects repository was created!. a lot of code and assets have been imported to be open sourced.

## Repository Structure

The project files are organized to separate assets, logic, and configuration. Ensure this structure is maintained for relative paths to function correctly.
aswell as containing the exported sql database for academic purposes!

```text
Academic-Portal/
├── CSS/                        # Stylesheets
├── db/                         # Contains the Exported Phpmyadmin SQL database (Academic reasons to why this exists in the first place)
├── ASSETS/                     # Image assets
├── JS/                         # JavaScript files
├── dashboard.php               # Student dashboard (needs login)
├── admin_dashboard.php         # Admin dashboard (needs login)
├── config.php                  # Database connection configuration
├── login_register.php          # Login and Register page with js switch logic
├── login_register_handler.php  # Login and password match logic
├── contact.php                 # Contact page with simple php logic
└── index.php                   # Main entry point/Landing page
