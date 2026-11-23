# AGENTS.md

Uputstvo za AI agente (Claude Code, Codex, Cursor…) i saradnike koji rade u ovom repozitorijumu.

## O repozitorijumu

- **Javni** repo sa WordPress / WooCommerce pluginovima i alatima koje izrađuje Prometix – Vuković Radovan.
- **Jedan folder u korenu = jedan plugin (ili alat)**. Svaki je potpuno samostalan: nema deljenog koda, deljenog `vendor/` ni build koraka na nivou repoa. Folder mora moći da se zapakuje u `.zip` i instalira sam.
- Nema build sistema, Composer-a ni npm-a za sada. Čist PHP + vanilla JS. Ne uvodi zavisnosti bez jasnog razloga.

## Jezik

- Korisnički tekstovi (UI, mejlovi, poruke grešaka) su na **srpskom, latinicom**, sa ispravnim dijakritikom (č, ć, š, ž, đ). Obraćanje kupcu sa „Vi/Vaš" (veliko V).
- **Komentari u kodu su na srpskom**. PHPDoc tagovi i imena (funkcije, klase, promenljive) ostaju na engleskom.
- README i dokumentacija na srpskom.

## Konvencije za nov plugin

- **Slug** foldera, glavnog fajla i text domain-a su isti: `moj-plugin/moj-plugin.php`, `Text Domain: moj-plugin`.
- **Prefiks** (2–4 slova, npr. `upr_`, `UPR_`) za sve funkcije, klase, konstante, AJAX akcije, nonce-ove, meta ključeve (`_upr_…`) i handle-ove skripti. Bez globalnih imena bez prefiksa.
- Glavni fajl sadrži kompletan WP header: `Plugin Name`, `Description`, `Version`, `Author: Prometix – Vuković Radovan`, `Author URI: https://prometix.net/`, `Requires at least`, `Requires PHP`, `License: GPL-2.0-or-later`, `Text Domain`. Za WooCommerce dodatke i `Requires Plugins: woocommerce`, `WC requires at least`, `WC tested up to`.
- Konstante `PREFIX_VERSION`, `PREFIX_FILE`, `PREFIX_DIR` u glavnom fajlu. `PREFIX_VERSION` mora da se poklapa sa `Version` u headeru — menjaj oba zajedno.
- Raspored: `includes/` (PHP), `assets/` (JS/CSS), `templates/` (šabloni koje tema može da prepiše).
- Admin kod se učitava samo uz `is_admin()`; skripte samo na ekranu gde trebaju (proveri `get_current_screen()`).
- Posle dodavanja plugina **dopuni tabelu i sekciju u [README.md](README.md)**.

## WordPress / WooCommerce pravila

- Svaki PHP fajl počinje sa `defined( 'ABSPATH' ) || exit;`.
- Prati [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/): tabovi, razmaci u zagradama, Yoda poređenja, `array()` sintaksa. Kod mora da prođe `phpcs --standard=WordPress`; `phpcs:ignore` samo uz objašnjenje zašto.
- Minimalno **PHP 7.4** — bez `match`, named argumenata, `readonly`, `enum`, union tipova, `str_contains` itd.
- **Bezbednost (obavezno):**
  - Svaki AJAX/form handler: nonce (`check_ajax_referer` / `check_admin_referer`) **i** `current_user_can()` provera, po mogućstvu za konkretan objekat (`edit_shop_order`, `$order_id`).
  - Ulaz: `wp_unslash()` pa `sanitize_*` / `absint`. Izlaz: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
  - Upload fajlova: proveri `error`, veličinu, `is_uploaded_file`, i tip po sadržaju preko `wp_check_filetype_and_ext()` sa listom dozvoljenih MIME tipova. Ne čuvaj fajlove u javnom folderu ako nije neophodno.
- **WooCommerce:**
  - Porudžbine isključivo preko CRUD API-ja (`wc_get_order`, `$order->get_meta()`, `update_meta_data()` + `save()`) — nikad `get_post_meta` / direktan SQL. Uvek deklariši HPOS kompatibilnost (`custom_order_tables`) u `before_woocommerce_init`.
  - Ekran porudžbine: `wc_get_page_screen_id( 'shop-order' )`; meta box callback dobija `WP_Post` ili `WC_Order` — podrži oba.
  - Mejlovi kupcu se prave kao klase koje nasleđuju `WC_Email` (registracija kroz `woocommerce_email_classes`), sa HTML i plain šablonom, da bi bili podesivi u WooCommerce podešavanjima i da bi tema mogla da ih prepiše.
- Stringovi idu kroz `__()` / `esc_html__()` sa text domain-om plugina, i `/* translators: */` komentar uz svaki `sprintf` placeholder.

## Javni repo — šta ne sme da uđe

- Nikakvi ključevi, lozinke, tokeni, `.env`, `wp-config.php`, dump-ovi baze, logovi.
- Podaci klijenata i kupaca (mejlovi, adrese, brojevi pošiljki, računi) — ni u kodu, ni u primerima, ni u test fajlovima.
- Interni URL-ovi staging/produkcionih sajtova klijenata.
- Build artefakti (`*.zip`, `dist/`, `vendor/`, `node_modules/`) — vidi `.gitignore`.

## Provera pre commita

Nema automatskih testova. Minimum:

1. `php -l` na svakom izmenjenom PHP fajlu.
2. `phpcs --standard=WordPress <folder-plugina>` ako je dostupan.
3. Ručni test na lokalnom WordPress + WooCommerce sajtu (aktivacija bez upozorenja, sa `WP_DEBUG` uključenim) za izmenjenu funkcionalnost — i sa HPOS uključenim i isključenim kada se dira porudžbina.
4. Ažuriraj `Version` + konstantu verzije ako se plugin menja, i README ako se menja ponašanje.

Commit poruke kratko, na srpskom ili engleskom, sa prefiksom plugina: `unix-posiljka-racun: ...`.
