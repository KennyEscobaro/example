<?php

namespace Local\Main\Admin\List\Service;

use Bitrix\Main\Context;
use Bitrix\Main\Request;
use CAdminPage;
use CAdminUiList;
use CAdminUiSorting;
use Local\Main\Admin\List\Collection\ListRowCollection;
use Local\Main\Admin\List\Entity\ListRow;

/**
 * Абстрактный класс для построения административных списков
 *
 * Предоставляет базовую функциональность для создания и отображения
 * административных списков с поддержкой сортировки, фильтрации и действий.
 *
 * @package Local\Main\Admin\List\Service
 */
abstract class UiListBuilder
{
    /** @var string $selfFolderUrl URL текущей папки административного раздела */
    protected string $selfFolderUrl;

    /** @var Request $request Текущий HTTP-запрос */
    protected Request $request;

    /** @var bool $isShowExcel Флаг отображения кнопки экспорта в Excel */
    private bool $isShowExcel;

    /** @var bool $isShowSettings Флаг отображения кнопки настроек */
    private bool $isShowSettings;

    /** @var string $fileName Имя файла текущей страницы */
    private string $fileName;

    /**
     * Конструктор построителя списка
     *
     * @param string $fileName Имя файла текущей страницы.
     * @param bool $isShowExcel Показывать ли кнопку экспорта в Excel (по умолчанию true).
     * @param bool $isShowSettings Показывать ли кнопку настроек (по умолчанию true).
     */
    public function __construct(string $fileName, bool $isShowExcel = true, bool $isShowSettings = true)
    {
        /** @global CAdminPage $adminPage */
        global $adminPage;

        $this->selfFolderUrl = $adminPage->getSelfFolderUrl();
        $this->request = Context::getCurrent()->getRequest();

        $this->fileName = $fileName;
        $this->isShowExcel = $isShowExcel;
        $this->isShowSettings = $isShowSettings;
    }

    /**
     * Метод построения и отображения списка
     *
     * Создает и настраивает административный список, добавляет заголовки,
     * строки, контекстное меню и обработчики действий.
     *
     * @return void
     */
    public function build(): void
    {
        [$sortField, $sortOrder] = $this->getDefaultSort();

        $adminSort = new CAdminUiSorting(static::getTableId(), $sortField, $sortOrder);
        $adminList = new CAdminUiList(static::getTableId(), $adminSort);

        $this->handlerAction($adminList);

        $adminList->AddHeaders(static::getTableHeaders());

        $this->addRows($adminList);
        $this->addAdminContextMenu($adminList);

        $adminList->AddGroupActionTable($this->getGroupActionTable());
        $adminList->CheckListMode();
        $adminList->DisplayFilter($this->getFilterFields());
        $adminList->DisplayList($this->getDisplayListParams());

        $this->addJs();
    }

    /**
     * Метод возвращает URL текущей страницы
     *
     * @return string Полный URL текущей страницы.
     */
    public function getPageUrl(): string
    {
        return $this->selfFolderUrl . $this->fileName;
    }

    /**
     * Абстрактный метод получения идентификатора таблицы
     *
     * @return string Уникальный идентификатор таблицы.
     */
    abstract public function getTableId(): string;

    /**
     * Обработчик действий списка
     *
     * @param CAdminUiList $adminList Объект административного списка.
     * @return void
     */
    protected function handlerAction(CAdminUiList $adminList): void
    {
    }

    /**
     * Добавление строк в список
     *
     * @param CAdminUiList $adminList Объект административного списка.
     * @return void
     */
    protected function addRows(CAdminUiList $adminList): void
    {
        /** @var ListRow $row */
        foreach (static::getRowCollection($adminList) as $row) {
            $listRow = &$adminList->AddRow($row->getPrimary(), $row->getFields());
            $listRow->AddActions($row->getActions());
        }
    }

    /**
     * Добавление контекстного меню
     *
     * @param CAdminUiList $adminList Объект административного списка.
     * @return void
     */
    protected function addAdminContextMenu(CAdminUiList $adminList): void
    {
        $adminList->setContextSettings(['pagePath' => $this->getPageUrl()]);
        $adminList->AddAdminContextMenu($this->getAdminContextMenu(), $this->isShowExcel, $this->isShowSettings);
    }

    /**
     * Добавление JavaScript на страницу
     *
     * @return void
     */
    protected function addJs(): void
    {
        echo $this->getJs();
    }

    /**
     * Получение контекстного меню
     *
     * @return array Массив элементов контекстного меню.
     */
    protected function getAdminContextMenu(): array
    {
        return [];
    }

    /**
     * Получение групповых действий
     *.=
     * @return array Массив групповых действий.
     */
    protected function getGroupActionTable(): array
    {
        return [];
    }

    /**
     * Получение параметров отображения списка
     *
     * @return array Массив параметров отображения.
     */
    protected function getDisplayListParams(): array
    {
        return [];
    }

    /**
     * Получение JavaScript кода
     *
     * @return string JavaScript код для списка.
     */
    protected function getJs(): string
    {
        return '';
    }

    /**
     * Получение параметров сортировки по умолчанию
     *
     * @return array Массив с полем сортировки и направлением [поле, направление].
     */
    protected function getDefaultSort(): array
    {
        return [false, false];
    }

    /**
     * Абстрактный метод получения заголовков таблицы
     *
     * @return array Массив заголовков таблицы.
     */
    abstract protected function getTableHeaders(): array;

    /**
     * Абстрактный метод получения полей фильтра
     *
     * @return array Массив полей фильтра.
     */
    abstract protected function getFilterFields(): array;

    /**
     * Абстрактный метод получения коллекции строк
     *
     * @param CAdminUiList $adminList Объект административного списка
     * @return ListRowCollection Коллекция строк списка.
     */
    abstract protected function getRowCollection(CAdminUiList $adminList): ListRowCollection;
}
