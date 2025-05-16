<?php

namespace Local\Main\Admin\Tab\Option\Enum;

/**
 * Перечисление типов загружаемых файлов.
 *
 * @package Local\Main\Admin\Tab\Option\Enum
 */
enum FileUploadedType: string
{
    /**
     * Тип: Обычный файл.
     */
    case FILE = 'F';

    /**
     * Тип: Изображение.
     */
    case IMAGE = 'I';

    /**
     * Тип: Все типы файлов.
     */
    case ALL = 'A';
}
