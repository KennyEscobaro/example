<?php

namespace Sprint\Migration;

use Bitrix\Main\Type\DateTime;

class create_agent_for_form_synchronization20250514115916 extends Version
{
    protected $author = "admin_morizo_rae";

    protected $description = "122524 | Разработка бэка и подключение вёрстки Заявок | Создание агента по синхронизации форм";

    protected $moduleVersion = "5.0.0";

    /**
     * @return void
     * @throws Exceptions\HelperException
     */
    public function up(): void
    {
        $this->getHelperManager()->Agent()->addAgent($this->getAgent());
    }

    /**
     * @return void
     */
    public function down(): void
    {
        $agent = $this->getAgent();
        $this->getHelperManager()->Agent()->deleteAgent($agent['MODULE_ID'], $agent['NAME']);
    }

    /**
     * @return array
     */
    private function getAgent(): array
    {
        return
            [
                'NAME' => '\Local\FormBuilder\Form\Service\FormManager::runFullFormsSync();',
                'MODULE_ID' => 'local.formbuilder',
                'IS_PERIOD' => 'Y',
                'AGENT_INTERVAL' => 3600,
                'ACTIVE' => 'Y',
                'NEXT_EXEC' => (new DateTime())->format('d.m.Y H:i:s'),
            ];
    }
}
