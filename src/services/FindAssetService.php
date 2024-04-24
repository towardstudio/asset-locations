<?php
namespace towardstudio\assetlocations\services;

use towardstudio\assetlocations\AssetLocations;

use Craft;
use craft\base\Component;

use craft\db\Query;
use craft\db\Table;

use craft\helpers\Db;

use craft\elements\Asset as AssetElement;
use craft\elements\Entry as EntryElement;

use verbb\supertable\elements\SuperTableBlockElement;

class FindAssetService extends Component
{
	// Public Methods
	// =========================================================================

	/**
	 * Check Asset Fields
	 * @description Check to see if the asset is used within any asset fields
	 *
	 * @param String $type
	 * @param Asset $asset
	 * @param Int $siteId
	 * @return ?object
	 */
	public function checkAssetFields(string $type, AssetElement $asset, int $siteId): ?object
	{
		$entries = (object) [];
		$relatedEntries = $type
			::find()
			->siteId($siteId)
			->status(null)
			->relatedTo(["targetElement" => $asset->id])
			->all();

		foreach ($relatedEntries as $entry) {
			$slug = $entry->slug;

            if ($slug) {
			    $entries->$slug = (object) [
				    "title" =>
					    $entry->title ?? AssetLocations::$plugin->section->getSetName($entry),
				    "cpUrl" => $entry->cpEditUrl,
				    "url" => $entry->url,
				    "status" => $entry->status,
				    "section" => AssetLocations::$plugin->section->getSectionName($entry),
			    ];
            } else {
                $id = $entry->id;

                $elementObject = EntryElement::find()
                    ->id($id)
                    ->siteId($siteId)
                    ->status(null)
                    ->one();

                if ($elementObject)
                {
                    $entryObject = EntryElement::find()
                        ->id($elementObject->primaryOwnerId)
                        ->status(null)
                        ->drafts(true)
                        ->one();

                    if ($entryObject)
                    {
                        $slug = $entryObject->slug;

                        $entries->$slug = (object) [
				            "title" =>
					            $entryObject->title ?? AssetLocations::$plugin->section->getSetName($entryObject),
				            "cpUrl" => $entryObject->cpEditUrl,
				            "url" => $entryObject->url,
				            "status" => $entryObject->status,
				            "section" => AssetLocations::$plugin->section->getSectionName($entryObject),
			            ];
                    }
                }
            }
		}

		return $entries;
	}

    /**
	 * Check Entries Element
	 * @description Check to see if the asset is used within any entries links
	 *
	 * @param String $id
	 * @param Int $siteId
	 * @return ?object
	 */
	public function checkEntriesElement(string $id, int $siteId): ?array
	{
		$linkEntry = EntryElement::find()
            ->id($id)
            ->siteId($siteId)
            ->all();

        return $linkEntry;
	}

	/**
	 * Check Super Table Fields
	 * @description Check to see if the asset is used within any super table fields
	 *
	 * @param String $type
	 * @param Asset $asset
	 * @return ?object
	 */
	public function checkSuperTableFields(string $type, AssetElement $asset, int $siteId): ?object
	{
		// Check the plugin is installed
		$table = Craft::$app->plugins->getPlugin("super-table", false);

		$tableEntries = (object) [];

		if (isset($table) && $table->isInstalled) {
			$relatedTable = SuperTableBlockElement::find()
				->siteId($siteId)
				->relatedTo(["targetElement" => $asset->id])
				->orderBy("id")
				->all();

			// Get the entry/category the block appears on
			foreach ($relatedTable as $item) {
				// Check for Matrix Blocks
				$results = (new Query())
					->from(Table::ELEMENTS)
					->where(["canonicalId" => $item->primaryOwnerId])
					->all();

				foreach ($results as $result) {
					if (str_contains($result["type"], "Matrix")) {
						$matrixQuery = new Query();
						$matrix = $matrixQuery
							->select("primaryOwnerId")
							->from(Table::MATRIXBLOCKS)
							->where(["id" => $result["id"]])
							->one();

						if ($matrix) {
							$elementQuery = new Query();
							$element = $elementQuery
								->select("canonicalId")
								->from(Table::ELEMENTS)
								->where(["id" => $matrix["primaryOwnerId"]])
								->one();

							if ($element) {
								$entry = $type
									::find()
									->siteId($siteId)
									->id($element["canonicalId"])
									->status(null)
									->one();

								if ($entry) {
									if (!isset($tableEntries->{$entry->slug})) {
										$tableEntries->{$entry->slug} = (object) [
											"title" =>
												$entry->title ??
												AssetLocations::$plugin->section->getSetName(
													$entry
												),
											"cpUrl" => $entry->cpEditUrl,
											"url" => $entry->url,
											"status" => $entry->status,
											"section" => AssetLocations::$plugin->section->getSectionName(
												$entry
											),
										];
									}
								}
							}
						}
					}
				}

				// Elements
				$elements = (new Query())
					->from(Table::ELEMENTS)
					->where(["id" => $item->primaryOwnerId])
					->andWhere("canonicalId IS NULL")
					->all();

				foreach ($elements as $element) {
					$entry = $type
						::find()
						->siteId($siteId)
						->status(null)
						->id($element["id"])
						->one();

					if ($entry) {
						if (!isset($tableEntries->{$entry->slug})) {
							$tableEntries->{$entry->slug} = (object) [
								"title" =>
									$entry->title ??
									AssetLocations::$plugin->section->getSetName(
										$entry
									),
								"cpUrl" => $entry->cpEditUrl,
								"url" => $entry->url,
								"status" => $entry->status,
								"section" => AssetLocations::$plugin->section->getSectionName(
									$entry
								),
							];
						}
					}
				}
			}
		}

		return $tableEntries;
	}

    /**
	 * Check Super Table Elements
	 * @description Check to see if the asset is used within any super table elements
	 *
	 * @param String $type
	 * @param Asset $asset
	 * @return ?object
	 */
	public function checkSuperTableElements(string $id, int $siteId): ?object
	{
		// Check the plugin is installed
		$table = Craft::$app->plugins->getPlugin("super-table", false);

		$tableEntries = (object) [];

		if (isset($table) && $table->isInstalled) {
			$relatedTable = SuperTableBlockElement::find()
                ->id($id)
				->siteId($siteId)
				->orderBy("id")
				->all();

			// Get the entry/category the block appears on
			foreach ($relatedTable as $item) {
				// Check for Matrix Blocks
				$results = (new Query())
					->from(Table::ELEMENTS)
					->where(["canonicalId" => $item->primaryOwnerId])
					->all();

				foreach ($results as $result) {
					if (str_contains($result["type"], "Matrix")) {
						$matrixQuery = new Query();
						$matrix = $matrixQuery
							->select("primaryOwnerId")
							->from(Table::MATRIXBLOCKS)
							->where(["id" => $result["id"]])
							->one();

						if ($matrix) {
							$elementQuery = new Query();
							$element = $elementQuery
								->select("canonicalId")
								->from(Table::ELEMENTS)
								->where(["id" => $matrix["primaryOwnerId"]])
								->one();

							if ($element) {
								$entry = EntryElement
									::find()
									->siteId($siteId)
									->id($element["canonicalId"])
									->status(null)
									->one();

								if ($entry) {
									if (!isset($tableEntries->{$entry->slug})) {
										$tableEntries->{$entry->slug} = (object) [
											"title" =>
												$entry->title ??
												AssetLocations::$plugin->section->getSetName(
													$entry
												),
											"cpUrl" => $entry->cpEditUrl,
											"url" => $entry->url,
											"status" => $entry->status,
											"section" => AssetLocations::$plugin->section->getSectionName(
												$entry
											),
										];
									}
								}
							}
						}
					}
				}

				// Elements
				$elements = (new Query())
					->from(Table::ELEMENTS)
					->where(["id" => $item->primaryOwnerId])
					->andWhere("canonicalId IS NULL")
					->all();

				foreach ($elements as $element) {
					$entry = EntryElement
						::find()
						->siteId($siteId)
						->status(null)
						->id($element["id"])
						->one();

					if ($entry) {
						if (!isset($tableEntries->{$entry->slug})) {
							$tableEntries->{$entry->slug} = (object) [
								"title" =>
									$entry->title ??
									AssetLocations::$plugin->section->getSetName(
										$entry
									),
								"cpUrl" => $entry->cpEditUrl,
								"url" => $entry->url,
								"status" => $entry->status,
								"section" => AssetLocations::$plugin->section->getSectionName(
									$entry
								),
							];
						}
					}
				}
			}
		}

		return $tableEntries;
	}
}
