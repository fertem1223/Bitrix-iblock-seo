<?php
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class fertem_iblockseo extends CModule
{
    public $MODULE_ID = 'fertem.iblockseo';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME = 'fertem1223';
    public $PARTNER_URI = 'https://github.com/fertem1223/Bitrix-iblock-seo';

    public function __construct()
    {
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('FERTEM_IBLOCKSEO_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('FERTEM_IBLOCKSEO_DESCRIPTION');
    }

    public function DoInstall()
    {
        if (!ModuleManager::isModuleInstalled('iblock')) { global $APPLICATION; $APPLICATION->ThrowException('Для установки требуется модуль «Информационные блоки».'); return false; }
        ModuleManager::registerModule($this->MODULE_ID);
        EventManager::getInstance()->registerEventHandler('main', 'OnPageStart', $this->MODULE_ID, '\\Fertem\\IblockSeo\\SeoManager', 'apply');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, '\\Fertem\\IblockSeo\\SeoManager', 'clearCache');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, '\\Fertem\\IblockSeo\\SeoManager', 'clearCache');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnAfterIBlockElementDelete', $this->MODULE_ID, '\\Fertem\\IblockSeo\\SeoManager', 'clearCache');
        return true;
    }

    public function DoUninstall()
    {
        $events = EventManager::getInstance();
        $events->unRegisterEventHandler('main', 'OnPageStart', $this->MODULE_ID, '\\Fertem\\IblockSeo\\SeoManager', 'apply');
        foreach (['OnAfterIBlockElementAdd','OnAfterIBlockElementUpdate','OnAfterIBlockElementDelete'] as $event) { $events->unRegisterEventHandler('iblock', $event, $this->MODULE_ID, '\\Fertem\\IblockSeo\\SeoManager', 'clearCache'); }
        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }
}

