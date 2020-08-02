<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite32afa96b7c9b7e5168b73f1f9c938b4
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'src\\' => 4,
        ),
        'c' => 
        array (
            'core\\' => 5,
        ),
        'C' => 
        array (
            'ClanCats\\Hydrahon\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'src\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core',
        ),
        'ClanCats\\Hydrahon\\' => 
        array (
            0 => __DIR__ . '/..' . '/clancats/hydrahon/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite32afa96b7c9b7e5168b73f1f9c938b4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite32afa96b7c9b7e5168b73f1f9c938b4::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
