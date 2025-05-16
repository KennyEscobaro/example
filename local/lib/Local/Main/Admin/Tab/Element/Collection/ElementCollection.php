<?php

namespace Local\Main\Admin\Tab\Element\Collection;

use Local\DataMapper\Collection;
use Local\Main\Admin\Tab\Element\Entity\Element;
use Local\Main\Admin\Tab\Option\Entity\Option;

/**
 * Класс коллекции элементов вкладки административного интерфейса
 *
 * Предоставляет типизированную коллекцию для работы с набором элементов (Element)
 * административной вкладки, включая фильтрацию и преобразование данных
 * для отображения в интерфейсе настроек.
 *
 * @package Local\Main\Admin\Tab\Element\Collection
 */
class ElementCollection extends Collection
{
    /**
     * Метод возвращает полное имя класса сущности элемента
     *
     * @return string Полное имя класса Element (Local\Main\Admin\Tab\Element\Entity\Element).
     */
    public static function getEntityClass(): string
    {
        return Element::class;
    }

    /**
     * Метод преобразует коллекцию в формат для отображения в настройках административного интерфейса
     *
     * Фильтрует элементы, поддерживающие настройки (isSupportAdminSettings = true),
     * и конвертирует их в массив параметров для отображения.
     *
     * @return array Массив параметров для отображения элементов в настройках.
     */
    public function toAdminSettingsDrawListParams(): array
    {
        $data = [];

        /** @var Element $element */
        foreach ($this->elements as $element) {
            if (!$element->isSupportAdminSettings()) {
                continue;
            }

            $data[] = $element->toAdminSettingsDrawListParams();
        }

        return $data;
    }

    /**
     * Метод возвращает массив элементов, являющихся опциями (Option)
     *
     * Фильтрует коллекцию, оставляя только элементы, которые являются
     * экземплярами класса Option, и возвращает их с сохранением исходных ключей.
     *
     * @return array<string, Option> Массив опций с сохранением ключей коллекции.
     */
    public function getOptions(): array
    {
        $options = [];

        /** @var Element $element */
        foreach ($this->elements as $elementCollectionKey => $element) {
            if (!$element instanceof Option) {
                continue;
            }

            $options[$elementCollectionKey] = $element;
        }

        return $options;
    }
}
