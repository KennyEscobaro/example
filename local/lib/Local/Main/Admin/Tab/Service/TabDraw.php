<?php

namespace Local\Main\Admin\Tab\Service;

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;
use CAdminTabControl;
use Local\Main\Admin\Tab\Collection\TabCollection;
use Local\Main\Admin\Tab\Entity\Tab;
use Local\Main\Admin\Tab\Form\Entity\Form;
use Local\Main\Admin\Tab\Option\Entity\Option;

/**
 * Класс для отрисовки вкладок административной панели.
 * Обеспечивает визуальное представление и функционал вкладок настроек.
 *
 * @package Local\Main\Admin\Tab\Service
 */
class TabDraw
{
    /** @var string $moduleId Идентификатор модуля */
    private string $moduleId;

    /** @var HttpRequest $request HTTP-запрос */
    protected HttpRequest $request;

    /**
     * Конструктор класса TabDraw.
     *
     * @param string $moduleId Идентификатор модуля.
     */
    public function __construct(string $moduleId = '')
    {
        $this->moduleId = $moduleId;
        $this->request = Application::getInstance()->getContext()->getRequest();
    }

    /**
     * Метод отрисовывает вкладки административной панели.
     *
     * @param string $tabControlName Имя элемента управления вкладками.
     * @param Form $form Объект формы.
     * @param TabCollection $tabCollection Коллекция вкладок.
     *
     * @return void
     */
    public function drawTabs(string $tabControlName, Form $form, TabCollection $tabCollection): void
    {
        $tabControl = new CAdminTabControl($tabControlName, $tabCollection->toAdminSettingsDrawListParams());
        $tabControl->Begin();

        echo '<form ' . implode(' ', $this->getFormAttributes($form)) . '>';
        echo bitrix_sessid_post();

        foreach ($tabCollection as $tab) {
            $tabControl->BeginNextTab();
            echo $this->renderTab($tab);
        }

        echo '</form>';

        $this->drawButtons($tabControl, $form);

        $tabControl->End();
    }

    /**
     * Метод отрисовывает кнопки формы.
     *
     * @param CAdminTabControl $tabControl Объект управления вкладками.
     * @param Form $form Объект формы.
     *
     * @return void
     */
    private function drawButtons(CAdminTabControl $tabControl, Form $form): void
    {
        $buttons = $form->getButtons();

        if (!$buttons) {
            return;
        }

        $tabControl->Buttons($buttons->toArray());

        foreach ($buttons->getHtmlCustomButtons() as $htmlCustomButton) {
            echo $htmlCustomButton;
        }
    }

    /**
     * Метод рендерит содержимое вкладки.
     *
     * @param Tab $tab Объект вкладки.
     *
     * @return string HTML-код вкладки.
     */
    private function renderTab(Tab $tab): string
    {
        if (!empty($tab->getIncludedFilePath())) {
            ob_start();
            global $APPLICATION;
            $module_id = $this->moduleId;
            $Update = $this->request->get('save') || $this->request->get('apply');
            $REQUEST_METHOD = $this->request->getRequestMethod();
            $GROUPS = $this->request->get('GROUPS');
            $RIGHTS = $this->request->get('RIGHTS');
            $SITES = $this->request->get('SITES');

            require($tab->getIncludedFilePath());
            $tab = ob_get_contents();
            ob_end_clean();

            return $tab;
        }

        $renderedTab = '';

        /** @var Option $option */
        foreach ($tab->getElements() as $element) {
            $renderedTab .= $element->render();
        }

        return $renderedTab;
    }

    /**
     * Метод возвращает атрибуты формы.
     *
     * @param Form $form Объект формы.
     *
     * @return array Массив атрибутов формы.
     */
    private function getFormAttributes(Form $form): array
    {
        $attributes = [];

        $attributes[] = 'action="' . $form->getAction() . '"';
        $attributes[] = 'id="' . $form->getPrimary() . '"';
        $attributes[] = 'name="' . $form->getName() . '"';
        $attributes[] = 'method="' . $form->getMethod() . '"';

        return $attributes;
    }
}
