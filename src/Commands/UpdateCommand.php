<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Events\Events;
use Liaison\Revision\Application;
use Liaison\Revision\Events\UpdateEvents;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * This command starts the update of the project.
 *
 * @codeCoverageIgnore
 */
class UpdateCommand extends BaseCommand
{
    /**
     * The Command's group.
     *
     * @var string
     */
    protected $group = 'Revision';

    /**
     * The Command's name.
     *
     * @var string
     */
    protected $name = 'revision:update';

    /**
     * The Command's usage.
     *
     * @var string
     */
    protected $usage = 'revision:update';

    /**
     * The Command's description.
     *
     * @var string
     */
    protected $description = 'Starts the update of your CodeIgniter4 project.';

    /**
     * Current instance of Application
     *
     * @var \Liaison\Revision\Application
     */
    private $application;

    /**
     * Execute the update of the project.
     *
     * @param array<int|string, string> $params
     *
     * @return void
     */
    public function run(array $params)
    {
        // Boot our application
        $this->application = new Application();

        CLI::write(Application::NAME, 'green');
        CLI::write('Version: ' . CLI::color(Application::VERSION, 'yellow'));
        CLI::write('Run Date: ' . CLI::color(date('D, d F Y, H:i:s') . ' UTC' . date('P'), 'yellow'));
        CLI::newLine();

        $config = $this->application->getConfiguration();

        CLI::write(lang('Revision.loadedConfigurationSettings', [CLI::color(\get_class($config->getConfig()), 'yellow')]));
        CLI::table([
            ['Root Path', $config->rootPath],
            ['Write Path', $config->writePath],
            ['Ignored Directories Count', \count($config->ignoreDirs)],
            ['Ignored Files Count', \count($config->ignoreFiles)],
            ['Allow Gitignore Entry', $config->allowGitIgnoreEntry ? lang('Revision.accessAllowed') : lang('Revision.accessDenied')],
            ['Fall Through to Project', $config->fallThroughToProject ? lang('Revision.accessAllowed') : lang('Revision.accessDenied')],
            ['Consolidator', $config->consolidator],
            ['Upgrader', $config->upgrader],
            ['Pathfinder', $config->pathfinder],
            ['Diff Output Builder', $config->diffOutputBuilder],
            ['Log Handlers Count', \count($config->logHandlers)],
        ], [CLI::color('Key', 'green'), CLI::color('Value', 'green')]);

        CLI::write(lang('Revision.startUpdateText'), 'green');
        CLI::newLine();

        $this->registerCommandLineEvents();
        $this->application->execute();
        $this->unregisterCommandLineEvents();
    }

    /**
     * Presents a choice question for consolidation.
     *
     * @param \Liaison\Revision\Application $app
     *
     * @return bool false to terminate the process
     */
    public function consolidationChoice(Application $app): bool
    {
        $manager = $app->getFileManager();
        $updates = [
            'createdFiles'  => \count($manager->createdFiles),
            'modifiedFiles' => \count($manager->modifiedFiles),
            'deletedFiles'  => \count($manager->deletedFiles),
        ];
        $count = array_sum($updates);

        if (!$count) {
            CLI::write(lang('Revision.emptyFilesToConsolidate'), 'yellow');
            CLI::write(CLI::color('[p] ', 'green') . lang('Revision.proceedAction'));
            CLI::write(CLI::color('[a] ', 'green') . lang('Revision.abortAction'));
            CLI::newLine();

            return 'p' === CLI::prompt(CLI::color(lang('Revision.confirmQuestionPrompt'), 'yellow'), ['p', 'a']);
        }

        $files = 1 === $count ? lang('Revision.fileSingular') : lang('Revision.filePlural');
        CLI::write(lang('Revision.someFilesToConsolidate', [$count, $files]), 'yellow');
        CLI::write(CLI::color('[p] ', 'green') . lang('Revision.proceedAction'));
        CLI::write(CLI::color('[l] ', 'green') . lang('Revision.listAllToConsolidate'));
        CLI::write(CLI::color('[c] ', 'green') . lang('Revision.listCreatedFilesOnly', [$updates['createdFiles']]));
        CLI::write(CLI::color('[m] ', 'green') . lang('Revision.listModifiedFilesOnly', [$updates['modifiedFiles']]));
        CLI::write(CLI::color('[d] ', 'green') . lang('Revision.listDeletedFilesOnly', [$updates['deletedFiles']]));
        CLI::write(CLI::color('[a] ', 'green') . lang('Revision.abortAction'));
        CLI::newLine();
        unset($count, $files);

        switch (CLI::prompt(CLI::color(lang('Revision.confirmQuestionPrompt'), 'yellow'), ['p', 'l', 'c', 'm', 'd', 'a'])) {
            case 'p':
                return true;
            case 'l':
                $this
                    ->listFiles($manager->createdFiles, lang('Revision.filesCreatedLabel'), $tbody)
                    ->listFiles($manager->modifiedFiles, lang('Revision.filesModifiedLabel'), $tbody)
                    ->listFiles($manager->deletedFiles, lang('Revision.filesDeletedLabel'), $tbody)
                    ->renderFiles($tbody)
                ;

                break;
            case 'c':
                $this
                    ->listFiles($manager->createdFiles, lang('Revision.filesCreatedLabel'), $tbody)
                    ->renderFiles($tbody)
                ;

                break;
            case 'm':
                $this
                    ->listFiles($manager->modifiedFiles, lang('Revision.filesModifiedLabel'), $tbody)
                    ->renderFiles($tbody)
                ;

                break;
            case 'd':
                $this
                    ->listFiles($manager->deletedFiles, lang('Revision.filesDeletedLabel'), $tbody)
                    ->renderFiles($tbody)
                ;

                break;
            case 'a':
                return false;
        }

        // If we reached here, then user has chosen to render files. So,
        // return this choice question until we get a returning boolean.
        return $this->consolidationChoice($app);
    }

    /**
     * Presents a choice question for conflicts resolution and execution.
     *
     * @param \Liaison\Revision\Application $app
     *
     * @return bool
     */
    public function conflictsChoice(Application $app): bool
    {
        $manager   = $app->getFileManager();
        $conflicts = [
            'created'  => \count($manager->conflicts['created']),
            'modified' => \count($manager->conflicts['modified']),
            'deleted'  => \count($manager->conflicts['deleted']),
        ];
        $count = array_sum($conflicts);

        if (!$count) {
            // No conflicts, just exit the event.
            return true;
        }

        $files = 1 === $count ? lang('Revision.fileSingular') : lang('Revision.filePlural');
        CLI::newLine();
        CLI::write(lang('Revision.someFilesInConflict', [$count, $files]), 'yellow');
        CLI::write(CLI::color('[l] ', 'green') . lang('Revision.listAllInConflict'));
        CLI::write(CLI::color('[o] ', 'green') . lang('Revision.conflictsOverwriteAll'));
        CLI::write(CLI::color('[s] ', 'green') . lang('Revision.conflictsSkipAll'));
        CLI::write(CLI::color('[r] ', 'green') . lang('Revision.conflictsEachResolve'));
        CLI::write(CLI::color('[a] ', 'green') . lang('Revision.abortAction'));
        CLI::newLine();
        unset($count, $files);

        switch (CLI::prompt(CLI::color(lang('Revision.confirmQuestionPrompt'), 'yellow'), ['l', 'o', 's', 'r', 'a'])) {
            case 'l':
                $this
                    ->listFiles($manager->conflicts['created'], lang('Revision.filesCreatedLabel'), $tbody)
                    ->listFiles($manager->conflicts['modified'], lang('Revision.filesModifiedLabel'), $tbody)
                    ->listFiles($manager->conflicts['deleted'], lang('Revision.filesDeletedLabel'), $tbody)
                    ->renderFiles($tbody)
                ;

                break;
            case 'o':
                foreach ($manager->conflicts as $status => $files) {
                    foreach ($files as $file) {
                        if (!$this->conflictsResolutionExecution($file, $status)) {
                            CLI::newLine();

                            return false;
                        }
                    }
                }
                CLI::newLine();

                return true;
            case 's':
                CLI::newLine();

                return true;
            case 'r':
                foreach ($manager->conflicts as $status => $files) {
                    foreach ($files as $file) {
                        if (!$this->conflictsResolutionChoice($file, $status)) {
                            CLI::newLine();

                            return false;
                        }
                    }
                }
                CLI::newLine();

                return true;
            case 'a':
                CLI::newLine();

                return false;
        }

        return $this->conflictsChoice($app);
    }

    /**
     * Writes a nice message before terminating.
     *
     * @param \Liaison\Revision\Application $app
     *
     * @return bool
     */
    public function preTerminationMessage(Application $app)
    {
        CLI::newLine();
        CLI::write(lang('Revision.logsLocationMessage'));
        CLI::write(realpath($app->getConfiguration()->writePath . 'revision/logs') . \DIRECTORY_SEPARATOR, 'yellow');

        return true;
    }

    /**
     * Registers our own events into the application before it executes the process.
     *
     * @return void
     */
    protected function registerCommandLineEvents()
    {
        Events::on(UpdateEvents::PRECONSOLIDATE, [$this, 'consolidationChoice'], 1);
        Events::on(UpdateEvents::POSTCONSOLIDATE, [$this, 'conflictsChoice'], 1);
        Events::on(UpdateEvents::TERMINATE, [$this, 'preTerminationMessage'], 1);
    }

    /**
     * Unregisters CLI events so that it won't pollute the application.
     *
     * @return void
     */
    protected function unregisterCommandLineEvents()
    {
        Events::removeListener(UpdateEvents::PRECONSOLIDATE, [$this, 'consolidationChoice']);
        Events::removeListener(UpdateEvents::POSTCONSOLIDATE, [$this, 'conflictsChoice']);
        Events::removeListener(UpdateEvents::TERMINATE, [$this, 'preTerminationMessage']);
    }

    /**
     * Presents individual choice questions for each file in conflict.
     *
     * @param string $file
     * @param string $status
     *
     * @return bool
     */
    protected function conflictsResolutionChoice(string $file, string $status): bool
    {
        CLI::newLine();
        CLI::write(lang('Revision.conflicts' . ucfirst($status) . 'File'), 'yellow');
        CLI::write($file);
        CLI::newLine();
        CLI::write(CLI::color('[d] ', 'green') . lang('Revision.conflictsDisplayDiff'));
        CLI::write(CLI::color('[o] ', 'green') . lang('Revision.conflictsOverwriteOne'));
        CLI::write(CLI::color('[s] ', 'green') . lang('Revision.conflictsSkipOne'));
        CLI::write(CLI::color('[a] ', 'green') . lang('Revision.abortAction'));
        CLI::newLine();

        switch (CLI::prompt(CLI::color(lang('Revision.confirmQuestionPrompt'), 'yellow'), ['d', 'o', 's', 'a'])) {
            case 'd':
                $diff        = explode("\n", $this->application->calculateDiff($file));
                $coloredDiff = [];

                foreach ($diff as $line) {
                    if (0 === mb_strpos($line, '-')) {
                        $coloredDiff[] = CLI::color($line, 'red');
                    } elseif (0 === mb_strpos($line, '+')) {
                        $coloredDiff[] = CLI::color($line, 'green');
                    } else {
                        $coloredDiff[] = $line;
                    }
                }

                CLI::newLine();
                CLI::write(lang('Revision.displayDiffPrompt', [CLI::color($file, 'yellow')]));
                CLI::write(trim(implode("\n", $coloredDiff)));

                break;
            case 'o':
                return $this->conflictsResolutionExecution($file, $status);
            case 's':
                return true;
            case 'a':
                return false;
        }

        return $this->conflictsResolutionChoice($file, $status);
    }

    /**
     * Executes the mode of resolution for a conflict.
     *
     * @param string $file
     * @param string $status
     *
     * @return bool
     */
    protected function conflictsResolutionExecution(string $file, string $status): bool
    {
        $fs  = $this->application->getFilesystem();
        $new = $this->application->workspace . 'newSnapshot' . \DIRECTORY_SEPARATOR . $file;
        $own = $this->application->getConfiguration()->rootPath . $file;

        try {
            // Status is hard coded from File Manager's conflicts index
            $status = ucfirst($status);

            if ('Deleted' === $status) {
                $fs->chmod($own, 0777);
                $fs->remove($own);
            } else {
                $fs->copy($new, $own, true);
            }

            return true;
        } catch (IOExceptionInterface $e) {
            $this->application->getLogManager()->logMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Render a pretty table in the terminal.
     *
     * @param array<int|string> $files
     *
     * @return void
     */
    protected function renderFiles(array $files)
    {
        CLI::newLine();

        if (empty($files)) {
            CLI::write(lang('Revision.emptyFilesToRender'), 'yellow');
            CLI::newLine();

            return;
        }

        CLI::table($files, [
            CLI::color(lang('Revision.renderFileLabel'), 'green'),
            CLI::color(lang('Revision.renderStatusLabel'), 'green'),
            CLI::color(lang('Revision.renderDiffLabel'), 'green'),
        ]);
        CLI::newLine();

        unset($files);
    }

    /**
     * Creates an array of files with its status and diff count.
     *
     * @param string[]               $files
     * @param string                 $status
     * @param null|array<int|string> $output
     *
     * @return $this
     */
    protected function listFiles(array $files, string $status, ?array &$output)
    {
        $output = $output ?? [];

        foreach ($files as $file) {
            $diffCount = \count(explode("\n", $this->application->calculateDiff($file)));

            if ($diffCount >= 3) {
                // don't count the diff labels and the final trailing whitespace
                $diffCount -= 3;
            }

            $output[] = [$file, $status, $diffCount];
        }

        return $this;
    }
}
