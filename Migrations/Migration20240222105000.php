<?php
namespace Plugin\newsletter_coupons\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20240222105000 extends Migration implements IMigration {
    public function up() {
        $this->execute(
        "CREATE TABLE `newslettercoupon_allocation` (`allocationID` INT(11) AUTO_INCREMENT, `mailAddress` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL, `couponID` INT, PRIMARY KEY (`allocationID`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;"
        );
    }
    public function down() {
        if($this->doDeleteData()){
            $this->execute('DROP TABLE IF EXISTS `newslettercoupon_allocation`');
        }
    }
}