<?php

namespace Local\Main\Admin\List\Collection;

use Local\DataMapper\Collection;
use Local\Main\Admin\List\Entity\ListRow;

/**
 * Класс коллекции для работы с набором элементов списка административного раздела
 *
 * Предоставляет типизированную коллекцию для объектов ListRow,
 * наследуя базовую функциональность от DataMapper Collection.
 *
 * @package Local\Main\Admin\List\Collection
 */
class ListRowCollection extends Collection
{
    /**
     * Метод возвращает полное имя класса сущности, с которой работает коллекция
     *
     * @return string Полное имя класса ListRow (Local\Main\Admin\List\Entity\ListRow).
     */
    public static function getEntityClass(): string
    {
        return ListRow::class;
    }
}
