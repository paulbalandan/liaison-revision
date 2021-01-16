# Configuration

## Introduction

**Revision** is set to run using the configuration settings defined by `Liaison\Revision\Config\Revision`
class. This class contains the core settings needed to efficiently run the update process. To create a
personalised setting for your application, you can make your own copy by using the command:

    php spark revision:config

## Settings Defined

**public string `$rootPath`**

This is the path to the project's root directory. This defaults to `ROOTPATH`.

**public string `$writePath`**

This is the path to the project's writable directory where Revision will write its logs.
Defaults to `WRITEPATH`.

**public array\<string\> `$ignoreDirs`**

This accepts a single string array of directories to ignore during the updates. These directories
can include the top directories, like `app/`, `public/`, or its subdirectories.

**public array\<string\> `$ignoreFiles`**

This accepts a single string array of files to explicitly ignore during the updates.

**IMPORTANT**: The strings provided to `$ignoreDirs` or `$ignoreFiles` arrays should be in absolute paths
and must point to the **source** and not the project-equivalent path. For example, if you are ignoring
spark updates, you should add `spark` as `ROOTPATH . vendor/codeigniter4/framework/spark` in the
`$ignoreFiles` array.

**public bool `$allowGitIgnoreEntry`**

This boolean flag gives permission to Revision to write an entry to your `.gitignore` file so that Revision's
files would not be tracked by Git. This defaults to `true`. Please refer to [Temporary Files Source Control](overview.md#temporary-files-source-control).

You can, however, override this option at runtime when using the `revision:gitignore` command by using the
`--allow-entry` or `--disallow-entry` option flags.

**public bool `$fallThroughToProject`**

At heart, Revision compares the current copy of the framework with the upcoming copy from the updates. If no
changes are detected, then no updates will happen. However, there may be cases wherein you need to check this
upcoming updates against your copy. This comparison is especially handy when you are at times using the
unstable version (i.e., the development branch of the framework) to check in the latest updates. Your local
copy may be locked at, let's say, v4.0.4 and your vendor copy is using the latest changes of the development
branch. It is expected then that for every update no to little changes will be detected for the framework
but against your copy there is a massive change.

This setting now comes into play. Defaults to `true`, this flag allows Revision to optionally check your local
copy for comparison against the upcoming updates to ensure it is always updated. Then after getting your
updates, you may turn it off for a while. Simple as that.

**public int `$retries`**

During the consolidation of updates, there are times where you will get conflicts like this file is identical
to your copy, or your changes conflicts with that, or even that file was already deleted but you still have
it. At the decision stage, you can choose to either overwrite your copy with the updates or skip it. If you
chose to overwrite, then you can either totally overwrite or choose the *safe* overwrite.

Safe overwrites usually create backups of your local copies to where it is originally saved. But it is limited
to the number of `$retries` you have set here. By default, you have `10` tries for creating a backup for
**each** file. If that number is breached, Revision will complain that it cannot further continue and would
ask you to delete some older backups before continuing.

Backups are named after the operation where the safe overwrite is initiated and the current retry used. If the
filename is `spark` and it is to be modified for the 4th time, the backup would be `spark-Modified-004`.

**public ConsolidatorInterface `$consolidator`**

The name of the consolidator class to use. Consolidators manages the consolidation of changes
brought by the updates and sorts them to whether successfully merged or run into merge conflicts.
Consolidators must implement the `Liaison\Revision\Consolidation\ConsolidatorInterface` interface.

Available consolidators:
- `Liaison\Revision\Consolidator\DefaultConsolidator` (default)

**public UpgraderInterface `$upgrader`**

The name of the upgrader class to use. Upgraders directly manages the update process ensuring the smooth
download of the updates before passing it down to the consolidator. Upgraders must implement the
`Liaison\Revision\Upgrader\UpgraderInterface` interface.

Available upgraders:
- `Liaison\Revision\Upgrade\ComposerUpgrader` (default)

**public AbstractPathfinder `$pathfinder`**

The name of the pathfinder class to use. Pathfinders are simple classes extending
`Liaison\Revision\Paths\AbstractPathfinder` which manages which files to seek and check during the update
process. Usually you feed it with the paths of the files to be monitored, which can be an array of
directories, an array of specific files, or both. The directories and files listed in the `$ignore**`
settings will be considered by `AbstractPathfinder` before giving Revision the final array of files to seek.

Available pathfinders:
- `Liaison\Revision\Paths\DefaultPathfinder` (default)

**public DiffOutputBuilderInterface `$diffOutputBuilder`**

Revision prints the local modifications (diff) of the changes brought by the updates using the excellent
[sebastian/diff](https://github.com/sebastianbergmann/diff) library. It uses the
`SebastianBergmann\Diff\Differ` class in generating the diffs but gives us the option on the format of the
resulting diff by setting the `$diffOutputBuilder` class.

The library gives us three available diff output builders:
- `SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder` (default)
- `SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder`
- `SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder`

**public array `$diffOutputSettings`**

This defines the settings to be supplied to the constructor of the diff output builder chosen. For a
discussion of the settings, please check out the library.

**public array\<AbstractLogHandler\> `$logHandlers`**

This array holds the names of the log handler classes to use. Log handlers must extend
`Liaison\Revision\Logs\AbstractLogHandler`. Log handlers gives you the logs of the operation in the format
defined by each handler. All log handlers are managed by the `LogManager` class.

Available log handlers:
- `Liaison\Revision\Logs\JsonLogHandler` (enabled by default)
- `Liaison\Revision\Logs\PlaintextLogHandler` (enabled by default)
- `Liaison\Revision\Logs\XmlLogHandler`

## Extending

Settings defined in your local copy will take over the default settings provided by Revision. It is paramount
that the default copy not be tampered and so copies should be made only using the configured spark command.

If you want to use production-specific settings that are too sensitive to be tracked by Git or any other VCS,
you can instead set the settings in an `.env` file and supply the settings using the `revision` short prefix.
