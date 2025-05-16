<?php

namespace Local\FormBuilder\Form\ORM;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Exception;
use Local\FormBuilder\Form\Enum\Status;
use Local\FormBuilder\Form\Field\ORM\FormFieldTable;
use Local\FormBuilder\Form\Result\ORM\FormResultTable;

/**
 * Класс для работы с таблицей форм.
 *
 * @package Local\FormBuilder\Form\ORM
 */
class FormTable extends DataManager
{
    /**
     * Метод возвращает имя таблицы в базе данных.
     *
     * @return string Имя таблицы 'lfb_form'.
     */
    public static function getTableName(): string
    {
        return 'lfb_form';
    }

    /**
     * Метод возвращает карту полей таблицы.
     *
     * @return array Массив с описанием полей:
     *               - ID (первичный ключ)
     *               - DATE_CREATE (дата создания)
     *               - ACTIVE (флаг активности)
     *               - CODE (уникальный код)
     *               - XML_ID (внешний идентификатор)
     *               - NAME (название формы)
     */
    public static function getMap(): array
    {
        return
            [
                (new IntegerField('ID'))->configurePrimary()->configureAutocomplete(),
                (new StringField('IS_SYNC'))->configureRequired()->configureDefaultValue('N'),
                (new StringField('IS_DELETED'))->configureRequired()->configureDefaultValue('N'),
                (new DatetimeField('DATE_CREATE'))
                    ->configureRequired()
                    ->configureDefaultValue([self::class, 'getCurrentDateTime']),
                (new EnumField('STATUS'))
                    ->configureRequired()
                    ->configureValues(Status::getStatuses())
                    ->configureDefaultValue(Status::EDITING->value),
                (new StringField('ACTIVE'))->configureRequired()->configureDefaultValue('Y'),
                (new StringField('CODE'))->configureRequired()->configureUnique(),
                (new StringField('XML_ID'))->configureNullable()->configureUnique(),
                (new StringField('NAME'))->configureRequired(),
                (new StringField('PREVIOUS_VERSIONS'))->configureNullable(),
            ];
    }

    /**
     * Добавляет новую запись в таблицу с предварительной обработкой данных
     *
     * @param array $data Массив данных для добавления.
     *
     * @return AddResult
     * @throws Exception
     */
    public static function add(array $data): AddResult
    {
        $data = static::prepareDataBeforeSave($data);
        $result = parent::add($data);

        if (!$result->isSuccess()) {
            return $result;
        }

        $supportFields = self::getSupportFormFields();
        $supportFieldsInData = array_intersect_key($data, $supportFields);

        $supportFieldsInData['ID'] = $result->getId();
        $supportFieldsInData['ACTIVE'] = ($supportFieldsInData['ACTIVE'] === 'Y' && $supportFieldsInData['STATUS'] === Status::PUBLISHED->value) ? 'Y' : 'N';

        $connection = Application::getConnection(SupportFormTable::getConnectionName());
        $connection->startTransaction();

        $addSupportFormResult = SupportFormTable::add($supportFieldsInData);

        if (!$addSupportFormResult->isSuccess()) {
            $connection->rollbackTransaction();
            return $result;
        }

        $updateFormResult = static::update($result->getId(), ['IS_SYNC' => 'Y']);

        if ($updateFormResult->isSuccess()) {
            $connection->commitTransaction();
        } else {
            $connection->rollbackTransaction();
        }

        return $result;
    }

    /**
     * Обновляет существующую запись с предварительной обработкой данных.
     *
     * @param mixed $primary Первичный ключ (ID или массив ключей для составного первичного ключа).
     * @param array $data Массив обновляемых полей.
     *
     * @return UpdateResult
     * @throws Exception
     */
    public static function update($primary, array $data): UpdateResult
    {
        $data = static::prepareDataBeforeSave($data);

        $supportFields = self::getSupportFormFields();
        $supportFieldsInData = array_intersect_key($data, $supportFields);

        if (!isset($data['IS_SYNC']) && !!$supportFieldsInData) {
            $data['IS_SYNC'] = 'N';
        }

        $result = parent::update($primary, $data);

        if (!$result->isSuccess()) {
            return $result;
        }

        if (!!$supportFieldsInData) {
            $form = static::query()
                ->setSelect(array_merge(['ID'], self::getSupportFormFields()))
                ->setFilter(['ID' => $primary])
                ->exec()
                ->fetch();

            if (!$form) {
                return (new UpdateResult())->addError(new Error('Форма не найдена'));
            }

            $isActive = $data['ACTIVE'] === 'Y';
            $isPublished = $data['STATUS'] === Status::PUBLISHED->value;

            $form['ACTIVE'] = ($isActive && $isPublished) ? 'Y' : 'N';

            $connection = Application::getConnection(SupportFormTable::getConnectionName());
            $connection->startTransaction();

            $updateSupportFormResult = self::updateSupportForm($form);

            if (!$updateSupportFormResult->isSuccess()) {
                $connection->rollbackTransaction();
                return $result;
            }

            $updateFormResult = static::update($primary, ['IS_SYNC' => 'Y']);

            if ($updateFormResult->isSuccess()) {
                $connection->commitTransaction();
            } else {
                $connection->rollbackTransaction();
            }
        }

        return $result;
    }

    /**
     * Метод удаляет форму и связанные с ней данные.
     *
     * @param mixed $primary Первичный ключ удаляемой формы
     *
     * @return DeleteResult Результат операции удаления
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function delete($primary): DeleteResult
    {
        $form = static::query()
            ->setSelect(array_merge(['ID'], self::getSupportFormFields()))
            ->setFilter(['ID' => $primary])
            ->exec()
            ->fetch();

        if (!$form) {
            return (new DeleteResult())->addError(new Error('Форма не найдена'));
        }

        $connection = Application::getConnection();
        $connection->startTransaction();

        $result = parent::delete($primary);

        if (!$result->isSuccess()) {
            $connection->commitTransaction();
            return $result;
        }

        $fieldQuery = FormFieldTable::query()->setSelect(['ID'])->setFilter(['FORM_ID' => $primary]);
        $resultQuery = FormResultTable::query()->setSelect(['ID'])->setFilter(['FORM_ID' => $primary]);

        while ($field = $fieldQuery->fetch()) {
            $fieldResult = FormFieldTable::delete($field['ID']);

            if (!$fieldResult->isSuccess()) {
                $result->addErrors($fieldResult->getErrors());
                $connection->rollbackTransaction();
                return $result;
            }
        }

        while ($item = $resultQuery->fetch()) {
            $deleteResult = FormResultTable::delete($item['ID']);

            if (!$deleteResult->isSuccess()) {
                $result->addErrors($deleteResult->getErrors());
                $connection->rollbackTransaction();
                return $result;
            }
        }

        $form['ACTIVE'] = 'N';

        $updateSupportFormResult = self::updateSupportForm($form);

        if (!$updateSupportFormResult->isSuccess()) {
            $connection->rollbackTransaction();
            static::update($primary, ['IS_SYNC' => 'N', 'IS_DELETED' => 'Y']);
            return $result;
        }

        $connection->commitTransaction();

        return $result;
    }

    /**
     * Метод возвращает текущую дату и время.
     *
     * @return DateTime Объект с текущей датой и временем
     */
    public static function getCurrentDateTime(): DateTime
    {
        return new DateTime();
    }

    public static function getSupportFormFields(): array
    {
        $fields = [];

        foreach (SupportFormTable::getMap() as $field) {
            $fields[$field->getName()] = $field->getName();
        }

        return $fields;
    }

    /**
     * Подготавливает данные полей перед сохранением в базу данных
     *
     * @param array $data Массив данных полей для сохранения.
     *
     * @return array Обработанный массив данных, готовый к сохранению.
     */
    protected static function prepareDataBeforeSave(array $data): array
    {
        if (isset($data['XML_ID'])) {
            $data['XML_ID'] = empty($data['XML_ID']) ? null : $data['XML_ID'];
        }

        if (isset($data['STATUS'])) {
            $data['STATUS'] = (int)$data['STATUS'];
        }

        return $data;
    }

    private static function updateSupportForm(array $form): UpdateResult|AddResult
    {
        if (isset($form)) {
            $formId = $form['ID'];
            unset($form['ID']);

            return SupportFormTable::update($formId, $form);
        }

        return SupportFormTable::add($form);
    }
}
