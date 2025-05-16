<?php

use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Local\FormBuilder\Main\Admin\Tab\Service\TabBuild;
use Local\Main\Admin\Tab\Collection\TabCollection;
use Local\Main\Admin\Tab\Form\Entity\Form;
use Local\Main\Admin\Tab\Service\TabDraw;

$request = HttpApplication::getInstance()->getContext()->getRequest();

$moduleId = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);

if (Loader::includeModule($moduleId)) {
    $adminTabBuilder = new TabBuild($request);
    $adminTabBuilderResult = $adminTabBuilder->build();

    /** @var TabCollection $tabCollection */
    $tabCollection = $adminTabBuilderResult->getData()['TAB_COLLECTION'] ?? [];

    /** @var Form $form */
    $form = $adminTabBuilderResult->getData()['FORM'];

    if (!isset($tabCollection)) {
        return;
    }

    if (!$adminTabBuilderResult->isSuccess()) {
        $errorMessagesStr = implode(PHP_EOL, $adminTabBuilderResult->getErrorMessages());
        CAdminMessage::ShowMessage($errorMessagesStr);
    }

    (new TabDraw($moduleId))->drawTabs('tabControl', $form, $tabCollection);
} else {
    ShowError('Модуль ' . $moduleId . ' не установлен');
}
