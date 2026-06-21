<?php
/**
 * Law & Reason - Footer Template
 * Included at the bottom of every public page
 */
$lang = getCurrentLang();
$strings = require __DIR__ . '/../lang/' . $lang . '.php';
$disclaimer = $lang === 'hi' ? getSiteSetting('disclaimer_hi') : getSiteSetting('disclaimer_en');
if (empty($disclaimer)) {
    $disclaimer = $strings['announcement']; // fallback
}
?>
    </main>

    <!-- Footer -->
    <footer class="site-footer" id="about">
        <div class="container footer-grid">
            <div class="footer-about">
                <h2><?php echo $strings['footer_about_title']; ?></h2>
                <p><?php echo $strings['footer_about_text']; ?></p>
                <div class="social-links">
                    <?php
                    $instagram = getSiteSetting('social_instagram');
                    $linkedin = getSiteSetting('social_linkedin');
                    $youtube = getSiteSetting('social_youtube');
                    ?>
                    <?php if ($instagram): ?>
                    <a href="<?php echo htmlspecialchars($instagram); ?>" aria-label="Instagram" target="_blank" rel="noopener"><svg><use href="#icon-instagram"></use></svg></a>
                    <?php endif; ?>
                    <a href="mailto:<?php echo SITE_EMAIL; ?>" aria-label="Email"><svg><use href="#icon-mail"></use></svg></a>
                </div>
            </div>
            <div>
                <h2><?php echo $strings['footer_quick_links']; ?></h2>
                <a href="/pages/scenarios/"><?php echo $strings['nav_guidance']; ?></a>
                <a href="/pages/legal-areas/"><?php echo $strings['nav_legal_guides']; ?></a>
                <a href="/pages/resources/"><?php echo $strings['nav_resources']; ?></a>
                <a href="/blog/"><?php echo $strings['nav_blog']; ?></a>
                <button class="footer-link js-open-query" type="button"><?php echo $strings['nav_ask']; ?></button>
                <a href="/about"><?php echo $strings['nav_about']; ?></a>
            </div>
            <div>
                <h2><?php echo $strings['footer_popular_resources']; ?></h2>
                <a href="/pages/resources/property-verification-checklist">Property Verification Checklist</a>
                <a href="/pages/resources/family-legal-organiser">Family Legal Organiser</a>
                <a href="/pages/resources/consumer-complaint-checklist">Consumer Complaint Checklist</a>
                <a href="/pages/resources/rent-agreement-checklist">Rent Agreement Checklist</a>
                <a href="/pages/resources/legal-notice-preparation">Legal Notice Preparation Sheet</a>
                <a href="/pages/resources/court-timeline-organiser">Court Timeline Organiser</a>
            </div>
            <div id="contact">
                <h2><?php echo $strings['footer_connect']; ?></h2>
                <?php if ($instagram): ?><a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
                <?php if ($youtube): ?><a href="<?php echo htmlspecialchars($youtube); ?>" target="_blank" rel="noopener">YouTube</a><?php endif; ?>
                <?php if ($linkedin): ?><a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener">LinkedIn</a><?php endif; ?>
                <a href="mailto:<?php echo SITE_EMAIL; ?>">Email</a>
            </div>
            <div>
                <h2><?php echo $strings['footer_newsletter']; ?></h2>
                <p><?php echo $strings['footer_newsletter_copy']; ?></p>
                <form class="newsletter-form js-newsletter" action="/api/subscribe.php" method="POST">
                    <?php echo csrfField(); ?>
                    <!-- Honeypot -->
                    <input type="text" name="website_url" style="display:none" tabindex="-1" autocomplete="off">
                    <label class="sr-only" for="footer-email"><?php echo $strings['weekly_placeholder']; ?></label>
                    <input id="footer-email" type="email" name="email" placeholder="<?php echo $strings['weekly_placeholder']; ?>" required>
                    <button type="submit"><?php echo $strings['weekly_subscribe']; ?></button>
                </form>
                <small class="form-note"><?php echo $strings['weekly_note']; ?></small>
            </div>
        </div>
        <div class="container footer-legal">
            <p><?php echo htmlspecialchars($disclaimer); ?></p>
            <div class="footer-bottom">
                <span><?php echo $strings['footer_copyright']; ?></span>
                <span><a href="/privacy"><?php echo $strings['footer_privacy']; ?></a><i></i><a href="/terms"><?php echo $strings['footer_terms']; ?></a></span>
            </div>
        </div>
    </footer>

    <!-- Ask Law & Reason Modal -->
    <dialog class="query-modal" id="query-modal">
        <button class="modal-close" type="button" aria-label="Close">
            <svg><use href="#icon-close"></use></svg>
        </button>
        <p class="eyebrow"><?php echo $strings['ask_eyebrow']; ?></p>
        <h2><?php echo $strings['ask_title']; ?></h2>
        <p><?php echo $strings['ask_modal_copy']; ?></p>
        <form id="query-form" action="/api/contact.php" method="POST">
            <?php echo csrfField(); ?>
            <!-- Honeypot -->
            <input type="text" name="website_url" style="display:none" tabindex="-1" autocomplete="off">
            <label>
                <?php echo $strings['ask_name']; ?>
                <input name="name" type="text" placeholder="<?php echo $strings['ask_placeholder_name']; ?>" required>
            </label>
            <label>
                <?php echo $strings['ask_email']; ?>
                <input name="email" type="email" placeholder="<?php echo $strings['ask_placeholder_email']; ?>" required>
            </label>
            <label>
                <?php echo $strings['ask_area']; ?>
                <select name="area" required>
                    <option value=""><?php echo $strings['ask_select']; ?></option>
                    <option value="property">Property &amp; Rent</option>
                    <option value="family">Family Matters</option>
                    <option value="consumer">Consumer Rights</option>
                    <option value="employment">Employment Issues</option>
                    <option value="cheque">Cheque Bounce</option>
                    <option value="criminal">Police &amp; Criminal</option>
                    <option value="senior">Senior Citizen</option>
                    <option value="other">Other</option>
                </select>
            </label>
            <label>
                <?php echo $strings['ask_query']; ?>
                <textarea name="query" rows="5" placeholder="<?php echo $strings['ask_placeholder_query']; ?>" required></textarea>
            </label>
            <button class="button button-gold" type="submit"><?php echo $strings['ask_btn']; ?></button>
            <p class="modal-status" role="status"></p>
        </form>
    </dialog>

    <!-- Search Modal -->
    <dialog class="search-modal" id="search-modal">
        <button class="modal-close" type="button" aria-label="Close">
            <svg><use href="#icon-close"></use></svg>
        </button>
        <form class="search-form" action="/search" method="GET">
            <label class="sr-only" for="search-input"><?php echo $strings['nav_search']; ?></label>
            <input id="search-input" type="search" name="q" placeholder="<?php echo $strings['search_placeholder']; ?>" required autofocus>
            <button class="button button-navy" type="submit"><?php echo $strings['search_btn']; ?></button>
        </form>
    </dialog>

    <!-- Toast Notification -->
    <div class="toast" role="status" aria-live="polite"></div>

    <!-- Main JS -->
    <script src="/public/js/main.js"></script>
</body>
</html>
