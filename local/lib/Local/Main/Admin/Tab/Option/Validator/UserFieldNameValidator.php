<?php

namespace Local\Main\Admin\Tab\Option\Validator;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Local\Main\Admin\Tab\Option\Entity\Option;
use Local\Main\Admin\Tab\Option\Entity\Validator;

/**
 * Класс валидатора для проверки имен пользовательских полей.
 * Проверяет, что имя поля соответствует требованиям (начинается с UF_).
 *
 * @package Local\Main\Admin\Tab\Option\Validator
 */
final class UserFieldNameValidator extends Validator
{
    /**
     * Метод проверяет, что значение является корректным именем пользовательского поля.
     *
     * @param Option $option Опция, для которой выполняется проверка.
     * @param int|bool|array|string|null $value Проверяемое значение.
     *
     * @return Result Результат валидации. Содержит ошибку, если значение не начинается с UF_.
     */
    public function validate(Option $option, int|bool|array|string|null $value): Result
    {
        $result = new Result();

        if (!isset($value)) {
            return $result;
        }

        if (!str_starts_with($value, 'UF_')) {
            $result->addError(new Error('Значение поля ' . $option->getName() . ' должно начинаться с UF_'));
        }

        return $result;
    }
}
