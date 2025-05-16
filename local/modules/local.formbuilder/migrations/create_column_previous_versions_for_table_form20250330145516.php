<?php

namespace Sprint\Migration;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\ORM\FormTable;

class create_column_previous_versions_for_table_form20250330145516 extends Version
{
    protected $description = "121104 | Реализация конструктора форм заявки на услуги | Создание столбца \"Предыдущие версии\" для таблицы \"Форма\"";

    protected $moduleVersion = "4.2.4";

    /**
     * @return bool
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws SystemException
     */
    public function up(): bool
    {
        $entity = $this->getEntity();
        $connection = $entity->getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $tableName = $entity->getDBTableName();
        $columnName = $this->getColumnName();

        if (isset($connection->getTableFields($tableName)[$columnName])) {
            $this->out('Столбец ' . $columnName . ' уже существует в БД');
            return true;
        }

        $field = $entity->getField($columnName);

        if (!$field) {
            $this->outError('Поле ' . $columnName . ' не найдено в getMap()');
            return false;
        }

        $columnDefinition = $sqlHelper->getColumnTypeByField($field);

        $nullable = $field->isRequired() ? 'NOT NULL' : 'NULL';
        $defaultValue = $field->getDefaultValue() ? 'DEFAULT \'' . $sqlHelper->forSql(
                $field->getDefaultValue()
            ) . '\'' : '';

        $sql = 'ALTER TABLE ' . $sqlHelper->quote($tableName) .
            ' ADD ' . $sqlHelper->quote($columnName) . ' ' . $columnDefinition . ' ' . $nullable . ' ' . $defaultValue;

        $connection->queryExecute($sql);

        $this->outSuccess('Столбец ' . $columnName . ' успешно добавлен');

        return true;
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws SystemException
     * @throws SqlQueryException
     */
    public function down(): bool
    {
        $entity = $this->getEntity();
        $connection = $entity->getConnection();
        $sqlHelper = $connection->getSqlHelper();
        $tableName = $entity->getDBTableName();
        $columnName = $this->getColumnName();

        if (!isset($connection->getTableFields($tableName)[$columnName])) {
            $this->out('Столбец ' . $columnName . ' не существует в БД');
            return true;
        }

        $sql = 'ALTER TABLE ' . $sqlHelper->quote($tableName) .
            ' DROP COLUMN ' . $sqlHelper->quote($columnName);

        $connection->queryExecute($sql);

        $this->outSuccess('Столбец ' . $columnName . ' успешно удалён');
        return true;
    }

    /**
     * @return string
     */
    private function getColumnName(): string
    {
        return 'PREVIOUS_VERSIONS';
    }

    /**
     * @return Entity
     * @throws ArgumentException
     * @throws SystemException
     */
    private function getEntity(): Entity
    {
        return FormTable::getEntity();
    }
}
