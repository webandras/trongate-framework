<?php

declare(strict_types=1);

/**
 * Language Switcher
 */
final class Localization extends Trongate
{
    /**
     * Change website language, store its value in a cookie
     */
    public function language(): void
    {
        $lang = segment(3, 'string');
        setcookie('language', htmlspecialchars($lang), time() + (86400 * 30), '/');
        redirect('');
    }
}
