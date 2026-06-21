-- Law & Reason Database Schema
-- Run this on Hostinger MySQL to create all tables

CREATE DATABASE IF NOT EXISTS law_reason 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE law_reason;

-- Admin Users
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'editor') DEFAULT 'editor',
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Legal Topics (8 main areas)
CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title_en VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_hi TEXT,
    content_en LONGTEXT,
    content_hi LONGTEXT,
    icon VARCHAR(50) DEFAULT NULL,
    meta_title_en VARCHAR(160),
    meta_title_hi VARCHAR(160),
    meta_description_en VARCHAR(320),
    meta_description_hi VARCHAR(320),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Scenarios (What Should I Do?)
CREATE TABLE scenarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    question_en VARCHAR(500) NOT NULL,
    question_hi VARCHAR(500) NOT NULL,
    content_en LONGTEXT,
    content_hi LONGTEXT,
    topic_id INT NULL,
    meta_title_en VARCHAR(160),
    meta_title_hi VARCHAR(160),
    meta_description_en VARCHAR(320),
    meta_description_hi VARCHAR(320),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Blog Posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title_en VARCHAR(500) NOT NULL,
    title_hi VARCHAR(500) NOT NULL,
    excerpt_en TEXT,
    excerpt_hi TEXT,
    content_en LONGTEXT,
    content_hi LONGTEXT,
    featured_image VARCHAR(500) NULL,
    featured_image_thumb VARCHAR(500) NULL,
    featured_image_alt VARCHAR(255) NULL,
    author_id INT NULL,
    category VARCHAR(100) DEFAULT 'general',
    tags VARCHAR(500) NULL,
    meta_title_en VARCHAR(160),
    meta_title_hi VARCHAR(160),
    meta_description_en VARCHAR(320),
    meta_description_hi VARCHAR(320),
    views INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    published_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Blog Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name_en VARCHAR(100) NOT NULL,
    name_hi VARCHAR(100) NOT NULL,
    description_en TEXT NULL,
    description_hi TEXT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Myths vs Reality
CREATE TABLE myths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    myth_en VARCHAR(500) NOT NULL,
    myth_hi VARCHAR(500) NOT NULL,
    reality_en TEXT NOT NULL,
    reality_hi TEXT NOT NULL,
    detail_content_en LONGTEXT NULL,
    detail_content_hi LONGTEXT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    topic_id INT NULL,
    meta_title_en VARCHAR(160),
    meta_title_hi VARCHAR(160),
    meta_description_en VARCHAR(320),
    meta_description_hi VARCHAR(320),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Contact Queries (Ask Law & Reason)
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    legal_area VARCHAR(100) NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    is_replied TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Newsletter Subscribers
CREATE TABLE subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME NULL
) ENGINE=InnoDB;

-- Resources/Checklists
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    title_en VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_hi TEXT,
    content_en LONGTEXT NULL,
    content_hi LONGTEXT NULL,
    file_path VARCHAR(500) NULL,
    topic_id INT NULL,
    resource_type ENUM('checklist', 'organiser', 'tracker', 'guide') DEFAULT 'checklist',
    is_published TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- FAQ Schema entries (for SEO/AEO)
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_en VARCHAR(500) NOT NULL,
    question_hi VARCHAR(500) NOT NULL,
    answer_en TEXT NOT NULL,
    answer_hi TEXT NOT NULL,
    topic_id INT NULL,
    post_id INT NULL,
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Site Settings (key-value store for dynamic settings)
CREATE TABLE site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Law & Reason'),
('site_tagline_en', 'Understanding Law. Navigating Life.'),
('site_tagline_hi', 'कानून को समझें। जीवन को सँवारें।'),
('notification_email', 'contact@lawandreason.com'),
('social_instagram', ''),
('social_linkedin', ''),
('social_youtube', ''),
('disclaimer_en', 'The information contained on this website is intended solely for educational and informational purposes and should not be construed as legal advice. Accessing this website, communicating through it, or submitting a query does not create an advocate-client relationship.'),
('disclaimer_hi', 'इस वेबसाइट पर दी गई जानकारी केवल शैक्षिक और सूचनात्मक उद्देश्यों के लिए है और इसे कानूनी सलाह नहीं माना जाना चाहिए। इस वेबसाइट तक पहुँचने, इसके माध्यम से संवाद करने, या कोई प्रश्न सबमिट करने से वकील-मुवक्किल का संबंध नहीं बनता।');

-- Insert default admin (password: change_this_password — MUST be changed after first login)
-- Password hash for 'LawReason@2026' using bcrypt
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@lawandreason.com', '$2y$12$placeholder_hash_change_on_setup', 'Admin', 'super_admin');

-- Insert default categories
INSERT INTO categories (slug, name_en, name_hi, sort_order) VALUES
('legal-awareness', 'Legal Awareness', 'कानूनी जागरूकता', 1),
('property-law', 'Property Law', 'संपत्ति कानून', 2),
('family-law', 'Family Law', 'पारिवारिक कानून', 3),
('consumer-rights', 'Consumer Rights', 'उपभोक्ता अधिकार', 4),
('employment-law', 'Employment Law', 'रोजगार कानून', 5),
('criminal-law', 'Criminal Law', 'आपराधिक कानून', 6),
('senior-citizen', 'Senior Citizen Rights', 'वरिष्ठ नागरिक अधिकार', 7),
('general', 'General', 'सामान्य', 8);

-- Insert default topics (8 legal areas)
INSERT INTO topics (slug, title_en, title_hi, description_en, description_hi, icon, sort_order) VALUES
('property-rent', 'Property & Rent', 'संपत्ति और किराया', 'Buying, selling, rent agreements, disputes and more.', 'खरीदना, बेचना, किराया अनुबंध, विवाद और अधिक।', 'icon-home', 1),
('family-matters', 'Family Matters', 'पारिवारिक मामले', 'Divorce, maintenance, inheritance, child custody and more.', 'तलाक, भरण-पोषण, विरासत, बच्चों की कस्टडी और अधिक।', 'icon-users', 2),
('consumer-rights', 'Consumer Rights', 'उपभोक्ता अधिकार', 'Refunds, defective products, online complaints and more.', 'रिफंड, दोषपूर्ण उत्पाद, ऑनलाइन शिकायतें और अधिक।', 'icon-cart', 3),
('employment-issues', 'Employment Issues', 'रोजगार के मुद्दे', 'Termination, notice periods, salary issues and more.', 'नौकरी से निकालना, नोटिस अवधि, वेतन समस्याएं और अधिक।', 'icon-briefcase', 4),
('cheque-bounce', 'Cheque Bounce', 'चेक बाउंस', 'Legal notice, process, complications and consequences.', 'कानूनी नोटिस, प्रक्रिया, जटिलताएं और परिणाम।', 'icon-document', 5),
('police-criminal', 'Police & Criminal Procedure', 'पुलिस और आपराधिक प्रक्रिया', 'FIR, arrest, bail, rights during investigation and more.', 'FIR, गिरफ्तारी, जमानत, जांच के दौरान अधिकार और अधिक।', 'icon-shield', 6),
('senior-citizen', 'Senior Citizen Rights', 'वरिष्ठ नागरिक अधिकार', 'Maintenance, property, protection and legal safeguards.', 'भरण-पोषण, संपत्ति, सुरक्षा और कानूनी सुरक्षा उपाय।', 'icon-person', 7),
('documentation', 'Documentation & Records', 'दस्तावेज़ और रिकॉर्ड', 'Important documents, organisers, checklists and more.', 'महत्वपूर्ण दस्तावेज़, ऑर्गेनाइज़र, चेकलिस्ट और अधिक।', 'icon-clipboard', 8);

-- Insert default scenarios
INSERT INTO scenarios (slug, question_en, question_hi, topic_id, sort_order) VALUES
('received-legal-notice', 'What should I do if I receive a legal notice?', 'अगर मुझे कानूनी नोटिस मिले तो मैं क्या करूं?', NULL, 1),
('cheque-bounces', 'What should I do if my cheque bounces?', 'अगर मेरा चेक बाउंस हो जाए तो मैं क्या करूं?', 5, 2),
('police-contact', 'What should I do if police contact me?', 'अगर पुलिस मुझसे संपर्क करे तो मैं क्या करूं?', 6, 3),
('tenant-refuses-vacate', 'What should I do if my tenant refuses to vacate?', 'अगर मेरा किरायेदार मकान खाली करने से मना कर दे तो मैं क्या करूं?', 1, 4),
('builder-delays-possession', 'What should I do if my builder delays possession?', 'अगर बिल्डर कब्जा देने में देरी करे तो मैं क्या करूं?', 1, 5);

-- Insert default myths
INSERT INTO myths (slug, myth_en, myth_hi, reality_en, reality_hi, icon, sort_order) VALUES
('police-arrest-without-procedure', 'Police can arrest anyone without procedure.', 'पुलिस बिना प्रक्रिया के किसी को भी गिरफ्तार कर सकती है।', 'Arrest requires legal grounds and must follow due procedure.', 'गिरफ्तारी के लिए कानूनी आधार आवश्यक है और उचित प्रक्रिया का पालन करना होता है।', 'icon-shield', 1),
('unregistered-agreements-invalid', 'All unregistered agreements are invalid.', 'सभी अपंजीकृत अनुबंध अमान्य होते हैं।', 'An unregistered agreement can still be valid in many situations.', 'एक अपंजीकृत अनुबंध कई स्थितियों में अभी भी मान्य हो सकता है।', 'icon-document', 2),
('bounced-cheque-imprisonment', 'A bounced cheque always leads to imprisonment.', 'बाउंस चेक हमेशा कारावास की ओर ले जाता है।', 'Imprisonment is not automatic; it depends on various factors.', 'कारावास स्वचालित नहीं है; यह विभिन्न कारकों पर निर्भर करता है।', 'icon-gavel', 3);

-- Indexes for performance
CREATE INDEX idx_posts_published ON posts(is_published, published_at DESC);
CREATE INDEX idx_posts_category ON posts(category);
CREATE INDEX idx_posts_slug ON posts(slug);
CREATE INDEX idx_topics_published ON topics(is_published, sort_order);
CREATE INDEX idx_scenarios_published ON scenarios(is_published, sort_order);
CREATE INDEX idx_myths_published ON myths(is_published, sort_order);
CREATE INDEX idx_subscribers_active ON subscribers(is_active);
CREATE INDEX idx_contact_read ON contact_messages(is_read, created_at DESC);
