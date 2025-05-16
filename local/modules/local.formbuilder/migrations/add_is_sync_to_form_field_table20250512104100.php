<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Fields\StringField;
use Local\FormBuilder\Form\Field\ORM\FormFieldTable;

class add_is_sync_to_form_field_table20250512104100 extends Version
{
    protected $author = "admin_morizo_rae";

    protected $description = "122524 | Разработка бэка и подключение вёрстки Заявок | Добавление колонки \"Синхронизирован\" для таблицы \"Поля формы\"";

    protected $moduleVersion = "5.0.0";

    /**
     * @return void
     * @throws Exceptions\HelperException
     */
    public function up(): void
    {
        $this->getHelperManager()->Sql()->addColumn(
            FormFieldTable::getTableName(),
            (new StringField('IS_SYNC'))->configureRequired()->configureDefaultValue('N')
        );
    }

    /**
     * @return void
     * @throws SqlQueryException
     */
    public function down(): void
    {
        $connection = Application::getConnection();
        $connection->dropColumn(FormFieldTable::getTableName(), 'IS_SYNC');
    }
}
