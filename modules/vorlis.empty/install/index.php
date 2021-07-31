<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

/**
 * Class vorlis_empty extends Cmodule !
 */
class vorlis_empty extends CModule
{
    /**
     * @var array
     */
    public $exclusionAdminFiles;

    /**
     * vorlis_empty constructor.
     */
    public function __construct()
    {
        $arModuleVersion = include 'version.php';

        $this->MODULE_ID = GetMessage('VORLIS_EMPTY_MODULE_ID');

        $this->MODULE_NAME = GetMessage('VORLIS_EMPTY_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('VORLIS_EMPTY_MODULE_DESC');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';

        $this->PARTNER_NAME = GetMessage('VORLIS_EMPTY_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('VORLIS_EMPTY_PARTNER_URI');

        $this->exclusionAdminFiles = [
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php',
        ];

    }

    /**
     *
     */
    public function DoInstall()
    {
        try {
            global $APPLICATION;

            if ($this->isVersionD7()) {

                ModuleManager::registerModule($this->MODULE_ID);

                $this->InstallDB();
                $this->InstallEvents();
                $this->InstallFiles();

            } else {
                $APPLICATION->ThrowException(Loc::getMessage('VORLIS_EMPTY_INSTALL_ERROR_VERSION'));
            }

            $APPLICATION->IncludeAdminFile(Loc::getMessage('VORLIS_EMPTY_INSTALL_TITLE'), $this->GetPath() . '/install/step.php');

        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     *
     */
    public function DoUninstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if ($request['step'] < 2) {

            $APPLICATION->IncludeAdminFile(Loc::getMessage('VORLIS_EMPTY_UNISTALL_TITLE'), $this->GetPath() . '/install/unstep1.php');

        } elseif ($request['step'] == 2) {

            $this->UnInstallFiles();
            $this->UnInstallEvents();
            if ($request['savedata'] !== 'Y') {
                $this->UnInstallDB();
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage('VORLIS_EMPTY_UNISTALL_TITLE'), $this->GetPath() . '/install/unstep2.php');
        }
    }

    /**
     * @param array $arParams
     * @return bool
     */
    public function InstallFiles($arParams = []): bool
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/vorlis.empty/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/vorlis.empty/install/components/", $_SERVER["DOCUMENT_ROOT"] . "/local/components", true, true);
        return true;
    }

    /**
     * @return bool
     */
    public function UnInstallFiles(): bool
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/vorlis.empty/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        return true;
    }

    /**
     * @param array $arParams
     * @return bool|void
     */
    public function InstallDB($arParams = []): void
    {
        Loader::includeModule($this->MODULE_ID);
    }

    /**
     * @param array $arParams
     */
    public function UnInstallDB($arParams = []): void
    {
        Loader::includeModule($this->MODULE_ID);
    }

    /**
     * @return bool
     */
    public function InstallEvents(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function UnInstallEvents(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function GetModuleRightList(): array
    {
        return [
            'reference_id' => ['D', 'K', 'S', 'W'],
            'reference' => [
                '[D] ' . Loc::getMessage('VORLIS_EMPTY_DENIED'),
                '[K] ' . Loc::getMessage('VORLIS_EMPTY_READ_COMPONENT'),
                '[S] ' . Loc::getMessage('VORLIS_EMPTY_WRITE_SETTINGS'),
                '[W] ' . Loc::getMessage('VORLIS_EMPTY_FULL'),
            ],
        ];
    }


    /**
     * @return bool
     */
    private function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    /**
     * Определяем место размещения модуля
     *
     * @param bool $notDocumentRoot
     * @return mixed|string
     */
    private function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        }

        return dirname(__DIR__);
    }
}