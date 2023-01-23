<?php
namespace bluegg\assetlocations\services;

use bluegg\assetlocations\AssetLocations;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset as AssetElement;

use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\GlobalSet;


class GetUsageService extends Component
{

	/**
	 * Get the Asset's Usage
	 * @description Return the amount of times an asset has been used
	 *
	 * @param object $element
	 * @return bool
	 */
	public function getUsage(AssetElement $asset): bool {

		// Check Entries
		$entries = AssetLocations::$plugin->elementService->getElements(Entry::class, $asset, 0);

		// If Entries isn't empty, return true
		if (!empty($entries)) {
			return true;
		}

		// Check Categories
		$categories = AssetLocations::$plugin->elementService->getElements(
			Category::class,
			$asset, 0
		);

		// If Categories isn't empty, return true
		if (!empty($categories)) {
			return true;
		}

		// Check Globals
		$globals = AssetLocations::$plugin->elementService->getElements(GlobalSet::class, $asset, 0);

		// If Globals isn't empty, return true
		if (!empty($globals)) {
			return true;
		}

		return false;

	}

}
