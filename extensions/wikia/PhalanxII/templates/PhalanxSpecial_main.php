<div id="phalanx-mainframe">
	<div id="phalanx-nav-area">
<?= $app->renderView('PhalanxSpecial', 'tabs', array('currentTab' => 'main')); ?>
	</div>

	<div id="phalanx-content-area">
		<div id="phalanx-filter-area">
			<fieldset id="phalanx-input-filter">
				<legend><?php echo wfMsg( 'phalanx-legend-input' ) ?></legend>
				<form id="phalanx-block" method="post" action="<?= $action ?>">
					<!-- Filter -->
					<div id="phalanx-block-texts">
						<?php if (!empty($editMode)): ?>
						<input type="hidden" name="id" value="<?= $data['id'] ?>"/>
						<strong>ID:</strong> <?= $data['id'] ?>
						<?php endif; ?>
						<div id="singlemode">
							<label for="wpPhalanxFilter" class="left"><?php echo wfMsg( 'phalanx-label-filter' ) ?></label>
							<input type="text" id="wpPhalanxFilter" name="wpPhalanxFilter" class="blue" size="40" value="<?= $data['text'] ?>" />
							<input type="button" id="validate" value="<?php echo wfMsg( 'phalanx-validate-regexp' ) ?>" />
							<?php if (empty($editMode)): ?>
							<input type="button" id="enterbulk" value="<?= wfMsg('phalanx-bulkmode') ?>">
						</div>
						<div id="bulkmode" style="display: none">
							<label for="wpPhalanxFilterBulk" class="left">Bulk<br/><?php echo wfMsg( 'phalanx-label-filter' ) ?></label>
							<textarea type="text" id="wpPhalanxFilterBulk" name="wpPhalanxFilterBulk" class="blue" rows="10" cols="40" value="" ></textarea>
							<input type="button" id="entersingle" value="<?= wfMsg('phalanx-singlemode') ?>">
						<?php endif; ?>
						</div>
						<div>
							<span id="validateMessage"></span>
						</div>
						<div class="clearfix">
							<div class="left-spacer">&nbsp;</div>
							<?= Xml::check( 'wpPhalanxFormatRegex', !empty( $data['regex'] ), array( 'id' => 'wpPhalanxFormatRegex' ) ) ?>
							<label for="wpPhalanxFormatRegex"><?php echo wfMsg( 'phalanx-format-regex' ) ?></label>

							<?= Xml::check( 'wpPhalanxFormatCase', !empty( $data['case'] ), array( 'id' => 'wpPhalanxFormatCase' ) ) ?>
							<label for="wpPhalanxFormatCase"><?php echo wfMsg( 'phalanx-format-case' ) ?></label>

							<?= Xml::check( 'wpPhalanxFormatExact', !empty( $data['exact'] ), array( 'id' => 'wpPhalanxFormatExact' ) ) ?>
							<label for="wpPhalanxFormatExact"><?php echo wfMsg( 'phalanx-format-exact' ) ?></label>
						</div>
						<div class="clearfix">
							<label>
								<strong><?= wfMsg( 'phalanx-label-expiry' ) ?></strong>
								<?php if (!empty($editMode)): ?>
								<span class="expires"><?=
									($data['expire'] === null)
									?
									wfMsg('phalanx-expires-infinite')
									:
									wfMsg('phalanx-expires', $app->wg->Lang->timeanddate($data['expire']))
								?></span>
								<?php endif; ?>
								<select name="wpPhalanxExpire" id="wpPhalanxExpire" class="blue" >
									<? foreach ($expiries as $k => $v) { ?>
									<option value="<?=$k?>"><?=$v?></option>
									<? } ?>
								</select>
							</label>
						</div>
					</div>
					<!-- Type -->
					<div class="clearfix">
						<div class="left-spacer"><?php echo wfMsg( 'phalanx-label-type' ) ?></div>
						<div id="phalanx-block-types" class="phalanx-block-types">
							<div>
								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][1] ), array( 'id' => 'wpPhalanxTypeContent', 'value' => 1, 'title'=>1 ) ) ?>
								<label for="wpPhalanxTypeContent"><?php echo wfMsg( 'phalanx-type-content' ) ?></label>

								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][2] ), array( 'id' => 'wpPhalanxTypeSummary', 'value' => 2, 'title'=>2 ) ) ?>
								<label for="wpPhalanxTypeSummary"><?php echo wfMsg( 'phalanx-type-summary' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][4] ), array( 'id' => 'wpPhalanxTypeTitle', 'value' => 4, 'title'=>4 ) ) ?>
								<label for="wpPhalanxTypeTitle"><?php echo wfMsg( 'phalanx-type-title' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][8] ), array( 'id' => 'wpPhalanxTypeUser', 'value' => 8, 'title'=>8 ) ) ?>
								<label for="wpPhalanxTypeUser"><?php echo wfMsg( 'phalanx-type-user' ) ?></label>

								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][64] ), array( 'id' => 'wpPhalanxTypeCreation', 'value' => 64, 'title'=>64 ) ) ?>
								<label for="wpPhalanxTypeCreation"><?php echo wfMsg( 'phalanx-type-wiki-creation' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][16] ), array( 'id' => 'wpPhalanxTypeQuestion', 'value' => 16, 'title'=>16 ) ) ?>
								<label for="wpPhalanxTypeQuestion"><?php echo wfMsg( 'phalanx-type-answers-question-title' ) ?></label>

								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][32] ), array( 'id' => 'wpPhalanxTypeFilterWords', 'value' => 32, 'title'=>32 ) ) ?>
								<label for="wpPhalanxTypeFilterWords"><?php echo wfMsg( 'phalanx-type-answers-recent-questions' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxType[]', !empty( $data['type'][256] ), array( 'id' => 'wpPhalanxTypeEmail', 'value' => 256 ) ) ?>
								<label for="wpPhalanxTypeEmail"><?php echo wfMsg( 'phalanx-type-user-email' ) ?></label>
							</div>
						</div>
						<!--
						<div id="phalanx-help">
							<div id="phalanx-help-1" class="accent"><?php echo wfMsg( 'phalanx-help-type-content' ) ?></div>
							<div id="phalanx-help-2" class="accent"><?php echo wfMsg( 'phalanx-help-type-summary' ) ?></div>
							<div id="phalanx-help-4" class="accent"><?php echo wfMsg( 'phalanx-help-type-title' ) ?></div>
							<div id="phalanx-help-8" class="accent"><?php echo wfMsg( 'phalanx-help-type-user' ) ?></div>
							<div id="phalanx-help-64" class="accent"><?php echo wfMsg( 'phalanx-help-type-wiki-creation' )  ?></div>
							<div id="phalanx-help-16" class="accent"><?php echo wfMsg( 'phalanx-help-type-answers-question-title' ) ?></div>
							<div id="phalanx-help-32" class="accent"><?php echo wfMsg( 'phalanx-help-type-answers-recent-questions' ) ?></div>
							<div id="phalanx-help-256" class="accent"><?php echo wfMsg( 'phalanx-help-type-user-email' ) ?></div>
						</div>
						-->
					</div>
					<!-- Reason -->
					<div id="phalanx-block-optionals" class="clearfix">
						<div class="clearfix">
							<label for="wpPhalanxReason" class="left"><?php echo wfMsg( 'phalanx-label-reason' ) ?></label>
							<input type="text" id="wpPhalanxReason" name="wpPhalanxReason" class="blue" size="40" value="<?= $data['reason'] ?>" />
						</div>
						<div class="clearfix">
							<label for="wpPhalanxLanguages" class="left"><?php echo wfMsg( 'phalanx-label-lang' ) ?></label>
							<select name="wpPhalanxLanguages" id="wpPhalanxLanguages" class="blue" >
								<? foreach ($languages as $k => $v) { ?>
									<option <?=($k == $data['lang']) ? "selected" : ""?> value="<?=$k?>"><?=$v?></option>
								<? } ?>
							</select>
						</div>
						<div class="clearfix">
							<input type="submit" id="wpPhalanxSubmit" name="wpPhalanxSubmit" value="<?php echo wfMsg( 'phalanx-add-block' ) ?>" />
							<input type="reset" value="<?php echo wfMsg( 'phalanx-reset-form' ) ?>" />
						</div>
					</div>
				</form>
			</fieldset>

			<fieldset>
				<legend><?php echo wfMsg( 'phalanx-legend-listing' ) ?></legend>
				<form id="phalanx-filters" method="get" action="<?= $action ?>">
					<div id="phalanx-check-options">
						<label for="wpPhalanxCheckBlocker"><?php echo wfMsg( 'phalanx-view-blocker' ) ?></label>
						<input type="text" id="wpPhalanxCheckBlocker" name="wpPhalanxCheckBlocker" class="blue" size="30" value="<?= htmlspecialchars($data['checkBlocker']) ?>">
						<input type="submit" value="<?php echo wfMsg( 'phalanx-view-blocks' ) ?>"  />

						<label for="wpPhalanxCheckId"><?php echo wfMsg( 'phalanx-view-id' ) ?></label>
						<input type="text" id="wpPhalanxCheckId" name="id" class="blue" size="5" value="<?= $data['checkId'] ?>" />

						<input type="submit" value="<?php echo wfMsg( 'phalanx-view-id-submit' ) ?>"  />

						<div id="phalanx-block-types-filter" class="phalanx-block-types">
							<div>
								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][1] ), array( 'id' => 'wpPhalanxTypeContentFilter', 'value' => 1 ) ) ?>
								<label for="wpPhalanxTypeContentFilter"><?php echo wfMsg( 'phalanx-type-content' ) ?></label>

								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][2] ), array( 'id' => 'wpPhalanxTypeSummaryFilter', 'value' => 2 ) ) ?>
								<label for="wpPhalanxTypeSummaryFilter"><?php echo wfMsg( 'phalanx-type-summary' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][4] ), array( 'id' => 'wpPhalanxTypeTitleFilter', 'value' => 4 ) ) ?>
								<label for="wpPhalanxTypeTitleFilter"><?php echo wfMsg( 'phalanx-type-title' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][8] ), array( 'id' => 'wpPhalanxTypeUserFilter', 'value' => 8 ) ) ?>
								<label for="wpPhalanxTypeUserFilter"><?php echo wfMsg( 'phalanx-type-user' ) ?></label>

								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][64] ), array( 'id' => 'wpPhalanxTypeCreationFilter', 'value' => 64 ) ) ?>
								<label for="wpPhalanxTypeCreationFilter"><?php echo wfMsg( 'phalanx-type-wiki-creation' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][16] ), array( 'id' => 'wpPhalanxTypeQuestionFilter', 'value' => 16 ) ) ?>
								<label for="wpPhalanxTypeQuestionFilter"><?php echo wfMsg( 'phalanx-type-answers-question-title' ) ?></label>

								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][32] ), array( 'id' => 'wpPhalanxTypeFilterWordsFilter', 'value' => 32 ) ) ?>
								<label for="wpPhalanxTypeFilterWordsFilter"><?php echo wfMsg( 'phalanx-type-answers-recent-questions' ) ?></label>
							</div>
							<div>
								<?= Xml::check( 'wpPhalanxTypeFilter[]', !empty( $data['typeFilter'][256] ), array( 'id' => 'wpPhalanxTypeEmailFilter', 'value' => 256 ) ) ?>
								<label for="wpPhalanxTypeEmailFilter"><?php echo wfMsg( 'phalanx-type-user-email' ) ?></label>
							</div>
						</div>

					</div>
				</form>

				<div id="phalanx-check-results">
					<?= $listing ?>
				</div>
			</fieldset>
		</div>
	</div>
</div>