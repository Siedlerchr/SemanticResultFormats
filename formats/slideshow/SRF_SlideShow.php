<?php
/**
 * File holding the SRF_SlideShow class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

/**
 * The SRF_SlideShow class.
 *
 * @ingroup SemanticResultFormats
 */
class SRFSlideShow extends SMWResultPrinter {

	/**
	 * Get a human readable label for this printer.
	 *
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf-printername-slideshow' )->text();
	}

	/**
	 * Return serialised results in specified format.
	 * Implemented by subclasses.
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		$html = '';
		$id = uniqid();

		// build an array of article IDs contained in the result set
		$objects = [];
		foreach ( $res->getResults() as $key => $dataItem ) {

			$objects[] = [ $dataItem->getTitle()->getArticleId() ];

			$html .= $key . ': ' . $dataItem->getSerialization() . "<br>\n";
		}

		// build an array of data about the printrequests
		$printrequests = [];
		foreach ( $res->getPrintRequests() as $key => $printrequest ) {
			$data = $printrequest->getData();
			if ( $data instanceof SMWPropertyValue ) {
				$name = $data->getDataItem()->getKey();
			} else {
				$name = null;
			}
			$printrequests[] = [
				$printrequest->getMode(),
				$printrequest->getLabel(),
				$name,
				$printrequest->getOutputFormat(),
				$printrequest->getParameters(),
			];

		}

		// write out results and query params into JS arrays
		// Define the srf_filtered_values array
		SMWOutputs::requireScript(
			'srf_slideshow',
			Html::inlineScript(
				'srf_slideshow = {};'
			)
		);

		SMWOutputs::requireScript(
			'srf_slideshow' . $id,
			Html::inlineScript(
				'srf_slideshow["' . $id . '"] = ' . json_encode(
					[
						$objects,
						$this->params['template'],
						$this->params['delay'] * 1000,
						$this->params['height'],
						$this->params['width'],
						$this->params['nav controls'],
						$this->params['effect'],
						json_encode( $printrequests ),
					]
				) . ';'
			)
		);

		SMWOutputs::requireResource( 'ext.srf.slideshow' );

		if ( $this->params['nav controls'] ) {
			SMWOutputs::requireResource( 'jquery.ui.slider' );
		}

		return Html::element(
			'div',
			[
				'id' => $id,
				'class' => 'srf-slideshow ' . $id . ' ' . $this->params['class']
			]
		);
	}

	/**
	 * Check whether a "further results" link would normally be generated for this
	 * result set with the given parameters.
	 *
	 * @param SMWQueryResult $results
	 *
	 * @return bool
	 */
	protected function linkFurtherResults( SMWQueryResult $results ) {
		return false;
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['template'] = [
			'default' => '',
			'message' => 'smw_paramdesc_template',
		];

		// TODO: Implement named args
//		$params['named args'] = new Parameter( 'named args', Parameter::TYPE_BOOLEAN, false );
//		$params['named args']->setMessage( 'smw_paramdesc_named_args' );

		$params['class'] = [
			'default' => '',
			'message' => 'srf-paramdesc-class',
		];

		$params['height'] = [
			'default' => '100px',
			'message' => 'srf-paramdesc-height',
		];

		$params['width'] = [
			'default' => '200px',
			'message' => 'srf-paramdesc-width',
		];

		$params['delay'] = [
			'type' => 'integer',
			'default' => 5,
			'message' => 'srf-paramdesc-delay',
		];

		$params['nav controls'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-navigation-controls',
		];

		$params['effect'] = [
			'default' => 'none',
			'message' => 'srf-paramdesc-effect',
			'values' => [
				'none',
				'slide left',
				'slide right',
				'slide up',
				'slide down',
				'fade',
				'hide',
			],
		];

		return $params;
	}

}
