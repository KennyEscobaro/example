<?php

namespace Local\Main\Admin\Tab\Element\Enum;

/**
 * Перечисление типов элементов административной вкладки
 *
 * Определяет все возможные типы элементов, которые могут использоваться
 * в административном интерфейсе для построения форм и настроек.
 *
 * @package Local\Main\Admin\Tab\Element\Enum
 */
enum ElementType: string
{
    /** Множественный выбор (мультиселект) */
    case MULTI_SELECT_BOX = 'multiselectbox';

    /** Многострочное текстовое поле */
    case TEXT_AREA = 'textarea';

    /** Статический текст (только для отображения) */
    case STATIC_TEXT = 'statictext';

    /** Статический HTML-контент */
    case STATIC_HTML = 'statichtml';

    /** Чекбокс (флажок) */
    case CHECKBOX = 'checkbox';

    /** Однострочное текстовое поле */
    case TEXT = 'text';

    /** Поле для ввода пароля */
    case PASSWORD = 'password';

    /** Выпадающий список (селект) */
    case SELECT_BOX = 'selectbox';

    /** Подзаголовок секции */
    case SUB_TITLE = 'subtitle';

    /** Текстовая заметка (подсказка) */
    case NOTE = 'note';

    /** Поле для загрузки файла */
    case FILE = 'file';

    /** Визуальный HTML-редактор */
    case HTML_EDITOR = 'htmleditor';

    /** Поле выбора пользователя */
    case USER = 'user';

    /** Цветовой индикатор типа отсутствия */
    case ABSENCE_TYPE_COLOR = 'absencetypecolor';

    /** Поля экспорта телефонной книги */
    case PHONEBOOK_EXPORT_FIELDS = 'phone_book_export_fields';
}
