<?php

namespace Local\Main\Admin\Tab\Option\Entity;

use Bitrix\Main\Result;

/**
 * Абстрактный класс валидатора для проверки значений опций.
 *
 * @package Local\Main\Admin\Tab\Option\Entity
 */
abstract class Validator
{
    /**
     * Метод выполняет валидацию значения опции.
     *
     * @param Option $option Опция для валидации.
     * @param string|bool|array|int|null $value Значение для проверки.
     *
     * @return Result Результат валидации, содержащий информацию об ошибках при наличии.
     */
    abstract public function validate(Option $option, string|bool|array|int|null $value): Result;
}
