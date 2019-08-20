define(['Ajax', 'Language', 'Ui/Dialog'], function (Ajax, Language, UiDialog) {
	"use strict";
	
	function IPAddressHandler() {
		this.init();
	}
	
	IPAddressHandler.prototype = {
		_cache: {},
		
		init: function () {
			this._cache = {};
			
			this._initButtons();
			
			WCF.DOMNodeInsertedHandler.addCallback('Fireball.News.IPAddressHandler', $.proxy(this._initButtons, this));
		},
		
		_initButtons: function () {
			var self = this;
			
			elBySelAll('.jsIpAddress', elBySel('.buttonList'), (function(button) {
				let newsID = elData(button, 'object-id');
				if (self._cache[newsID] === undefined) {
					self._cache[newsID] = '';
					button.addEventListener(WCF_CLICK_EVENT, self._click.bind(self));
				}
			}));
		},
		
		_click: function (event) {
			let newsID = elData(event.currentTarget, 'object-id');
			
			if (this._cache[newsID]) {
				this._showDialog(newsID);
			}
			else {
				Ajax.api(this, {
					parameters: {
						position: newsID
					}
				});
			}
		},
		
		_ajaxSuccess: function (data) {
			this._cache[data.returnValues.newsID] = data.returnValues.template;
			this._showDialog(data.returnValues.newsID);
		},
		
		_showDialog: function (newsID) {
			UiDialog.open(this, this._cache[newsID]);
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'getIpLog',
					className: 'cms\\data\\news\\NewsAction'
				}
			};
		},
		
		_dialogSetup: function() {
			return {
				id: 'newsIpAddressLog',
				options: {
					title: Language.get('cms.news.ipAddress.title')
				}
			};
		}
	};
	return IPAddressHandler;
});
