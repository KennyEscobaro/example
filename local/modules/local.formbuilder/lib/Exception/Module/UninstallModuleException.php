<?php

namespace Local\FormBuilder\Exception\Module;

/**
 * Класс-исключение для ошибок удаления модуля.
 *
 * Наследуется от базового ModuleException и используется для обработки специфичных ошибок,
 * возникающих в процессе удаления (деинсталляции) модуля.
 * Например: ошибки отката миграций, проблемы с удалением файлов или отменой регистрации модуля.
 *
 * @package Local\FormBuilder\Exception\Module
 */
class UninstallModuleException extends ModuleException
{
}
