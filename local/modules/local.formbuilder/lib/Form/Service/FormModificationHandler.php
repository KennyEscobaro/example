<?php

namespace Local\FormBuilder\Form\Service;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Local\FormBuilder\Form\Enum\Status;
use Local\FormBuilder\Form\ORM\FormTable;

/**
 * Класс, отвечающий за обработку модификаций формы.
 *
 * @package Local\FormBuilder\Form\Service
 */
class FormModificationHandler
{
    private Connection $connection;
    private static FormModificationHandler $instance;

    private function __construct()
    {
        $this->connection = Application::getConnection();
    }

    /**
     * Метод обрабатывает модификацию формы.
     *
     * @param int $formId Идентификатор формы.
     * @param array $form Данные формы.
     *
     * @return Result Результат операции.
     * @throws ArgumentException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws SqlQueryException
     * @throws SystemException
     */
    public function handleFormModification(int $formId, array $form): Result
    {
        $result = new Result();

        $currentForm = $this->getForm($formId);

        if (!$currentForm) {
            $result->addError(new Error('Форма не найдена'));
            return $result;
        }

        $currentStatus = Status::tryFrom($currentForm['STATUS']);
        $newStatus = Status::tryFrom($form['STATUS']);

        if (!isset($currentStatus)) {
            $result->addError(new Error('Неизвестный текущий статус формы'));
            return $result;
        }

        if (!isset($currentStatus)) {
            $result->addError(new Error('Неизвестный новый статус формы'));
            return $result;
        }

        $canChangeFormResult = $this->canChangeForm($form, $currentForm);

        if (!$canChangeFormResult->isSuccess()) {
            $result->addErrors($canChangeFormResult->getErrors());
            return $result;
        }

        $canChangeStatusResult = $this->canChangeStatus($form, $currentForm);

        if (!$canChangeStatusResult->isSuccess()) {
            $result->addErrors($canChangeStatusResult->getErrors());
            return $result;
        }

        if ($currentStatus !== Status::PUBLISHED && $newStatus === Status::PUBLISHED) {
            $archivePreviousVersionResult = $this->archivePreviousVersion($formId, $form, $currentForm);

            if ($archivePreviousVersionResult->isSuccess()) {
                $result->setData($archivePreviousVersionResult->getData());
            } else {
                $result->addErrors($archivePreviousVersionResult->getErrors());
                return $result;
            }
        }

        if ($currentStatus === Status::PUBLISHED) {
            $addVersionResult = $this->addVersion($formId, $form, $currentForm);

            if ($addVersionResult->isSuccess()) {
                $result->setData($addVersionResult->getData());
            } else {
                $result->addErrors($addVersionResult->getErrors());
                return $result;
            }
        }

        return $result;
    }

    /**
     * Метод создает новую версию формы.
     *
     * @param int $formId Идентификатор формы.
     * @param array $form Данные формы.
     * @param array $currentForm Текущие данные формы.
     *
     * @return Result Результат операции.
     * @throws ArgumentException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws SqlQueryException
     * @throws SystemException
     */
    private function addVersion(int $formId, array $form, array $currentForm): Result
    {
        $createFormDate = new DateTime($form['DATE_CREATE']);

        $previousVersionsId = [];

        if ($currentForm['PREVIOUS_VERSIONS']) {
            $previousVersionsId = explode(',', $currentForm['PREVIOUS_VERSIONS']);
        }

        $previousVersionsId[] = $formId;

        $fields =
            [
                'CODE' => $createFormDate->getTimestamp() . '_' . $form['CODE'],
                'STATUS' => Status::EDITING->value,
                'PREVIOUS_VERSIONS' => implode(',', $previousVersionsId),
                'DATE_CREATE' => new DateTime()
            ];

        $copyFormResult = FormCopier::getInstance()->copyForm($formId, $fields);

        if (!$copyFormResult->isSuccess()) {
            return $copyFormResult;
        }

        $copyFormResult->setData([
            'ID' => $copyFormResult->getData()['ID'],
            'FIELDS' => array_replace_recursive(
                $form,
                array_intersect_key($fields, $form)
            )
        ]);

        return $copyFormResult;
    }

    /**
     * Метод архивирует предыдущую версию формы.
     *
     * @param int $formId Идентификатор формы.
     * @param array $form Данные формы.
     * @param array $currentForm Текущие данные формы.
     *
     * @return Result Результат операции.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws SqlQueryException
     * @throws ObjectException
     */
    private function archivePreviousVersion(int $formId, array $form, array $currentForm): Result
    {
        $result = new Result();

        $previousVersionsId = [];

        if ($currentForm['PREVIOUS_VERSIONS']) {
            $previousVersionsId = explode(',', $currentForm['PREVIOUS_VERSIONS']);
        }

        $formQuery = FormTable::query()
            ->setFilter(['ID' => $previousVersionsId, 'STATUS' => Status::PUBLISHED->value])
            ->setSelect(['ID', 'CODE'])
            ->exec();

        $code = '';

        $this->connection->startTransaction();

        while ($formItem = $formQuery->fetch()) {
            $createFormDate = new DateTime($formItem['DATE_CREATE']);
            $updateFormResult = FormTable::update(
                $formItem['ID'],
                [
                    'CODE' => $createFormDate->getTimestamp() . '_' . $formItem['CODE'],
                    'STATUS' => Status::ARCHIVED->value
                ]
            );

            if (!$updateFormResult->isSuccess()) {
                $result->addErrors($updateFormResult->getErrors());
                $this->connection->rollbackTransaction();
                return $result;
            }

            $code = $formItem['CODE'];
        }

        if (empty($code)) {
            $this->connection->commitTransaction();
            return $result;
        }

        $updateFormResult = FormTable::update(
            $formId,
            [
                'CODE' => $code,
            ]
        );

        if (!$updateFormResult->isSuccess()) {
            $result->addErrors($updateFormResult->getErrors());
            $this->connection->rollbackTransaction();
            return $result;
        }

        $this->connection->commitTransaction();

        $result->setData([
            'FIELDS' => array_replace_recursive(
                $form,
                array_intersect_key(['CODE' => $code], $form)
            )
        ]);

        return $result;
    }

    /**
     * Метод проверяет возможность изменения статуса формы.
     *
     * @param array $form Данные формы.
     * @param array $currentForm Текущие данные формы.
     * @return Result Результат проверки.
     */
    private function canChangeStatus(array $form, array $currentForm): Result
    {
        $result = new Result();

        $currentStatus = Status::from($currentForm['STATUS']);
        $newStatus = Status::from($form['STATUS']);

        if ($currentStatus === $newStatus) {
            return $result;
        }

        if ($newStatus === Status::ARCHIVED) {
            $result->addError(new Error('Запрещено в ручную присваивать архивный статус'));
            return $result;
        }

        if ($currentStatus == Status::PUBLISHED && $newStatus != Status::PUBLISHED) {
            $result->addError(new Error('Запрещено изменять статус опубликованным формам'));
            return $result;
        }

        return $result;
    }

    /**
     * Метод проверяет возможность изменения формы.
     *
     * @param array $form Данные формы.
     * @param array $currentForm Текущие данные формы.
     * @return Result Результат проверки.
     */
    private function canChangeForm(array $form, array $currentForm): Result
    {
        $result = new Result();

        $currentStatus = Status::from($currentForm['STATUS']);

        if ($currentStatus === Status::ARCHIVED) {
            $result->addError(new Error('Запрещено редактировать архивную форму'));
            return $result;
        }

        return $result;
    }

    /**
     * Метод получает данные формы по её идентификатору.
     *
     * @param int $formId Идентификатор формы.
     *
     * @return array Данные формы
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
     * Метод возвращает единственный экземпляр класса.
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
