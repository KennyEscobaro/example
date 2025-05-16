<?php

namespace Local\Main\Admin\Tab\Option\Entity;

use Bitrix\Main\Result;
use Local\Exception\Argument\InvalidArgumentException;
use Local\Main\Admin\Tab\Element\Entity\Element;

/**
 * Абстрактный класс опции административной вкладки
 *
 * Предоставляет базовую функциональность для работы с настройками/опциями
 * в административном интерфейсе, включая валидацию, нормализацию значений
 * и работу с различными типами данных.
 *
 * @package Local\Main\Admin\Tab\Option\Entity
 */
abstract class Option extends Element
{
    /** @var string|bool|array|int|null $value Текущее значение опции */
    protected string|bool|array|int|null $value;

    /** @var string|bool|array|int|null $defaultValue Значение по умолчанию */
    protected string|bool|array|int|null $defaultValue;

    /** @var string $name Имя опции (ключ) */
    private string $name;

    /** @var bool $isRequired Флаг обязательности опции */
    private bool $isRequired;

    /** @var array<Validator|callable> $validators Массив валидаторов */
    protected array $validators = [];

    /**
     * Конструктор опции
     *
     * @param string $primary Уникальный идентификатор опции.
     * @param string $name Имя опции (ключ).
     * @param bool|array|string|int|null $value Текущее значение.
     * @param bool|array|string|int|null $defaultValue Значение по умолчанию.
     * @param bool $isRequired Обязательность опции.
     */
    public function __construct(
        string $primary,
        string $name,
        bool|array|string|int|null $value,
        bool|array|string|int|null $defaultValue,
        bool $isRequired
    ) {
        parent::__construct($primary);
        $this->name = $name;
        $this->value = $value;
        $this->defaultValue = $defaultValue;
        $this->isRequired = $isRequired;
    }

    /**
     * Получает имя опции
     *
     * @return string Имя опции (ключ).
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает имя опции
     *
     * @param string $name Новое имя опции.
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Проверяет обязательность опции
     *
     * @return bool true если опция обязательна, false если нет.
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * Устанавливает обязательность опции
     *
     * @param bool $isRequired Флаг обязательности.
     * @return $this
     */
    public function setRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    /**
     * Получает текущее значение опции
     *
     * @return array|bool|string|int|null Текущее значение.
     */
    public function getValue(): bool|array|string|int|null
    {
        return $this->value;
    }

    /**
     * Получает значение по умолчанию
     *
     * @return array|bool|string|int|null Значение по умолчанию.
     */
    public function getDefaultValue(): bool|array|string|int|null
    {
        return $this->defaultValue;
    }

    /**
     * Проверяет наличие валидаторов
     *
     * @return bool true если есть валидаторы, false если нет.
     */
    public function hasValidators(): bool
    {
        return !empty($this->validators);
    }

    /**
     * Получает все валидаторы
     *
     * @return array<Validator|callable> Массив валидаторов.
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Добавляет валидатор
     *
     * @param Validator|callable $validator Валидатор (объект или callback).
     * @return $this
     * @throws InvalidArgumentException Если передан некорректный валидатор.
     */
    public function addValidator($validator): static
    {
        if (!$validator instanceof Validator && !is_callable($validator, true)) {
            throw new InvalidArgumentException(
                'Валидатор должен быть экземпляром Local\Admin\Tab\Option\Entity\Validator или callback функцией'
            );
        }

        if (is_callable($validator, true) && is_array($validator)) {
            [$objectOrClass, $method] = $validator;
            if (is_string($objectOrClass)) {
                if (!method_exists($objectOrClass, $method)) {
                    throw new InvalidArgumentException(
                        'Метод ' . $method . ' не существует в классе ' . $objectOrClass
                    );
                }
            } elseif (is_object($objectOrClass)) {
                if (!method_exists($objectOrClass, $method)) {
                    throw new InvalidArgumentException(
                        'Метод ' . $method . ' не существует в объекте класса ' . get_class($objectOrClass)
                    );
                }
            }
        } elseif (is_callable($validator, true) && is_string($validator)) {
            if (!function_exists($validator)) {
                throw new InvalidArgumentException(
                    'Функция ' . $validator . ' не существует'
                );
            }
        }

        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Выполняет валидацию значения
     *
     * @param string|bool|array|int|null $value Значение для валидации.
     * @return Result Результат валидации с возможными ошибками.
     */
    public function validate(string|bool|array|int|null $value): Result
    {
        $result = new Result();

        foreach ($this->validators as $validator) {
            if ($validator instanceof Validator) {
                $validationResult = $validator->validate($this, $value);
            } else {
                $validationResult = call_user_func_array($validator, [$this, $value]);
            }

            $result->addErrors($validationResult->getErrors());
        }

        return $result;
    }

    /**
     * Извлекает значение из массива по сложному ключу.
     *
     * Поддерживает доступ к вложенным элементам через нотацию key[subkey].
     *
     * @param array $values Исходный массив значений.
     * @param string $primary Ключ (может быть сложным, например "options[enabled]").
     * @return mixed Найденное значение или null.
     */
    protected function extractValueFromArrayValues(array $values, string $primary): mixed
    {
        if (preg_match('/^([^\[]+)((\[[^\]]+\])+)$/', $primary, $matches)) {
            $baseKey = $matches[1];
            $path = $matches[2];

            preg_match_all('/\[([^\]]+)\]/', $path, $keys);
            $keys = $keys[1];

            $current = $values[$baseKey] ?? null;

            foreach ($keys as $k) {
                if (is_array($current) && array_key_exists($k, $current)) {
                    $current = $current[$k];
                } else {
                    return null;
                }
            }

            return $current;
        }

        return $values[$primary] ?? null;
    }

    /**
     * Устанавливает текущее значение опции
     *
     * @param string|bool|array|int|null $value Новое значение.
     * @return $this
     */
    abstract public function setValue($value): static;

    /**
     * Устанавливает значение по умолчанию
     *
     * @param string|bool|array|int|null $defaultValue Новое значение по умолчанию.
     * @return $this
     */
    abstract public function setDefaultValue($defaultValue): static;

    /**
     * Нормализует значение из данных запроса
     *
     * @param array $requestData Данные запроса.
     * @return mixed Нормализованное значение.
     */
    abstract public function normalizeFromRequest(array $requestData);

    /**
     * Нормализует значение из базы данных
     *
     * @param string $databaseValue Значение из БД.
     * @return mixed Нормализованное значение.
     */
    abstract public function normalizeFromDatabase(string $databaseValue);

    /**
     * Подготавливает значение для сохранения в БД
     *
     * @return string Значение в формате для БД.
     */
    abstract public function normalizeValueForDatabase(): string;

    /**
     * Подготавливает значение по умолчанию для сохранения в БД
     *
     * @return string Значение по умолчанию в формате для БД.
     */
    abstract public function normalizeDefaultValueForDatabase(): string;
}
