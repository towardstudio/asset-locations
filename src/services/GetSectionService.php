<?php
namespace towardstudio\assetlocations\services;

use Craft;
use craft\base\Component;

use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\GlobalSet;

class GetSectionService extends Component
{
	/**
	 * Check Get the Section/Group Name
	 * @description Return the Section/Group name of the element
	 *
	 * @param object $element
	 * @return ?object
	 */
	public function getSectionName($element)
	{
		if (!$element or empty($element)) {
			return null;
		}

		if ($element instanceof Entry) {
			$section = Craft::$app->sections->getSectionById($element->sectionId);

			if ($section) {
				$sectionName = $section->name;

				if ($section->type === "single") {
					$sectionName = "Singles";
				}

				return $sectionName;
			} else {
				return null;
			}
		} elseif ($element instanceof Category) {
			$group = Craft::$app->categories->getGroupById($element->groupId);

			if ($group) {
				return $group->name;
			} else {
				return null;
			}
		} elseif ($element instanceof GlobalSet) {
			return null;
		}
	}

	/**
	 * Check Get the Global Set Name
	 * @description Return the Global Set Name
	 *
	 * @param object $element
	 * @return ?string
	 */
	public function getSetName($element): string
	{
		return $element->name;
	}
}
