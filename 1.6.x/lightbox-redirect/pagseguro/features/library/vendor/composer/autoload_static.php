<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcd10d61f35bf9f729b939201c04dec2e
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PagSeguro\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PagSeguro\\' => 
        array (
            0 => __DIR__ . '/../..' . '/source',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcd10d61f35bf9f729b939201c04dec2e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcd10d61f35bf9f729b939201c04dec2e::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}