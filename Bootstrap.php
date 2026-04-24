<?php declare(strict_types=1);

namespace Plugin\newsletter_coupons;

use JTL\Alert\Alert;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Product\Artikel;
use JTL\Consent\Item;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Link\LinkInterface;
use JTL\Plugin\Bootstrapper;
use JTL\Router\Router;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use \JTL\Backend\Notification;
use Laminas\Diactoros\ServerRequestFactory;
use function Functional\first;
use Rapidmail\ApiClient\Client;
use Rapidmail\ApiClient\Exception\ApiClientException;
use JTL\Plugin\PluginInterface;
use JTL\Mail\Mailer;
use JTL\Mail\Mail;
use JTL\Checkout\Kupon;


/**
 * Class Bootstrap
 * @package Plugin\jtl_test
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @var TestHelper
     */

    /**
     * @inheritdoc
     */
    public function boot(Dispatcher $dispatcher)
    {
        parent::boot($dispatcher);

        if((String)$this->getDB()->select('tplugineinstellungen', 'cName', 'coupon_set_is_active')->cWert == "on"){
  
            $dispatcher->hookInto(
                \HOOK_NEWSLETTER_PAGE_EMPFAENGERFREISCHALTEN,
            function (array $args) {

                    $logger = $this->getPlugin()->getLogger();

                    $logger->warning('Newsletter Coupon wurde erstellt');

                    $value = $this->getPlugin()->getConfig()->getValue('coupon_set_Wert');
                    $minValue = $this->getPlugin()->getConfig()->getValue('coupon_set_Mindestbestellwert');
                    $duration = $this->getPlugin()->getConfig()->getValue('coupon_set_DauerTage');
                    $ID = $this->getPlugin()->getID();


                    $orgNewsletterEmpfaenger = $args['oNewsletterEmpfaenger'];
                    $cEmail = $orgNewsletterEmpfaenger->cEmail;
                    $kuponname_de = "Newsletterkupon";
                    $kuponname_en = "Newsletter Coupon";


                    if (strlen($cEmail) > 0) {
                        if (filter_var($cEmail, FILTER_VALIDATE_EMAIL)){
                            if($this->alreadysignedup($cEmail)){
                
                                $kuponId;
                
                                if($this->alreadyassigned($cEmail)) {
                                    $email = $cEmail;
                
                                    $oNewsletterEmpfaenger = $this->getDB()->select('newslettercoupon_allocation', 'mailAddress', $cEmail);
                
                                    $kuponId = $oNewsletterEmpfaenger->couponID;
                                }
                                else{
                
                                    $kuponCode = $this->createCoupon($cEmail, $value, $minValue, $duration);
                
                                    $Kupon = new Kupon();
                                    $Kupon_arr = $Kupon->getByCode((String)$kuponCode);
                                    $kuponId = $Kupon_arr->kKupon;
                
                                    $couponAllocation = new \stdClass();
                                    $couponAllocation->mailAddress = $cEmail;
                                    $couponAllocation->couponID = $kuponId;
                                    $this->getDB()->insert("newslettercoupon_allocation", $couponAllocation);
                
                                    $Lang_de = new \stdClass();
                                    $Lang_de->kKupon = $kuponId;
                                    $Lang_de->cISOSprache = 'ger';
                                    $Lang_de->cName = ($kuponname_de .': ' . $cEmail);
                
                
                                    $Lang_en = new \stdClass();
                                    $Lang_en->kKupon = $kuponId;
                                    $Lang_en->cISOSprache = 'eng';
                                    $Lang_en->cName = ($kuponname_en . ': ' . $cEmail);
                
                                    $this->getDB()->insert("tkuponsprache", $Lang_de);
                                    $this->getDB()->insert("tkuponsprache", $Lang_en);
                
                                }
                
                                $couponCode = $this->getDB()->select('tkupon','kKupon', $kuponId);
                
                                $mailContent = new \stdClass();
                                $mailContent->tkuponCode = $couponCode->cCode;
                                $mailContent->tVal = $value;
                                $mailContent->tminVal = $minValue;
                                $mailContent->tduration = $duration;
                
                                $this->sendMail($cEmail, $mailContent, $ID);
                
                
                            }
                
                        }
                    }
                }
            );
        } 

    }

    private function sendMail($cEmail, $data, $ID){

        
        $mailer = Shop::Container()->get(\JTL\Mail\Mailer::class);
        
        $mail   = new \JTL\Mail\Mail\Mail();
    
        $mail = $mail->createFromTemplateID('kPlugin_'. $ID . '_newslettercoupons', $data);
        $mail->setToMail($cEmail);
        $mailer->send($mail);
    }
    
    
    private function alreadysignedup($cEmail){
        $oNewsletterEmpfaenger = $this->getDB()->select('tnewsletterempfaenger', 'cEmail', $cEmail);
    
        return(isset($oNewsletterEmpfaenger->cEmail));
    }
    
    private function alreadyassigned($cEmail){
        $oNewsletterEmpfaenger = $this->getDB()->select('newslettercoupon_allocation', 'mailAddress', $cEmail);
    
        return (isset($oNewsletterEmpfaenger->mailAddress));
    }


    private function createCoupon($cEmail, $val, $minVal, $duration){
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
    
        $startDate = date_create()->format('Y-m-d H:i' . ':00');
        $oCoupon->setGueltigAb($startDate);
        $oCoupon->setErstellt($startDate);
    
        if (isset($duration)){
            $actualDate = date_create();
            $endofDay = date_time_set($actualDate, 23,59,59);
            $setDays = new \DateInterval('P' . $duration . 'D');
            $oCoupon->setGueltigBis(date_add($endofDay, $setDays)->format('Y-m-d H:i:s'));    
        }
    
        $oCoupon->setAktiv('Y');

        $code = $oCoupon->getCode();
    
        $oCoupon->save();
    
        return $code;
    }


    /**
     * @param array $args
     */
    public function addConsentItem(array $args): void
    {  
    }

    /**
     * @inheritdoc
     */
    public function installed(): void
    {   
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion): void
    {
    }

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
    }

    /**
     * @inheritdoc
     */
    public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool
    {
    }

    /**
     * @inheritdoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {  
    }
}
