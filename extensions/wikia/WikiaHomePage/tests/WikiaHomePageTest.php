<?php
require_once __DIR__ . '/../WikiaHomePage.setup.php';
require_once __DIR__ . '/../../WikiaHubsServices/WikiaHubsServicesHelper.class.php';
require_once __DIR__ . '/../../CityVisualization/CityVisualization.setup.php' ;

class WikiaHomePageTest extends WikiaBaseTest {
	const TEST_CITY_ID = 80433;
	const TEST_URL = 'http://testing';
	const TEST_MEMBER_DATE = 'Jun 2005';
	const MOCK_FILE_URL = 'Mock file URL';
	const BLANK_IMG_URL = 'data:image/gif;base64,R0lGODlhAQABAIABAAAAAP///yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D';

	protected $wgServerOrg = null;

	protected function setUp() {
		parent::setUp();

		$this->mockStaticMethod( 'AvatarService', 'getAvatarUrl', null );
	}

	protected function setUpMock($cacheParams = null) {
		// mock cache
		$memcParams = array(
			'set' => null,
			'get' => null,
		);
		if (is_array($cacheParams)) {
			$memcParams = $memcParams + $cacheParams;
		}
		$this->setUpMockObject('stdClass', $memcParams, false, 'wgMemc');

		$this->mockGlobalVariable('wgCityId', self::TEST_CITY_ID);

		$this->mockGlobalVariable('wgWikiaHubsPages', array(
			1 => array('Lifestyle'),
			2 => array('Video_Games'),
			3 => array('Entertainment')
		));
	}

	protected function setUpMockObject($objectName, $objectParams = null, $needSetInstance = false, $globalVarName = null, $callOriginalConstructor = true, $globalFunc = array()) {
		$mockObject = $objectParams;
		if (is_array($objectParams)) {
			// extract params from methods
			$objectValues = array(); // $objectValues is stored in $objectParams[params]
			$methodParams = array();
			foreach ($objectParams as $key => $value) {
				if ($key == 'params' && !empty($value)) {
					$objectValues = array($value);
				} else {
					$methodParams[$key] = $value;
				}
			}
			$methods = array_keys($methodParams);

			// call original contructor or not
			if ($callOriginalConstructor) {
				$mockObject = $this->getMock($objectName, $methods, $objectValues);
			} else {
				$mockObject = $this->getMock($objectName, $methods, $objectValues, '', false);
			}

			foreach ($methodParams as $method => $value) {
				if ($value === null) {
					$mockObject->expects($this->any())
						->method($method);
				} else {
					if (is_array($value) && array_key_exists('mockExpTimes', $value) && array_key_exists('mockExpValues', $value)) {
						if ($value['mockExpValues'] == null) {
							$mockObject->expects($this->exactly($value['mockExpTimes']))
								->method($method);
						} else {
							$mockObject->expects($this->exactly($value['mockExpTimes']))
								->method($method)
								->will($this->returnValue($value['mockExpValues']));

						}
					} else {
						$mockObject->expects($this->any())
							->method($method)
							->will($this->returnValue($value));
					}
				}
			}
		}

		// mock global variable
		if (!empty($globalVarName)) {
			$this->mockGlobalVariable($globalVarName, $mockObject);
		}

		// mock global function
		if (!empty($globalFunc)) {
			$this->getGlobalFunctionMock( $globalFunc['name'] )
				->expects( $this->exactly( $globalFunc['time'] ) )
				->method( $globalFunc['name'] )
				->will( $this->returnValue( $mockObject ) );
		}

		// set instance
		if ($needSetInstance) {
			$this->mockClassEx($objectName, $mockObject);
		}
		return $mockObject;
	}

	/**
	 * @dataProvider getHubV2ImagesDataProvider
	 */
	public function testGetHubV2Images($mockedImageUrl, $expHubImages) {
		// setup
		$this->mockGlobalVariable('wgEnableWikiaHubsV2Ext', true);
		$this->mockGlobalVariable('wgWikiaHubsV2Pages', array(
			WikiFactoryHub::CATEGORY_ID_ENTERTAINMENT => 'Entertainment',
			WikiFactoryHub::CATEGORY_ID_GAMING => 'Video_games',
			WikiFactoryHub::CATEGORY_ID_LIFESTYLE => 'Lifestyle',
		));

		$mock_cache = $this->getMock('stdClass', array('get', 'set'));
		$mock_cache->expects($this->any())
			->method('get')
			->will($this->returnValue(null));
		$mock_cache->expects($this->any())
			->method('set');
		$this->mockGlobalVariable('wgMemc', $mock_cache);

		$homePageMock = $this->getMock('WikiaHomePageController', array('getHubSliderData'));
		$homePageMock->expects($this->any())
			->method('getHubSliderData')
			->will($this->returnValue(array(
					'data' => array(
						'slides' => array(
							0 => array(
								'photoUrl' => $mockedImageUrl
							)
						)
					)
				)
			));

		$this->mockClass('WikiaHomePageController', $homePageMock);

		$this->setUpMock();

		$response = $this->app->sendRequest('WikiaHomePage', 'getHubImages');
		$responseData = $response->getVal('hubImages');

		$this->assertEquals($expHubImages, $responseData);
	}

	public function getHubV2ImagesDataProvider() {
		return array(
			array(
				null,
				array(
					WikiFactoryHub::CATEGORY_ID_ENTERTAINMENT => null,
					WikiFactoryHub::CATEGORY_ID_GAMING => null,
					WikiFactoryHub::CATEGORY_ID_LIFESTYLE => null,
				)
			),
			array(
				'testUrl',
				array(
					WikiFactoryHub::CATEGORY_ID_ENTERTAINMENT => 'testUrl',
					WikiFactoryHub::CATEGORY_ID_GAMING => 'testUrl',
					WikiFactoryHub::CATEGORY_ID_LIFESTYLE => 'testUrl',
				)
			),
		);
	}

	public function testGetList() {
		$this->markTestSkipped('This test needs to be rewritten to serve its purpose');
		$this->setUpMock();

		$wikiaHomePageHelperStub = $this->getMock(
			'WikiaHomePageHelper',
			array('getVarFromWikiFactory'));
		$wikiaHomePageHelperStub->expects($this->any())->method('getVarFromWikiFactory')->will($this->returnValue(5));

		$this->mockClass('WikiaHomePageHelper', $wikiaHomePageHelperStub);

		$visualizationStub = $this->getMock(
			'CityVisualization',
			array('getList'));
		$visualizationStub->expects($this->any())->method('getList')->will($this->returnValue(array()));
		$this->mockClass('CityVisualization', $visualizationStub);

		//2nd failover
		$response = $this->app->sendRequest('WikiaHomePageController', 'getList', array());
		$status = $response->getVal('wgWikiaBatchesStatus');
		$failoverData = $response->getVal('initialWikiBatchesForVisualization');

		$this->assertEquals('false', $status);
		$this->assertNotEmpty($failoverData);

		$wikiaHomePageControllerStub = $this->getMock('WikiaHomePageController', array('getMediaWikiMessage'));
		$mediaWikiMsgMock = <<<TXT
*Video Games
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
**A video games wiki|http://a-video-games-wiki.wikia.com|image|description
*Entertainment
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
**An entertainment wiki|http://an-entertainment-wiki.wikia.com|image|description
*Lifestyle
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
**A lifestyle wiki|http://a-lifestyle-wiki.wikia.com|image|description
TXT;
		$wikiaHomePageControllerStub->expects($this->any())->method('getMediaWikiMessage')->will($this->returnValue($mediaWikiMsgMock));
		$this->mockClass('WikiaHomePageController', $wikiaHomePageControllerStub);

		$response = $this->app->sendRequest('WikiaHomePageController', 'getList', array());
		$status = $response->getVal('wgWikiaBatchesStatus');
		$exception = $response->getVal('exception');
		$receivedData = $response->getVal('initialWikiBatchesForVisualization');
		$receivedData = json_decode($receivedData);
		asort($receivedData);

		$expectedData = '[{"bigslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"mediumslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"smallslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}]},{"bigslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"mediumslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"smallslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}]},{"bigslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"mediumslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"smallslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}]},{"bigslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"mediumslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"smallslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}]},{"bigslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"mediumslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}],"smallslots":[{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A video games wiki","wikiurl":"http:\/\/a-video-games-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"An entertainment wiki","wikiurl":"http:\/\/an-entertainment-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"},{"wikiid":0,"wikiname":"A lifestyle wiki","wikiurl":"http:\/\/a-lifestyle-wiki.wikia.com","wikinew":false,"wikihot":false,"wikipromoted":false,"wikiblocked":false,"main_image":"image","image":"data:image\/gif;base64,R0lGODlhAQABAIABAAAAAP\/\/\/yH5BAEAAAEALAAAAAABAAEAQAICTAEAOw%3D%3D"}]}]';
		$expectedData = json_decode($expectedData);
		asort($expectedData);

		//1st failover
		$this->assertEquals(
			array(
				'status' => 'false',
				'exception' => '',
				'failoverData' => $expectedData
			),
			array(
				'status' => $status,
				'exception' => $exception,
				'failoverData' => $failoverData
			)
		);
	}
	
	private function getMocksForAdminsAvatarsTests( $mockedUsersIds, $mockedUserInfo, $mockedEditCount ) {
		$wikiServiceMock = $this->getMock( 'WikiService', [ 'getWikiAdminIds', 'getTopEditors', 'getUserInfo' ] );
		$wikiServiceMock
			->expects( $this->any() )
			->method( 'getWikiAdminIds' )
			->will( $this->returnValue( $mockedUsersIds ) );
		$wikiServiceMock
			->expects( $this->any() )
			->method( 'getUserInfo' )
			->will( $this->onConsecutiveCalls( $mockedUserInfo[0], $mockedUserInfo[1], $mockedUserInfo[2], $mockedUserInfo[3], $mockedUserInfo[4], $mockedUserInfo[5] ) );

		$userStatsServiceMock = $this->getMock( 'UserStatsService', [ 'getEditCountWiki' ], [], '', false );
		$userStatsServiceMock
			->expects( $this->any() )
			->method( 'getEditCountWiki' )
			->will( $this->returnValue( $mockedEditCount ) );

		$wikiaHomePageHelperMock = $this->getMock( 'WikiaHomePageHelper', [ 'getWikiService', 'getUserStats' ] );
		$wikiaHomePageHelperMock
			->expects( $this->any() )
			->method( 'getWikiService' )
			->will( $this->returnValue( $wikiServiceMock ) );
		$wikiaHomePageHelperMock
			->expects( $this->any() )
			->method( 'getUserStats' )
			->will( $this->returnValue( $userStatsServiceMock ) );
		
		return $wikiaHomePageHelperMock;
	}

	/**
	 * @dataProvider getWikiAdminAvatarsDataProvider
	 */
	public function testGetWikiAdminAvatars( $mockedWikiAdminsIds, $mockedUserInfo, $mockedEditCount, $mockedWikiId, $expAdminAvatars ) {
		$wikiaHomePageHelperMock = $this->getMocksForAdminsAvatarsTests( $mockedWikiAdminsIds, $mockedUserInfo, $mockedEditCount );
		
		/** @var WikiaHomePageHelper $wikiaHomePageHelperMock */
		$adminAvatars = array_values( $wikiaHomePageHelperMock->getWikiAdminAvatars( $mockedWikiId ) );

		$this->assertEquals( $expAdminAvatars, $adminAvatars );
	}

	public function getWikiAdminAvatarsDataProvider() {
		return [
			'invalid wiki id' => [
				'mockedUsersIds' => [],
				'mockedUserInfo' => [[], [], [], [], [], []],
				'mockedEditCount' => 0,
				'mockedWikiId' => 0,
				'expectedResult' => [],
			],
			'no admins/editors' => [
				'mockedUsersIds' => [],
				'mockedUserInfo' => [[], [], [], [], [], []],
				'mockedEditCount' => 0,
				'mockedWikiId' => 531,
				'expectedResult' => [],
			],
			'user not found' => [
				'mockedUsersIds' => [ 123 ],
				'mockedUserInfo' => [[], [], [], [], [], []],
				'mockedEditCount' => 0,
				'mockedWikiId' => self::TEST_CITY_ID,
				'expectedResult' => [],
			],
			'only one user found' => [
				'mockedUsersIds' => [ 123 ],
				'mockedUserInfo' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					],
					[],
					[],
					[],
					[],
					[]
				],
				'mockedEditCount' => 0,
				'mockedWikiId' => self::TEST_CITY_ID,
				'expectedResult' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					]
				],
			],
			'exactly as many users found as we needed' => [
				'mockedUsersIds' => [ 123, 345, 678 ],
				'mockedUserInfo' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName1',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName2',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '345'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName3',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '678'
					],
					[],
					[],
					[]
				],
				'mockedEditCount' => 0,
				'mockedWikiId' => self::TEST_CITY_ID,
				'expectedResult' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName1',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName2',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '345'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName3',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '678'
					]
				],
			],
		];
	}

	public function testGetWikiAdminAvatarsReturnElementsCount() {
		$mockedWikiId = self::TEST_CITY_ID;
		$mockedWikiAdminsIds = [ 123, 345, 678, 910, 111, 213 ];
		$mockedUserInfo = [
			[
				'avatarUrl' => null,
				'edits' => 0,
				'name' => 'TestName1',
				'userPageUrl' => self::TEST_URL,
				'userContributionsUrl' => self::TEST_URL,
				'since' => self::TEST_MEMBER_DATE,
				'userId' => '123'
			],
			[
				'avatarUrl' => null,
				'edits' => 0,
				'name' => 'TestName2',
				'userPageUrl' => self::TEST_URL,
				'userContributionsUrl' => self::TEST_URL,
				'since' => self::TEST_MEMBER_DATE,
				'userId' => '345'
			],
			[
				'avatarUrl' => null,
				'edits' => 0,
				'name' => 'TestName3',
				'userPageUrl' => self::TEST_URL,
				'userContributionsUrl' => self::TEST_URL,
				'since' => self::TEST_MEMBER_DATE,
				'userId' => '678'
			],
			[
				'avatarUrl' => null,
				'edits' => 0,
				'name' => 'TestName1',
				'userPageUrl' => self::TEST_URL,
				'userContributionsUrl' => self::TEST_URL,
				'since' => self::TEST_MEMBER_DATE,
				'userId' => '123'
			],
			[
				'avatarUrl' => null,
				'edits' => 0,
				'name' => 'TestName2',
				'userPageUrl' => self::TEST_URL,
				'userContributionsUrl' => self::TEST_URL,
				'since' => self::TEST_MEMBER_DATE,
				'userId' => '345'
			],
			[
				'avatarUrl' => null,
				'edits' => 0,
				'name' => 'TestName3',
				'userPageUrl' => self::TEST_URL,
				'userContributionsUrl' => self::TEST_URL,
				'since' => self::TEST_MEMBER_DATE,
				'userId' => '678'
			],
		];
		$mockedEditCount = 0;
		$wikiaHomePageHelperMock = $this->getMocksForAdminsAvatarsTests( $mockedWikiAdminsIds, $mockedUserInfo, $mockedEditCount );
		$expAdminAvatarsCount = 3;

		/** @var WikiaHomePageHelper $wikiaHomePageHelperMock */
		$adminAvatarsCount = count( $wikiaHomePageHelperMock->getWikiAdminAvatars( $mockedWikiId ) );

		$this->assertEquals( $expAdminAvatarsCount, $adminAvatarsCount );
	}

	/**
	 * @dataProvider getWikiTopEditorAvatarsDataProvider
	 */
	public function testGetWikiTopEditorAvatars( $mockedUsersIds, $mockedUserInfo, $mockedWikiId, $expTopEditorsAvatars ) {
		$wikiServiceMock = $this->getMock( 'WikiService', [ 'getWikiAdminIds', 'getTopEditors', 'getUserInfo' ] );
		$wikiServiceMock
			->expects( $this->any() )
			->method( 'getTopEditors' )
			->will( $this->returnValue( $mockedUsersIds ) );
		$wikiServiceMock
			->expects( $this->any() )
			->method( 'getUserInfo' )
			->will( $this->onConsecutiveCalls( $mockedUserInfo[0], $mockedUserInfo[1], $mockedUserInfo[2], $mockedUserInfo[3], $mockedUserInfo[4], $mockedUserInfo[5] ) );

		$wikiaHomePageHelperMock = $this->getMock( 'WikiaHomePageHelper', [ 'getWikiService', 'getUserStats' ] );
		$wikiaHomePageHelperMock
			->expects( $this->any() )
			->method( 'getWikiService' )
			->will( $this->returnValue( $wikiServiceMock ) );
		
		/** @var WikiaHomePageHelper $wikiaHomePageHelperMock */
		$topEditorsAvatars = array_values( $wikiaHomePageHelperMock->getWikiTopEditorAvatars( $mockedWikiId ) );

		$this->assertEquals( $expTopEditorsAvatars, $topEditorsAvatars );
	}

	public function getWikiTopEditorAvatarsDataProvider() {
		return [
			'invalid wiki id' => [
				'mockedUsersIds' => [],
				'mockedUserInfo' => [[], [], [], [], [], []],
				'mockedWikiId' => 0,
				'expectedResult' => [],
			],
			'no admins/editors' => [
				'mockedUsersIds' => [],
				'mockedUserInfo' => [[], [], [], [], [], []],
				'mockedWikiId' => 531,
				'expectedResult' => [],
			],
			'user not found' => [
				'mockedUsersIds' => [ 123 => 0 ],
				'mockedUserInfo' => [[], [], [], [], [], []],
				'mockedWikiId' => self::TEST_CITY_ID,
				'expectedResult' => [],
			],
			'only one user found' => [
				'mockedUsersIds' => [ 123 => 0 ],
				'mockedUserInfo' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					],
					[],
					[],
					[],
					[],
					[]
				],
				'mockedWikiId' => self::TEST_CITY_ID,
				'expectedResult' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					]
				],
			],
			'exactly as many users found as we needed' => [
				'mockedUsersIds' => [ 123 => 0, 345 => 0, 678 => 0 ],
				'mockedUserInfo' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName1',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName2',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '345'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName3',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '678'
					],
					[],
					[],
					[]
				],
				'mockedWikiId' => self::TEST_CITY_ID,
				'expectedResult' => [
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName1',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '123'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName2',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '345'
					],
					[
						'avatarUrl' => null,
						'edits' => 0,
						'name' => 'TestName3',
						'userPageUrl' => self::TEST_URL,
						'userContributionsUrl' => self::TEST_URL,
						'since' => self::TEST_MEMBER_DATE,
						'userId' => '678'
					]
				],
			],
		];
	}

	/**
	 * @dataProvider getProcessedWikisImgSizesDataProvider
	 */
	public function testGetProcessedWikisImgSizes($limit, $width, $height) {
		$whh = new WikiaHomePageHelper();
		$size = $whh->getProcessedWikisImgSizes($limit);

		$this->assertEquals($width, $size->width);
		$this->assertEquals($height, $size->height);
	}

	public function getProcessedWikisImgSizesDataProvider() {
		$whh = new WikiaHomePageHelper();
		return array(
			array(WikiaHomePageHelper::SLOTS_BIG, $whh->getRemixBigImgWidth(), $whh->getRemixBigImgHeight()),
			array(WikiaHomePageHelper::SLOTS_MEDIUM, $whh->getRemixMediumImgWidth(), $whh->getRemixMediumImgHeight()),
			array(WikiaHomePageHelper::SLOTS_SMALL, $whh->getRemixSmallImgWidth(), $whh->getRemixSmallImgHeight()),
			array(666, $whh->getRemixBigImgWidth(), $whh->getRemixBigImgHeight()),
		);
	}

}
