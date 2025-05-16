<?php

namespace Local\Main\Admin\Tab\Option\Validator;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Local\Main\Admin\Tab\Option\Entity\Option;
use Local\Main\Admin\Tab\Option\Entity\Validator;

/**
 * Класс валидатора целочисленных значений.
 *
 * @package Local\Main\Admin\Tab\Option\Validator
 */
final class IntegerValidator extends Validator
{
    /**
     * Метод выполняет валидацию целочисленного значения.
     *
     * @param Option $option Опция для валидации.
     * @param int|bool|array|string|null $value Значение для проверки.
     *
     * @return Result Результат валидации. Содержит ошибку, если значение не является целым числом.
     */
    public function validate(Option $option, int|bool|array|string|null $value): Result
    {
        $result = new Result();

        if (!isset($value)) {
            return $result;
        }

        if (!is_numeric($value) || (int)$value != $value) {
            $result->addError(new Error('Значение поля "' . rtrim($option->getName(), ':') . '" должно быть целым числом'));
        }

        return $result;
    }
}
