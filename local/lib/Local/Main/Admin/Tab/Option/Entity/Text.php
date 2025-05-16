<?php

namespace Local\Main\Admin\Tab\Option\Entity;

use Bitrix\Main\ArgumentTypeException;
use Local\Main\Admin\Tab\Element\Enum\ElementType;

/**
 * Класс, реализующий функционал текстового поля для админки.
 *
 * @package Local\Main\Admin\Tab\Option\Entity
 */
class Text extends Option
{
    /** @var int $size */
    private int $size;

    /** @var bool $isDisabled */
    private bool $isDisabled;

    /** @var string $textHint */
    private string $textHint;

    /**
     * Конструктор класса Text.
     *
     * @param string $primary Уникальный идентификатор элемента.
     * @param string $name Название элемента.
     * @param string $defaultValue Значение по умолчанию.
     * @param string|null $value Текущее значение.
     * @param bool $isRequired Обязательное ли поле.
     * @param int $size Размер текстового поля.
     * @param bool $isDisabled Заблокировано ли поле.
     * @param string $textHint Подсказка для поля.
     */
    public function __construct(
        string $primary,
        string $name,
        string $defaultValue = '',
        ?string $value = null,
        bool $isRequired = false,
        int $size = 0,
        bool $isDisabled = false,
        string $textHint = ''
    ) {
        parent::__construct($primary, $name, $value, $defaultValue, $isRequired);
        $this->size = $size;
        $this->isDisabled = $isDisabled;
        $this->textHint = $textHint;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $row = $this->isRequired() ? '<tr class="adm-detail-required-field">' : '<tr>';

        return $row . $this->renderLabel() . $this->renderInput() . '</tr>';
    }

    /**
     * Метод нормализует значение из запроса.
     *
     * @param array $requestData Данные запроса.
     *
     * @return string|null Нормализованное значение или null.
     */
    public function normalizeFromRequest(array $requestData): ?string
    {
        $requestValue = $this->extractValueFromArrayValues($requestData, $this->getPrimary());

        return is_string($requestValue) ? trim($requestValue) : null;
    }

    /**
     * Метод нормализует значение из базы данных.
     *
     * @param string $databaseValue Значение из базы данных.
     *
     * @return string Нормализованное значение.
     */
    public function normalizeFromDatabase(string $databaseValue): string
    {
        return $databaseValue;
    }

    /**
     * Метод подготавливает значение для сохранения в базу данных.
     *
     * @return string Подготовленное значение.
     */
    public function normalizeValueForDatabase(): string
    {
        return $this->getValue() ?? '';
    }

    /**
     * Метод подготавливает значение по умолчанию для сохранения в базу данных.
     *
     * @return string Подготовленное значение.
     */
    public function normalizeDefaultValueForDatabase(): string
    {
        return $this->getDefaultValue() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function toAdminSettingsDrawListParams(): array
    {
        return
            [
                $this->getPrimary(),
                $this->getName(),
                $this->getDefaultValue(),
                [
                    $this->getType(),
                    $this->getSize(),
                ],
                $this->isDisabled() ? 'Y' : 'N',
                $this->getTextHint(),
                'N',
            ];
    }

    /**
     * Метод возвращает размер текстового поля.
     *
     * @return int Размер поля.
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Метод устанавливает размер текстового поля.
     *
     * @param int $size Размер поля.
     *
     * @return $this
     */
    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Метод проверяет, заблокировано ли поле.
     *
     * @return bool Результат проверки.
     */
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    /**
     * Метод устанавливает статус блокировки поля.
     *
     * @param bool $isDisabled Заблокировать ли поле.
     *
     * @return $this
     */
    public function setDisabled(bool $isDisabled): static
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    /**
     * Метод возвращает подсказку для поля.
     *
     * @return string Текст подсказки.
     */
    public function getTextHint(): string
    {
        return $this->textHint;
    }

    /**
     * Метод устанавливает подсказку для поля.
     *
     * @param string $textHint Текст подсказки.
     *
     * @return $this
     */
    public function setTextHint(string $textHint): static
    {
        $this->textHint = $textHint;
        return $this;
    }

    /**
     * Метод устанавливает значение элемента.
     *
     * @param string|null $value Устанавливаемое значение.
     *
     * @return $this
     * @throws ArgumentTypeException Если передан неверный тип значения.
     */
    public function setValue($value): static
    {
        if (!is_string($value) && !is_null($value)) {
            throw new ArgumentTypeException('value');
        }

        $this->value = $value;
        return $this;
    }

    /**
     * Метод устанавливает значение по умолчанию.
     *
     * @param string $defaultValue Значение по умолчанию.
     *
     * @return $this
     * @throws ArgumentTypeException Если передан неверный тип значения.
     */
    public function setDefaultValue($defaultValue): static
    {
        if (!is_string($defaultValue)) {
            throw new ArgumentTypeException('defaultValue');
        }

        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * Метод генерирует HTML-код для метки поля.
     *
     * @return string HTML-код метки.
     */
    protected function renderLabel(): string
    {
        $label = '<td width="50%">';
        $label .= $this->getName();

        if (!empty($this->getTextHint())) {
            $label .= '<span class="required"><sup>' . $this->getTextHint() . '</sup></span>';
        }

        $label .= '<a name="opt_' . $this->getPrimary() . '"></a>';
        $label .= '</td>';

        return $label;
    }

    /**
     * Метод генерирует HTML-код для текстового поля.
     *
     * @return string HTML-код поля.
     */
    protected function renderInput(): string
    {
        $input = '<td width="50%">';

        $inputAttributes = $this->getInputAttributes();
        $input .= '<input ' . implode(' ', $inputAttributes) . '>';

        $input .= '</td>';

        return $input;
    }

    /**
     * Метод возвращает атрибуты для текстового поля.
     *
     * @return array Массив атрибутов.
     */
    protected function getInputAttributes(): array
    {
        $attributes = [];

        $value = $this->getValue();

        if (!isset($value) || (empty($value) && $this->isRequired())) {
            $value = $this->getDefaultValue();
        }

        $attributes[] = 'type="text"';
        $attributes[] = 'maxlength="255"';
        $attributes[] = 'name="' . $this->getPrimary() . '"';
        $attributes[] = 'value="' . $value . '"';

        if ($this->getSize() > 0) {
            $attributes[] = 'size="' . $this->getSize() . '"';
        }

        if ($this->isDisabled()) {
            $attributes[] = 'disabled';
        }

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public static function getType(): string
    {
        return ElementType::TEXT->value;
    }

    /**
     * @inheritDoc
     */
    public static function isSupportAdminSettings(): bool
    {
        return true;
    }
}
