<?php declare(strict_types=1);

namespace Plugin\newsletter_coupons;

use JTL\Checkout\Kupon;
use JTL\Events\Dispatcher;
use JTL\Link\LinkInterface;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class Bootstrap
 * @package Plugin\newsletter_coupons
 */
class Bootstrap extends Bootstrapper
{
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        if ((string)$this->getDB()->select('tplugineinstellungen', 'cName', 'coupon_set_is_active')->cWert === 'on') {
            $dispatcher->hookInto(
                \HOOK_NEWSLETTER_PAGE_EMPFAENGERFREISCHALTEN,
                function (array $args) {
                    $logger = $this->getPlugin()->getLogger();
                    $logger->warning('Newsletter Coupon wurde erstellt');

                    $value       = $this->getPlugin()->getConfig()->getValue('coupon_set_Wert');
                    $minValue    = $this->getPlugin()->getConfig()->getValue('coupon_set_Mindestbestellwert');
                    $duration    = $this->getPlugin()->getConfig()->getValue('coupon_set_DauerTage');
                    $contactMail = $this->getPlugin()->getConfig()->getValue('coupon_set_ContactMail');
                    $ID          = $this->getPlugin()->getID();

                    $cEmail = $args['oNewsletterEmpfaenger']->cEmail;

                    if (strlen($cEmail) === 0 || !filter_var($cEmail, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }

                    if (!$this->alreadysignedup($cEmail)) {
                        return;
                    }

                    if ($this->alreadyassigned($cEmail)) {
                        $allocation = $this->getDB()->select('newslettercoupon_allocation', 'mailAddress', $cEmail);
                        if ($allocation === null) {
                            return;
                        }
                        $kuponId = $allocation->couponID;
                    } else {
                        $kuponCode = $this->createCoupon($cEmail, $value, $minValue, $duration);
                        $kupon     = new Kupon();
                        $kuponArr  = $kupon->getByCode((string)$kuponCode);
                        if ($kuponArr === null) {
                            return;
                        }
                        $kuponId = $kuponArr->kKupon;

                        $allocation              = new \stdClass();
                        $allocation->mailAddress = $cEmail;
                        $allocation->couponID    = $kuponId;
                        $this->getDB()->insert('newslettercoupon_allocation', $allocation);

                        $langDe              = new \stdClass();
                        $langDe->kKupon      = $kuponId;
                        $langDe->cISOSprache = 'ger';
                        $langDe->cName       = 'Newsletterkupon: ' . $cEmail;

                        $langEn              = new \stdClass();
                        $langEn->kKupon      = $kuponId;
                        $langEn->cISOSprache = 'eng';
                        $langEn->cName       = 'Newsletter Coupon: ' . $cEmail;

                        $this->getDB()->insert('tkuponsprache', $langDe);
                        $this->getDB()->insert('tkuponsprache', $langEn);
                    }

                    $couponRow = $this->getDB()->select('tkupon', 'kKupon', $kuponId);

                    $mailContent               = new \stdClass();
                    $mailContent->tkuponCode   = $couponRow->cCode;
                    $mailContent->tVal         = $value;
                    $mailContent->tminVal      = $minValue;
                    $mailContent->tduration    = $duration;
                    $mailContent->tContactMail = $contactMail;

                    $this->sendMail($cEmail, $mailContent, $ID);
                }
            );
        }
    }

    private function sendMail(string $cEmail, object $data, int $ID): void
    {
        $mailer = Shop::Container()->get(\JTL\Mail\Mailer::class);
        $mail   = new \JTL\Mail\Mail\Mail();
        $mail   = $mail->createFromTemplateID('kPlugin_' . $ID . '_newslettercoupons', $data);
        $mail->setToMail($cEmail);
        $mailer->send($mail);
    }

    private function alreadysignedup(string $cEmail): bool
    {
        $result = $this->getDB()->select('tnewsletterempfaenger', 'cEmail', $cEmail);
        return isset($result->cEmail);
    }

    private function alreadyassigned(string $cEmail): bool
    {
        $result = $this->getDB()->select('newslettercoupon_allocation', 'mailAddress', $cEmail);
        return isset($result->mailAddress);
    }

    private function createCoupon(string $cEmail, $val, $minVal, $duration): string
    {
        $oCoupon = new Kupon();
        $oCoupon->setName('Newsletter Kupon: ' . $cEmail);
        $oCoupon->setWert($val);
        $oCoupon->setWertTyp('festpreis');
        $oCoupon->setKuponTyp('standard');
        $oCoupon->setGanzenWKRabattieren(1);
        $oCoupon->setZusatzgebuehren('N');
        $oCoupon->setMindestbestellwert($minVal);
        $oCoupon->setCode($oCoupon->generateCode());
        $oCoupon->setVerwendungen(1);
        $oCoupon->setVerwendungenProKunde(0);
        $oCoupon->setArtikel('');
        $oCoupon->setKategorien('-1');
        $oCoupon->setKunden('-1');

        $startDate = date_create()->format('Y-m-d H:i:00');
        $oCoupon->setGueltigAb($startDate);
        $oCoupon->setErstellt($startDate);

        if (isset($duration)) {
            $actualDate = date_create();
            $endofDay   = date_time_set($actualDate, 23, 59, 59);
            $setDays    = new \DateInterval('P' . $duration . 'D');
            $oCoupon->setGueltigBis(date_add($endofDay, $setDays)->format('Y-m-d H:i:s'));
        }

        $oCoupon->setAktiv('Y');
        $code = $oCoupon->getCode();
        $oCoupon->save();

        return $code;
    }

    public function installed(): void
    {
    }

    public function updated($oldVersion, $newVersion): void
    {
    }

    public function uninstalled(bool $deleteData = true): void
    {
    }

    public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool
    {
        return false;
    }

    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        return '';
    }
}
