<?php


namespace Zhan3333\LaravelSwooleManager\Commands;

use Illuminate\Console\Command;
use Zhan3333\LaravelSwooleManager\SwooleManager;

class SwooleListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List swoole config';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = config('swoole');
        $swooleManager = new SwooleManager();
        $rows = [];
        foreach ($config as $item) {
            $rows[] = [
                'name' => $item['name'] ?? '',
                'class' => $item['handle_class'] ?? '',
                'host' => $item['host'] . ':' . $item['port'],
                'status' => $swooleManager->setConfig($item)->getStatus(),
                'pid' => $swooleManager->setConfig($item)->getPid(),
            ];
        }
        $this->table(['name', 'class', 'host', 'status', 'pid'], $rows);
    }
}
