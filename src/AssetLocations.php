<?php
namespace bluegg\assetlocations;

/** Craft **/
use Craft;
use craft\base\Plugin;
use craft\elements\Asset;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\models\FieldLayout;

/** Custom **/
use bluegg\assetlocations\fieldlayoutelements\Locations;
use bluegg\assetlocations\services\FindAssetService;
use bluegg\assetlocations\services\GetSectionService;
use bluegg\assetlocations\services\FindElementService;
use bluegg\assetlocations\services\GetUsageService;

use yii\base\Event;

/**
 * @author    Bluegg
 * @package   AssetLocations
 * @since     1.0.0
 *
 */
class AssetLocations extends Plugin
{
	public static ?AssetLocations $plugin;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		self::$plugin = $this;

		$this->setComponents([
			"findAsset" => FindAssetService::class,
			"section" => GetSectionService::class,
			"elementService" => FindElementService::class,
			"usage" => GetUsageService::class,
		]);

		$assetFields = null;

		// Add Native Fields to be added to the Entries
		Event::on(
		FieldLayout::class,
			FieldLayout::EVENT_DEFINE_NATIVE_FIELDS,
			function (DefineFieldLayoutFieldsEvent $event)
			{
				/** @var FieldLayout $fieldLayout */
				$fieldLayout = $event->sender;
				if ($fieldLayout->type === Asset::class) {
					$event->fields[] = Locations::class;
					$assetFields = $event->fields;
				}
			}
		);

		/**
         * Add Usage Column to Assets which have the native field
         * NOTE: You still need to select them with the 'gear'
         *
         * @return array
         */
        Event::on(
			Asset::class,
			Asset::EVENT_REGISTER_TABLE_ATTRIBUTES,
			function (RegisterElementTableAttributesEvent $event)
			{
            	$event->tableAttributes['used'] = [
                	'label' => 'In Use',
            	];
        	}
		);

		/**
         * Set HTML for Usage Column
         *
         * @return string
         */
		Event::on(
			Asset::class,
			Asset::EVENT_SET_TABLE_ATTRIBUTE_HTML,
			function (SetElementTableAttributeHtmlEvent $event) {
				if ($event->attribute === 'used') {
					// Get Asset
                	$asset = $event->sender;

					$tickIcon = file_get_contents($this->getBasePath() . DIRECTORY_SEPARATOR . 'resources/images/tick.svg');
					$questionIcon = file_get_contents($this->getBasePath() . DIRECTORY_SEPARATOR . 'resources/images/question.svg');

					// Set HTML
                	$event->html = $this->usage->getUsage($asset) ?
						'<span title="This asset is used">' . $tickIcon . '</span><span class="visually-hidden">This asset is used</span>' :
						'<span title="This asset may be used">' . $questionIcon . '</span><span class="visually-hidden">This asset may be used</span>';

                	// Prevent other event listeners from getting invoked
                	$event->handled = true;
				}
			}
		);

	}
}
