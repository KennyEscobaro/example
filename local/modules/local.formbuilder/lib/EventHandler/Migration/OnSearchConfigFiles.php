<?php

namespace Local\FormBuilder\EventHandler\Migration;

use Local\FormBuilder\Helper\ModuleHelper;
use ReflectionException;

/**
 * Класс обработчика события поиска конфигурационных файлов миграций.
 * Предоставляет путь к директории с миграциями модуля.
 *
 * @package Local\FormBuilder\EventHandler\Migration
 */
class OnSearchConfigFiles
{
    /**
     * Метод возвращает путь к директории с конфигурационными файлами миграций.
     *
     * @return string Абсолютный путь к директории миграций модуля.
     * @throws ReflectionException
     */
    public static function getConfigDirectory(): string
    {
        return ModuleHelper::getModulePath();
    }
}
