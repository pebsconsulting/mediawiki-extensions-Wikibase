<?php

namespace Wikibase;

use Article;
use HistoryAction;
use IContextSource;
use Wikibase\DataModel\Services\Lookup\LabelDescriptionLookup;
use Wikibase\Store\EntityIdLookup;

/**
 * Handles the history action for Wikibase entities.
 *
 * @license GPL-2.0-or-later
 * @author John Erling Blad < jeblad@gmail.com >
 * @author Adrian Heine <adrian.heine@wikimedia.de>
 */
class HistoryEntityAction extends HistoryAction {

	/**
	 * @var EntityIdLookup
	 */
	private $entityIdLookup;

	/**
	 * @var LabelDescriptionLookup
	 */
	private $labelLookup;

	/**
	 * @param Article $article
	 * @param IContextSource|null $context
	 * @param EntityIdLookup $entityIdLookup
	 * @param LabelDescriptionLookup $labelLookup
	 */
	public function __construct(
		Article $article,
		IContextSource $context = null,
		EntityIdLookup $entityIdLookup,
		LabelDescriptionLookup $labelLookup
	) {
		parent::__construct( $article, $context );

		$this->entityIdLookup = $entityIdLookup;
		$this->labelLookup = $labelLookup;
	}

	/**
	 * Return a string for use as title.
	 *
	 * @return string
	 */
	protected function getPageTitle() {
		$entityId = $this->entityIdLookup->getEntityIdForTitle( $this->getTitle() );

		if ( !$entityId ) {
			return parent::getPageTitle();
		}

		$idSerialization = $entityId->getSerialization();
		$label = $this->labelLookup->getLabel( $entityId );

		if ( $label !== null ) {
			$labelText = $label->getText();
			// Escaping HTML characters in order to retain original label that may contain HTML
			// characters. This prevents having characters evaluated or stripped via
			// OutputPage::setPageTitle:
			return $this->msg( 'wikibase-history-title-with-label' )
				->rawParams( $idSerialization, htmlspecialchars( $labelText ) )->text();
		} else {
			return $this->msg( 'wikibase-history-title-without-label' )
				->rawParams( $idSerialization )->text();
		}
	}

}
