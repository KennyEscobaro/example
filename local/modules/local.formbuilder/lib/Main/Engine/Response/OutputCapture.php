<?php

namespace Local\FormBuilder\Main\Engine\Response;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetMode;
use LogicException;

/**
 * Класс для захвата вывода (HTML) и связанных ресурсов (JS, CSS)
 */
class OutputCapture
{
    /** @var array Список путей к JS-файлам */
    private array $jsPathList = [];

    /** @var array Список путей к CSS-файлам */
    private array $cssPathList = [];

    /** @var Asset Экземпляр менеджера ресурсов */
    private Asset $asset;

    /** @var int Уровень буферизации на момент старта захвата */
    private int $bufferLevel = 0;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        $this->asset = Asset::getInstance();
    }

    /**
     * Начинает захват вывода и ресурсов
     *
     * @throws LogicException Если захват уже начат
     */
    public function startCapture(): void
    {
        if ($this->bufferLevel > 0) {
            throw new LogicException(
                'Невозможно начать новый захват: предыдущий захват ещё не завершён. Сначала вызовите endCapture().'
            );
        }

        $this->asset->disableOptimizeCss();
        $this->asset->disableOptimizeJs();

        $this->jsPathList = [];
        $this->cssPathList = [];

        $this->bufferLevel = ob_get_level();
        ob_start();
    }

    /**
     * Завершает захват и возвращает собранные данные
     *
     * @return array{
     *      html: string,
     *      assets: array{
     *          js: array,
     *          css: array,
     *          string: array
     *      }
     *  }
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws LogicException
     */
    public function endCapture(): array
    {
        if ($this->bufferLevel === 0) {
            throw new LogicException('Невозможно завершить захват: захват не был начат.');
        }

        $content = '';

        while (ob_get_level() > $this->bufferLevel) {
            $content = ob_get_clean() . $content;
        }

        $this->bufferLevel = 0;
        $this->collectAssetsPathList();

        return [
            'HTML' => $content,
            'ASSETS' => [
                'JS' => $this->getJsList(),
                'CSS' => $this->getCssList(),
                'STRING' => $this->getStringList(),
            ],
        ];
    }

    /**
     * обирает пути к ресурсам (JS, CSS)
     *
     * @return void
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    private function collectAssetsPathList(): void
    {
        $this->asset->getCss();
        $this->asset->getJs();
        $this->asset->getStrings();

        $this->jsPathList = $this->asset->getTargetList('JS');
        $this->cssPathList = $this->asset->getTargetList('CSS');
    }

    /**
     * Возвращает список JS-файлов
     *
     * @return array
     */
    private function getJsList(): array
    {
        $jsList = [];

        foreach ($this->jsPathList as $targetAsset) {
            $assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);

            if (!empty($assetInfo['JS'])) {
                $jsList = array_merge($jsList, $assetInfo['JS']);
            }
        }

        return $jsList;
    }

    /**
     * Возвращает список CSS-файлов
     *
     * @return array
     */
    private function getCssList(): array
    {
        $cssList = [];

        foreach ($this->cssPathList as $targetAsset) {
            $assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);

            if (!empty($assetInfo['CSS'])) {
                $cssList = array_merge($cssList, $assetInfo['CSS']);
            }
        }

        return $cssList;
    }

    /**
     * Возвращает список строковых ресурсов
     *
     * @return array
     */
    private function getStringList(): array
    {
        $strings = [];

        foreach ($this->cssPathList as $targetAsset) {
            $assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
            if (!empty($assetInfo['STRINGS'])) {
                $strings = array_merge($strings, $assetInfo['STRINGS']);
            }
        }

        foreach ($this->jsPathList as $targetAsset) {
            $assetInfo = $this->asset->getAssetInfo($targetAsset['NAME'], AssetMode::ALL);
            if (!empty($assetInfo['STRINGS'])) {
                $strings = array_merge($strings, $assetInfo['STRINGS']);
            }
        }

        $strings[] = $this->asset->showFilesList();

        return $strings;
    }
}