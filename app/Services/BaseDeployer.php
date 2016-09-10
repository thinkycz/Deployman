<?php

namespace App\Services;

use App\Deploy;
use App\Helpers\DeployStatus;
use App\Project;
use RuntimeException;

/**
 * Class BaseDeployer
 * @package App\Services
 */
class BaseDeployer extends ProjectManager
{
    /**
     * @var string
     */
    protected $releasePath;

    /**
     * @var Deploy
     */
    protected $deploy;

    /**
     * DeployHelper constructor.
     * @param Deploy $deploy
     */
    public function __construct(Deploy $deploy)
    {

        parent::__construct($deploy->project);

        $this->deploy = $deploy;
        $this->deployPath = $deploy->project->path;
        $this->releasePath = "$this->deployPath/releases/$deploy->folder_name";
    }

    /**
     *  =========== Helper methods ===========
     */

    /**
     * @return Deploy
     */
    public function run()
    {
        $this->deploy->setFinished();
        $this->deploy->setStatus(DeployStatus::FAILED);
        $this->deploy->addToLog('ERROR: This project type is not supported.');
        return $this->deploy;
    }

    /**
     * @return string
     */
    protected function getComposerBinary()
    {
        $php = $this->getPHPBinary();

        if ($this->console->commandExists('composer')) {
            $composer = $this->console->runAndLog('which composer', $this->deploy)->toString();
        }

        if (empty($composer)) {
            $this->console->runAndLog("cd $this->releasePath && curl -sS https://getcomposer.org/installer | $php", $this->deploy);
            $composer = "$php $this->releasePath/composer.phar";
        }

        return $composer;
    }

    /**
     *  =========== Basic deployment process methods ===========
     */

    /**
     * Preparing server for deployment.
     */
    public function prepareToDeploy()
    {
        $this->deploy->addToLog('INFO: Checking requirements. Preparing server to deploy.');

        $this->console->getServer()->connect();

        // Check if shell is POSIX-compliant
        try {
            $this->console->cd(''); // To run command as raw.
            $result = $this->console->runAndLog('echo $0', $this->deploy)->toString();
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

        $this->console->runAndLog("if [ ! -d $this->deployPath ]; then mkdir -p $this->deployPath; fi", $this->deploy);

        // Check for existing /current directory (not symlink)
        $result = $this->console->runAndLog("if [ ! -L $this->deployPath/current ] && [ -d $this->deployPath/current ]; then echo true; fi", $this->deploy)->toBool();
        if ($result) {
            throw new \RuntimeException('There already is a directory (not symlink) named "current" in ' . env('deploy_path') . '. Remove this directory so it can be replaced with a symlink for atomic deployments.');
        }

        // Create releases dir.
        $this->console->runAndLog("cd $this->deployPath && if [ ! -d releases ]; then mkdir releases; fi", $this->deploy);

        // Create shared dir.
        $this->console->runAndLog("cd $this->deployPath && if [ ! -d shared ]; then mkdir shared; fi", $this->deploy);

        return $this->deploy;
    }

    /**
     * Prepare release folders
     */
    public function prepareReleaseFolders()
    {
        $this->deploy->addToLog('INFO: Preparing release folders.');

        $i = 0;
        while ($this->console->runAndLog("if [ -d $this->releasePath ]; then echo 'true'; fi", $this->deploy)->toBool()) {
            $this->releasePath .= '.' . ++$i;
        }

        $this->console->runAndLog("mkdir -p $this->releasePath", $this->deploy);

        $this->console->runAndLog("cd " . $this->deployPath . " && if [ -h release ]; then rm release; fi", $this->deploy);

        $this->console->runAndLog("ln -s $this->releasePath $this->deployPath/release", $this->deploy);

        return $this->deploy;
    }

    /**
     * Pull project code
     * @param string $branch
     * @param string $tag
     * @param string $revision
     * @return Deploy
     * @throws \Exception
     */
    public function pullCodeFromGit($branch = '', $tag = '', $revision = '')
    {
        $this->deploy->addToLog('INFO: Pulling code from Git repository.');

        $repository = $this->deploy->project->repository;
        $git = $this->getGitBinary();
        $gitCache = $this->useGitCache();
        $depth = $gitCache ? '' : '--depth 1';

        if (empty($branch) and !empty($this->deploy->branch)) {
            $branch = $this->deploy->branch;
        }

        if (empty($revision) and !empty($this->deploy->commit_hash)) {
            $revision = $this->deploy->commit_hash;
        }

        $at = '';
        if (!empty($tag)) {
            $at = "-b $tag";
        } elseif (!empty($branch)) {
            $at = "-b $branch";
        }

        $releases = $this->getListOfReleases();

        if (!empty($revision)) {
            // To checkout specified revision we need to clone all tree.
            $this->console->runAndLog("$git clone $at --recursive -q $repository $this->releasePath 2>&1", $this->deploy);
            $this->console->runAndLog("cd $this->releasePath && $git checkout $revision", $this->deploy);
        } elseif ($gitCache && isset($releases[1])) {
            try {
                $this->console->runAndLog("$git clone $at --recursive -q --reference $this->deployPath/releases/{$releases[1]} --dissociate $repository  $this->releasePath 2>&1", $this->deploy);
            } catch (RuntimeException $exc) {
                // If deploy_path/releases/{$releases[1]} has a failed git clone, is empty, shallow etc, git would throw error and give up. So we're forcing it to act without reference in this situation
                $this->console->runAndLog("$git clone $at --recursive -q $repository $this->releasePath 2>&1", $this->deploy);
            }
        } else {
            // if we're using git cache this would be identical to above code in catch - full clone. If not, it would create shallow clone.
            $this->console->runAndLog("$git clone $at $depth --recursive -q $repository $this->releasePath 2>&1", $this->deploy);
        }

        if (empty($this->deploy->commit_hash)) {
            $this->deploy->commit_hash = $this->console->runAndLog("cd $this->releasePath && git rev-parse HEAD", $this->deploy)->toString();
            $this->deploy->save();
        }

        return $this->deploy;
    }

    /**
     * Copy directories. Useful for vendors directories
     * @param $dirs array
     * @return Deploy
     * @throws \Exception
     */
    public function copyDirectories($dirs = [])
    {
        $this->deploy->addToLog('INFO: Copying selected directories.');

        foreach ($dirs as $dir) {
            // Delete directory if exists.
            $this->console->runAndLog("if [ -d $(echo $this->releasePath/$dir) ]; then rm -rf $this->releasePath/$dir; fi", $this->deploy);

            // Copy directory.
            $this->console->runAndLog("if [ -d $(echo $this->deployPath/current/$dir) ]; then cp -rpf $this->deployPath/current/$dir $this->releasePath/$dir; fi", $this->deploy);
        }

        return $this->deploy;
    }

    /**
     * Create symlinks for shared directories and files.
     * @param $dirs array
     * @param $files array
     * @return Deploy
     * @throws \Exception
     */
    public function createSymlinksToSharedResources($dirs = [], $files = [])
    {
        $this->deploy->addToLog('INFO: Creating symbolic links to shared directories.');

        $sharedPath = "$this->deployPath/shared";

        foreach ($dirs as $dir) {
            // Remove from source.
            $this->console->runAndLog("if [ -d $(echo $this->releasePath/$dir) ]; then rm -rf $this->releasePath/$dir; fi", $this->deploy);

            // Create shared dir if it does not exist.
            $this->console->runAndLog("mkdir -p $sharedPath/$dir", $this->deploy);

            // Create path to shared dir in release dir if it does not exist.
            // (symlink will not create the path and will fail otherwise)
            $this->console->runAndLog("mkdir -p `dirname $this->releasePath/$dir`", $this->deploy);

            // Symlink shared dir to release dir
            $this->console->runAndLog("ln -nfs $sharedPath/$dir $this->releasePath/$dir", $this->deploy);
        }

        foreach ($files as $file) {
            $dirname = dirname($file);

            // Remove from source.
            $this->console->runAndLog("if [ -f $(echo $this->releasePath/$file) ]; then rm -rf $this->releasePath/$file; fi", $this->deploy);

            // Ensure dir is available in release
            $this->console->runAndLog("if [ ! -d $(echo $this->releasePath/$dirname) ]; then mkdir -p $this->releasePath/$dirname;fi", $this->deploy);

            // Create dir of shared file
            $this->console->runAndLog("mkdir -p $sharedPath/" . $dirname, $this->deploy);

            // Touch shared
            $this->console->runAndLog("touch $sharedPath/$file", $this->deploy);

            // Symlink shared dir to release dir
            $this->console->runAndLog("ln -nfs $sharedPath/$file $this->releasePath/$file", $this->deploy);
        }

        return $this->deploy;
    }

    /**
     * Make writable dirs.
     * @param $directories
     * @param bool $useSudo
     * @param string $customHttpUser
     * @return Deploy
     * @throws \Exception
     */
    public function makeDirectoriesWritable($directories = [], $useSudo = true, $customHttpUser = null)
    {
        $this->deploy->addToLog('INFO: Changing ownership and permissions of directories.');

        $dirs = join(' ', $directories);
        $sudo = $useSudo ? 'sudo' : '';
        $httpUser = $customHttpUser;

        if (!empty($dirs)) {
            try {
                if (null === $httpUser) {
                    $httpUser = $this->console->runAndLog("ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1", $this->deploy)->toString();
                }

                $this->console->cd($this->releasePath);

                // Try OS-X specific setting of access-rights
                if (strpos($this->console->runAndLog("chmod 2>&1; true", $this->deploy), '+a') !== false) {
                    if (!empty($httpUser)) {
                        $this->console->runAndLog("$sudo chmod +a \"$httpUser allow delete,write,append,file_inherit,directory_inherit\" $dirs", $this->deploy);
                    }

                    $this->console->runAndLog("$sudo chmod +a \"`whoami` allow delete,write,append,file_inherit,directory_inherit\" $dirs", $this->deploy);
                    // Try linux ACL implementation with unsafe fail-fallback to POSIX-way
                } elseif ($this->console->commandExists('setfacl')) {
                    if (!empty($httpUser)) {
                        if (!empty($sudo)) {
                            $this->console->runAndLog("$sudo setfacl -R -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dirs", $this->deploy);
                            $this->console->runAndLog("$sudo setfacl -dR -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dirs", $this->deploy);
                        } else {
                            // When running without sudo, exception may be thrown
                            // if executing setfacl on files created by http user (in directory that has been setfacl before).
                            // These directories/files should be skipped.
                            // Now, we will check each directory for ACL and only setfacl for which has not been set before.
                            $writeableDirs = $directories;
                            foreach ($writeableDirs as $dir) {
                                // Check if ACL has been set or not
                                $hasfacl = $this->console->runAndLog("getfacl -p $dir | grep \"^user:$httpUser:.*w\" | wc -l", $this->deploy)->toString();
                                // Set ACL for directory if it has not been set before
                                if (!$hasfacl) {
                                    $this->console->runAndLog("setfacl -R -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dir", $this->deploy);
                                    $this->console->runAndLog("setfacl -dR -m u:\"$httpUser\":rwX -m u:`whoami`:rwX $dir", $this->deploy);
                                }
                            }
                        }
                    } else {
                        $this->console->runAndLog("$sudo chmod 777 -R $dirs", $this->deploy);
                    }
                    // If we are not on OS-X and have no ACL installed use POSIX
                } else {
                    $this->console->runAndLog("$sudo chmod 777 -R $dirs", $this->deploy);
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

        return $this->deploy;
    }

    /**
     * Installing vendors tasks.
     * @param string $environmentVariables
     * @param string $composerOptions
     * @return Deploy
     * @throws \Exception
     */
    public function installVendors($environmentVariables = null, $composerOptions = 'install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction')
    {
        $this->deploy->addToLog('INFO: Running composer.');

        $composer = $this->getComposerBinary();
        $envVars = $environmentVariables ? 'export ' . $environmentVariables . ' &&' : '';

        $this->console->runAndLog("cd $this->releasePath && $envVars $composer $composerOptions", $this->deploy);

        return $this->deploy;
    }

    /**
     * Create symlink to last release.
     * @return Deploy
     * @throws \Exception
     */
    public function createSymlinkToCurrent()
    {
        $this->deploy->addToLog('INFO: Linking this release to the `current` directory.');

        $this->console->runAndLog("cd $this->deployPath && ln -sfn $this->releasePath current", $this->deploy); // Atomic override symlink.
        $this->console->runAndLog("cd $this->deployPath && rm release", $this->deploy); // Remove release link.

        return $this->deploy;
    }
}