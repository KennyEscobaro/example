<?php

namespace Local\Main\Admin\List\Entity;

use Local\DataMapper\Entity;

/**
 * Класс, представляющий строку списка в административном разделе
 *
 * Содержит данные полей и доступных действий для одной строки
 * административного списка. Наследует базовую функциональность сущности.
 *
 * @package Local\Main\Admin\List\Entity
 */
class ListRow extends Entity
{
    /** @var array $fields Массив данных полей строки */
    private array $fields;

    /** @var array $actions Массив доступных действий для строки */
    private array $actions;

    /**
     * Конструктор строки списка
     *
     * @param int $primary Идентификатор строки (первичный ключ).
     * @param array $fields Массив данных полей строки.
     * @param array $actions Массив доступных действий для строки.
     */
    public function __construct(int $primary, array $fields = [], array $actions = [])
    {
        parent::__construct($primary);

        $this->fields = $fields;
        $this->actions = $actions;
    }

    /**
     * Метод возвращает массив данных полей строки
     *
     * @return array Массив полей строки.
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Метод устанавливает данные полей строки
     *
     * @param array $fields Новый массив полей строки.
     * @return $this Сущность с измененными данными полей строки.
     */
    public function setFields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Метод возвращает массив доступных действий для строки
     *
     * @return array Массив действий.
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Метод устанавливает доступные действия для строки
     *
     * @param array $actions Новый массив действий.
     * @return $this Сущность с измененным массивом действий.
     */
    public function setActions(array $actions): static
    {
        $this->actions = $actions;
        return $this;
    }
}
