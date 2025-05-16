<?php

namespace Local\FormBuilder\Form\Admin\List\Service;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use CAdminUiList;
use CAdminUiResult;
use Local\FormBuilder\Form\Enum\Status;
use Local\FormBuilder\Form\Field\ORM\FormFieldTable;
use Local\FormBuilder\Form\ORM\FormTable;
use Local\FormBuilder\Form\Result\ORM\FormResultTable;
use Local\Main\Admin\List\Collection\ListRowCollection;
use Local\Main\Admin\List\Entity\ListRow;
use Local\Main\Admin\List\Service\UiListBuilder;

/**
 * Класс для построения списка форм в административной панели.
 * Обеспечивает отображение, сортировку, фильтрацию и управление формами.
 *
 * @package Local\FormBuilder\Form\Admin\List\Service.
 */
class FormListBuilder extends UiListBuilder
{
    /**
     * Конструктор класса FormListBuilder.
     *
     * @param string $fileName Имя файла списка.
     * @param bool $isShowExcel Флаг отображения экспорта в Excel.
     * @param bool $isShowSettings Флаг отображения настроек.
     */
    public function __construct(string $fileName, bool $isShowExcel = true, bool $isShowSettings = true)
    {
        parent::__construct($fileName, $isShowExcel, $isShowSettings);
    }

    /**
     * Метод возвращает идентификатор таблицы списка.
     *
     * @return string Идентификатор таблицы.
     */
    public function getTableId(): string
    {
        return 'lfb_form';
    }

    /**
     * Метод возвращает параметры сортировки по умолчанию.
     *
     * @return array Массив с полем и направлением сортировки.
     */
    protected function getDefaultSort(): array
    {
        return ['ID', 'DESC'];
    }

    /**
     * Метод возвращает контекстное меню административного списка.
     *
     * @return array Массив элементов контекстного меню.
     */
    protected function getAdminContextMenu(): array
    {
        return
            [
                [
                    'TEXT' => 'Добавить форму',
                    'TITLE' => 'Добавить форму',
                    'LINK' => $this->getAddFormLink(),
                    'ICON' => 'btn_list',
                ],
            ];
    }

    /**
     * Метод возвращает заголовки колонок таблицы.
     *
     * @return array Массив заголовков колонок.
     */
    protected function getTableHeaders(): array
    {
        return
            [
                [
                    'id' => 'ID',
                    'content' => 'ID',
                    'default' => true,
                    'sort' => 'ID',
                ],
                [
                    'id' => 'DATE_CREATE',
                    'content' => 'Дата создания',
                    'default' => true,
                    'sort' => 'DATE_CREATE',
                ],
                [
                    'id' => 'CODE',
                    'content' => 'Символьный код',
                    'default' => true,
                ],
                [
                    'id' => 'XML_ID',
                    'content' => 'Внешний код',
                ],
                [
                    'id' => 'NAME',
                    'content' => 'Название',
                    'default' => true,
                    'sort' => 'NAME',
                ],
                [
                    'id' => 'FIELD_COUNT',
                    'content' => 'Поля',
                    'default' => true,
                ],
                [
                    'id' => 'RESULT_COUNT',
                    'content' => 'Результаты',
                    'default' => true,
                ],
            ];
    }

    /**
     * Метод возвращает поля фильтрации списка.
     *
     * @return array Массив полей фильтрации.
     */
    protected function getFilterFields(): array
    {
        return
            [
                [
                    'id' => 'ID',
                    'name' => 'ID',
                    'type' => 'number',
                    'filterable' => '',
                ],
                [
                    'id' => 'CODE',
                    'name' => 'Символьный код',
                    'type' => 'string',
                    'filterable' => '=',
                    'default' => true,
                ],
                [
                    'id' => 'XML_ID',
                    'name' => 'Внешний код',
                    'type' => 'string',
                    'filterable' => '=',
                ],
                [
                    'id' => 'NAME',
                    'name' => 'Название',
                    'type' => 'string',
                    'filterable' => '%',
                    'default' => true,
                ],
            ];
    }

    /**
     * Метод возвращает коллекцию строк для отображения в списке.
     *
     * @param CAdminUiList $adminList Объект административного списка.
     * @return ListRowCollection Коллекция строк списка.
     */
    protected function getRowCollection(CAdminUiList $adminList): ListRowCollection
    {
        $filter = ['!STATUS' => Status::ARCHIVED->value, 'IS_DELETED' => [false, 'N']];
        $adminList->AddFilter($this->getFilterFields(), $filter);
        $adminSort = $adminList->sort;

        $select = $this->getSelect($adminList);
        $runtime = [];

        if (in_array('FIELD_COUNT', $select)) {
            $runtime = array_merge(
                $runtime,
                [
                    new Reference(
                        'FIELD',
                        FormFieldTable::class,
                        Join::on('this.ID', 'ref.FORM_ID')
                    ),
                    new ExpressionField(
                        'FIELD_COUNT',
                        'COUNT(%s)',
                        ['FIELD.ID']
                    ),
                ]
            );
        }

        if (in_array('RESULT_COUNT', $select)) {
            $runtime = array_merge(
                $runtime,
                [
                    new Reference(
                        'RESULT',
                        FormResultTable::class,
                        Join::on('this.ID', 'ref.FORM_ID')
                    ),
                    new ExpressionField(
                        'RESULT_COUNT',
                        'COUNT(%s)',
                        ['RESULT.ID']
                    ),
                ]
            );
        }

        $formQuery = FormTable::getList([
            'order' => [$adminSort->getField() => $adminSort->getOrder()],
            'filter' => $filter,
            'select' => $select,
            'runtime' => $runtime,
        ]);

        $formQuery = new CAdminUiResult($formQuery, $this->getTableId());
        $formQuery->NavStart();
        $adminList->SetNavigationParams(
            $formQuery,
            ['BASE_LINK' => $this->getPageUrl()],
        );

        $listRowCollection = new ListRowCollection();

        while ($form = $formQuery->fetch()) {
            $listRow = new ListRow(
                $form['ID'],
                $form,
                [
                    [
                        'ICON' => 'edit',
                        'TEXT' => 'Изменить',
                        'LINK' => $this->getEditFormLink($form['ID']),
                    ],
                    [
                        'ICON' => 'delete',
                        'TEXT' => 'Удалить',
                        'LINK' => $this->getDeleteFormLink($form['ID']),
                    ],
                ]
            );

            $listRowCollection->add($listRow);
        }

        return $listRowCollection;
    }

    /**
     * Метод возвращает список выбираемых полей для запроса.
     *
     * @param CAdminUiList $adminUiList Объект административного списка.
     * @return array Массив выбираемых полей.
     */
    private function getSelect(CAdminUiList $adminUiList): array
    {
        $select = $adminUiList->GetVisibleHeaderColumns();

        if (!$select) {
            $tableHeaders = $this->getTableHeaders();
            $select = array_column($tableHeaders, 'id');
        }

        if (!in_array('ID', $select)) {
            $select[] = 'ID';
        }

        return $select;
    }

    /**
     * Метод возвращает ссылку для редактирования формы.
     *
     * @param int $formId ID формы.
     * @return string URL для редактирования формы.
     */
    private function getEditFormLink(int $formId): string
    {
        return $this->selfFolderUrl . 'local_formbuilder_form_edit.php?ID=' . $formId;
    }

    /**
     * Метод возвращает ссылку для удаления формы.
     *
     * @param int $formId ID формы.
     * @return string URL для удаления формы.
     */
    private function getDeleteFormLink(int $formId): string
    {
        return $this->selfFolderUrl . 'local_formbuilder_form_edit.php?ID=' . $formId . '&action=delete';
    }

    /**
     * Метод возвращает ссылку для добавления формы.
     *
     * @return string URL для добавления формы.
     */
    private function getAddFormLink(): string
    {
        return $this->selfFolderUrl . 'local_formbuilder_form_edit.php';
    }
}
