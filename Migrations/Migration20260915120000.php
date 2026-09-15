<?php

declare(strict_types=1);

namespace Plugin\newsletter_coupons\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * "Plugin Aktiv" ist ab 1.1.4 wieder eine Checkbox. JTL speichert dafür 'on' (angehakt) bzw. '' (abgewählt).
 * Die Selectbox aus 1.1.2/1.1.3 hat 'Y'/'N' gespeichert – diese Werte werden hier umgewandelt.
 */
class Migration20260915120000 extends Migration implements IMigration
{
    public function up(): void
    {
        $this->execute(
            "UPDATE tplugineinstellungen SET cWert = 'on' WHERE cName = 'coupon_set_is_active' AND cWert = 'Y'"
        );
        $this->execute(
            "UPDATE tplugineinstellungen SET cWert = '' WHERE cName = 'coupon_set_is_active' AND cWert = 'N'"
        );
    }

    public function down(): void
    {
        $this->execute(
            "UPDATE tplugineinstellungen SET cWert = 'Y' WHERE cName = 'coupon_set_is_active' AND cWert = 'on'"
        );
        $this->execute(
            "UPDATE tplugineinstellungen SET cWert = 'N' WHERE cName = 'coupon_set_is_active' AND cWert = ''"
        );
    }
}
