# Swoole Process Manager

## Commands

### Manager

- Params
    - swoole_name: swoole.php setting item name
    - action: start|stop|reload|status|pid|restart

- example
    - `php artisan swoole all {action}`: operator all setting swoole
    - `php artisan swoole {swoole_name} {action}`: operator someone setting swoole

### List

- example
    - `php artisan swoole:list`: display all setting swoole status
