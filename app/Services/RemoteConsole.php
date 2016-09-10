<?php

namespace App\Services;

use App\Connection;
use App\Deploy;
use Deployer\Server\Configuration;
use Deployer\Server\Local;
use Deployer\Server\Remote\PhpSecLib;
use Deployer\Type\Result;

class RemoteConsole
{
    /**
     * @var PhpSecLib
     */
    protected $server;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var string
     */
    protected $workingPath;

    /**
     * Save host configuration
     *
     * @param $host
     * @return static
     */
    public function connectTo($host)
    {
        $this->config = new Configuration('connection', $host);

        return $this;
    }

    /**
     * Get localhost server instance
     *
     * @return $this
     */
    public function connectToLocalhost()
    {
        $this->server = new Local();

        return $this;
    }

    public function useConnectionObject(Connection $connection)
    {
        if ($connection->method == Configuration::AUTH_BY_PASSWORD) {
            return $this->connectTo($connection->hostname)->withCredentials($connection->username, $connection->password);
        } elseif ($connection->method == Configuration::AUTH_BY_IDENTITY_FILE) {
            return $this->connectTo($connection->hostname)->withIdentityFile();
        } else {
            return $this;
        }
    }

    /**
     * Authenticate with credentials
     *
     * @param $username
     * @param $password
     * @return $this
     * @throws \Exception
     */
    public function withCredentials($username, $password)
    {
        $this->config->setUser($username);
        $this->password($password);
        $this->server = new PhpSecLib($this->config);

        return $this;
    }

    /**
     * Authenticate with config file
     *
     * @param string $file Config file path
     *
     * @return RemoteConsole
     */
    public function withConfigFile($file = '~/.ssh/config')
    {
        $this->config->setAuthenticationMethod(Configuration::AUTH_BY_CONFIG);
        $this->config->setConfigFile($file);
        $this->server = new PhpSecLib($this->config);

        return $this;
    }

    /**
     * Authenticate with public key
     *
     * @param string $publicKeyFile
     * @param string $privateKeyFile
     * @param string $passPhrase
     *
     * @return RemoteConsole
     */
    public function withIdentityFile($publicKeyFile = '~/.ssh/id_rsa.pub', $privateKeyFile = '~/.ssh/id_rsa', $passPhrase = '')
    {
        $passPhrase = $this->checkPassword($passPhrase);

        if (is_null($publicKeyFile)) {
            $publicKeyFile = '~/.ssh/id_rsa.pub';
        }

        if (is_null($privateKeyFile)) {
            $privateKeyFile = '~/.ssh/id_rsa';
        }

        $this->config->setAuthenticationMethod(Configuration::AUTH_BY_IDENTITY_FILE);
        $this->config->setPublicKey($publicKeyFile);
        $this->config->setPrivateKey($privateKeyFile);
        $this->config->setPassPhrase($passPhrase);
        $this->server = new PhpSecLib($this->config);

        return $this;
    }

    /**
     * Authenticate with public key + password (2-factor)
     *
     * @param string $publicKeyFile
     * @param string $privateKeyFile
     * @param string $passPhrase
     * @param string $password
     *
     * @return RemoteConsole
     */
    public function withIdentityFileAndPassword($publicKeyFile = '~/.ssh/id_rsa.pub', $privateKeyFile = '~/.ssh/id_rsa', $passPhrase = '', $password = null)
    {
        $this->withIdentityFile($publicKeyFile, $privateKeyFile, $passPhrase);
        $this->password($password);
        $this->config->setAuthenticationMethod(Configuration::AUTH_BY_IDENTITY_FILE_AND_PASSWORD);
        $this->server = new PhpSecLib($this->config);

        return $this;
    }

    /**
     * Authenticate with pem file
     *
     * @param string $pemFile
     *
     * @return RemoteConsole
     */
    public function withPemFile($pemFile)
    {
        $this->config->setAuthenticationMethod(Configuration::AUTH_BY_PEM_FILE);
        $this->config->setPemFile($pemFile);
        $this->server = new PhpSecLib($this->config);

        return $this;
    }

    /**
     * Change the current working directory.
     *
     * @param string $path
     */
    public function cd($path)
    {
        $this->workingPath = $path;
    }

    /**
     * Run command on the remote server and log into Deploy entity
     *
     * @param $command
     * @param Deploy $deploy
     * @return Result
     */
    public function runAndLog($command, Deploy $deploy)
    {
        if (!empty($this->workingPath)) {
            $command = "cd $this->workingPath && $command";
        }

        $output = $this->server->run($command);
        $result = new Result($output);

        $deploy->addToLog('COMMAND: ' . $command);
        foreach ($result->toArray() as $outputLine) {
            $deploy->addToLog($outputLine);
        }

        return $result;
    }

    /**
     * Run command on the remote server
     *
     * @param $command
     * @return Result
     */
    public function run($command)
    {
        if (!empty($this->workingPath)) {
            $command = "cd $this->workingPath && $command";
        }

        $output = $this->server->run($command);
        $result = new Result($output);

        return $result;
    }

    /**
     * Set password for connection
     *
     * @param string $password If you did not define password it will be asked on connection.
     *
     * @return RemoteConsole
     */
    private function password($password = null)
    {
        $password = $this->checkPassword($password);

        $this->config->setAuthenticationMethod(Configuration::AUTH_BY_PASSWORD);
        $this->config->setPassword($password);

        return $this;
    }

    /**
     * Check password valid
     *
     * @param mixed $password
     *
     * @return mixed
     */
    private function checkPassword($password)
    {
        if (is_null($password)) {
            throw new \InvalidArgumentException("You must enter password.");
        }

        if (is_scalar($password)) {
            return $password;
        }

        // Invalid password
        throw new \InvalidArgumentException('The password should be a string.');
    }

    /**
     * @param $username
     * @return $this
     */
    public function andWithUsername($username)
    {
        $this->config->setUser($username);
        $this->server = new PhpSecLib($this->config);

        return $this;
    }

    public function getPublicKey()
    {
        $this->connectToLocalhost();
        return $this->run("cat ~/.ssh/id_rsa.pub");
    }

    /**
     * Check if command exists in bash.
     *
     * @param string $command
     * @return bool
     */
    public function commandExists($command)
    {
        return $this->run("if hash $command 2>/dev/null; then echo 'true'; fi")->toBool();
    }

    /**
     * @param string $workingPath
     * @return RemoteConsole
     */
    public function setWorkingPath($workingPath)
    {
        $this->workingPath = $workingPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkingPath()
    {
        return $this->workingPath;
    }

    /**
     * @return PhpSecLib
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param PhpSecLib $server
     * @return $this
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }
}