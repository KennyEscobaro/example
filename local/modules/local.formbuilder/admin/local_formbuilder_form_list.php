<?php

use Bitrix\Main\Loader;
use Local\FormBuilder\Form\Admin\List\Service\FormListBuilder;
use Local\FormBuilder\Helper\ModuleHelper;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

/**
 * @global CMain $APPLICATION
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/prolog.php');

$APPLICATION->SetTitle('Список форм');

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

if (Loader::includeModule('local.formbuilder')) {
    if (ModuleHelper::getModulePermission() === 'D') {
        ShowError('Доступ запрещен');
    } else {
        (new FormListBuilder(basename(__FILE__), false))->build();
    }
} else {
    ShowError('Модуль local.formbuilder не установлен');
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
