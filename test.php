<?php

const APP = __DIR__;

require_once('autoload.php');

$Config = new \Avangar\Ioc\Config();
$Ioc = new \Avangar\Ioc\Ioc($Config);

//Установим зависимости аргументов класса Test\Some2
$Some = $Ioc->set(Test\Some2::class)
    ->arg('a', '45')
    ->arg('b', 180);

//Устанавливаем глобально зависимость Test\Some на Test\Some2
//Все классы, ожидающие в аргументах конструктора экземпляр класа Test\Some
//получат экземпляр Test\Some2
$Ioc->delegate(Test\Some::class,  Test\Some2::class);

//Test\Test автоматически получит зависимость в конструкторе
$Test = $Ioc->get(Test\Test::class);

echo $Test->get(); //Test\Some2::get, $a = `45`, $b = `180`

