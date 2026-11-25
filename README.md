# Newsletter Coupons

JTL-Shop 5 Plugin, das bei erfolgreicher Newsletter-Anmeldung automatisch einen personalisierten Rabattcoupon erstellt und per E-Mail versendet.

## Funktionsweise

Sobald ein Kunde den Double-Opt-In-Link bestätigt, wird automatisch:
1. Ein einzigartiger Gutscheincode im JTL-Shop erstellt
2. Die Zuordnung (E-Mail → Coupon) in der Datenbank gespeichert
3. Eine E-Mail mit dem Coupon-Code an den Kunden versendet

Meldet sich derselbe Kunde erneut an, erhält er denselben bestehenden Code erneut per E-Mail — es wird kein doppelter Coupon erstellt.

## Einstellungen

| Einstellung       | Beschreibung                                              | Standard |
|-------------------|-----------------------------------------------------------|----------|
| Plugin Aktiv      | Aktiviert oder deaktiviert das Plugin                     | —        |
| Kupon Wert        | Rabattbetrag in Euro                                      | 5        |
| Mindestbestellwert| Mindestbestellwert zur Einlösung des Kupons               | 50       |
| Gültigkeit        | Gültigkeitsdauer des Kupons in Tagen                      | 31       |
| Kontakt-E-Mail    | Kontaktadresse, die in der Coupon-E-Mail angezeigt wird   | —        |

## E-Mail-Vorlage

Das Plugin legt beim Installieren eine E-Mail-Vorlage an (**Automatischer Newsletterkuponversand**), die im Shop-Backend unter **Einstellungen → E-Mail-Vorlagen** angepasst werden kann.

Verfügbare Platzhalter in der Vorlage:

| Platzhalter                       | Inhalt                        |
|-----------------------------------|-------------------------------|
| `{$oPluginMail->tkuponCode}`      | Generierter Gutscheincode     |
| `{$oPluginMail->tVal}`            | Rabattbetrag                  |
| `{$oPluginMail->tminVal}`         | Mindestbestellwert            |
| `{$oPluginMail->tduration}`       | Gültigkeitsdauer in Tagen     |
| `{$oPluginMail->tContactMail}`    | Konfigurierte Kontakt-E-Mail  |
| `{$Firma->cName}`                 | Name des Shops                |

## Kompatibilität

| Plugin-Version | JTL-Shop      |
|----------------|---------------|
| 1.1.8          | 5.2.4 – 5.8.5 |
| 1.1.7          | 5.2.4 – 5.8.4 |
| 1.1.6          | 5.2.4 – 5.8.3 |
| 1.1.5          | 5.2.4 – 5.8.2 |
| 1.1.4          | 5.2.4 – 5.8.0 |
| 1.1.3          | 5.2.4 – 5.8.0 |
| 1.1.1          | 5.2.4 – 5.7.0 |
| 1.0.3          | 5.2.4 – 5.5.3 |
| 1.0.2          | 5.2.4 – 5.5.3 |

## Installation

1. Plugin-Ordner in das Verzeichnis `plugins/` des JTL-Shops kopieren
2. Im Shop-Backend unter **Plugin Manager** → **Newsletter Coupons** installieren
3. Kontakt-E-Mail und Coupon-Werte in den Plugin-Einstellungen konfigurieren
4. E-Mail-Vorlage bei Bedarf im Backend anpassen

## Changelog

### 1.1.8
- Kompatibilität mit JTL-Shop 5.8.5 geprüft: die Core-Änderungen 5.8.4 → 5.8.5 (Sicherheitsupdate) betreffen nur Backend (Benutzersprache, Länderverwaltung, OPC-Editor, Dateimanager, Weiterleitungsprüfung), die Kodierung von Artikelnamen in der Bestellung und phpseclib; keine vom Plugin genutzte API und keine NOVA-Templates sind betroffen. MaxShopVersion auf 5.8.5 angehoben

### 1.1.7
- Kompatibilität mit JTL-Shop 5.8.4 geprüft: die Core-Änderungen 5.8.3 → 5.8.4 (Sicherheitsupdate) betreffen nur Lieferadressen-Prüfung im Checkout, OPC-Vorschau-Token, IP-Ermittlung hinter Proxys, CSRF-Schutz im Checkout, Rate-Limits für Wawi-Login und Backend, Backend-Benutzer- und Einstellungsverwaltung sowie Bibliotheks-Updates (Guzzle 7.15, Smarty 5.8); keine vom Plugin genutzte API und keine überschriebenen NOVA-Templates sind betroffen. MaxShopVersion auf 5.8.4 angehoben

### 1.1.6
- Kompatibilität mit JTL-Shop 5.8.3 geprüft: die Core-Änderungen 5.8.2 → 5.8.3 (Sicherheits- und Performance-Update) betreffen nur Backend-Benutzerverwaltung/2FA/Rechteprüfung, Backend-Benachrichtigungen, das Template-Änderungsprotokoll, den Wawi-Bildabgleich, die Zahlungsart-Prüfung im Checkout sowie die SQL von Preissortierung und Filtern (MariaDB 10.11); NOVA-Templates und Plugin-API sind unverändert. MaxShopVersion auf 5.8.3 angehoben

### 1.1.5
- Kompatibilität mit JTL-Shop 5.8.2 geprüft: die Core-Änderungen 5.8.0 → 5.8.2 (Sicherheitsupdate) betreffen nur Consent-Manager, RMA, Sonderpreis-Berechnung, Banner/ImageMap, PLZ-Autovervollständigung und Backend-Login; NOVA-Templates und Plugin-API sind unverändert. MaxShopVersion auf 5.8.2 angehoben

### 1.1.4
- Einstellung „Plugin Aktiv" ist wieder eine Checkbox. Die Selectbox aus 1.1.2 beruhte auf einer falschen Annahme: der Core schreibt für eine abgewählte Checkbox einen Leerstring, das Abschalten funktioniert also. Migration wandelt gespeicherte Werte `Y`/`N` in `on`/leer um.

### 1.1.3
- Kompatibilität mit JTL-Shop 5.8.0 geprüft (Bootstrapper, Kupon, Mail, Migration, Hook 39 und E-Mail-Vorlagen unverändert), MaxShopVersion auf 5.8.0 angehoben
- Prüfung nach `Kupon::getByCode()` korrigiert: die Methode liefert `false` statt `null`, wenn der Code nicht gefunden wird

### 1.1.1
- Kontakt-E-Mail-Platzhalter auch in Plaintext-E-Mail-Vorlagen ergänzt

### 1.1.0
- Neues Plugin-Setting: Kontakt-E-Mail (`coupon_set_ContactMail`)
- Hardcodiertes Shop-Branding durch dynamische Platzhalter ersetzt
- Englisches E-Mail-Template: „Tage" → „Days" korrigiert
- 15+ unbenutzte Imports entfernt
- Fehlende Return-Typen ergänzt
- Uninitialisierte Variable `$kuponId` behoben
- Null-Check nach `getByCode()` ergänzt
- Datenbank-Migration auf InnoDB und utf8mb4 umgestellt
- MaxShopVersion auf 5.7.0 erhöht

### 1.0.3
- Anpassungen für JTL-Shop 5.4.0
- Keine Nutzung von Plugin-Hooks mehr

### 1.0.2
- Initiales Release
