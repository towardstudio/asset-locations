<?php
namespace bluegg\assetlocations\services;

use bluegg\assetlocations\AssetLocations;

use Craft;
use craft\base\Component;

use craft\elements\Asset as AssetElement;

class FindElementService extends Component
{
	// Public Methods
	// =========================================================================

	/**
	 * Get Entries
	 * @description Check to see if the asset is used within any elements
	 *
	 * @param String $type
	 * @param AssetElement $asset
	 * @return ?array
	 */
	public function getElements(string $type, AssetElement $asset, int $siteId): ?array
	{
		$assetEntries = [];

		// Find all Asset Fields within Entries where this asset is used
		$relatedEntries = AssetLocations::$plugin->findAsset->checkAssetFields($type, $asset, $siteId);
		$assetEntries = array_merge((array) $assetEntries, (array) $relatedEntries);

		// Get all Matrix Blocks where this asset is used within Entries
		$relatedMatrix = AssetLocations::$plugin->findAsset->checkMatrixFields($type, $asset, $siteId);
		$assetEntries = array_merge((array) $assetEntries, (array) $relatedMatrix);

		// Get all Super Tables where this asset is used within Entries
		$tableEntries = AssetLocations::$plugin->findAsset->checkSuperTableFields($type, $asset, $siteId);
		$assetEntries = array_merge((array) $assetEntries, (array) $tableEntries);

		return $assetEntries;
	}
}
