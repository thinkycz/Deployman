<?php

namespace App\Services;
use Deployer\Deployer;
use Deployer\Task\Context;
use RuntimeException;


/**
 * Class DeployHelper
 * @package App\Services
 */
class DeployHelper
{
    /**
     * @var RemoteConsole
     */
    protected $console;

    protected $php;

    protected $git;

    protected $composer;

    protected $deployPath;

    protected $releasePath;

    /**
     * DeployHelper constructor.
     * @param RemoteConsole $console
     */
    public function __construct(RemoteConsole $console)
    {
        $this->console = $console;
    }

    public function init($deployPath = '/var/www/project')
    {
        $this->deployPath = $deployPath;

        $this->php = $this->getPHPBinary();
        $this->git = $this->getGitBinary();
        $this->composer = $this->getComposerBinary();
    }

    /**
     * Return list of releases on server.
     */
    public function getListOfReleases() {
        // find will list only dirs in releases/
        $list = $this->console->run('find ' . $this->deployPath . '/releases -maxdepth 1 -mindepth 1 -type d')->toArray();

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
     * Preparing server for deployment.
     */
    public function prepareToDeploy() {
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

        $this->console->run('if [ ! -d {{deploy_path}} ]; then mkdir -p {{deploy_path}}; fi');

        // Check for existing /current directory (not symlink)
        $result = $this->console->run('if [ ! -L {{deploy_path}}/current ] && [ -d {{deploy_path}}/current ]; then echo true; fi')->toBool();
        if ($result) {
            throw new \RuntimeException('There already is a directory (not symlink) named "current" in ' . env('deploy_path') . '. Remove this directory so it can be replaced with a symlink for atomic deployments.');
        }

        // Create releases dir.
        $this->console->run("cd {{deploy_path}} && if [ ! -d releases ]; then mkdir releases; fi");

        // Create shared dir.
        $this->console->run("cd {{deploy_path}} && if [ ! -d shared ]; then mkdir shared; fi");

        return true;
    }

    /**
     * Prepare release folders
     */
    public function prepareReleaseFolders() {
        $this->releasePath = $this->deployPath . "/releases/" . $this->getReleaseName();

        $i = 0;
        while ($this->console->run("if [ -d $this->releasePath ]; then echo 'true'; fi")->toBool()) {
            $this->releasePath .= '.' . ++$i;
        }

        $this->console->run("mkdir $this->releasePath");

        $this->console->run("cd " . $this->deployPath . " && if [ -h release ]; then rm release; fi");

        $this->console->run("ln -s $this->releasePath $this->deployPath/release");

        return $this->releasePath;
    }

    /**
     * Update project code
     * @param $repository
     * @param string $branch
     * @param string $tag
     * @param string $revision
     * @throws \Exception
     */
    public function updateCodeFromGit($repository, $branch = '', $tag = '', $revision = '') {
        if (empty($repository)) {
            throw new \Exception("You must enter a valid Git repository.");
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
            $this->console->run("cd {{release_path}} && $git checkout $revision");
        }
        elseif ($gitCache && isset($releases[1])) {
            try {
                $this->console->run("$git clone $at --recursive -q --reference $this->deployPath/releases/{$releases[1]} --dissociate $repository  $this->releasePath 2>&1");
            } catch (RuntimeException $exc) {
                // If {{deploy_path}}/releases/{$releases[1]} has a failed git clone, is empty, shallow etc, git would throw error and give up. So we're forcing it to act without reference in this situation
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
     * Whether to use git cache - faster cloning by borrowing objects from existing clones.
     *
     * @return mixed
     */
    protected function useGitCache() {
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
    protected function getReleaseName($timezone = 'Europe/Prague') {
        date_default_timezone_set($timezone);
        return date('YmdHis');
    }

    protected function getReleasePath()
    {
        try
        {
            return str_replace("\n", '', $this->console->run("readlink " . $this->deployPath . "/release"));
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    protected function getPHPBinary()
    {
        return $this->console->run('which php')->toString();
    }

    protected function getGitBinary()
    {
        return $this->console->run('which git')->toString();
    }

    protected function getComposerBinary()
    {
        $this->releasePath = $this->getReleasePath();

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