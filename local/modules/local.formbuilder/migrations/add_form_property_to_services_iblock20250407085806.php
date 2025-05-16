<?php

namespace Sprint\Migration;

class add_form_property_to_services_iblock20250407085806 extends Version
{
    protected $description = "121104 | Реализация конструктора форм заявки на услуги | Создание свойства \"Форма\" для инфоблока \"Услуги\"";

    protected $moduleVersion = "4.2.4";

    /**
     * @return bool
     * @throws Exceptions\HelperException
     */
    public function up(): bool
    {
        $helper = $this->getHelperManager();
        $iblockId = $this->getIblockId();
        $propertyId = $helper->Iblock()->saveProperty($iblockId, $this->getProperty());

        if (!$propertyId) {
            $this->outError('Не удалось создать свойство');
            return false;
        }

        $isElementFormSaved = $helper->UserOptions()->saveElementForm($iblockId, $this->getElementForm());

        if (!$isElementFormSaved) {
            $this->outError('Не удалось создать форму редактирования');
            return false;
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exceptions\HelperException
     */
    public function down(): bool
    {
        $helper = $this->getHelperManager();
        $iblockId = $this->getIblockId();
        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->getProperty()['CODE']);

        return true;
    }

    /**
     * @return array
     */
    private function getProperty(): array
    {
        return
            [
                'NAME' => 'Форма',
                'ACTIVE' => 'Y',
                'SORT' => '500',
                'CODE' => 'FORM',
                'DEFAULT_VALUE' => '',
                'PROPERTY_TYPE' => 'N',
                'ROW_COUNT' => '1',
                'COL_COUNT' => '30',
                'LIST_TYPE' => 'L',
                'MULTIPLE' => 'N',
                'XML_ID' => '',
                'FILE_TYPE' => '',
                'MULTIPLE_CNT' => '5',
                'LINK_IBLOCK_ID' => '0',
                'WITH_DESCRIPTION' => 'N',
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'IS_REQUIRED' => 'N',
                'VERSION' => '2',
                'USER_TYPE' => 'lfb_form',
                'USER_TYPE_SETTINGS' => null,
                'HINT' => '',
            ];
    }

    /**
     * @return array[]
     */
    private function getElementForm(): array
    {
        return
            [
                'Параметры|edit1' =>
                    [
                        'ID' => 'ID',
                        'DATE_CREATE' => 'Создан',
                        'TIMESTAMP_X' => 'Изменен',
                        'ACTIVE' => 'Активность',
                        'PROPERTY_STATUS' => 'Статус',
                        'ACTIVE_FROM' => 'Начало активности',
                        'ACTIVE_TO' => 'Окончание активности',
                        'NAME' => 'Название',
                        'CODE' => 'Символьный код',
                        'XML_ID' => 'Внешний код',
                        'SORT' => 'Сортировка',
                        'IBLOCK_ELEMENT_PROPERTY' => 'Значения свойств',
                        'IBLOCK_ELEMENT_PROP_VALUE' => 'Значения свойств',
                        'PROPERTY_AVAILABLE_TO_AUTHORIZED' => 'Доступен для авторизованных',
                        'PROPERTY_AVAILABLE_TO_USER' => 'Доступен для пользователей',
                        'PROPERTY_FORM' => 'Форма',
                        'PROPERTY_SINGLE_WINDOW' => 'Единое окно',
                        'PROPERTY_PAID' => 'Платная',
                        'PROPERTY_DESCRIPTION_POINT' => 'Пункты описания',
                        'PROPERTY_FILES_POINT' => 'Файлы пунктов описания',
                        'PROPERTY_CONTACT_BLOCK_LEFT' => 'Блок контакты (левая колонка)',
                        'PROPERTY_CONTACT_BLOCK_RIGHT' => 'Блок контакты (правая колонка)',
                        'PROPERTY_OPTION_TITLE_DESC' => 'Варианты получения услуги (заголовок и описание)',
                        'PROPERTY_OPTION_IMAGE' => 'Варианты получения услуги (картинка)',
                        'PROPERTY_OPTION_LINK' => 'Варианты получения услуги (ссылка)',
                        'PROPERTY_FILE_ICON' => 'Иконка услуги',
                        'PROPERTY_FILE_COVER' => 'Обложка услуги',
                        'PROPERTY_FILE_CONTRACT' => 'Файл договора',
                        'PROPERTY_FILE_BID' => 'Файл заявки',
                        'PROPERTY_FILE_FORM' => 'Файл формы',
                    ],
                'Анонс|edit5' =>
                    [
                        'PREVIEW_PICTURE' => 'Картинка для анонса',
                        'PREVIEW_TEXT' => 'Описание для анонса',
                    ],
                'Подробно|edit6' =>
                    [
                        'DETAIL_PICTURE' => 'Детальная картинка',
                        'DETAIL_TEXT' => 'Детальное описание',
                    ],
                'SEO|edit14' =>
                    [
                        'IPROPERTY_TEMPLATES_ELEMENT_META_TITLE' => 'Шаблон META TITLE',
                        'IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS' => 'Шаблон META KEYWORDS',
                        'IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION' => 'Шаблон META DESCRIPTION',
                        'IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE' => 'Заголовок элемента',
                        'IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE' => 'Настройки для картинок анонса элементов',
                        'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT' => 'Шаблон ALT',
                        'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE' => 'Шаблон TITLE',
                        'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME' => 'Шаблон имени файла',
                        'IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE' => 'Настройки для детальных картинок элементов',
                        'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT' => 'Шаблон ALT',
                        'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE' => 'Шаблон TITLE',
                        'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME' => 'Шаблон имени файла',
                        'SEO_ADDITIONAL' => 'Дополнительно',
                        'TAGS' => 'Теги',
                    ],
                'Разделы|edit2' =>
                    [
                        'SECTIONS' => 'Разделы',
                    ],
                'Версии|cedit1' =>
                    [
                        'PROPERTY_PREVIOUS_VERSIONS' => 'Предыдущие версии',
                    ],
            ];
    }

    /**
     * @return int
     * @throws Exceptions\HelperException
     */
    private function getIblockId(): int
    {
        return $this->getHelperManager()->Iblock()->getIblockIdIfExists('services', 'uslugi');
    }
}
