<?php
namespace towardstudio\assetlocations\services;

use towardstudio\assetlocations\AssetLocations;

use Craft;
use craft\base\Component;
use craft\db\Query;
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
	public function getElements(
		string $type,
		AssetElement $asset,
		int $siteId
	): ?array {
		$assetEntries = [];

		// Find all Asset Fields within Entries where this asset is used
		$relatedEntries = AssetLocations::$plugin->findAsset->checkAssetFields(
			$type,
			$asset,
			$siteId
		);
		$assetEntries = array_merge(
			(array) $assetEntries,
			(array) $relatedEntries
		);

		// Get all Matrix Blocks where this asset is used within Entries
		$relatedMatrix = AssetLocations::$plugin->findAsset->checkMatrixFields(
			$type,
			$asset,
			$siteId
		);
		$assetEntries = array_merge(
			(array) $assetEntries,
			(array) $relatedMatrix
		);

		// Get all Super Tables where this asset is used within Entries
		$tableEntries = AssetLocations::$plugin->findAsset->checkSuperTableFields(
			$type,
			$asset,
			$siteId
		);
		$assetEntries = array_merge(
			(array) $assetEntries,
			(array) $tableEntries
		);

		return $assetEntries;
	}

	/**
	 * Get Links
	 * @description Check to see if the asset is used within any links
	 *
	 * @param String $type
	 * @param AssetElement $asset
	 * @return ?array
	 */
	public function getLinks(AssetElement $asset, int $siteId): ?array
	{
		// Check the plugin is installed
		$links = Craft::$app->plugins->getPlugin("typedlinkfield", false);

		if ($links) {
			$entries = [];

			$linkQuery = new Query();
			$linkQuery = $linkQuery
				->select("elementId")
				->from("lenz_linkfield")
				->where([
					"linkedId" => $asset->id,
					"siteId" => $siteId,
				])
				->all();

			foreach ($linkQuery as $link) {
				// Check Entries
				$linkEntry = AssetLocations::$plugin->findAsset->checkEntriesElement(
					$link["elementId"],
					$siteId
				);

				if ((array) $linkEntry) {
					$entries = array_merge(
						(array) $entries,
						(array) $linkEntry
					);
				}

				// Check Matrix
				$matrixElement = AssetLocations::$plugin->findAsset->checkMatrixElement(
					$link["elementId"],
					$siteId
				);

				if ((array) $matrixElement) {
					$entries = array_merge(
						(array) $entries,
						(array) $matrixElement
					);
				}

				// Check Super Tables
				$superTableElement = AssetLocations::$plugin->findAsset->checkSuperTableElements(
					$link["elementId"],
					$siteId
				);

				if ((array) $superTableElement) {
					$entries = array_merge(
						(array) $entries,
						(array) $superTableElement
					);
				}
			}

			return $entries;
		} else {
			return [];
		}
	}
}
