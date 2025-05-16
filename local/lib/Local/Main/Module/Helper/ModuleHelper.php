<?php

namespace Local\Main\Module\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use ReflectionClass;
use ReflectionException;

/**
 * Класс-помощник для работы с модулями.
 * Предоставляет методы для получения информации о модуле и его параметрах.
 *
 * @package Local\Main\Module\Helper
 */
abstract class ModuleHelper
{
    /** @var string $moduleId Идентификатор модуля */
    private static string $moduleId;

    /**
     * Метод возвращает параметры модуля из настроек.
     *
     * @return array Массив параметров модуля.
     * @throws ArgumentNullException Если идентификатор модуля не задан.
     * @throws ReflectionException Если возникла ошибка рефлексии класса.
     */
    public static function getModuleParams(): array
    {
        return Option::getForModule(self::getModuleId());
    }

    /**
     * Метод возвращает идентификатор модуля.
     *
     * @return string Идентификатор модуля.
     * @throws ReflectionException Если возникла ошибка рефлексии класса.
     */
    public static function getModuleId(): string
    {
        if (isset(self::$moduleId)) {
            return self::$moduleId;
        }

        $class = get_called_class();
        $reflector = new ReflectionClass($class);

        self::$moduleId = GetModuleID($reflector->getFileName());

        return self::$moduleId;
    }

    /**
     * Метод возвращает уровень прав доступа к модулю.
     *
     * @return string Уровень прав доступа ('D', 'R', 'W', 'X').
     * @throws ReflectionException Если возникла ошибка рефлексии класса.
     */
    public static function getModulePermission(): string
    {
        global $APPLICATION;

        return $APPLICATION->GetGroupRight(self::getModuleId());
    }

    /**
     * Метод возвращает путь к модулю.
     *
     * @param bool $absolutPath Флаг возврата абсолютного пути.
     * @return string Путь к модулю.
     * @throws ReflectionException Если возникла ошибка рефлексии класса.
     */
    public static function getModulePath(bool $absolutPath = true): string
    {
        $class = get_called_class();
        $reflector = new ReflectionClass($class);
        $filePath = $reflector->getFileName();

        preg_match('~/(local|bitrix)/modules/[^/]+~', dirname($filePath, 3), $matches);
        $relativePath = $matches[0];

        if ($absolutPath) {
            return Application::getDocumentRoot() . $relativePath;
        }

        return $relativePath;
    }
}
