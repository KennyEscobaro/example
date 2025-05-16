<?php

namespace Local\FormBuilder\EventHandler\Main\AdminPage;

use Local\FormBuilder\Helper\ModuleHelper;
use ReflectionException;

/**
 * Класс обработчика события построения глобального меню админки.
 * Добавляет пункты меню для модуля "Конструктор форм".
 *
 * @package Local\FormBuilder\EventHandler\Main\AdminPage
 */
class OnBuildGlobalMenu
{
    /**
     * Метод обработчик события OnBuildGlobalMenu.
     * Добавляет пункты меню при наличии прав на чтение модуля.
     *
     * @param array &$globalMenu Ссылка на массив глобального меню.
     * @param array &$menuItems Ссылка на массив пунктов меню.
     *
     * @return void
     * @throws ReflectionException
     */
    public static function handler(array &$globalMenu, array &$menuItems): void
    {
        if (ModuleHelper::getModulePermission() >= 'R') {
            $globalMenu = array_merge($globalMenu, self::getAdditionalGlobalMenu());
            $menuItems = array_merge($menuItems, self::getAdditionalMenuItems());
        }
    }

    /**
     * Метод возвращает дополнительные элементы глобального меню.
     *
     * @return array[] Массив с элементами глобального меню.
     */
    private static function getAdditionalGlobalMenu(): array
    {
        return
            [
                'global_menu_project_setting' =>
                    [
                        'menu_id' => 'project_setting',
                        'text' => 'Настройки проекта',
                        'title' => 'Управление настройками проекта',
                        'sort' => '550',
                        'items_id' => 'project_setting',
                        'help_section' => 'project_setting',
                        'items' => [],
                    ],
            ];
    }

    /**
     * Метод возвращает дополнительные пункты меню модуля.
     *
     * @return array[] Массив с пунктами меню.
     */
    private static function getAdditionalMenuItems(): array
    {
        return
            [
                [
                    'parent_menu' => 'global_menu_project_setting',
                    'section' => 'form',
                    'sort' => 0,
                    'text' => 'Веб-формы',
                    'title' => 'Веб-формы',
                    'icon' => 'form_menu_icon',
                    'page_icon' => 'form_menu_icon',
                    'items_id' => 'menu_form',
                    'items' =>
                        [
                            [
                                'text' => 'Настройки модуля',
                                'url' => '/bitrix/admin/settings.php?lang=ru&mid=local.formbuilder',
                                'title' => 'Настройки модуля',
                            ],
                            [
                                'text' => 'Список форм',
                                'url' => '/bitrix/admin/local_formbuilder_form_list.php',
                                'title' => 'Список форм',
                                'more_url' =>
                                    [
                                        'local_formbuilder_field_edit.php',
                                        'local_formbuilder_field_list.php',
                                        'local_formbuilder_form_edit.php',
                                        'local_formbuilder_result_edit.php',
                                        'local_formbuilder_result_list.php',
                                    ]
                            ],
                        ],
                ],
            ];
    }
}
