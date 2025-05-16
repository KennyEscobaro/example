<?php

namespace Local\Main\Admin\Tab\Form\Entity;

use Local\DataMapper\Entity;

/**
 * Класс конфигурации кнопок административной формы
 *
 * Управляет отображением и состоянием стандартных и пользовательских кнопок
 * в административных формах Bitrix. Позволяет настроить видимость стандартных кнопок
 * (Сохранить, Применить, Отмена), добавить кастомные кнопки и управлять состоянием формы.
 *
 * @package Local\Main\Admin\Tab\Form\Entity
 */
class Buttons extends Entity
{
    /** @var bool $btnSave Флаг отображения кнопки "Сохранить" */
    private bool $btnSave;

    /** @var bool $btnApply Флаг отображения кнопки "Применить" */
    private bool $btnApply;

    /** @var bool $btnCancel Флаг отображения кнопки "Отмена" */
    private bool $btnCancel;

    /** @var bool $btnSaveAndAdd Флаг отображения кнопки "Сохранить и добавить" */
    private bool $btnSaveAndAdd;

    /** @var array $htmlCustomButtons Массив HTML-кодов кастомных кнопок */
    private array $htmlCustomButtons;

    /** @var bool $isDisabled Флаг блокировки всех кнопок формы */
    private bool $isDisabled;

    /** @var string $backUrl URL для возврата (используется кнопкой "Отмена") */
    private string $backUrl;

    /**
     * Конструктор конфигурации кнопок
     *
     * @param bool $btnSave Показывать кнопку "Сохранить" (по умолчанию true).
     * @param bool $btnApply Показывать кнопку "Применить" (по умолчанию true).
     * @param bool $btnCancel Показывать кнопку "Отмена" (по умолчанию true).
     * @param bool $btnSaveAndAdd Показывать кнопку "Сохранить и добавить" (по умолчанию false).
     * @param array $htmlCustomButtons Массив кастомных кнопок в HTML-формате (по умолчанию пустой).
     * @param bool $isDisabled Блокировать все кнопки (по умолчанию false).
     * @param string $backUrl URL для возврата (по умолчанию пустая строка).
     */
    public function __construct(
        bool $btnSave = true,
        bool $btnApply = true,
        bool $btnCancel = true,
        bool $btnSaveAndAdd = false,
        array $htmlCustomButtons = [],
        bool $isDisabled = false,
        string $backUrl = ''
    ) {
        parent::__construct('');
        $this->btnSave = $btnSave;
        $this->btnApply = $btnApply;
        $this->btnCancel = $btnCancel;
        $this->btnSaveAndAdd = $btnSaveAndAdd;
        $this->htmlCustomButtons = $htmlCustomButtons;
        $this->isDisabled = $isDisabled;
        $this->backUrl = $backUrl;
    }

    /**
     * Проверяет отображение кнопки "Сохранить"
     *
     * @return bool true если кнопка отображается, false если скрыта.
     */
    public function isBtnSave(): bool
    {
        return $this->btnSave;
    }

    /**
     * Устанавливает отображение кнопки "Сохранить"
     *
     * @param bool $btnSave Флаг отображения.
     * @return $this
     */
    public function setBtnSave(bool $btnSave): static
    {
        $this->btnSave = $btnSave;
        return $this;
    }

    /**
     * Проверяет отображение кнопки "Применить"
     *
     * @return bool true если кнопка отображается, false если скрыта.
     */
    public function isBtnApply(): bool
    {
        return $this->btnApply;
    }

    /**
     * Устанавливает отображение кнопки "Применить"
     *
     * @param bool $btnApply Флаг отображения.
     * @return $this
     */
    public function setBtnApply(bool $btnApply): static
    {
        $this->btnApply = $btnApply;
        return $this;
    }

    /**
     * Проверяет отображение кнопки "Отмена"
     *
     * @return bool true если кнопка отображается, false если скрыта.
     */
    public function isBtnCancel(): bool
    {
        return $this->btnCancel;
    }

    /**
     * Устанавливает отображение кнопки "Отмена"
     *
     * @param bool $btnCancel Флаг отображения.
     * @return $this
     */
    public function setBtnCancel(bool $btnCancel): static
    {
        $this->btnCancel = $btnCancel;
        return $this;
    }

    /**
     * Проверяет отображение кнопки "Сохранить и добавить"
     *
     * @return bool true если кнопка отображается, false если скрыта.
     */
    public function isBtnSaveAndAdd(): bool
    {
        return $this->btnSaveAndAdd;
    }

    /**
     * Устанавливает отображение кнопки "Сохранить и добавить"
     *
     * @param bool $btnSaveAndAdd Флаг отображения.
     * @return $this
     */
    public function setBtnSaveAndAdd(bool $btnSaveAndAdd): static
    {
        $this->btnSaveAndAdd = $btnSaveAndAdd;
        return $this;
    }

    /**
     * Получает массив кастомных кнопок
     *
     * @return array Массив HTML-кодов кастомных кнопок.
     */
    public function getHtmlCustomButtons(): array
    {
        return $this->htmlCustomButtons;
    }

    /**
     * Устанавливает кастомные кнопки
     *
     * @param array $htmlCustomButtons Массив HTML-кодов кнопок.
     * @return $this
     */
    public function setHtmlCustomButtons(array $htmlCustomButtons): static
    {
        $this->htmlCustomButtons = $htmlCustomButtons;
        return $this;
    }

    /**
     * Проверяет блокировку всех кнопок формы
     *
     * @return bool true если кнопки заблокированы, false если активны.
     */
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    /**
     * Устанавливает блокировку всех кнопок формы
     *
     * @param bool $isDisabled Флаг блокировки.
     * @return $this
     */
    public function setIsDisabled(bool $isDisabled): static
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    /**
     * Получает URL для возврата
     *
     * @return string URL для перенаправления кнопкой "Отмена".
     */
    public function getBackUrl(): string
    {
        return $this->backUrl;
    }

    /**
     * Устанавливает URL для возврата
     *
     * @param string $backUrl URL для перенаправления.
     * @return $this
     */
    public function setBackUrl(string $backUrl): static
    {
        $this->backUrl = $backUrl;
        return $this;
    }

    /**
     * Преобразует конфигурацию в массив
     *
     * @return array Ассоциативный массив с настройками кнопок:
     *              - btnSave: bool
     *              - btnApply: bool
     *              - btnCancel: bool
     *              - btnSaveAndAdd: bool
     *              - htmlCustomButtons: array
     *              - disabled: bool
     *              - back_url: string.
     */
    public function toArray(): array
    {
        return [
            'btnSave' => $this->isBtnSave(),
            'btnApply' => $this->isBtnApply(),
            'btnCancel' => $this->isBtnCancel(),
            'btnSaveAndAdd' => $this->isBtnSaveAndAdd(),
            'htmlCustomButtons' => $this->getHtmlCustomButtons(),
            'disabled' => $this->isDisabled(),
            'back_url' => $this->getBackUrl(),
        ];
    }
}
