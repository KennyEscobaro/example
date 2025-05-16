<?php

namespace Local\Main\Admin\Tab\Element\Entity;

use Local\Main\Admin\Tab\Element\Enum\ElementType;

/**
 * Класс элемента подзаголовка для административной вкладки
 *
 * Реализует отображение подзаголовка секции в административном интерфейсе.
 * Наследует функциональность статических элементов. Отображается как заголовок
 * с особым стилем оформления (класс heading).
 *
 * @package Local\Main\Admin\Tab\Element\Entity
 */
final class SubTitle extends StaticElement
{
    /**
     * Конструктор элемента подзаголовка
     *
     * @param string $primary Уникальный идентификатор элемента.
     * @param string $subTitle Текст подзаголовка.
     */
    public function __construct(string $primary, string $subTitle)
    {
        parent::__construct($primary, $subTitle);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return '<tr class="heading"><td colspan="2">' . $this->getSubTitle() . '</td></tr>';
    }

    /**
     * Метод получения текста подзаголовка
     *
     * @return string Текущий текст подзаголовка.
     */
    public function getSubTitle(): string
    {
        return $this->getHtml();
    }

    /**
     * Метод установки текста подзаголовка
     *
     * @param string $value Новый текст подзаголовка.
     * @return $this
     */
    public function setSubTitle(string $value): SubTitle
    {
        return $this->setHtml($value);
    }

    /**
     * @inheritDoc
     */
    public function toAdminSettingsDrawListParams(): string
    {
        return $this->getHtml();
    }

    /**
     * @inheritDoc
     */
    public static function getType(): string
    {
        return ElementType::SUB_TITLE->value;
    }

    /**
     * @inheritDoc
     */
    public static function isSupportAdminSettings(): bool
    {
        return true;
    }
}
