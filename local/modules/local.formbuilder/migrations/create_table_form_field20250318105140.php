<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\Field\ORM\FormFieldTable;

class create_table_form_field20250318105140 extends Version
{
    protected $description = "121104 | Реализация конструктора форм заявки на услуги | Создание таблицы \"Поля формы\" ";

    protected $moduleVersion = "4.2.4";

    /**
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function up(): void
    {
        FormFieldTable::createDbTable();
    }

    /**
     * @return void
     * @throws SqlQueryException
     */
    public function down(): void
    {
        $connection = Application::getConnection();
        $connection->dropTable(FormFieldTable::getTableName());
    }
}
