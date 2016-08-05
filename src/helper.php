<?php

if (!function_exists('allow')) {
    function allow($name, $allowDeny = 'allow')
    {
        $permissions = \Groovey\ACL\ACL::$permissions;
        $exist       = element($name, $permissions);

        if (!$exist) {
            return false;
        }

        $value = element('value', $permissions[$name]);

        if ($allowDeny === $value) {
            return true;
        }

        return false;
    }
}

if (!function_exists('deny')) {
    function deny($name, $allowDeny = 'deny')
    {
        return allow($name, 'deny');
    }
}
