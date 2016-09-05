<?php

namespace App\Services;

use App\Deploy;
use App\Project;
use RuntimeException;
use Session;


/**
 * Class BaseDeployer
 * @package App\Services
 */
class BaseDeployer
{
    /**
     * @var RemoteConsole
     */
    protected $console;

    /**
     * @var string
     */
    protected $php;

    /**
     * @var string
     */
    protected $git;

    /**
     * @var string
     */
    protected $deployPath;

    /**
     * @var string
     */
    protected $releasePath;

    /**
     * DeployHelper constructor.
     * @param RemoteConsole $console
     */
    public function __construct(RemoteConsole $console)
    {
        $this->console = $console;
    }

    /**
     * @param string $deployPath
     */
    public function initDirectory($deployPath)
    {
        $this->deployPath = $deployPath;

        $this->php = $this->getPHPBinary();
        $this->git = $this->getGitBinary();
    }

    /**
     * Return list of releases on server.
     */
    public function getListOfReleases()
    {
        // find will list only dirs in releases/
        $list = $this->console->run("find $this->deployPath/releases -maxdepth 1 -mindepth 1 -type d")->toArray();

        // filter out anything that does not look like a release
        foreach ($list as $key => $item) {
            $item = basename($item); // strip path returned from find

            // release dir can look like this: 20160216152237 or 20160216152237.1.2.3.4 ...
            $name_match = '[0-9]{14}'; // 20160216152237
            $extension_match = '\.[0-9]+'; // .1 or .15 etc
            if (!preg_match("/^$name_match($extension_match)*$/", $item)) {
                unset($list[$key]); // dir name does not match pattern, throw it out
                continue;
            }

            $list[$key] = $item; // $item was changed
        }

        rsort($list);

        return $list;
    }

    /**
     * Return current release path.
     */
    public function getCurrentReleasePath()
    {
        return $this->console->run("readlink $this->deployPath/current")->toString();
    }

    /**
     * Return the current release timestamp
     */
    public function getCurrentReleaseFolder()
    {
        return basename($this->getCurrentReleasePath());
    }

    public function getCurrentCommitHash()
    {
        return $this->console->run("cd $this->deployPath/current && git rev-parse HEAD")->toString();
    }

    /**
     * Cleanup old releases.
     * @param int $keep
     * @return bool
     */
    public function cleanupOldReleases($keep = 3)
    {
        $releases = $this->getListOfReleases();

        while ($keep > 0) {
            array_shift($releases);
            --$keep;
        }

        foreach ($releases as $release) {
            $this->console->run("rm -rf $this->deployPath/releases/$release");
        }

        $this->console->run("cd $this->deployPath && if [ -e release ]; then rm release; fi");
        $this->console->run("cd $this->deployPath && if [ -h release ]; then rm release; fi");

        return true;
    }

    /**
     * Cleanup files and directories
     * @param $paths array
     * @param bool $useSudo
     * @return bool
     */
    public function cleanupCustomPaths($paths, $useSudo = true) {
        $sudo  = $useSudo ? 'sudo' : '';

        foreach ($paths as $path) {
            $this->console->run("$sudo rm -rf $this->deployPath/$path");
        }

        return true;
    }

    /**
     * Rollback to previous release.
     */
    public function rollbackToPreviousRelease()
    {
        $releases = $this->getListOfReleases();

        if (isset($releases[1])) {
            $releaseDir = "$this->deployPath/releases/{$releases[1]}";

            // Symlink to old release.
            $this->console->run("cd $this->deployPath && ln -nfs $releaseDir current");

            // Remove release
            $this->console->run("rm -rf $this->deployPath/releases/{$releases[0]}");

            return $releaseDir;
        } else {
            throw new \Exception('No more releases you can revert to.');
        }
    }

    /**
     * Preparing server for deployment.
     */
    public function prepareToDeploy()
    {
        $this->console->getServer()->connect();

        // Check if shell is POSIX-compliant
        try {
            $this->console->cd(''); // To run command as raw.
            $result = $this->console->run('echo $0')->toString();
            if ($result == 'stdin: is not a tty') {
                throw new RuntimeException(
                    "Looks like ssh inside another ssh.\n" .
                    "Help: http://goo.gl/gsdLt9"
                );
            }
        } catch (\RuntimeException $e) {
            $errorMessage = [
                "Shell on your server is not POSIX-compliant. Please change to sh, bash or similar.",
                "Usually, you can change your shell to bash by running: chsh -s /bin/bash",
            ];
            throw new \Exception($errorMessage . ' ' . $e->getMessage());
        }

        $this->console->run("if [ ! -d $this->deployPath ]; then mkdir -p $this->deployPath; fi");

        // Check for existing /current directory (not symlink)
        $result = $this->console->run("if [ ! -L $this->deployPath/current ] && [ -d $this->deployPath/current ]; then echo true; fi")->toBool();
        if ($result) {
            throw new \RuntimeException('There already is a directory (not symlink) named "current" in ' . env('deploy_path') . '. Remove this directory so it can be replaced with a symlink for atomic deployments.');
        }

        // Create releases dir.
        $this->console->run("cd $this->deployPath && if [ ! -d releases ]; then mkdir releases; fi");

        // Create shared dir.
        $this->console->run("cd $this->deployPath && if [ ! -d shared ]; then mkdir shared; fi");

        return true;
    }

    /**
     * Prepare release folders
     */
    public function prepareReleaseFolders()
    {
        $this->releasePath = "$this->deployPath/releases/" . $this->getReleaseName();

        $i = 0;
        while ($this->console->run("if [ -d $this->releasePath ]; then echo 'true'; fi")->toBool()) {
            $this->releasePath .= '.' . ++$i;
        }

        $this->console->run("mkdir -p $this->releasePath");

        $this->console->run("cd " . $this->deployPath . " && if [ -h release ]; then rm release; fi");

        $this->console->run("ln -s $this->releasePath $this->deployPath/release");

        return $this->releasePath;
    }

    /**
     * Pull project code
     * @param $repository
     * @param string $branch
     * @param string $tag
     * @param string $revision
     * @return string
     * @throws \Exception
     */
    public function pullCodeFromGit($repository, $branch = '', $tag = '', $revision = '')
    {
        if (empty($repository)) {
            throw new \Exception("You must enter a valid Git repository.");
        }

        if (!$this->releasePath) {
            throw new \Exception("You must prepare release folders first.");
        }

        $git = $this->git;
        $gitCache = $this->useGitCache();
        $depth = $gitCache ? '' : '--depth 1';

        $at = '';
        if (!empty($tag)) {
            $at = "-b $tag";
        } elseif (!empty($branch)) {
            $at = "-b $branch";
        }

        $releases = $this->getListOfReleases();

        if (!empty($revision)) {
            // To checkout specified revision we need to clone all tree.
            $this->console->run("$git clone $at --recursive -q $repository $this->releasePath 2>&1");
            $this->console->run("cd $this->releasePath && $git checkout $revision");
        }
        elseif ($gitCache && isset($releases[1])) {
            try {
                $this->console->run("$git clone $at --recursive -q --reference $this->deployPath/releases/{$releases[1]} --dissociate $repository  $this->releasePath 2>&1");
            } catch (RuntimeException $exc) {
                // If deploy_path/releases/{$releases[1]} has a failed git clone, is empty, shallow etc, git would throw error and give up. So we're forcing it to act without reference in this situation
                $this->console->run("$git clone $at --recursive -q $repository $this->releasePath 2>&1");
            }
        }
        else {
            // if we're using git cache this would be identical to above code in catch - full clone. If not, it would create shallow clone.
            $this->console->run("$git clone $at $depth --recursive -q $repository $this->releasePath 2>&1");
        }

        return $this->releasePath;
    }

    /**
     * Copy directories. Useful for vendors directories
     * @param $dirs array
     * @return bool
     * @throws \Exception
     */
    public function copyDirectories($dirs)
    {
        if (!$this->releasePath) {
            throw new \Exception("You must prepare release folders first.");
        }

        foreach ($dirs as $dir) {
            // Delete directory if exists.
            $this->console->run("if [ -d $(echo $this->releasePath/$dir) ]; then rm -rf $this->releasePath/$dir; fi");

            // Copy directory.
            $this->console->run("if [ -d $(echo $this->deployPath/current/$dir) ]; then cp -rpf $this->deployPath/current/$dir $this->releasePath/$dir; fi");
        }

        return true;
    }

    /**
     * Create symlinks for shared directories and files.
     * @param $dirs array
     * @param $files array
     * @return bool
     * @throws \Exception
     */
    public function createSymlinksToSharedResources($dirs = [], $files = [])
    {
        if (!$this->releasePath) {
            throw new \Exception("You must prepare release folders first.");
        }

        $sharedPath = "$this->deployPath/shared";

        foreach ($dirs as $dir) {
            // Remove from source.
            $this->console->run("if [ -d $(echo $this->releasePath/$dir) ]; then rm -rf $this->releasePath/$dir; fi");

            // Create shared dir if it does not exist.
            $this->console->run("mkdir -p $sharedPath/$dir");

            // Create path to shared dir in release dir if it does not exist.
            // (symlink will not create the path and will fail otherwise)
            $this->console->run("mkdir -p `dirname $this->releasePath/$dir`");

            // Symlink shared dir to release dir
            $this->console->run("ln -nfs $sharedPath/$dir $this->releasePath/$dir");
        }

        foreach ($files as $file) {
            $dirname = dirname($file);

            // Remove from source.
            $this->console->run("if [ -f $(echo $this->releasePath/$file) ]; then rm -rf $this->releasePath/$file; fi");

            // Ensure dir is available in release
            $this->console->run("if [ ! -d $(echo $this->releasePath/$dirname) ]; then mkdir -p $this->releasePath/$dirname;fi");

            // Create dir of shared file
            $this->console->run("mkdir -p $sharedPath/" . $dirname);

            // Touch shared
            $this->console->run("touch $sharedPath/$file");

            // Symlink shared dir to release dir
            $this->console->run("ln -nfs $sharedPath/$file $this->releasePath/$file");
        }

        return true;
    }

    /**
     * Make writable dirs.
     * @param $directories
     * @param bool $useSudo
     * @param string $customHttpUser
     * @return bool
     * @throws \Exception
     */
    public function makeDirectoriesWritable($directories = [], $useSudo = true, $customHttpUser = null)
    {
        if (!$this->releasePath) {
            throw new \Exception("You must prepare release folders first.");
        }

        $dirs = join(' ', $directories);
        $sudo = $useSudo ? 'sudo' : '';
        $httpUser = $customHttpUser;

        if (!empty($dirs)) {
            try {
                if (null === $httpUser) {
                    $httpUser = $this->console->run("ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1")->toString();
                }

                $this->console->cd($this->releasePath);

                // Try OS-X specific setting of access-rights
                if (strpos($this->console->run("chmod 2>&1; true"), '+a') !== false) {
                    if (!empty($httpUser)) {
                        $this->console->run("$sudo chmod +a \"$httpUser allow delete,write,append,file_inherit,directory_inherit\" $dirs");
                    }

                    $this->console->run("$sudo chmod +a \"`whoami` allow delete,write,append,file_inherit,directory_inherit\" $dirs");
                    // Try linux ACL implementation with unsafe fail-fallback to POSIX-way
                } elseif ($this->console->commandExists('setfacl')) {
                    if (!empty($httpUser)) {
                        if (!empty($sudo)) {
                            $this->console->run("$sudo setfacl -R -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dirs");
                            $this->console->run("$sudo setfacl -dR -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dirs");
                        } else {
                            // When running without sudo, exception may be thrown
                            // if executing setfacl on files created by http user (in directory that has been setfacl before).
                            // These directories/files should be skipped.
                            // Now, we will check each directory for ACL and only setfacl for which has not been set before.
                            $writeableDirs = $directories;
                            foreach ($writeableDirs as $dir) {
                                // Check if ACL has been set or not
                                $hasfacl = $this->console->run("getfacl -p $dir | grep \"^user:$httpUser:.*w\" | wc -l")->toString();
                                // Set ACL for directory if it has not been set before
                                if (!$hasfacl) {
                                    $this->console->run("setfacl -R -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dir");
                                    $this->console->run("setfacl -dR -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dir");
                                }
                            }
                        }
                    } else {
                        $this->console->run("$sudo chmod 777 -R $dirs");
                    }
                    // If we are not on OS-X and have no ACL installed use POSIX
                } else {
                    $this->console->run("$sudo chmod 777 -R $dirs");
                }
            } catch (\RuntimeException $e) {
                $errorMessage = [
                    "Unable to setup correct permissions for writable dirs.                  ",
                    "You need to configure sudo's sudoers files to not prompt for password,",
                    "or setup correct permissions manually.                                  ",
                ];

                throw new \Exception($errorMessage . ' ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Installing vendors tasks.
     * @param string $environmentVariables
     * @param string $composerOptions
     * @return bool
     * @throws \Exception
     */
    public function installVendors($environmentVariables = null, $composerOptions = 'install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction')
    {
        if (!$this->releasePath) {
            throw new \Exception("You must prepare release folders first.");
        }

        $composer = $this->getComposerBinary();
        $envVars = $environmentVariables ? 'export ' . $environmentVariables . ' &&' : '';

        $this->console->run("cd $this->releasePath && $envVars $composer $composerOptions");

        return true;
    }

    /**
     * Create symlink to last release.
     * @return bool
     * @throws \Exception
     */
    public function createSymlinkToCurrent()
    {
        if (!$this->releasePath) {
            throw new \Exception("You must prepare release folders first.");
        }

        $this->console->run("cd $this->deployPath && ln -sfn $this->releasePath current"); // Atomic override symlink.
        $this->console->run("cd $this->deployPath && rm release"); // Remove release link.

        return true;
    }

    /////////////// Protected methods

    protected function initDeployLog()
    {
        Session::remove('deploy_log');
    }

    protected function createDeployRecord(Project $project)
    {
        $record = Deploy::create([
            'user_id' => $project->user->id,
            'project_id' => $project->id,
            'log' => Session::has('deploy_log') ? json_encode(Session::pull('deploy_log')) : 'logger error',
            'commit_hash' => '',
            'folder_name' => $this->getCurrentReleaseFolder()
        ]);

        return $record;
    }

    /**
     * Whether to use git cache - faster cloning by borrowing objects from existing clones.
     *
     * @return mixed
     */
    protected function useGitCache()
    {
        $gitVersion = $this->console->run("$this->git version");
        $regs = [];

        if (preg_match('/((\d+\.?)+)/', $gitVersion, $regs)) {
            $version = $regs[1];
        } else {
            $version = "1.0.0";
        }

        return version_compare($version, '2.3', '>=');
    }

    /**
     * Name of release folders
     * @param string $timezone
     * @return bool|string
     */
    protected function getReleaseName($timezone = 'Europe/Prague')
    {
        date_default_timezone_set($timezone);
        return date('YmdHis');
    }

    /**
     * @return string
     */
    protected function getPHPBinary()
    {
        return $this->console->run('which php')->toString();
    }

    /**
     * @return string
     */
    protected function getGitBinary()
    {
        return $this->console->run('which git')->toString();
    }

    /**
     * @return string
     */
    protected function getComposerBinary()
    {
        if ($this->console->commandExists('composer')) {
            $composer = $this->console->run('which composer')->toString();
        }

        if (empty($composer)) {
            $this->console->run("cd " . $this->releasePath . " && curl -sS https://getcomposer.org/installer | " . $this->php);
            $composer = $this->php . ' ' . $this->releasePath . '/composer.phar';
        }

        return $composer;
    }
}