<?php

namespace Xekvern\Core\Server\Watchdog;

interface Reasons {

    const HACK = "Hackity bad";

    const DDOS_THREATS = "DDoS threats";

    const BAN_EVADING = "Ban evading";

    const ALTING = "Alting/Impersonating";

    const ADVERTISING = "Advertising";

    const IRL_SCAMMING = "Scamming in real life stuff";

    const EXPLOITING = "Exploiting a bug or glitch";

    const SPAMMING = "Spamming";

    const STAFF_DISRESPECT = "Staff disrespect";

    const RACIAL_SLURS = "Racial slurs";
}