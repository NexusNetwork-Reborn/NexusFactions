<?php

declare(strict_types = 1);

namespace Xekvern\Core\Command\Forms;

use libs\form\CustomForm;
use libs\form\element\Label;
use pocketmine\utils\TextFormat;

class RulesForm extends CustomForm {

    /**
     * RulesForm constructor.
     */
    public function __construct() {
        $title = TextFormat::BOLD . TextFormat::AQUA . "Rules";
        $elements = [];
        $elements[] = new Label("Rules",  "Welcome to NexusPE! The rules here consist of guidelines you all must follow to ensure there is a safe community!");
        $elements[] = new Label("Rule1",  "1. Inappropriate behavior will not be acceptable! Spam, sexual slurs, racial slurs, and etc... will not be tolerated! If we find you doing it, you will be muted.");
        $elements[] = new Label("Rule2", "2. Respect others! What I mean by this is that, you must RESPECT other people. This includes there information, race, gender, sexuality, and etc... Breaking this rule will result in a mute.");
        $elements[] = new Label("Rule3", "3. If you are caught glitching or exploiting a bug, you will be banned! Please always inform us when there is a bug, dupe, glitch, and etc... so we can make this server better & greater for the rest of the community!");
        $elements[] = new Label("Rule4", "4. Any sort of hack/client will result in a ban. (This excludes clients such as zLauncher, Lunar, Badlion, etc....)");
        $elements[] = new Label("Rule5", "5. When a staff member asks you to screen share, you will have a time period of 5 minutes to send the code, once that time is up & the code hasn’t been sent, you will be banned for a month. \nNOTE: You can always ask for proof regarding the screen share. If we don’t have proof on why we should screen share, you then you don’t have too screen share. You must ask for the reason/proof if you wish too.\nDO NOT allow other people to screen share you as you would be putting yourself at harm!\nYou have been warned!");
        $elements[] = new Label("Rule6", "6. Any sort of DDOS threats will result in a IP-Ban. We take this seriously! Even a joke about DDOSing will get you IP-Banned. So don’t do it! \nNever give anyone your information. This is the internet & you don’t know who the person is or what his intentions are with your info. Also never click unknown links, don’t trust any links from players, and always ask a staff member for links to places such as voting, Buycraft, etc....");
        parent::__construct($title, $elements);
    }
}