<?php

namespace Local\Main\Admin\Tab\Element\Entity;

use Local\DataMapper\Entity;
use Local\Main\Admin\Tab\Element\Enum\ElementType;

/**
 * Абстрактный класс элемента вкладки административного интерфейса
 *
 * Определяет базовую структуру и обязательные методы для всех элементов
 * административной вкладки. Наследует базовую функциональность сущности.
 *
 * @package Local\Main\Admin\Tab\Element\Entity
 */
abstract class Element extends Entity
{
    /**
     * Метод рендеринга элемента
     *
     * Должен возвращать HTML-представление элемента
     * для отображения в административном интерфейсе.
     *
     * @return string HTML-код элемента.
     */
    abstract public function render(): string;

    /**
     * Метод преобразования элемента в формат для настроек
     *
     * Должен возвращать данные элемента в формате, пригодном
     * для отображения в разделе настроек административного интерфейса.
     *
     * @return string|array|null Параметры элемента для настроек или null, если не поддерживается.
     */
    abstract public function toAdminSettingsDrawListParams(): string|array|null;

    /**
     * Метод получения типа элемента
     *
     * Должен возвращать строковый идентификатор типа элемента,
     * соответствующий одному из значений ElementType.
     *
     * @return string Тип элемента (значение из ElementType).
     * @see ElementType
     */
    abstract public static function getType(): string;

    /**
     * Метод проверки поддержки настроек элемента
     *
     * Определяет, поддерживает ли элемент настройки
     * в административном интерфейсе.
     *
     * @return bool true если элемент поддерживает настройки, false если нет.
     */
    abstract public static function isSupportAdminSettings(): bool;
}
