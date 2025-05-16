<?php

namespace Local\FormBuilder\Form\Service;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\Field\ORM\FormFieldTable;
use Local\FormBuilder\Form\Field\Service\FieldCopier;
use Local\FormBuilder\Form\ORM\FormTable;

/**
 * Класс для копирования форм со всеми связанными полями
 *
 * @package Local\FormBuilder\Form\Service
 */
class FormCopier
{
    private Connection $connection;
    private static FormCopier $instance;

    private function __construct()
    {
        $this->connection = Application::getConnection();
    }

    /**
     * Копирует форму со всеми связанными полями
     *
     * @param int $formId Идентификатор исходной формы.
     * @param array $overrideValues Массив значений для переопределения в новой форме.
     *
     * @return Result Результат операции с ID новой формы в data['ID'] при успехе.
     *
     * @throws SqlQueryException При ошибках работы с базой данных.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function copyForm(int $formId, array $overrideValues = []): Result
    {
        $result = new Result();

        $form = $this->getForm($formId);

        if (!$form) {
            $result->addError(new Error('Форма не найдена'));
            return $result;
        }

        if ($overrideValues) {
            $form = array_replace_recursive(
                $form,
                array_intersect_key($overrideValues, $form)
            );
        }

        $formFields = $this->getFormFields($formId);

        $this->connection->startTransaction();

        unset($form['ID']);
        $addFormResult = FormTable::add($form);

        if (!$addFormResult->isSuccess()) {
            $result->addErrors($addFormResult->getErrors());
            $this->connection->rollbackTransaction();
            return $result;
        }

        $newFormId = $addFormResult->getId();
        $addFormFieldsResult = $this->addFormFields($newFormId, $formFields);

        if (!$addFormFieldsResult->isSuccess()) {
            $result->addErrors($addFormFieldsResult->getErrors());
            $this->connection->rollbackTransaction();
            return $result;
        }

        $this->connection->commitTransaction();

        $result->setData(['ID' => $newFormId]);

        return $result;
    }

    /**
     * Добавляет поля формы при копировании
     *
     * @param int $formId Идентификатор новой формы.
     * @param array $fields Массив полей исходной формы.
     *
     * @return Result Результат операции добавления полей.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws SqlQueryException
     */
    private function addFormFields(int $formId, array $fields): Result
    {
        foreach ($fields as $field) {
            $addFieldResult = FieldCopier::getInstance()->copyField($field['ID'], ['FORM_ID' => $formId]);

            if (!$addFieldResult->isSuccess()) {
                return $addFieldResult;
            }
        }

        return new Result();
    }

    /**
     * Получает список полей формы
     *
     * @param int $formId Идентификатор формы.
     *
     * @return array Массив идентификаторов полей формы.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getFormFields(int $formId): array
    {
        $fieldQuery = FormFieldTable::query()
            ->setOrder(['ID' => 'ASC'])
            ->setFilter(['FORM_ID' => $formId])
            ->setSelect(['ID'])
            ->exec();
        $fields = [];

        while ($field = $fieldQuery->fetch()) {
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Получает данные формы
     *
     * @param int $formId Идентификатор формы.
     *
     * @return array Массив данных формы или пустой массив если форма не найдена.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getForm(int $formId): array
    {
        $form = FormTable::query()
            ->setFilter(['ID' => $formId])
            ->setSelect(['*'])
            ->exec()
            ->fetch();

        if (!$form) {
            return [];
        }

        if ($form['STATUS']) {
            $form['STATUS'] = (int)$form['STATUS'];
        }

        $form['XML_ID'] = empty($form['XML_ID']) ? null : $form['XML_ID'];

        return $form;
    }

    /**
     * Возвращает экземпляр класса
     *
     * @return static Экземпляр класса.
     */
    public static function getInstance(): static
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
