<?php

namespace Liaison\Revision\Commands;

use CodeIgniter\Config\DotEnv;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\CIUnitTestCase;

class WriteGitignoreEntryTest extends CIUnitTestCase
{
    private $streamFilter;
    private $origGitignore   = ROOTPATH . '.gitignore';
    private $backupGitignore = ROOTPATH . 'backup.gitignore';

    protected function setUp(): void
    {
        parent::setUp();
        CITestStreamFilter::$buffer = '';
        $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');

        if (is_file($this->origGitignore)) {
            copy($this->origGitignore, $this->backupGitignore);
        }

        $dotenv = new DotEnv(SUPPORTPATH . 'Environments');
        $dotenv->load();
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        if (is_file($this->origGitignore)) {
            @unlink($this->origGitignore);
        }

        if (is_file($this->backupGitignore)) {
            rename($this->backupGitignore, $this->origGitignore);
        }
    }

    protected function getBuffer(): string
    {
        return CITestStreamFilter::$buffer;
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDeniedWriteToGitignore()
    {
        $_ENV['revision.allowGitIgnoreEntry'] = false;
        command('revision:gitignore');

        $this->assertStringContainsString('not allowed to write entries to `.gitignore`.', $this->getBuffer());
        unset($_ENV['revision.allowGitIgnoreEntry']);
    }
}
