// Requires JSON.stringify

(function ($) {
	
$.widget("strath.gradeeditor", {

	options: {
		delimiters: [13, 59], // what user can type to complete a recipient in char codes: [enter], [comma]
		outputDelimiters: [], // delimiter for recipients in original input field
		cssClass: 'widget-container', // CSS class to style the widget div and recipients, see stylesheet
		addPartPrompt: '', //'add recipients', // placeholder text
		ajaxScript: null,
		width: 500,
		debug: false,
		imageRoot: '' // must have trailing slash, e.g. http://localhost/moodle/theme/blocks/pix/
	},
	
	_create: function() {
		var self = this,
		el = self.element,
		opts = self.options;
		this.formulaParts = [];
		
		// dummy console to get around IE
		if(!window.console) { window.console = { log: function(e) {}}; };
		console.log("started up ");
		
		$(el).addClass('strath-gradeeditor');
		
		this.formulaInput = $("<input type='text'>")
			.width( 'auto' ).height( 'auto' )
			.attr( 'placeholder', opts.addPartPrompt)
			.keypress( function(e) {
				var $this = $(this), pressed = e.which;
								
				for ( i in opts.delimiters ) {
				
					if (pressed == opts.delimiters[i]) {
						self.add( $this.val() );
						e.preventDefault();
						return false;
					}
					
				}
			})
			.keydown( function(e) {
				self.keyDownValue = $(this).val(); // Fixes #965
			})
		// for some reason, in Safari, backspace is only recognized on keyup
			.keyup( function(e) {
				var $this = $(this),
				pressed = e.which;
				
				// if backspace is hit with no input, remove the last recipient
				if (pressed == 8) { // backspace
					if ( self.keyDownValue == '' ) {
						self.remove();
						return false;
					}
				return;
				}
			});
		
		this.formulaDiv = $('<div>')
			.addClass(opts.cssClass)
			.width(opts.width)
			.height('auto')
			.click(function(e) {
				if (e.target != this) {
					return true;
				}
				$(this).children('input').focus();
			}).
			append( this.formulaInput ).
			insertAfter( el.hide() )
		;
		
		if (opts.debug) {
			$('<div />')
				.insertAfter(this.formulaDiv)
				.append('<input type="button" value="Debug"/>')
				.on('click',function() {console.log(self.element.text()); })
			;
		}
		
		var initVal = $.trim(el.val() );
		if (initVal) {
			console.log('Have initial value');
		}
		$(document).on('click', function(e) {
			//self._hideSettings(e);
		});
	},
	
	inputField: function() {
		return this.formulaInput;
	},
	
	containerDiv: function() {
		return this.formulaDiv;
	},
	
	_setOption:function(key,value) {
		switch( key ) {
			case "clear":
				//handle removing 
				break;
		}
		$.Widget.prototype._setOption.apply( this, arguments );
		
		this._super( "_setOption", key, value );
	},
	
	destroy: function() {
		$.Widget.prototype.destroy.apply(this);
		this.formulaDiv.remove();
		this.element.show();
	},
	
	/* manipulation functions */
	add: function(text) {
		var self = this;
		text = text || self.formulaInput.val();
		if(text) {
			var fIndex= self.formulaParts.length;
			parts = text.match(/([0-9]*|[\+])/);
			console.log(parts);
			//text.split(/\s*[ +-\/*]\s*/);	//split by spaces and math operators!
			for(var i =0; i < parts.length; i++) {
				console.log(parts[i]);
				if (parts[i] == '') {
					continue;	//skip blank entries
				}
				var ind = fIndex+i;
				var newClass = $('<span>').class({editor:self, id:ind, text:parts[i]});
				self.formulaInput.before(newClass);
				self.formulaParts.push(newClass);
			}
			self.formulaInput.val('');
		}
		//push the updated formula to underlying field
	}
});

$.widget("strath.class", {
	initialised: false,
	
	options: {
		editor:null,
		id:null,
		text:null
	},
	
	_create: function() {
		var self = this;
		$(self.element).addClass('strath-class');
		var labelText = self.options.text ? self.options.text : '';
		self.element.append( $("<span>").addClass('label').text(labelText) );
	},
	_isEditable: function() {
		return true;
	},
});

} ( jQuery ) );