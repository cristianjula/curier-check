<?php

if (!defined('CURIERO_PLUGIN_PATH')) {
    define('CURIERO_PLUGIN_PATH', realpath('../..'));
}

class CurieRO_Emergency_Mode
{
    /**
     * Trigger the emergency mode.
     *
     * @return void
     */
    public static function trigger(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            static::abort();
        }

        if (($_REQUEST['key'] ?? '') !== 'digitaln') {
            static::abort();
        }

        if (strpos(CURIERO_PLUGIN_PATH, 'emergency') !== false) {
            static::abort('CurieRO folder already renamed.', 209);
        }

        if (@rename(CURIERO_PLUGIN_PATH, CURIERO_PLUGIN_PATH . '-emergency')) {
            static::abort('CurieRO folder renamed to emergency state.', 200);
        } else {
            static::abort('Could not rename folder.', 500);
        }
    }

    /**
     * Abort the execution of the script with a message and a code.
     *
     * @param string $message
     * @param int $code
     * @return void
     */
    private static function abort(string $message = '', int $code = 404): void
    {
        http_response_code($code);
        exit($message);
    }
}

CurieRO_Emergency_Mode::trigger();
