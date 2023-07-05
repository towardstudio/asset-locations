<?php
namespace towardstudio\assetlocations\services;

use towardstudio\assetlocations\AssetLocations;

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
		$entries = AssetLocations::$plugin->elementService->getElements(Entry::class, $asset, $asset->siteId);

		// If Entries isn't empty, return true
		if (!empty($entries)) {
			return true;
		}

        // Check Links
		$links = AssetLocations::$plugin->elementService->getLinks($asset, $asset->siteId);

		// If Links isn't empty, return true
		if (!empty($links)) {
			return true;
		}

		// Check Categories
		$categories = AssetLocations::$plugin->elementService->getElements(
			Category::class,
			$asset, $asset->siteId
		);

		// If Categories isn't empty, return true
		if (!empty($categories)) {
			return true;
		}

		// Check Globals
		$globals = AssetLocations::$plugin->elementService->getElements(GlobalSet::class, $asset, $asset->siteId);

		// If Globals isn't empty, return true
		if (!empty($globals)) {
			return true;
		}

		return false;

	}

}
