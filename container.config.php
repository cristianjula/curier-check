<?php

use function CurieRO\DI\get;
use function CurieRO\DI\value;
use function CurieRO\DI\autowire;

return [
    // Variables
    'is_valid_auth' => value(false),
    'woocommerce_hpos_enabled' => value(false),

    // Autowires
    CurieRO_Plugin_Update::class => autowire(),
    CurieRO_Plugin_DB_Update::class => autowire(),
    CurieRO_Plugin_Internals_Update::class => autowire(),
    CurieRO_Settings::class => autowire(),
    CurieRO_Additional_Hooks::class => autowire(),
    CurieRO_Email_Methods::class => autowire(),
    CurieRO_Admin_Report::class => autowire(),
    CurieRO_Admin_Menu::class => autowire(),
    CurieRO_Plugin_States::class => autowire(),

    // Aliases
    'shipping_methods' => get(CurieRO_Shipping_Methods_Loader::class),
    'printing_methods' => get(CurieRO_Printing_Methods_Loader::class),
    'addon_methods' => get(CurieRO_Addon_Methods_Loader::class),
];
