<?php
/**
 * Law & Reason - Homepage
 * Dynamic multi-language homepage pulling content from DB
 */

$currentPage = 'home';
$pageTitle = 'Law & Reason | Understanding Law. Navigating Life.';
$pageDescription = 'Simplifying legal processes through awareness, practical resources, and structured pathways to seek guidance.';
$pageCanonical = 'https://lawandreason.com';

require_once __DIR__ . '/templates/header.php';

// Get topics from DB
try {
    $db = getDB();
    $topics = $db->query("SELECT * FROM topics WHERE is_published = 1 ORDER BY sort_order ASC")->fetchAll();
    $scenarios = $db->query("SELECT * FROM scenarios WHERE is_published = 1 ORDER BY sort_order ASC LIMIT 5")->fetchAll();
    $myths = $db->query("SELECT * FROM myths WHERE is_published = 1 ORDER BY sort_order ASC LIMIT 3")->fetchAll();
    $latestPosts = $db->query("SELECT * FROM posts WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3")->fetchAll();
} catch (Exception $e) {
    // Fallback: use empty arrays if DB not connected (for static preview)
    $topics = [];
    $scenarios = [];
    $myths = [];
    $latestPosts = [];
}

$lang = getCurrentLang();
$strings = require __DIR__ . '/lang/' . $lang . '.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <p class="eyebrow"><?php echo $strings['hero_eyebrow']; ?></p>
            <h1><?php echo $strings['hero_title_1']; ?><br><span><?php echo $strings['hero_title_2']; ?></span></h1>
            <p class="hero-copy"><?php echo $strings['hero_copy']; ?></p>
            <div class="hero-actions">
                <a class="button button-navy" href="/pages/legal-areas/"><?php echo $strings['hero_btn_guides']; ?></a>
                <a class="button button-gold" href="/pages/scenarios/"><?php echo $strings['hero_btn_guidance']; ?></a>
                <a class="button button-outline" href="/blog/"><?php echo $strings['hero_btn_weekly']; ?></a>
            </div>
        </div>
    </section>

    <!-- Legal Areas Section -->
    <section class="legal-areas section" id="legal-areas">
        <div class="container">
            <div class="section-heading centered">
                <p class="eyebrow"><?php echo $strings['areas_eyebrow']; ?></p>
                <h2><?php echo $strings['areas_title']; ?></h2>
            </div>
            <div class="area-grid">
                <?php if (!empty($topics)): ?>
                    <?php foreach ($topics as $topic): ?>
                    <a class="area-card" href="/pages/legal-areas/<?php echo htmlspecialchars($topic['slug']); ?>">
                        <svg><use href="#<?php echo htmlspecialchars($topic['icon']); ?>"></use></svg>
                        <h3><?php echo htmlspecialchars(getLangValue($topic, 'title')); ?></h3>
                        <p><?php echo htmlspecialchars(getLangValue($topic, 'description')); ?></p>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Static fallback for GitHub Pages preview -->
                    <a class="area-card" href="/pages/legal-areas/property-rent">
                        <svg><use href="#icon-home"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'संपत्ति और किराया' : 'Property & Rent'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'खरीदना, बेचना, किराया अनुबंध, विवाद और अधिक।' : 'Buying, selling, rent agreements, disputes and more.'; ?></p>
                    </a>
                    <a class="area-card" href="/pages/legal-areas/family-matters">
                        <svg><use href="#icon-users"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'पारिवारिक मामले' : 'Family Matters'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'तलाक, भरण-पोषण, विरासत, बच्चों की कस्टडी और अधिक।' : 'Divorce, maintenance, inheritance, child custody and more.'; ?></p>
                    </a>
                    <a class="area-card" href="/pages/legal-areas/consumer-rights">
                        <svg><use href="#icon-cart"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'उपभोक्ता अधिकार' : 'Consumer Rights'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'रिफंड, दोषपूर्ण उत्पाद, ऑनलाइन शिकायतें और अधिक।' : 'Refunds, defective products, online complaints and more.'; ?></p>
                    </a>
                    <a class="area-card" href="/pages/legal-areas/employment-issues">
                        <svg><use href="#icon-briefcase"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'रोजगार के मुद्दे' : 'Employment Issues'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'नौकरी से निकालना, नोटिस अवधि, वेतन समस्याएं और अधिक।' : 'Termination, notice periods, salary issues and more.'; ?></p>
                    </a>
                    <a class="area-card" href="/pages/legal-areas/cheque-bounce">
                        <svg><use href="#icon-document"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'चेक बाउंस' : 'Cheque Bounce'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'कानूनी नोटिस, प्रक्रिया, जटिलताएं और परिणाम।' : 'Legal notice, process, complications and consequences.'; ?></p>
                    </a>
                    <a class="area-card" href="/pages/legal-areas/police-criminal">
                        <svg><use href="#icon-shield"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'पुलिस और आपराधिक प्रक्रिया' : 'Police & Criminal Procedure'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'FIR, गिरफ्तारी, जमानत, जांच के दौरान अधिकार और अधिक।' : 'FIR, arrest, bail, rights during investigation and more.'; ?></p>
                    </a>
                    <a class="area-card" href="/pages/legal-areas/senior-citizen">
                        <svg><use href="#icon-person"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'वरिष्ठ नागरिक अधिकार' : 'Senior Citizen Rights'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'भरण-पोषण, संपत्ति, सुरक्षा और कानूनी सुरक्षा उपाय।' : 'Maintenance, property, protection and legal safeguards.'; ?></p>
                    </a>
                    <a class="area-card" href="/pages/legal-areas/documentation">
                        <svg><use href="#icon-clipboard"></use></svg>
                        <h3><?php echo $lang === 'hi' ? 'दस्तावेज़ और रिकॉर्ड' : 'Documentation & Records'; ?></h3>
                        <p><?php echo $lang === 'hi' ? 'महत्वपूर्ण दस्तावेज़, ऑर्गेनाइज़र, चेकलिस्ट और अधिक।' : 'Important documents, organisers, checklists and more.'; ?></p>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- What Should I Do Section -->
    <section class="guidance section" id="guidance">
        <div class="container guidance-panel">
            <div class="guidance-intro">
                <p class="eyebrow"><?php echo $strings['guidance_eyebrow']; ?></p>
                <h2><?php echo $strings['guidance_title']; ?></h2>
                <p><?php echo $strings['guidance_copy']; ?></p>
                <a class="button button-gold" href="/pages/scenarios/"><?php echo $strings['guidance_view_all']; ?> <span>&rarr;</span></a>
            </div>
            <div class="scenario-wrap">
                <div class="scenario-grid">
                    <?php if (!empty($scenarios)): ?>
                        <?php foreach ($scenarios as $scenario): ?>
                        <a class="scenario-card" href="/pages/scenarios/<?php echo htmlspecialchars($scenario['slug']); ?>">
                            <span><?php echo htmlspecialchars(getLangValue($scenario, 'question')); ?></span><b>&rarr;</b>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a class="scenario-card" href="/pages/scenarios/received-legal-notice">
                            <span><?php echo $lang === 'hi' ? 'अगर मुझे कानूनी नोटिस मिले तो मैं क्या करूं?' : 'What should I do if I receive a legal notice?'; ?></span><b>&rarr;</b>
                        </a>
                        <a class="scenario-card" href="/pages/scenarios/cheque-bounces">
                            <span><?php echo $lang === 'hi' ? 'अगर मेरा चेक बाउंस हो जाए तो मैं क्या करूं?' : 'What should I do if my cheque bounces?'; ?></span><b>&rarr;</b>
                        </a>
                        <a class="scenario-card" href="/pages/scenarios/police-contact">
                            <span><?php echo $lang === 'hi' ? 'अगर पुलिस मुझसे संपर्क करे तो मैं क्या करूं?' : 'What should I do if police contact me?'; ?></span><b>&rarr;</b>
                        </a>
                        <a class="scenario-card" href="/pages/scenarios/tenant-refuses-vacate">
                            <span><?php echo $lang === 'hi' ? 'अगर मेरा किरायेदार मकान खाली करने से मना कर दे तो मैं क्या करूं?' : 'What should I do if my tenant refuses to vacate?'; ?></span><b>&rarr;</b>
                        </a>
                        <a class="scenario-card" href="/pages/scenarios/builder-delays-possession">
                            <span><?php echo $lang === 'hi' ? 'अगर बिल्डर कब्जा देने में देरी करे तो मैं क्या करूं?' : 'What should I do if my builder delays possession?'; ?></span><b>&rarr;</b>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="topic-features">
                    <strong><?php echo $strings['guidance_feature_title']; ?></strong>
                    <span><svg><use href="#icon-book"></use></svg><?php echo $strings['guidance_f1']; ?></span>
                    <span><svg><use href="#icon-clipboard"></use></svg><?php echo $strings['guidance_f2']; ?></span>
                    <span><svg><use href="#icon-document"></use></svg><?php echo $strings['guidance_f3']; ?></span>
                    <span><svg><use href="#icon-clock"></use></svg><?php echo $strings['guidance_f4']; ?></span>
                    <span><svg><use href="#icon-gavel"></use></svg><?php echo $strings['guidance_f5']; ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Cards: Resources, Weekly, Ask -->
    <section class="feature-cards section" id="resources">
        <div class="container feature-grid">
            <article class="feature-card resources-card">
                <svg class="feature-icon"><use href="#icon-folder"></use></svg>
                <div>
                    <p class="eyebrow"><?php echo $strings['resources_eyebrow']; ?></p>
                    <h2><?php echo $strings['resources_title']; ?></h2>
                    <p><?php echo $strings['resources_copy']; ?></p>
                    <a class="button button-gold" href="/pages/resources/"><?php echo $strings['resources_btn']; ?> <span>&rarr;</span></a>
                    <small><?php echo $strings['resources_note']; ?></small>
                </div>
            </article>

            <article class="feature-card weekly-card" id="weekly">
                <svg class="feature-icon"><use href="#icon-news"></use></svg>
                <div>
                    <p class="eyebrow"><?php echo $strings['weekly_eyebrow']; ?></p>
                    <h2><?php echo $strings['weekly_title']; ?></h2>
                    <p><?php echo $strings['weekly_copy']; ?></p>
                    <ul class="check-list">
                        <li><?php echo $strings['weekly_f1']; ?></li>
                        <li><?php echo $strings['weekly_f2']; ?></li>
                        <li><?php echo $strings['weekly_f3']; ?></li>
                        <li><?php echo $strings['weekly_f4']; ?></li>
                    </ul>
                    <form class="newsletter-form js-newsletter" action="/api/subscribe.php" method="POST">
                        <?php echo csrfField(); ?>
                        <input type="text" name="website_url" style="display:none" tabindex="-1" autocomplete="off">
                        <label class="sr-only" for="weekly-email"><?php echo $strings['weekly_placeholder']; ?></label>
                        <input id="weekly-email" type="email" name="email" placeholder="<?php echo $strings['weekly_placeholder']; ?>" required>
                        <button type="submit"><?php echo $strings['weekly_subscribe']; ?></button>
                    </form>
                    <small class="form-note"><?php echo $strings['weekly_note']; ?></small>
                </div>
            </article>

            <article class="feature-card query-card">
                <svg class="feature-icon"><use href="#icon-chat"></use></svg>
                <div>
                    <p class="eyebrow"><?php echo $strings['ask_eyebrow']; ?></p>
                    <h2><?php echo $strings['ask_title']; ?></h2>
                    <p><?php echo $strings['ask_copy']; ?></p>
                    <ul class="check-list">
                        <li><?php echo $strings['ask_f1']; ?></li>
                        <li><?php echo $strings['ask_f2']; ?></li>
                        <li><?php echo $strings['ask_f3']; ?></li>
                    </ul>
                    <button class="button button-gold js-open-query" type="button"><?php echo $strings['ask_btn']; ?> <span>&rarr;</span></button>
                    <small><?php echo $strings['ask_note']; ?></small>
                </div>
            </article>
        </div>
    </section>

    <!-- Myths vs Reality -->
    <section class="myths section">
        <div class="container myths-panel">
            <div class="section-heading split-heading">
                <div>
                    <p class="eyebrow"><?php echo $strings['myths_eyebrow']; ?></p>
                    <h2><?php echo $strings['myths_title']; ?></h2>
                </div>
                <a href="/pages/myths/"><?php echo $strings['myths_view_all']; ?> <span>&rarr;</span></a>
            </div>
            <div class="myths-grid">
                <?php if (!empty($myths)): ?>
                    <?php foreach ($myths as $myth): ?>
                    <article class="myth-card">
                        <div>
                            <span class="label"><?php echo $strings['myths_label_myth']; ?></span>
                            <h3><?php echo htmlspecialchars(getLangValue($myth, 'myth')); ?></h3>
                        </div>
                        <svg><use href="#<?php echo htmlspecialchars($myth['icon']); ?>"></use></svg>
                        <div>
                            <span class="label reality"><?php echo $strings['myths_label_reality']; ?></span>
                            <p><?php echo htmlspecialchars(getLangValue($myth, 'reality')); ?></p>
                            <a href="/pages/myths/<?php echo htmlspecialchars($myth['slug']); ?>"><?php echo $strings['myths_read_more']; ?> <span>&rarr;</span></a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="myth-card">
                        <div>
                            <span class="label"><?php echo $strings['myths_label_myth']; ?></span>
                            <h3><?php echo $lang === 'hi' ? 'पुलिस बिना प्रक्रिया के किसी को भी गिरफ्तार कर सकती है।' : 'Police can arrest anyone without procedure.'; ?></h3>
                        </div>
                        <svg><use href="#icon-shield"></use></svg>
                        <div>
                            <span class="label reality"><?php echo $strings['myths_label_reality']; ?></span>
                            <p><?php echo $lang === 'hi' ? 'गिरफ्तारी के लिए कानूनी आधार आवश्यक है और उचित प्रक्रिया का पालन करना होता है।' : 'Arrest requires legal grounds and must follow due procedure.'; ?></p>
                            <a href="/pages/myths/police-arrest-without-procedure"><?php echo $strings['myths_read_more']; ?> <span>&rarr;</span></a>
                        </div>
                    </article>
                    <article class="myth-card">
                        <div>
                            <span class="label"><?php echo $strings['myths_label_myth']; ?></span>
                            <h3><?php echo $lang === 'hi' ? 'सभी अपंजीकृत अनुबंध अमान्य होते हैं।' : 'All unregistered agreements are invalid.'; ?></h3>
                        </div>
                        <svg><use href="#icon-document"></use></svg>
                        <div>
                            <span class="label reality"><?php echo $strings['myths_label_reality']; ?></span>
                            <p><?php echo $lang === 'hi' ? 'एक अपंजीकृत अनुबंध कई स्थितियों में अभी भी मान्य हो सकता है।' : 'An unregistered agreement can still be valid in many situations.'; ?></p>
                            <a href="/pages/myths/unregistered-agreements-invalid"><?php echo $strings['myths_read_more']; ?> <span>&rarr;</span></a>
                        </div>
                    </article>
                    <article class="myth-card">
                        <div>
                            <span class="label"><?php echo $strings['myths_label_myth']; ?></span>
                            <h3><?php echo $lang === 'hi' ? 'बाउंस चेक हमेशा कारावास की ओर ले जाता है।' : 'A bounced cheque always leads to imprisonment.'; ?></h3>
                        </div>
                        <svg><use href="#icon-gavel"></use></svg>
                        <div>
                            <span class="label reality"><?php echo $strings['myths_label_reality']; ?></span>
                            <p><?php echo $lang === 'hi' ? 'कारावास स्वचालित नहीं है; यह विभिन्न कारकों पर निर्भर करता है।' : 'Imprisonment is not automatic; it depends on various factors.'; ?></p>
                            <a href="/pages/myths/bounced-cheque-imprisonment"><?php echo $strings['myths_read_more']; ?> <span>&rarr;</span></a>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
