<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Local\FormBuilder\EventHandler\Main\AdminPage\OnBuildGlobalMenu;
use Local\FormBuilder\EventHandler\Migration\OnSearchConfigFiles;
use Local\FormBuilder\Exception\Module\InstallModuleException;
use Local\FormBuilder\Exception\Module\UninstallModuleException;
use Local\FormBuilder\Helper\ModuleHelper;
use Local\FormBuilder\Iblock\UserField\Type\FormType;
use Sprint\Migration\Installer;

/**
 * Класс модуля "Конструктор форм".
 * Обеспечивает установку, удаление и управление функционалом модуля.
 *
 * @package Local\FormBuilder
 */
final class local_formbuilder extends CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'local.formbuilder';
        $this->MODULE_NAME = 'Local: Конструктор форм';
        $this->MODULE_DESCRIPTION = 'Конструктор форм';
        $this->PARTNER_NAME = '';
        $this->PARTNER_URI = '';

        if (is_file(__DIR__ . '/version.php')) {
            include_once(__DIR__ . '/version.php');

            /** @var array $arModuleVersion */
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        } else {
            CAdminMessage::ShowMessage('Файл version.php не найден');
        }
    }

    /**
     * Метод подключает текущий модуль.
     *
     * @return void
     * @throws InstallModuleException Если возникла ошибка при подключении модуля.
     */
    public function includeCurrentModule(): void
    {
        try {
            Loader::includeModule($this->MODULE_ID);
        } catch (Exception $e) {
            throw new InstallModuleException('Ошибка при подключении модуля', 0, '', 0, $e);
        }
    }

    /**
     * Метод подключает дополнительные модули.
     *
     * @return void
     * @throws InstallModuleException Если возникла ошибка при подключении модулей.
     */
    public function includeAdditionalModules(): void
    {
        try {
            Loader::includeModule('sprint.migration');
        } catch (Exception $e) {
            throw new InstallModuleException('Ошибка при подключении дополнительных модулей', 0, '', 0, $e);
        }
    }

    /**
     * Метод выполняет установку модуля.
     *
     * @return void
     */
    public function doInstall(): void
    {
        global $APPLICATION;

        $installationSteps = $this->getInstallationSteps();
        $lastInstallationStep = '';

        try {
            foreach ($installationSteps as $installationStep => $rollbackInstallationStep) {
                if (!method_exists($this, $installationStep)) {
                    throw new InstallModuleException('Отсутствует шаг ' . $installationStep);
                }

                call_user_func([self::class, $installationStep]);
                $lastInstallationStep = $installationStep;
            }
        } catch (InstallModuleException $e) {
            $this->rollback($installationSteps, $lastInstallationStep);
            $APPLICATION->ThrowException($e->getMessage());
        } finally {
            $this->includeStepFile();
        }
    }

    /**
     * Метод устанавливает базу данных модуля.
     *
     * @return void
     * @throws InstallModuleException Если возникла ошибка при установке миграций.
     */
    public function installDB(): void
    {
        try {
            (new Installer(
                [
                    'migration_dir' => ModuleHelper::getModulePath() . '/migrations/',
                    'migration_dir_absolute' => true,
                    'migration_table' => 'lfb_migration_version'
                ]
            ))->up();
        } catch (Exception $e) {
            throw new InstallModuleException('Ошибка при установке миграций', 0, '', 0, $e);
        }
    }

    /**
     * Метод регистрирует обработчики событий модуля.
     *
     * @return void
     * @throws InstallModuleException Если возникла ошибка при регистрации событий.
     */
    public function installEvents(): void
    {
        try {
            $eventManager = EventManager::getInstance();

            $eventManager->registerEventHandler(
                'sprint.migration',
                'OnSearchConfigFiles',
                $this->MODULE_ID,
                OnSearchConfigFiles::class,
                'getConfigDirectory',
            );

            $eventManager->registerEventHandler(
                'main',
                'OnBuildGlobalMenu',
                $this->MODULE_ID,
                OnBuildGlobalMenu::class,
                'handler'
            );

            $eventManager->registerEventHandler(
                'iblock',
                'OnIBlockPropertyBuildList',
                $this->MODULE_ID,
                FormType::class,
                'getUserTypeDescription'
            );
        } catch (Exception $e) {
            throw new InstallModuleException('Ошибка при регистрации обработчиков событий', 0, '', 0, $e);
        }
    }

    /**
     * Метод регистрирует модуль в системе.
     *
     * @return void
     * @throws InstallModuleException Если возникла ошибка при регистрации.
     */
    public function registerCurrentModule(): void
    {
        try {
            ModuleManager::registerModule($this->MODULE_ID);
        } catch (Exception $e) {
            throw new InstallModuleException('Ошибка при регистрации модуля', 0, '', 0, $e);
        }
    }

    /**
     * Метод выполняет удаление модуля.
     *
     * @return void
     */
    public function doUninstall(): void
    {
        global $APPLICATION;

        $uninstallationSteps = $this->getUninstallationSteps();
        $lastUninstallationStep = '';

        try {
            foreach ($uninstallationSteps as $uninstallationStep => $rollbackUninstallationStep) {
                if (!method_exists($this, $uninstallationStep)) {
                    throw new UninstallModuleException('Отсутствует шаг ' . $uninstallationStep);
                }

                call_user_func([self::class, $uninstallationStep]);
                $lastUninstallationStep = $uninstallationStep;
            }
        } catch (UninstallModuleException $e) {
            $this->rollback($uninstallationSteps, $lastUninstallationStep);
            $APPLICATION->ThrowException($e->getMessage());
        } finally {
            $this->includeUnstepFile();
        }
    }

    /**
     * Метод удаляет базу данных модуля.
     *
     * @return void
     * @throws UninstallModuleException Если возникла ошибка при откате миграций.
     */
    public function unInstallDB(): void
    {
        try {
            (new Sprint\Migration\Installer(
                [
                    'migration_dir' => ModuleHelper::getModulePath() . '/migrations/',
                    'migration_dir_absolute' => true,
                    'migration_table' => 'lfb_migration_version'
                ]
            ))->down();
        } catch (Exception $e) {
            throw new UninstallModuleException('Ошибка при откате миграций', 0, '', 0, $e);
        }
    }

    /**
     * Метод удаляет обработчики событий модуля.
     *
     * @return void
     * @throws UninstallModuleException Если возникла ошибка при удалении событий.
     */
    public function unInstallEvents(): void
    {
        try {
            $eventManager = EventManager::getInstance();

            $eventManager->unRegisterEventHandler(
                'sprint.migration',
                'OnSearchConfigFiles',
                $this->MODULE_ID,
                OnSearchConfigFiles::class,
                'getConfigDirectory',
            );

            $eventManager->unRegisterEventHandler(
                'main',
                'OnBuildGlobalMenu',
                $this->MODULE_ID,
                OnBuildGlobalMenu::class,
                'handler'
            );

            $eventManager->unRegisterEventHandler(
                'iblock',
                'OnIBlockPropertyBuildList',
                $this->MODULE_ID,
                FormType::class,
                'getUserTypeDescription'
            );
        } catch (Exception $e) {
            throw new UninstallModuleException('Ошибка при отмене регистрации обработчиков событий', 0, '', 0, $e);
        }
    }

    /**
     * Метод удаляет регистрацию модуля в системе.
     *
     * @return void
     * @throws UninstallModuleException Если возникла ошибка при удалении регистрации.
     */
    public function unregisterCurrentModule(): void
    {
        try {
            ModuleManager::unRegisterModule($this->MODULE_ID);
        } catch (Exception $e) {
            throw new UninstallModuleException('Ошибка при отмене регистрации модуля', 0, '', 0, $e);
        }
    }

    /**
     * Метод выполняет откат установки/удаления модуля.
     *
     * @param array $steps Шаги установки/удаления.
     * @param string $lastStep Последний выполненный шаг.
     * @return void
     */
    private function rollback(array $steps, string $lastStep): void
    {
        foreach ($steps as $step => $rollbackStep) {
            if (empty($rollbackStep)) {
                continue;
            }

            call_user_func([self::class, $rollbackStep]);

            if ($step === $lastStep) {
                break;
            }
        }
    }

    /**
     * Метод возвращает шаги установки модуля.
     *
     * @return string[] Массив шагов установки.
     */
    private function getInstallationSteps(): array
    {
        return
            [
                'registerCurrentModule' => 'unregisterCurrentModule',
                'includeCurrentModule' => '',
                'includeAdditionalModules' => '',
                'installDB' => '',
                'installEvents' => 'unInstallEvents',
            ];
    }

    /**
     * Метод возвращает шаги удаления модуля.
     *
     * @return string[] Массив шагов удаления.
     */
    private function getUninstallationSteps(): array
    {
        return
            [
                'includeCurrentModule' => '',
                'includeAdditionalModules' => '',
                'unInstallDB' => '',
                'uninstallFiles' => 'installFiles',
                'unregisterCurrentModule' => 'registerCurrentModule',
            ];
    }

    /**
     * Метод подключает файл step.php.
     *
     * @return void
     */
    private function includeStepFile(): void
    {
        global $APPLICATION;
        $APPLICATION->IncludeAdminFile(
            'Установка модуля ' . $this->MODULE_ID,
            __DIR__ . '/step.php',
        );
    }

    /**
     * Метод подключает файл unstep.php.
     *
     * @return void
     */
    private function includeUnstepFile(): void
    {
        global $APPLICATION;
        $APPLICATION->IncludeAdminFile(
            'Удаление модуля ' . $this->MODULE_ID,
            __DIR__ . '/unstep.php',
        );
    }
}
