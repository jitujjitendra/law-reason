# Law & Reason

**Understanding Law. Navigating Life.**

A bilingual (English + Hindi) legal awareness platform built with PHP + MySQL. Simplifies legal concepts for everyday life in India through guides, scenarios, blog articles, and practical resources.

---

## Features

- **Multi-page website** with 8 legal area guides, 5+ scenarios, myths vs reality, blog, and resources
- **Bilingual** (English + Hindi) with language switch on every page
- **Admin Panel** with rich-text editor for non-technical content creators
- **Blog System** with auto image optimization (resize → WebP + thumbnails)
- **SEO/AEO/GEO optimized** — JSON-LD schema, FAQ markup, sitemap, Open Graph, hreflang, `llms.txt`
- **Security** — CSRF protection, honeypot spam filter, SQL injection prevention (PDO), XSS protection, login lockout, secure sessions
- **Responsive** — Mobile-first design, accessible (ARIA labels, reduced motion support)
- **Newsletter + Contact** — Form data saved to MySQL + email notifications

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3 (custom, no framework), Vanilla JS |
| Backend | PHP 8.x |
| Database | MySQL (InnoDB, utf8mb4) |
| Hosting | Hostinger (shared hosting compatible) |
| Images | Auto WebP conversion via GD library |

---

## Project Structure

```
law-reason/
├── admin/               # Admin panel (protected)
│   ├── includes/        # Admin header/footer templates
│   ├── login.php        # Admin login
│   ├── index.php        # Dashboard
│   ├── post-edit.php    # Blog editor
│   ├── posts.php        # Blog list
│   ├── topics.php       # Topics management
│   ├── scenarios.php    # Scenarios management
│   ├── myths.php        # Myths management
│   ├── inbox.php        # Contact messages
│   ├── subscribers.php  # Newsletter subscribers
│   └── settings.php     # Site settings
├── api/                 # API endpoints
│   ├── contact.php      # Ask form handler
│   └── subscribe.php    # Newsletter handler
├── blog/                # Blog pages
│   ├── index.php        # Blog listing
│   └── post.php         # Single post
├── config/              # Configuration
│   ├── config.php       # Main config (EDIT THIS)
│   └── database.php     # PDO connection
├── database/            # DB setup
│   └── schema.sql       # Full schema + seed data
├── includes/            # Shared PHP includes
│   ├── helpers.php      # Utility functions
│   ├── security.php     # Security functions
│   └── image-handler.php # Image processing
├── lang/                # Language files
│   ├── en.php           # English strings
│   └── hi.php           # Hindi strings
├── pages/               # Content pages
│   ├── legal-areas/     # Topic pages
│   ├── scenarios/       # Scenario pages
│   ├── myths/           # Myth detail pages
│   └── resources/       # Resource pages
├── public/              # Static assets
│   ├── css/styles.css   # Main stylesheet
│   ├── js/main.js       # Frontend JavaScript
│   └── uploads/         # User uploads (images)
├── templates/           # Reusable page templates
│   ├── header.php       # Site header
│   └── footer.php       # Site footer
├── assets/              # Static images
├── index.php            # Homepage
├── search.php           # Search page
├── sitemap.php          # Dynamic XML sitemap
├── robots.txt           # Crawler rules
├── llms.txt             # AI/GEO discoverability
└── .htaccess            # URL rewrites + security
```

---

## Deployment to Hostinger

### Step 1: Database Setup
1. Go to Hostinger → hPanel → Databases → MySQL
2. Create a new database (e.g., `u123456789_law_reason`)
3. Create a database user with full privileges
4. Import `database/schema.sql` via phpMyAdmin

### Step 2: Configuration
1. Edit `config/config.php`:
   - Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - Update `SITE_URL` to your domain
   - Add GA4 Measurement ID (when ready)
2. Upload all files to `public_html/` via File Manager or FTP

### Step 3: First-Time Setup
1. Visit `https://yourdomain.com/admin/setup.php`
2. Set your admin username and password
3. Login at `/admin/login.php`

### Step 4: SSL & Domain
1. Enable free SSL in Hostinger panel
2. Point your domain DNS to Hostinger nameservers

---

## Admin Panel Usage

### Writing a Blog Post
1. Login at `/admin/login.php`
2. Click **"New Post"** or go to Blog Posts → Add New
3. Write title and content in **both English and Hindi** (side-by-side fields)
4. Upload a featured image (auto-resized to WebP, ~80% quality)
5. Select category, add tags, fill SEO fields
6. Check "Published" and click **Save** → Instantly live!

### Managing Topics/Scenarios
1. Go to the respective section in admin sidebar
2. Edit content, SEO meta, and translations
3. Save → changes are live immediately

---

## SEO Features Built-In

- **Clean URLs** — `/blog/property-rights-india` (via .htaccess)
- **JSON-LD Schema** — WebSite, Article, FAQ, BreadcrumbList
- **Open Graph + Twitter Cards** — Social sharing optimization
- **Hreflang tags** — Bilingual SEO (en/hi)
- **XML Sitemap** — Auto-generated at `/sitemap.xml`
- **robots.txt** — Proper crawler directives
- **llms.txt** — AI engine discoverability (GEO)
- **FAQ Markup** — On topic/scenario pages (Answer Engine Optimization)
- **Image alt text** — Required field in admin
- **Fast loading** — No heavy frameworks, WebP images, gzip compression

---

## Security Measures

- SQL Injection: PDO prepared statements everywhere
- XSS: All output escaped with `htmlspecialchars()`
- CSRF: Token verification on all forms
- Spam: Honeypot fields + rate limiting
- Authentication: bcrypt password hashing, session timeout, login lockout
- File Upload: Type/size validation, renamed files, execution blocked
- Headers: CSP, X-Frame-Options, X-Content-Type-Options, HTTPS forced

---

## License

All rights reserved. &copy; 2026 Law & Reason.
