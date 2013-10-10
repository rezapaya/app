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

	function insertAd(slotname) {
		log(['insertAd', slotname], 5, 'AdProviderSevenOneMedia');

		/*var elPostponed = document.getElementById('ads-postponed'),
			elTable = document.createElement('table'),
			elTr = document.createElement('tr'),
			elTd = document.createElement('td'),
			elSlot = document.createElement('div'),
			slotId = slotname + '-postponed';

		elSlot.id = slotId;
		elSlot.className = 'ad-wrapper';

		elPostponed.appendChild(elTable);
		elTable.appendChild(elTr);
		elTr.appendChild(elTd);
		elTd.appendChild(elSlot);
*/
		scriptWriter.injectScriptByText(slotname, "myAd.insertAd('" + slotname.replace('ad-', '') + "');", function () {
			console.log('done ' + slotname.replace('ad-', ''));
			myAd.finishAd(slotname.replace('ad-', ''));
			//scriptWriter.injectScriptByText(slotId, "myAd.finishAd('" + slotname.replace('ad-', '') + "', 'move');");
		});
	}

	function flushAds(slot) {
		log(['flushAds', slot], 5, 'AdProviderSevenOneMedia');

		var head = document.getElementsByTagName('head')[0],
			link = document.createElement('link');

		link.rel = 'stylesheet';
		link.href = '/__am/90987245/one/-/extensions/wikia/AdEngine/SevenOneMedia/my_ad_integration.css';
		head.appendChild(link);

		scriptWriter.injectScriptByText(slot[0],
			"var SOI_SITE     = 'wikia';" +
			"var SOI_SUBSITE  = 'videospiele';" + // first level (home for home)" +
			"var SOI_SUB2SITE = 'gta';" + // second level" +
			"var SOI_SUB3SITE = '';" + // third level" +
			"var SOI_CONTENT  = 'content';" + // content|video|gallery|game" +
			"var SOI_WERBUNG  = true;" +
			// "	// Available tags" +
			"var SOI_PU1 = true;" + // popup1" +
			"var SOI_FB2 = true;" + // fullbanner2" +
			"var SOI_SC1 = true;" + // skyscraper1" +
			// "// Suitability for special ads" +
			// "// - from popup1" +
//	var SOI_PU = false; // popup/popunder
			"var SOI_PL = true;" + // powerlayer" +
//	var SOI_FA = false; // baseboard (mnemonic: FooterAd, FloorAd)

			// - from fullbanner2
			"var SOI_PB = true;" + // powerbanner (728x180)
			"var SOI_PD = true;" + // pushdown
			"var SOI_BB = true;" + // billboard
			"var SOI_WP = true;" + // wallpaper
			"var SOI_FP = true;" + // fireplace

			// - from skyscraper1
			"var SOI_SB = true;",
			function () {
				scriptWriter.injectScriptByUrl(slot[0], "/__am/90987245/one/-/extensions/wikia/AdEngine/SevenOneMedia/my_ad_integration.js", function () {
					scriptWriter.injectScriptByText(slot[0], "myAd.loadScript('site');",
						function () {
							scriptWriter.injectScriptByText(slot[0], "myAd.loadScript('global');",
								function () {
									/*
									var c = document.createElement('div');
									c.id = 'ads-postponed';
									document.getElementById(slot[0]).appendChild(c);*/
									var i, len;
									for (i = 0, len = slotsToRender.length; i < len ; i += 1) {
										insertAd(slotsToRender[i]);
									}
								});
						});
					});
				});
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
