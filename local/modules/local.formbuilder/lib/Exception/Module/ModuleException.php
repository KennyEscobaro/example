<?php

namespace Local\FormBuilder\Exception\Module;

use Bitrix\Main\SystemException;

/**
 * Базовый класс исключений модуля.
 * Наследуется от SystemException Битрикс и служит родительским классом
 * для всех специфичных исключений модуля.
 *
 * Используется для обработки общих ошибок, связанных с работой модуля.
 *
 * @package Local\FormBuilder\Exception\Module
 */
class ModuleException extends SystemException
{
}
