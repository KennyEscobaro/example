<?php

namespace Local\Main\Admin\Tab\Form\Entity;

use Local\DataMapper\Entity;

/**
 * Класс административной формы
 *
 * Представляет конфигурацию HTML-формы в административном интерфейсе,
 * включая основные параметры формы и настройки кнопок.
 *
 * @package Local\Main\Admin\Tab\Form\Entity
 */
class Form extends Entity
{
    /** @var string $name Имя формы (атрибут name) */
    private string $name;

    /** @var string $action URL обработчика формы (атрибут action) */
    private string $action;

    /** @var string $method HTTP-метод отправки формы (GET/POST) */
    private string $method;

    /** @var Buttons|null $buttons Конфигурация кнопок формы */
    private ?Buttons $buttons;

    /**
     * Конструктор административной формы
     *
     * @param string $primary Уникальный идентификатор формы.
     * @param string $name Имя формы.
     * @param string $action URL обработчика формы.
     * @param Buttons|null $buttons Конфигурация кнопок (опционально).
     * @param string $method HTTP-метод (по умолчанию POST).
     */
    public function __construct(
        string $primary,
        string $name,
        string $action,
        ?Buttons $buttons = null,
        string $method = 'POST'
    ) {
        parent::__construct($primary);
        $this->name = $name;
        $this->action = $action;
        $this->method = $method;
        $this->buttons = $buttons;
    }

    /**
     * Получает имя формы
     *
     * @return string Текущее имя формы.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает имя формы
     *
     * @param string $name Новое имя формы.
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Получает URL обработчика формы
     *
     * @return string Текущий URL обработчика.
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Устанавливает URL обработчика формы
     *
     * @param string $action Новый URL обработчика.
     * @return $this
     */
    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Получает HTTP-метод формы
     *
     * @return string Текущий метод (GET/POST).
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Устанавливает HTTP-метод формы
     *
     * @param string $method Новый метод (GET/POST).
     * @return $this
     */
    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Получает конфигурацию кнопок формы
     *
     * @return Buttons|null Объект конфигурации кнопок или null.
     */
    public function getButtons(): ?Buttons
    {
        return $this->buttons;
    }

    /**
     * Устанавливает конфигурацию кнопок формы
     *
     * @param Buttons|null $buttons Новая конфигурация кнопок.
     * @return $this
     */
    public function setButtons(?Buttons $buttons): static
    {
        $this->buttons = $buttons;
        return $this;
    }
}
