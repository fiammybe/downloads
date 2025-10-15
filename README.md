# ImpressCMS Downloads Module

[![ImpressCMS](https://img.shields.io/badge/ImpressCMS-2.0%2B-blue.svg)](https://www.impresscms.org/)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL%202.0-green.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-2.0.0-orange.svg)](https://github.com/ImpressCMS/downloads)

A modern, feature-rich download management module for ImpressCMS. Manage files, categories, reviews, and track downloads with a clean, controller-based architecture.

## 🚀 Features

### Core Functionality
- **File Management** - Upload and manage downloadable files with detailed metadata
- **Category System** - Organize downloads in hierarchical categories
- **Review System** - Allow users to review and rate downloads
- **Activity Logging** - Track all download activities and changes
- **Permission System** - Granular permissions for viewing, submitting, and managing downloads
- **Search Integration** - Full-text search across downloads
- **RSS Feeds** - Syndicate your downloads via RSS
- **AJAX Support** - Dynamic reviews and tags without page reload

### Advanced Features
- **Mirror Support** - Multiple download mirrors with approval system
- **Version Tracking** - Track version history and updates
- **Screenshot Gallery** - Multiple screenshots per download
- **Tag System** - Integration with Sprockets module for tagging
- **Album Integration** - Extended image galleries via Album module
- **Catalogue Integration** - Sell downloads via Catalogue module
- **Broken Link Detection** - Mark and track broken downloads
- **Download Counter** - Track download statistics
- **Like/Dislike System** - User engagement tracking

### Modern Architecture (v2.0)
- **PHP 8.2+ Features** - Typed properties, match expressions, modern syntax
- **Controller-Based MVC** - Clean separation of concerns
- **Fluent Query Builder** - Chainable database queries
- **Security First** - CSRF protection, prepared statements, input validation
- **Type Safety** - Full type hints throughout codebase
- **PSR Compliance** - Modern PHP standards

## 📋 Requirements

- **ImpressCMS**: 2.0 or higher
- **PHP**: 8.2 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.2 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### Optional Dependencies
- **Sprockets Module** - For advanced tagging functionality
- **Album Module** - For extended image galleries
- **Catalogue Module** - For selling downloads

## 📦 Installation

### Fresh Installation

1. **Download** the module and extract to your ImpressCMS installation:
   ```bash
   cd /path/to/impresscms/modules/
   unzip downloads-2.0.0.zip
   ```

2. **Install** via ImpressCMS Admin:
   - Log in as administrator
   - Go to **System Admin > Modules**
   - Find "Downloads" in uninstalled modules
   - Click the install icon
   - Follow the on-screen instructions

3. **Configure Permissions**:
   - Go to **Downloads Admin > Permissions**
   - Set view, submit, and upload permissions for user groups

4. **Optional Plugins**:
   - Copy `extras/modules/sitemap/plugins/downloads.php` to `modules/sitemap/plugins/` for sitemap integration
   - Copy `extras/waiting/downloads.php` to `plugins/waiting/` for waiting content block

### Upgrading from 1.x

See [MIGRATION.md](MIGRATION.md) for detailed upgrade instructions.

## 🎯 Quick Start

### Creating Your First Category

1. Go to **Downloads Admin > Categories**
2. Click "Add Category"
3. Fill in:
   - Title
   - Description
   - Parent category (optional)
   - Image (optional)
4. Click "Submit"

### Adding a Download

1. Go to **Downloads Admin > Downloads**
2. Click "Add Download"
3. Fill in required fields:
   - Title
   - Category
   - Description
   - File upload or URL
4. Optional fields:
   - Screenshots
   - Version information
   - Requirements
   - License
   - Platform
5. Click "Submit"

### User Submissions

Users with submit permissions can add downloads from the front-end:
1. Navigate to Downloads module
2. Click "Submit Download"
3. Fill in the form
4. Admins can approve submissions from admin panel

## 📖 Documentation

- **[User Guide](docs/USER_GUIDE.md)** - Complete user documentation
- **[Admin Guide](docs/ADMIN_GUIDE.md)** - Administration instructions
- **[Developer Guide](docs/DEVELOPER_GUIDE.md)** - API and development documentation
- **[Migration Guide](MIGRATION.md)** - Upgrading from version 1.x
- **[Changelog](CHANGELOG.md)** - Version history and changes
- **[Security Audit](SECURITY_AUDIT.md)** - Security assessment report

## 🏗️ Architecture

### MVC Structure

```
downloads/
├── class/
│   ├── Controllers/          # Controller layer
│   │   ├── BaseController.php
│   │   ├── IndexController.php
│   │   ├── CategoryController.php
│   │   ├── DownloadController.php
│   │   ├── SingleDownloadController.php
│   │   └── Admin/
│   │       ├── IndexController.php
│   │       ├── CategoryController.php
│   │       └── DownloadController.php
│   ├── *Handler.php          # Data access layer (Handlers)
│   └── *.php                 # Entity classes (Models)
├── templates/                # View layer
├── admin/                    # Admin entry points
├── include/                  # Helper functions
└── language/                 # Translations
```

### Key Components

- **Controllers** - Handle HTTP requests and coordinate between models and views
- **Handlers** - Database operations with fluent query builder
- **Entities** - Data objects representing database records
- **Templates** - Smarty templates for presentation

## 🔒 Security

Version 2.0 includes comprehensive security improvements:

- ✅ **CSRF Protection** - All forms include security tokens
- ✅ **Prepared Statements** - All database queries use parameterized statements
- ✅ **Input Validation** - Strict input filtering and sanitization
- ✅ **Output Escaping** - All output properly escaped
- ✅ **Permission Checks** - Granular access control throughout
- ✅ **XSS Prevention** - Protection against cross-site scripting
- ✅ **SQL Injection Prevention** - Parameterized queries throughout

See [SECURITY_AUDIT.md](SECURITY_AUDIT.md) for detailed security assessment.

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Use PHP 8.2+ features (typed properties, match expressions)
- Write PHPDoc comments for all classes and methods
- Maintain backward compatibility where possible
- Add tests for new features

## 📝 License

This module is licensed under the GNU General Public License v2.0 - see the [LICENSE](docs/license.txt) file for details.

## 👥 Credits

### Version 2.0 (2025)
- **fiammybe** - Complete refactoring to ImpressCMS 2.0+ standards

### Version 1.x (2011-2012)
- **QM-B (Steffen Flohrer)** - Original development
- **MacDonald** - Contributions
- **Sato-san** - Japanese translation and testing

### Special Thanks
- The ImpressCMS development team
- All contributors and testers
- The ImpressCMS community

## 🐛 Bug Reports & Support

- **Issues**: [GitHub Issues](https://github.com/ImpressCMS/downloads/issues)
- **Forum**: [ImpressCMS Community Forums](https://community.impresscms.org/)
- **Documentation**: [Module Manual](admin/manual.php) (accessible from admin panel)

## 📊 Statistics

- **Lines of Code**: ~15,000
- **Controllers**: 7
- **Entity Classes**: 5
- **Handler Classes**: 5
- **Templates**: 15+
- **Languages**: English, German, Spanish, Japanese

## 🗺️ Roadmap

### Future Enhancements
- [ ] REST API for external integrations
- [ ] Bulk operations for downloads
- [ ] Advanced statistics dashboard
- [ ] Download scheduling
- [ ] Multi-file downloads (ZIP)
- [ ] CDN integration
- [ ] Enhanced search with filters
- [ ] Mobile app support

## 📞 Contact

- **Website**: [https://www.impresscms.org/](https://www.impresscms.org/)
- **GitHub**: [https://github.com/ImpressCMS/downloads](https://github.com/ImpressCMS/downloads)
- **Community**: [https://community.impresscms.org/](https://community.impresscms.org/)

---

**Made with ❤️ for the ImpressCMS Community**
