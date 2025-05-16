<?php

namespace Local\Main\Admin\Tab\Entity;

use Local\DataMapper\Entity;
use Local\Main\Admin\Tab\Element\Collection\ElementCollection;

/**
 * Класс вкладки административного интерфейса
 *
 * Представляет отдельную вкладку в административном разделе,
 * содержащую коллекцию элементов и дополнительные параметры отображения.
 * Наследует базовую функциональность сущности.
 *
 * @package Local\Main\Admin\Tab\Entity
 */
class Tab extends Entity
{
    /** @var string $name Системное имя вкладки */
    private string $name;

    /** @var string $title Заголовок вкладки для отображения */
    private string $title;

    /** @var ElementCollection $elements Коллекция элементов вкладки */
    private ElementCollection $elements;

    /** @var string $includedFilePath Путь к включаемому файлу (если требуется) */
    private string $includedFilePath;

    /**
     * Конструктор вкладки
     *
     * @param string $primary Уникальный идентификатор вкладки.
     * @param string $name Системное имя вкладки.
     * @param string $title Заголовок для отображения.
     * @param ElementCollection $elements Коллекция элементов вкладки.
     * @param string $includedFilePath Путь к дополнительному включаемому файлу (по умолчанию пустая строка).
     */
    public function __construct(
        string $primary,
        string $name,
        string $title,
        ElementCollection $elements,
        string $includedFilePath = ''
    ) {
        parent::__construct($primary);
        $this->name = $name;
        $this->title = $title;
        $this->elements = $elements;
        $this->includedFilePath = $includedFilePath;
    }

    /**
     * Получение системного имени вкладки
     *
     * @return string Системное имя вкладки.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Установка системного имени вкладки
     *
     * @param string $name Новое системное имя.
     * @return $this
     */
    public function setName(string $name): Tab
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Получение заголовка вкладки
     *
     * @return string Текущий заголовок вкладки.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Установка заголовка вкладки
     *
     * @param string $title Новый заголовок.
     * @return $this
     */
    public function setTitle(string $title): Tab
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Получение коллекции элементов вкладки
     *
     * @return ElementCollection Коллекция элементов.
     */
    public function getElements(): ElementCollection
    {
        return $this->elements;
    }

    /**
     * Установка коллекции элементов
     *
     * @param ElementCollection $elements Новая коллекция элементов.
     * @return $this
     */
    public function setElements(ElementCollection $elements): Tab
    {
        $this->elements = $elements;
        return $this;
    }

    /**
     * Получение пути к включаемому файлу
     *
     * @return string Путь к файлу или пустая строка.
     */
    public function getIncludedFilePath(): string
    {
        return $this->includedFilePath;
    }

    /**
     * Установка пути к включаемому файлу
     *
     * @param string $includedFilePath Новый путь к файлу.
     * @return $this
     */
    public function setIncludedFilePath(string $includedFilePath): static
    {
        $this->includedFilePath = $includedFilePath;
        return $this;
    }

    /**
     * Преобразование вкладки в формат для настроек
     *
     * Возвращает параметры вкладки в формате, пригодном для использования
     * в административных настройках Bitrix.
     *
     * @return array Массив параметров:
     *              - DIV: Идентификатор вкладки
     *              - TAB: Системное имя
     *              - TITLE: Заголовок
     *              - OPTIONS: Параметры элементов (из коллекции).
     */
    public function toAdminSettingsDrawListParams(): array
    {
        return [
            'DIV' => $this->getPrimary(),
            'TAB' => $this->getName(),
            'TITLE' => $this->getTitle(),
            'OPTIONS' => $this->elements->toAdminSettingsDrawListParams()
        ];
    }
}
