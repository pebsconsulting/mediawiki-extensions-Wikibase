<?php

namespace Wikibase\Lib\Tests\Formatters;

use DataValues\NumberValue;
use DataValues\StringValue;
use InvalidArgumentException;
use MediaWikiTestCase;
use Wikibase\Lib\Formatters\CommonsInlineImageFormatter;

/**
 * @covers Wikibase\Lib\Formatters\CommonsInlineImageFormatter
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 * @group Wikibase
 * @group Database
 *
 * @license GPL-2.0-or-later
 * @author Adrian Heine <adrian.heine@wikimedia.de>
 * @author Marius Hoch
 */
class CommonsInlineImageFormatterTest extends MediaWikiTestCase {

	public function commonsInlineImageFormatterProvider() {
		$exampleJpgHtmlRegex = '@<ul .*<a[^>]+href="//commons.wikimedia.org/wiki/File:Example.jpg"[^>]*>' .
				'<img.*src=".*//upload.wikimedia.org/wikipedia/commons/.*/Example.jpg".*/></a>.*' .
				'<a[^>]+href="//commons.wikimedia.org/wiki/File:Example.jpg"[^>]*>Example.jpg</a>.*</ul>@s';

		return [
			[
				new StringValue( 'example.jpg' ), // Lower-case file name
				$exampleJpgHtmlRegex
			],
			[
				new StringValue( 'Example.jpg' ),
				$exampleJpgHtmlRegex
			],
			[
				new StringValue( 'Example-That-Does-Not-Exist.jpg' ),
				'@^.*<a[^>]+href="//commons.wikimedia.org/wiki/File:Example-That-Does-Not-Exist.jpg"[^>]*>@s'
			],
			[
				new StringValue( 'Dangerous-quotes""' ),
				'@/""/@s',
				false
			],
			[
				new StringValue( '<eviltag>' ),
				'@/<eviltag>/@s',
				false
			],
		];
	}

	/**
	 * @dataProvider commonsInlineImageFormatterProvider
	 */
	public function testFormat( StringValue $value, $pattern, $shouldContain = true ) {
		if ( $shouldContain && !wfFindFile( 'Example.jpg' ) ) {
			$this->markTestSkipped( '"Example.jpg" not found? Instant commons disabled?' );
		}

		$formatter = new CommonsInlineImageFormatter();

		$html = $formatter->format( $value );
		if ( $shouldContain ) {
			$this->assertRegExp( $pattern, $html );
		} else {
			$this->assertNotRegExp( $pattern, $html );
		}
	}

	public function testFormatError() {
		$formatter = new CommonsInlineImageFormatter();
		$value = new NumberValue( 23 );

		$this->setExpectedException( InvalidArgumentException::class );
		$formatter->format( $value );
	}

}
