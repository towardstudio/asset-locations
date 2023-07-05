<?php

namespace towardstudio\assetlocations\fieldlayoutelements;

use towardstudio\assetlocations\AssetLocations;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseNativeField;

use craft\elements\Asset as AssetElement;
use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\GlobalSet;

class Locations extends BaseNativeField
{
	/**
	 * @inheritdoc
	 */
	public string $attribute = "locations";

	/**
	 * @inheritdoc
	 */
	public function __construct($config = [])
	{
		unset(
			$config["attribute"],
			$config["mandatory"],
			$config["requirable"],
			$config["translatable"]
		);

		parent::__construct($config);
	}

	/**
	 * @inheritdoc
	 */
	public function defaultLabel(?ElementInterface $element = null, bool $static = false): ?string
	{
		return Craft::t("app", "Asset Locations");
	}

	/**
	 * @var string The input type
	 */
	public string $type = "text";

	/**
	 * @var string|null The input’s `name` attribute value.
	 *
	 * If this is not set, [[attribute()]] will be used by default.
	 */
	public ?string $name = null;

	/**
	 * @var bool Whether the input should get a `readonly` attribute.
	 */
	public bool $readonly = true;

	/**
	 * @var string|null The input’s `title` attribute value.
	 */
	public ?string $title = null;

	/**
	 * @var string|null The input’s `placeholder` attribute value.
	 */
	public ?string $placeholder = "0";

	/**
	 * @inheritdoc
	 */
	public function fields(): array
	{
		$fields = parent::fields();

		// Don't include the value
		unset($fields["value"]);

		return $fields;
	}

	/**
	 * Create an array of entries where the asset displays. This is used within the template
	 *
	 * @param  AssetElement $asset
	 * @return array
	 */
	public function getValue(AssetElement $asset): array
	{
		$entries = AssetLocations::$plugin->elementService->getElements(Entry::class, $asset, $asset->siteId);
        $links = AssetLocations::$plugin->elementService->getLinks($asset, $asset->siteId);
		$categories = AssetLocations::$plugin->elementService->getElements(
			Category::class,
			$asset,
			$asset->siteId
		);
		$globals = AssetLocations::$plugin->elementService->getElements(GlobalSet::class, $asset, $asset->siteId);

		return [
			"entries" => $this->formatResults($entries),
            "links" => $this->formatResults($links),
			"categories" => $this->formatResults($categories),
			"globals" => $this->formatResults($globals),
		];
	}

	/**
	 * Check if there are any global sets
	 *
	 * @return int
	 */
	public function globalsCheck(): int
	{
		$globalSets = \craft\elements\GlobalSet::find()
    		->all();

		return count($globalSets);
	}

	/**
	 * Check if there are any categories
	 *
	 * @return int
	 */
	public function categoryCheck(): int
	{
		$categories = \craft\elements\Category::find()
    		->all();

		return count($categories);
	}

	/**
	 * Format the array so we can use the sections as headings
	 *
	 * @param array $array
	 * @return array
	 */
	private function formatResults($array): array
	{
		$newArray = [];

		foreach ($array as $entity) {
			if (!isset($newArray[$entity->section])) {
				$newArray[$entity->section] = [];
			}

			$newArray[$entity->section][] = $entity;
		}

		// Move Singles to the top of the Array
		if (isset($newArray["Singles"])) {
			$item = $newArray["Singles"];
			unset($newArray["Singles"]);
			$newArray = ["Singles" => $item] + $newArray;
		}

		return $newArray;
	}

	/**
	 * @inheritdoc
	 */
	protected function inputHtml(?ElementInterface $element = null, bool $static = false): ?string
	{
		return Craft::$app->getView()->renderTemplate("assetlocations/locations", [
			"locations" => $this->getValue($element),
			"categoriesCount" => $this->categoryCheck(),
			"globalsCount" => $this->globalsCheck(),
		]);
	}
}
