<?php

declare(strict_types=1);

/**
 * Default homepage class serving as the entry point for public website access.
 * Renders the initial landing page as configured in the framework settings.
 */
final class Welcome extends Trongate
{
    /**
     * Renders the (default) homepage for public access.
     */
    public function index(): void
    {
        $data = [
            'view_module' => 'welcome',
            'view_file' => 'default_homepage',
        ];

        $this->templates->public($data);
    }
}
