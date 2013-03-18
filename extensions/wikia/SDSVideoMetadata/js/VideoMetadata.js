var VideoMetadata = {
	cachedSelectors: {},
	videoPlayerPosition: null,
	init: function() {
		var that = this;
		this.cachedSelectors.form = $('#VMDForm');
		this.cachedSelectors.typeSelect = $('#vcType');
		this.cachedSelectors.typeMDProperties = $('#VMDSpecificMD');
		this.cachedSelectors.saveButton = $('#VMDFormSave');
		this.cachedSelectors.videoPlayer = $('#VMD-player-wrapper > div');

		// attach handlers
		this.cachedSelectors.form.on('click', 'button.add', function(event) {
			event.preventDefault();
			that.addListItem(event);
		});
		this.cachedSelectors.form.on('click', 'button.remove', function(event) {
			event.preventDefault();
			that.removeListItem(event);
		});

		// TODO: this if prevent some strange behavior when pressing enter on different input filed (triggers other buttons in form). Find the root of this problem, solve and remove this handlers!!!
		this.cachedSelectors.form.on('keydown', 'input[type="text"]', function(event) {
			if (event.which == 13) {
				event.preventDefault();
			}
		});
		this.cachedSelectors.form.on('keydown', ' li input[type="text"]', function(event) {
			that.listEnterKeyHelper(event);
		});

		this.cachedSelectors.typeSelect.on('change', function(event) {
			that.chooseClipType(event);
			that.simpleValidation();
		});

		this.videoPlayerPosition = this.cachedSelectors.videoPlayer.offset().top;
		var throttled = $.throttle( 100, $.proxy(this.setVideoPlayerPosition, this));
		$(window).on('scroll', throttled);

		this.setObjTypeForEdit();
	},

	// add new blank input field for reference list type properties
	addListItem: function(event) {
		var lastListElement = $(event.target).prev().children().last();

		lastListElement.clone().insertBefore(lastListElement).find('.remove').removeClass('hidden');
		lastListElement.find('input').val('').focus().next().addClass('hidden');
	},
	// remove selected reference in the list
	removeListItem: function(event) {
		var selectedRefObj = $(event.target).parent(),
			focusPoint = selectedRefObj.siblings().last().find('input');

		selectedRefObj.remove();
		focusPoint.focus();
	},
	// use 'enter' key to quickly move through lists or add new list items
	listEnterKeyHelper: function(event) {
		if (event.which == 13) {
			var $target = $(event.target),
				$nextField = $target.parent().next().find('input');

			if ($nextField.length > 0) {
				$nextField.focus();
			} else {
				$target.parents('ul').siblings('button.add').click();
			}
		}
	},
	// show form part for type specific properties
	chooseClipType: function(event) {
		var $target = $(event.target),
			targetValue = $target.val(),
			targetClass = '.' + targetValue,

			// cache selectors
			propertiesWrapper = this.cachedSelectors.typeMDProperties,
			propertiesFormFields = propertiesWrapper.find('input, select, textarea');

		if(targetValue !== '') {
			propertiesFormFields.attr('disabled', 'disabled');
			propertiesWrapper.find(targetClass).find('input, select, textarea').removeAttr('disabled');
			propertiesWrapper.children(':not(legend)').addClass('hidden').filter(targetClass).removeClass('hidden');
			propertiesWrapper.removeClass('hidden');
		} else {
			propertiesFormFields.attr('disabled', 'disabled');
			propertiesWrapper.addClass('hidden');
		}
	},
	// Temporary method to prevent errors on PHP side when sending empty form
	simpleValidation: function() {
		if (this.cachedSelectors.typeSelect.val() !== '') {
			this.cachedSelectors.saveButton.removeAttr('disabled');
		} else {
			this.cachedSelectors.saveButton.attr('disabled', 'disabled');
		}
	},
	// Temporary method for setting video object type in edit mode
	setObjTypeForEdit: function() {
		var type = this.cachedSelectors.typeSelect.data('type');
		if (type === '') {
			return false;
		}
		var $type = 'option[value="' + type + '"]';
		this.cachedSelectors.typeSelect.children($type).attr('selected', 'selected');
		this.cachedSelectors.typeSelect.trigger('change');
	},
	// Method controlling video player position
	setVideoPlayerPosition: function() {
		if ($(window).scrollTop() >= this.videoPlayerPosition) {
			this.cachedSelectors.videoPlayer.addClass('fixed');
		} else {
			this.cachedSelectors.videoPlayer.removeClass('fixed');
		}
	}
};

$(function() {
	VideoMetadata.init();
});