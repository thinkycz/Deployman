<?php

namespace App\Services;

use App\Project;

class ProjectManager
{
    /**
     * @var RemoteConsole
     */
    protected $console;

    /**
     * @var string
     */
    protected $deployPath;

    /**
     * ProjectManager constructor.
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->console = app(RemoteConsole::class);
        $this->console->useConnectionObject($project->connection);
        $this->deployPath = $project->path;
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

    /**
     * @return string
     */
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

        return $this->getListOfReleases();
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
     * @return RemoteConsole
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * @param RemoteConsole $console
     */
    public function setConsole($console)
    {
        $this->console = $console;
    }

    /**
     * Whether to use git cache - faster cloning by borrowing objects from existing clones.
     *
     * @return mixed
     */
    protected function useGitCache()
    {
        $git = $this->getGitBinary();
        $gitVersion = $this->console->run("$git version");
        $regs = [];

        if (preg_match('/((\d+\.?)+)/', $gitVersion, $regs)) {
            $version = $regs[1];
        } else {
            $version = "1.0.0";
        }

        return version_compare($version, '2.3', '>=');
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
}