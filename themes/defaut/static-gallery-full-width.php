<?php
if (!defined('PLX_ROOT')) {
    exit;
}

const FULL_WIDTH = true;
include preg_replace('@-full-width\.php$@', '.php', basename(__FILE__));
