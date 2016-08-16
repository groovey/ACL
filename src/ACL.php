<?php

namespace Groovey\ACL;

use Pimple\Container;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Groovey\DB\DB;

class ACL
{
    private $app;
    private $yaml;
    public static $permissions;

    public function __construct(Container $app, $file)
    {
        $this->app = $app;

        $this->load($file);
    }

    public static function setPermission($name, $key, $value)
    {
        self::$permissions[$name][$key] = $value;
    }

    public static function getPermissions()
    {
        return self::$permissions;
    }

    private function load($file)
    {
        $app = $this->app;

        $contents = file_get_contents($file);

        try {
            $yaml = Yaml::parse($contents);
        } catch (ParseException $e) {
            $app['trace']->show(true);
            $app['trace']->debug('Unable to parse the YAML permission string:');
            $app['trace']->debug($e, true);
            exit();
        }

        return $this->yaml = $yaml;
    }

    public function authorize($userId)
    {
        $app = $this->app;

        $datas = DB::table('users as u')
                    ->select([
                            'p.role_id',
                            'p.node',
                            'p.item',
                            'p.value',
                            DB::raw("CONCAT(p.node,'.', p.item) as permission"),
                        ])
                    ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
                    ->leftJoin('permissions as p', 'u.role_id', '=', 'p.role_id')
                    ->where('u.id', '=', $userId)
                    ->get()
                ;

        $permissions = [];
        foreach ($datas as $data) {
            $permissions[$data->permission] = (array) $data;
        }

        return self::$permissions = $permissions;
    }

    public function allow($name, $allowDeny = 'allow')
    {
        $app         = $this->app;
        $permissions = self::$permissions;
        $exist       = element($name, $permissions);

        if (!$exist) {
            $app['trace']->show(true);
            $app['trace']->debug("Permission does not exists - $name");
            die();
        }

        $value = element('value', $permissions[$name]);

        if ($allowDeny === $value) {
            return true;
        }

        return false;
    }

    public function deny($name)
    {
        return $this->allow($name, 'deny');
    }
}
