# Tony Dev Portfolio — setup (Laragon)

## 1. Install
1. Make sure you have a WordPress site running in Laragon (e.g. `laragon/www/tony-portfolio/`). If not yet:
   - Laragon → right-click → Quick app → WordPress, or download WP core manually and set up the DB via `localhost/phpmyadmin`.
2. Drop this whole `tony-portfolio` folder into:
   `laragon/www/<your-site-folder>/wp-content/themes/`
3. In wp-admin → **Appearance → Themes**, activate **Tony Dev Portfolio**.

## 2. Set the homepage
This theme uses `front-page.php`, which WordPress shows automatically on the site's front page — no need to touch Settings → Reading.

## 3. Add your projects
Go to **Projects → Add New** in the sidebar (new menu added by this theme).
- Title = project name
- Featured image = thumbnail shown on the card
- Excerpt = short one-liner for the card
- Content = full write-up for the single project page
- Sidebar "Project Details" box = Client / Role / Year / Live URL
- Project Categories = tag it (e.g. "Web Dev", "WordPress")

Projects publish straight to the homepage Work section automatically — no code edits needed.

## 4. Edit hero / experience / contact text
Those sections are hard-coded in `front-page.php` (search for `<section class="tdp-hero"`, `id="experience"`, `id="contact"`) — quick to find and edit directly since they don't change often. Update:
- Your real email under `#contact` (currently a placeholder `your-email@example.com`)
- LinkedIn URL (currently `#` — add your real link)
- Experience timeline entries if your role details change

## 5. Menu (optional)
By default the nav falls back to the built-in anchor links (`tdp_fallback_menu()` in `functions.php`). If you want a custom menu instead, create one under **Appearance → Menus** and assign it to "Primary Menu".

## 6. Fonts
Space Grotesk (headings), Inter (body), JetBrains Mono (labels/terminal-style tags) are pulled from Google Fonts in `functions.php`. Swap the URL there if you'd rather self-host.

## Notes
- No page builder plugin required — Elementor Free will still work fine on regular Pages if you want to build extra landing pages later, this just doesn't depend on it for the homepage.
- Structure is intentionally readable PHP (no framework) so it's easy to show off in interviews / screen-shares as proof of custom theme work, separate from your Elementor client work.
