<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitae9af7a8ad9165b9d2b1240bb0ae7699
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitae9af7a8ad9165b9d2b1240bb0ae7699', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitae9af7a8ad9165b9d2b1240bb0ae7699', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitae9af7a8ad9165b9d2b1240bb0ae7699::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
