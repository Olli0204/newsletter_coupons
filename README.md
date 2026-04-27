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
| 1.1.1          | 5.2.4 – 5.7.0 |
| 1.0.3          | 5.2.4 – 5.5.3 |
| 1.0.2          | 5.2.4 – 5.5.3 |

## Installation

1. Plugin-Ordner in das Verzeichnis `plugins/` des JTL-Shops kopieren
2. Im Shop-Backend unter **Plugin Manager** → **Newsletter Coupons** installieren
3. Kontakt-E-Mail und Coupon-Werte in den Plugin-Einstellungen konfigurieren
4. E-Mail-Vorlage bei Bedarf im Backend anpassen

## Changelog

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
