# Prometix WordPress Plugins

Javna kolekcija WordPress / WooCommerce pluginova i alata koje izrađuje [Prometix](https://prometix.net/) – Vuković Radovan.

Svaki plugin živi u svom folderu u korenu repozitorijuma i potpuno je samostalan: folder se može zapakovati u `.zip` i instalirati na WordPress sajt bez ičega drugog iz ovog repoa.

## Pluginovi

| Plugin | Opis | Zahtevi | Verzija |
| --- | --- | --- | --- |
| [`unix-posiljka-racun`](unix-posiljka-racun/) | Na ekranu WooCommerce porudžbine dodaje kutiju iz koje se kupcu ručno šalje mejl sa brojem pošiljke i mejl sa fiskalnim računom (PDF/JPG/PNG) u prilogu. | WP 6.5+, PHP 7.4+, WooCommerce 8.2+ | 1.0.0 |

---

### Unix – Pošiljka i račun

Mali plugin za prodavnice koje račun i broj pošiljke šalju kupcu ručno, posle pakovanja.

**Šta radi**

- Na ekranu porudžbine (desna kolona) dodaje kutiju **„Pošiljka i račun"**.
- **Broj pošiljke** – upiše se broj, klik na *Sačuvaj i pošalji kupcu*. Broj se čuva na porudžbini (`_upr_tracking_number`) čak i ako slanje ne uspe, a kupac dobija mejl „Vaša porudžbina je poslata".
- **Fiskalni račun** – izabere se fajl (PDF, JPG ili PNG, do 5 MB) i klik na *Pošalji račun kupcu*. Fajl se šalje kao prilog pod imenom `fiskalni-racun-{broj}.{ext}` i **ne čuva se na sajtu** – drži se privremeno u sistemskom temp folderu i briše odmah posle slanja.
- Posle svakog uspešnog slanja upisuje se vreme slanja i dodaje beleška na porudžbinu.
- Oba mejla su prave WooCommerce mejl klase, pa se naslov, zaglavlje, uvodni tekst i dodatni sadržaj menjaju u **WooCommerce → Podešavanja → Mejlovi** („Broj pošiljke" i „Fiskalni račun").
- Iz mejla sa računom uklanjaju se uputstva za plaćanje (npr. „plaćanje pouzećem"), jer im tu nije mesto.

**Kompatibilnost**

- HPOS (High-Performance Order Storage) i stari način čuvanja porudžbina.
- WooCommerce „email improvements" izgled mejlova.

**Šabloni mejlova** se mogu prepisati u temi:

```
yourtheme/woocommerce/emails/upr-shipment.php
yourtheme/woocommerce/emails/upr-invoice.php
yourtheme/woocommerce/emails/plain/upr-shipment.php
yourtheme/woocommerce/emails/plain/upr-invoice.php
```

**Oznake** u naslovu/uvodnom tekstu: `{order_number}`, `{order_date}`, `{site_title}` i (samo mejl o pošiljci) `{tracking_number}`.

**Bezbednost**

- AJAX zahtevi su zaštićeni nonce-om i proverom `edit_shop_order` prava za tu porudžbinu.
- Tip fajla se proverava po stvarnom sadržaju (`wp_check_filetype_and_ext`), ne samo po ekstenziji.

## Instalacija

1. Preuzmite repozitorijum ili samo folder željenog plugina.
2. Zapakujte folder plugina u `.zip` (npr. `unix-posiljka-racun.zip`, sa folderom `unix-posiljka-racun/` unutra).
3. U WordPress-u: **Dodaci → Dodaj novi → Otpremi dodatak**, izaberite `.zip` i aktivirajte.

Ili kopirajte folder direktno u `wp-content/plugins/`.

## Struktura repozitorijuma

```
prometix-wordpress-plugins/
├── <slug-plugina>/            # jedan folder = jedan plugin
│   ├── <slug-plugina>.php     # glavni fajl sa WP headerom
│   ├── includes/              # PHP logika
│   ├── assets/                # JS / CSS
│   └── templates/             # šabloni (mejlovi i sl.)
├── AGENTS.md                  # pravila za AI agente / saradnike
└── README.md
```

## Licenca

Svi pluginovi su objavljeni pod licencom [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html), u skladu sa WordPress ekosistemom.
