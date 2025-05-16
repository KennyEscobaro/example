<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\Result\ORM\FormResultTable;

class create_table_form_result20250324002226 extends Version
{
    protected $description = "121104 | Реализация конструктора форм заявки на услуги | Создание таблицы \"Результаты\"";

    protected $moduleVersion = "4.2.4";

    /**
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function up(): void
    {
        FormResultTable::getEntity()->createDbTable();
    }

    /**
     * @return void
     * @throws SqlQueryException
     */
    public function down(): void
    {
        $connection = Application::getConnection();
        $connection->dropTable(FormResultTable::getTableName());
    }
}
