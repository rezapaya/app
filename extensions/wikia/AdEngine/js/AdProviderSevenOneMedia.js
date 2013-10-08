var AdProviderSevenOneMedia = function(adLogicPageLevelParamsLegacy, scriptWriter, tracker, log, window, slotTweaker) {
	'use strict';

	var ord = Math.round(Math.random() * 23456787654),
		slotMap = {
			'HOME_TOP_LEADERBOARD': {slot: 'fullbanner2'},
			'HOME_TOP_RIGHT_BOXAD': {slot: 'rectangle1'},
			'HUB_TOP_LEADERBOARD': {slot: 'fullbanner2'},
			'TOP_INVISIBLE': {slot: 'popup1'},
			'TOP_LEADERBOARD': {slot: 'fullbanner2'},
			'TOP_RIGHT_BOXAD': {slot: 'rectangle1'},
			'TOP_SKYSCRAPER': {slot: 'skyscraper1'},
			'SEVENONEMEDIA_FLUSH': 'flushonly'
		},
		slotsToRender = [];

	function canHandleSlot(slot) {
		var slotname = slot[0];

		log('canHandleSlot', 5, 'AdProviderSevenOneMedia');
		log([slotname], 5, 'AdProviderSevenOneMedia');

		if (slotMap[slotname]) {
			return true;
		}

		return false;
	}

	function pushAd(slot) {
		var slotname = slot[0],
			slotnameDe = slotMap[slotname].slot,
			ourSlot = document.getElementById(slotname),
			outerSlot = document.createElement('div'),
			innerSlot = document.createElement('div');

		outerSlot.id = 'ad-' + slotnameDe + '-outer';
		innerSlot.id = 'ad-' + slotnameDe;
		innerSlot.className = 'ad-wrapper';

		ourSlot.appendChild(outerSlot);
		outerSlot.appendChild(innerSlot);

		slotsToRender.push(slotMap[slot[0]].slot);
	}

	function insertAd(slotname) {
		var elPostponed = document.getElementById('ads-postponed'),
			elTable = document.createElement('table'),
			elTr = document.createElement('tr'),
			elTd = document.createElement('td'),
			elSlot = document.createElement('div'),
			slotId = 'ad-' + slotname + '-postponed';

		elSlot.id = slotId;
		elSlot.className = 'ad-wrapper';

		elPostponed.appendChild(elTable);
		elTable.appendChild(elTr);
		elTr.appendChild(elTd);
		elTd.appendChild(elSlot);

		scriptWriter.injectScriptByText(slotId, "myAd.insertAd('" + slotname + "');", function () {
			setTimeout(function () {
				console.log('move ' + slotname);
				myAd.finishAd(slotname, 'move');
			}, 100);
		});
	}

	function flushAds(slot) {
		var head = document.getElementsByTagName('head')[0],
			link = document.createElement('link');

		link.rel = 'stylesheet';
		link.href = '/__am/90987245/one/-/extensions/wikia/AdEngine/SevenOneMedia/wikia-my_ad_integration.css';
		head.appendChild(link);

		scriptWriter.injectScriptByUrl(slot[0], "/__am/90987245/one/-/extensions/wikia/AdEngine/SevenOneMedia/my_ad_integration.js",
			function () {
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
				scriptWriter.injectScriptByText(slot[0], "myAd.loadScript('site');",
					function () {
						scriptWriter.injectScriptByText(slot[0], "myAd.loadScript('global');",
							function () {
								var c = document.createElement('div');
								c.id = 'ads-postponed';
								document.getElementById(slot[0]).appendChild(c);
								var i, len, postponed;
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

		var slotname = slot[0],
			slotInfo = slotMap[slotname];

		if (slotInfo === 'flushonly') {
			flushAds(slot);
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
