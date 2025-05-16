<?php

namespace Local\FormBuilder\Main\Admin\Tab\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Local\Exception\Argument\InvalidArgumentException;
use Local\FormBuilder\Helper\ModuleHelper;
use Local\Main\Admin\Tab\Collection\TabCollection;
use Local\Main\Admin\Tab\Element\Collection\ElementCollection;
use Local\Main\Admin\Tab\Entity\Tab;
use Local\Main\Module\Admin\Tab\Service\AbstractTabBuild;
use ReflectionException;

/**
 * Класс для построения административных вкладок модуля FormBuilder.
 *
 * @package Local\FormBuilder\Main\Admin\Tab\Service
 */
class TabBuild extends AbstractTabBuild
{
    /** @var string Идентификатор модуля */
    private string $moduleId;

    /** @var string Права доступа модуля */
    private string $modulePermission;

    /** @var array Параметры модуля */
    private array $moduleParams;

    /**
     * Конструктор класса TabBuild.
     *
     * @param HttpRequest|null $request HTTP-запрос
     * @throws ArgumentNullException
     * @throws ReflectionException
     */
    public function __construct(?HttpRequest $request)
    {
        parent::__construct($request);

        $this->moduleId = ModuleHelper::getModuleId();
        $this->modulePermission = ModuleHelper::getModulePermission();
        $this->moduleParams = ModuleHelper::getModuleParams();
    }

    /**
     * @inheritDoc
     */
    public function getModulePermission(): string
    {
        return $this->modulePermission;
    }

    /**
     * @inheritDoc
     */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    /**
     * @inheritDoc
     */
    public function getModuleParams(): array
    {
        return $this->moduleParams;
    }

    /**
     * Метод создает и возвращает коллекцию вкладок.
     *
     * @return TabCollection Коллекция вкладок
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getTabCollection(): TabCollection
    {
        $tabCollection = new TabCollection();

        if ($this->currentUser->isAdmin()) {
            $tabCollection->add($this->getAccessRightsTab());
        }

        return $tabCollection;
    }

    /**
     * Метод создает и возвращает вкладку "Права доступа".
     *
     * @return Tab Вкладка с правами доступа
     */
    private function getAccessRightsTab(): Tab
    {
        return new Tab(
            'ACCESS_RIGHTS',
            'Права доступа',
            'Права доступа',
            new ElementCollection(),
            $this->documentRoot . '/bitrix/modules/main/admin/group_rights.php'
        );
    }

    /**
     * @inheritDoc
     */
    public static function getFormId(): string
    {
        return 'local_formbuilder_settings';
    }

    /**
     * @inheritDoc
     */
    public static function getFormName(): string
    {
        return 'local_formbuilder_settings';
    }
}
