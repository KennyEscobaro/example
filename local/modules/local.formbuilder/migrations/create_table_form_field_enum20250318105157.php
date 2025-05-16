<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\Field\ORM\FormFieldEnumTable;

class create_table_form_field_enum20250318105157 extends Version
{
    protected $description = "121104 | Реализация конструктора форм заявки на услуги | Создание таблицы \"Значения поля формы\" ";

    protected $moduleVersion = "4.2.4";

    /**
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function up(): void
    {
        FormFieldEnumTable::createDbTable();
    }

    /**
     * @return void
     * @throws SqlQueryException
     */
    public function down(): void
    {
        $connection = Application::getConnection();
        $connection->dropTable(FormFieldEnumTable::getTableName());
    }
}
