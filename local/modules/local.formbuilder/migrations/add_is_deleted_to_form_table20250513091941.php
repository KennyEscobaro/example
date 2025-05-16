<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Fields\StringField;
use Local\FormBuilder\Form\ORM\FormTable;

class add_is_deleted_to_form_table20250513091941 extends Version
{
    protected $author = "admin_morizo_rae";

    protected $description = "122524 | Разработка бэка и подключение вёрстки Заявок | Добавление колонки \"Удален\" для таблицы \"Формы\"";

    protected $moduleVersion = "5.0.0";

    /**
     * @return void
     * @throws Exceptions\HelperException
     */
    public function up(): void
    {
        $this->getHelperManager()->Sql()->addColumn(
            FormTable::getTableName(),
            (new StringField('IS_DELETED'))->configureRequired()->configureDefaultValue('N')
        );
    }

    /**
     * @return void
     * @throws SqlQueryException
     */
    public function down(): void
    {
        $connection = Application::getConnection();
        $connection->dropColumn(FormTable::getTableName(), 'IS_DELETED');
    }
}
