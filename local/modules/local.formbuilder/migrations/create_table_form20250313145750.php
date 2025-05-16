<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\ORM\FormTable;

class create_table_form20250313145750 extends Version
{
    protected $description = "121104 | Реализация конструктора форм заявки на услуги | Создание таблицы \"Формы\"";

    protected $moduleVersion = "4.2.4";

    /**
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function up(): void
    {
        FormTable::getEntity()->createDbTable();
    }

    /**
     * @return void
     * @throws SqlQueryException
     */
    public function down(): void
    {
        $connection = Application::getConnection();
        $connection->dropTable(FormTable::getTableName());
    }
}
