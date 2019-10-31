<?php


namespace Zhan3333\LaravelSwooleManager\Commands;

use Illuminate\Console\Command;
use Swoole\Process;
use Zhan3333\LaravelSwooleManager\SwooleManager;

class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole 
    {name=all : 配置的swoole name}
    {action=status : start|stop|reload|status|pid|restart}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole start|stop|reload|status|pid|restart';

    /**
     * Swoole config
     * @var array
     */
    protected $config;

    /**
     * Swoole process name
     * @var mixed
     */
    protected $swooleName = '';

    /** @var SwooleManager */
    protected $manager;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->manager = app(SwooleManager::class);
        $name = $this->argument('name');
        $action = $this->argument('action');
        $configs = config('swoole');
        if (!method_exists($this, $action)) {
            $this->info('swoole start|stop|reload|status|pid|restart');
            return;
        }

        if ($name === 'all') {
            array_walk($configs, function ($config) use ($action) {
                $this->doAction($action, $config);
            });
        } else {
            $filterConfigs = array_filter($configs, function ($config) use ($name) {
                return isset($config['name']) && $config['name'] === $name;
            });
            if (count($filterConfigs) === 0) {
                $this->error("swoole config $name not exists");
                return;
            }
            if (count($filterConfigs) > 1) {
                $this->error("swoole config $name duplicate");
                return;
            }
            $this->doAction($action, $filterConfigs[array_key_first($filterConfigs)]);
        }
    }

    protected function doAction($action, $config)
    {
        $this->config = $config;
        $this->manager->setConfig($this->config);
        $this->swooleName = $config['name'];
        $this->{$action}();
    }

    protected function start()
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $this->info("[$this->swooleName] start listen: $host:$port ...");
        if (empty($this->config['handle_class'])) {
            $this->error("{$this->swooleName} start failed (handle_class not config)");
            return;
        }
        if (!$this->manager->isRun()) {
            $config = $this->config;
            $process = new Process(function (Process $worker) use ($config) {
                @swoole_set_process_name("[$this->swooleName] starting process");
                $this->manager->start();
            }, false, false);
            $process->start();
            $this->info("[$this->swooleName] start in process");
            // 被阻塞，后面在进程结束钱是不会执行的
        } else {
            $this->warn("[$this->swooleName] already run.");
        }
    }

    protected function reload()
    {
        $name = $this->swooleName;
        $this->info("Reload $name ...");
        if (!$this->manager->isRun()) {
            $this->warn("$name not run.");
            $this->manager->start();
        } else {
            $this->manager->reload();
            $this->info("Reload $name success");
        }
    }

    protected function stop()
    {
        $this->info("[$this->swooleName] stop...");
        if (!$this->manager->isRun()) {
            $this->warn("[$this->swooleName] not run");
        } else {
            $this->manager->stop();
            $this->info("[$this->swooleName] stop");
        }
    }

    protected function status()
    {

        $this->info("[$this->swooleName] status: " . $this->manager->getStatus());
    }

    protected function pid()
    {
        $this->info("[$this->swooleName] pid: {$this->manager->getPid()}");
    }

    protected function restart()
    {
        if ($this->manager->isRun()) {
            $this->info("[$this->swooleName] restart...");
            $this->manager->stop();
            $this->info("[$this->swooleName] stop");
            $this->info("[$this->swooleName] starting...");
            while (1) {
                if (!$this->manager->isRun()) {
                    $this->start();
                    break;
                }
            }
        } else {
            $this->start();
            $this->info("[$this->swooleName] start");
        }
    }
}
