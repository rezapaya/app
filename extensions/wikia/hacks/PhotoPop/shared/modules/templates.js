var exports = exports || {};

define.call(exports, {

	mainPage: "<div id='wrapper'>\
			<div id='logoWrapper'>\
					<img id='logo_photopop' src='{{#image}}PHOTOPOP_LOGO{{/image}}'/><br/>\
					<img id='logo_wikia' src='{{#image}}POWERED_BY_LOGO{{/image}}'>\
			</div>\
		</div>",
	
	selectorScreen: "<div id='sliderWrapper'>\
			<div id='buttonWrapper'>\
				<div id='button_scores'>\
					<img src='{{#image}}buttonSrc_scores{{/image}}'/>\
				</div>\
				<div id='button_tutorial'>\
					<a href='{{#url}}tutorialButtonUrl{{/url}}'><img src='{{#image}}buttonSrc_tutorial{{/image}}'/></a>\
				</div>\
				<div id='button_volume'>\
					<img class='on' src='{{#image}}buttonSrc_volumeOn{{/image}}'/>\
					<img class='off' src='{{#image}}buttonSrc_volumeOff{{/image}}'/>\
				</div>\
			</div>\
			<div id='sliderContent' data-scroll='x'>\
				<ul>{{#games}}\
					<li class='gameIcon' data-gameurl='{{gameUrl}}'>\
						<img src ='{{#image}}gameicon_{{name}}{{/image}}'><br/>\
						<div class='gameName'>\
							{{gameName}}\
						</div>\
					</li>\
				{{/games}}</ul> \
			</div>\
		</div>",
	
	gameScreen: "<div id='scoreBarWrapper'>\
			<div id='scoreBar'></div>\
		</div>\
		<div id='bgWrapper'>\
			<div id='bgPic'><img src='{{path}}'></div>\
		</div>\
		<div id='gameBoard'>\
			<div id='endGameOuterWrapper'>\
				<div id='endGameInnerWrapper'>\
					<div id='highScore'>\
				HIGH SCORE SUMMARY\
					</div>\
					<div id='summaryWrapper'>\
						<div id='endGameSummary'>\
							<div class='headingText'>\
								FINISHED\
							</div>\
							<div class='summaryTextWrapper'>\
								<div class='summaryText_completion'>\
								</div>\
								<div class='summaryText_score'>\
								</div>\
							</div>\
						</div>\
						<a id='playAgain' href=''><img src='{{#image}}buttonSrc_play{{/image}}'/></a>\
						<a id='goHome' href=''><img src='{{#image}}buttonSrc_home{{/image}}'/></a>\
						<a id='goToHighScores' href=''><img src='{{#image}}buttonSrc_scores{{/image}}'/></a>\
					</div>\
				</div>\
			</div>\
			<div id='timeUpText'>\
				CONTINUE TIME UP\
			</div>\
			<div id='continue'>\
				<span id='continueText'>CONTINUE</span>\
				<img src='{{#image}}buttonSrc_contiunue{{/image}}'/>\
			</div>\
			<div id='answerDrawer'>\
				<img id='answerButton' class='closed' src='{{#image}}buttonSrc_answerOpen{{/image}}' />\
				<ul id='answerList'>\
					<li id='answer1'>1</li>\
					<li id='answer2'>2</li>\
					<li id='answer3'>3</li>\
					<li id='answer4'>4</li>\
				</ul>\
			</div>\
			<div id='hud'>\
				<div id='home'>\
					<img src='{{#image}}buttonSrc_home{{/image}}'/>\
				</div>\
				<div id='score'>\
					SCORE\
				</div>\
				<div id='progress'>\
					PROGRESS\
				</div>\
			</div><table id='tilesWrapper'></table>\
		</div>",
	
	tutorialOverlap: "<div id='instructionsWrapper' class='triangle-isosceles right'>\
				<div>\
					TUTORIAL\
				</div>\
				<div class='buttonBar'>\
					<a href=''><img src='{{#image}}buttonSrc_tutorial{{/image}}' /></a>\
				</div>\
			</div>"
});
