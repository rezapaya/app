var AdProviderSevenOneMedia = function(adLogicPageLevelParamsLegacy, scriptWriter, tracker, log, window, slotTweaker) {
	'use strict';

	var ord = Math.round(Math.random() * 23456787654),
		slots = {
			'ad-popup1': true,
			'ad-fullbanner2': true,
			'ad-rectangle1': true,
			'ad-skyscraper1': true,
			'TOP_RIGHT_BOXAD': true,
			'HOME_TOP_RIGHT_BOXAD': true,
			'SEVENONEMEDIA_FLUSH': true
		},
		slotsToRender = [];

	function canHandleSlot(slot) {
		log(['canHandleSlot', slot], 5, 'AdProviderSevenOneMedia');

		var slotname = slot[0];

		if (slots[slotname]) {
			log(['canHandleSlot', slot, true], 5, 'AdProviderSevenOneMedia');
			return true;
		}

		log(['canHandleSlot', slot, false], 5, 'AdProviderSevenOneMedia');
		return false;
	}

	function pushAd(slot) {
		log(['pushAd', slot], 5, 'AdProviderSevenOneMedia');

		var slotname = slot[0];
		slotsToRender.push(slotname);
	}

	function insertAd() {
		var slotname = slotsToRender.shift();

		if (!slotname) {
			return;
		}

		log(['insertAd', slotname], 5, 'AdProviderSevenOneMedia');
		//alert('insertAd ' + slotname.replace('ad-', '') + ' to #' + slotname);

		scriptWriter.injectScriptByText(slotname, "myAd.insertAd('" + slotname.replace('ad-', '') + "');", function () {
			myAd.finishAd(slotname.replace('ad-', ''));
			log(['finish	Ad', slotname], 5, 'AdProviderSevenOneMedia');
			insertAd();
		});
	}

	function flushAds(slot) {
		log(['flushAds', slot], 5, 'AdProviderSevenOneMedia');

		var head = document.getElementsByTagName('head')[0],
			link = document.createElement('link'),
			originalWikiaTopAds = document.getElementById('WikiaTopAds');

		originalWikiaTopAds.style.display = 'none';

		link.rel = 'stylesheet';
		link.href = '/__am/90987245/one/-/extensions/wikia/AdEngine/SevenOneMedia/my_ad_integration.css';
		head.appendChild(link);

		window.SOI_SITE = 'wikia';
		window.SOI_SUBSITE = 'videospiele'; // first level (home for home)
		window.SOI_SUB2SITE = 'gta'; // second level
		window.SOI_SUB3SITE = ''; // third level
		window.SOI_CONTENT  = 'content'; // content|video|gallery|game
		window.SOI_WERBUNG  = true;
		// "	// Available tags
		window.SOI_PU1 = true; // popup1
		window.SOI_FB2 = true; // fullbanner2
		window.SOI_SC1 = true; // skyscraper1
			// "// Suitability for special ads
			// "// - from popup1
//	var SOI_PU = false; // popup/popunder
		window.SOI_PL = true; // powerlayer
//	var SOI_FA = false; // baseboard (mnemonic: FooterAd, FloorAd)

		// - from fullbanner2
		window.SOI_PB = true; // powerbanner (728x180)
		window.SOI_PD = true; // pushdown
		window.SOI_BB = true; // billboard
		window.SOI_WP = true; // wallpaper
		window.SOI_FP = true; // fireplace

		// - from skyscraper1
		window.SOI_SB = true;

		scriptWriter.injectScriptByUrl(
			slot[0],
			"/__am/90987245/one/-/extensions/wikia/AdEngine/SevenOneMedia/my_ad_integration.js",
			function () {
				scriptWriter.injectScriptByText(
					slot[0],
					"myAd.loadScript('site');",
					function () {
						scriptWriter.injectScriptByText(
							slot[0],
							"myAd.loadScript('global');",
							function () {
								insertAd();
							}
						);
					}
				);
			}
		);
	}

	function fillInSlot(slot) {
		log('fillInSlot', 5, 'AdProviderSevenOneMedia');
		log(slot, 5, 'AdProviderSevenOneMedia');

		var slotname = slot[0];

		if (slotname === 'SEVENONEMEDIA_FLUSH') {
			flushAds(slot);
		} else if (slotname === 'TOP_RIGHT_BOXAD' || slotname === 'HOME_TOP_RIGHT_BOXAD') {
			var slot = document.getElementById(slotname),
				outer = document.createElement('div'),
				inner = document.createElement('div');

			window.SOI_RT1 = true;
			window.SOI_HP  = true;

			outer.id = 'ad-rectangle1-outer';
			inner.id = 'ad-rectangle1';
			inner.className = 'ad-wrapper';

			slot.appendChild(outer);
			outer.appendChild(inner);

			pushAd(['ad-rectangle1']);
		} else {
			pushAd(slot);
		}
	}

	return {
		name: 'SevenOneMedia',
		fillInSlot: fillInSlot,
		canHandleSlot: canHandleSlot
	};
};
