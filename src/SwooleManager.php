<?php


namespace Zhan3333\LaravelSwooleManager;


use Swoole\Process;

class SwooleManager
{
    private $config;

    public function __construct()
    {
    }

    public function setConfig($config): SwooleManager
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getStatus(): string
    {
        return $this->isRun() ? 'run' : 'stop';
    }

    public function isRun()
    {
        $pid = $this->getPid();
        if ($pid) {
            return Process::kill($pid, 0);
        }
        return false;
    }

    public function getPid(): int
    {
        $pid = null;
        // try get pid from pid_file
        if (@file_exists($this->config['pid_file'])) {
            $pid = @file_get_contents($this->config['pid_file']);
        }
        // try get pid from pidof
        if (!$pid) {
            $pid = @shell_exec("pidof {$this->config['name']}");
            if ($pid) {
                $pid = str_replace("\n", '', $pid);
            }
        }
        return (integer)$pid;
    }

    public function stop(): void
    {
        Process::kill($this->getPid());
    }

    public function reload(): void
    {
        shell_exec("kill -USR1 {$this->getPid()}");
        Process::kill($this->getPid(), SIGUSR1);
    }

    public function start(): void
    {
        $swoole = new $this->config['handle_class']($this->config);
        $swoole->start();
    }
}
