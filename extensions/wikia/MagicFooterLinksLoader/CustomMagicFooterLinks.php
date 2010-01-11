<?php

$wgHooks["getMagicFooterLinks"][] = "wfSolarMagicFooterLinks";
$wgHooks["getMagicFooterLinks"][] = "wfAnswersMagicFooterLinks";

function wfSolarMagicFooterLinks($results) {
	if (!empty($results)) return true;

	global $wgDBname;
	if ("solarcooking" != $wgDBname) return true;

	$results = array("*" => "[[w:c:recipes|Recipes]] | [[w:c:answers:Category:Recipes_Wiki|Recipe Answers]]");

	return true;
}

function wfAnswersMagicFooterLinks($results) {
	if (!empty($results)) return true;

	global $wgDBname;
	if ("wswiki" == $wgDBname) return true; // requested in Websitewiki skype chan.

	$links = array(
		"de" => "Besuche das [[w:c:websitewiki|WebsiteWiki]] und [[w:c:frag|Wikianswers: Frag Wikia!]]",
		"es" => "[[w:c:respuestas|WikiRespuestas]]",
	);

	global $wgContLang;
	if (empty($links[$wgContLang->getCode()])) return true;

	$results = array("*" => $links[$wgContLang->getCode()]);

	return true;
}
