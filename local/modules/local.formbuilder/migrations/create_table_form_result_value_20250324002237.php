<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\Result\ORM\FormResultValueTable;

class create_table_form_result_value_20250324002237 extends Version
{
    protected $description = "121104 | Реализация конструктора форм заявки на услуги | Создание таблицы \"Значения результатов\"";

    protected $moduleVersion = "4.2.4";

    /**
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function up(): void
    {
        FormResultValueTable::getEntity()->createDbTable();
    }

    /**
     * @return void
     * @throws SqlQueryException
     */
    public function down(): void
    {
        $connection = Application::getConnection();
        $connection->dropTable(FormResultValueTable::getTableName());
    }
}
