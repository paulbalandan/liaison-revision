<?php

namespace Tests\Support\Configurations;

use Liaison\Revision\Config\Revision;

class SimpleConfig extends Revision
{
    public $ignoredDirs = [
        'app/Config',
    ];

    public $ignoredFiles = [
        'app/.htaccess',
    ];
}
