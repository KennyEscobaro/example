<?php

namespace Local\Main\Admin\Tab\Collection;

use Local\DataMapper\Collection;
use Local\Main\Admin\Tab\Entity\Tab;

/**
 * Класс коллекции вкладок административного интерфейса
 *
 * Предоставляет типизированную коллекцию для работы с набором вкладок (Tab)
 * административного раздела, включая преобразование в формат для отображения.
 *
 * @package Local\Main\Admin\Tab\Collection
 */
class TabCollection extends Collection
{
    /**
     * Метод возвращает полное имя класса сущности вкладки
     *
     * @return string Полное имя класса Tab (Local\Main\Admin\Tab\Entity\Tab).
     */
    public static function getEntityClass(): string
    {
        return Tab::class;
    }

    /**
     * Метод преобразует коллекцию в формат для отображения в административном интерфейсе
     *
     * Конвертирует все элементы коллекции в массив параметров для метода
     * CAdminForm::DrawList(). Каждый элемент коллекции преобразуется с помощью
     * метода Tab::toAdminSettingsDrawListParams().
     *
     * @return array Массив параметров для отображения вкладок
     * @see \CAdminForm::DrawList().
     */
    public function toAdminSettingsDrawListParams(): array
    {
        $data = [];

        /** @var Tab $element */
        foreach ($this->elements as $element) {
            $data[] = $element->toAdminSettingsDrawListParams();
        }

        return $data;
    }
}
