/*
 * jQuery simple-color plugin
 * @requires jQuery v1.4.2 or later
 *
 * See http://recursive-design.com/projects/jquery-simple-color/
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Version: @VERSION (@DATE)
 */
 (function($) {
/**
 * simpleColor() provides a mechanism for displaying simple color-pickers.
 *
 * If an options Object is provided, the following attributes are supported:
 *
 *  defaultColor:       Default (initially selected) color.
 *                      Default value: '#FFF'
 *
 *  border:             CSS border properties.
 *                      Default value: '1px solid #000'
 *
 *  cellWidth:          Width of each individual color cell.
 *                      Default value: 10
 *
 *  cellHeight:         Height of each individual color cell.
 *                      Default value: 10
 *
 *  cellMargin:         Margin of each individual color cell.
 *                      Default value: 1
 *
 *  boxWidth:           Width of the color display box.
 *                      Default value: 115px
 *
 *  boxHeight:          Height of the color display box.
 *                      Default value: 20px
 *
 *  columns:            Number of columns to display. Color order may look strange if this is altered.
 *                      Default value: 16
 *
 *  insert:             The position to insert the color picker. 'before' or 'after'.
 *                      Default value: 'after'
 *
 *  colors:             An array of colors to display, if you want to customize the default color set.
 *                      Default value: default color set - see 'default_colors' below.
 *
 *  displayColorCode:   Display the color code (eg #333333) as text inside the button. true or false.
 *                      Default value: false
 *
 *  colorCodeAlign:     Text alignment used to display the color code inside the button. Only used if 'displayColorCode' is true. 'left', 'center' or 'right'
 *                      Default value: 'center'
 *
 *  colorCodeColor:     Text color of the color code inside the button. Only used if 'displayColorCode' is true.
 *                      Default value: '#FFF'            
 */
  $.fn.simpleColor = function(options) {

    var default_colors = ["888888","8888AD","8888C1","8888D6","8888EA","8888FF","AD8888","AD88AD","AD88C1","AD88D6","AD88EA","AD88FF","C18888","C188AD","C188C1","C188D6","C188EA","C188FF","D68888","D688AD","D688C1","D688D6","D688EA","D688FF","EA8888","EA88AD","EA88C1","EA88D6","EA88EA","EA88FF","FF8888","FF88AD","FF88C1","FF88D6","FF88EA","FF88FF","88AD88","88ADAD","88ADC1","88ADD6","88ADEA","88ADFF","ADAD88","ADADAD","ADADC1","ADADD6","ADADEA","ADADFF","C1AD88","C1ADAD","C1ADC1","C1ADD6","C1ADEA","C1ADFF","D6AD88","D6ADAD","D6ADC1","D6ADD6","D6ADEA","D6ADFF","EAAD88","EAADAD","EAADC1","EAADD6","EAADEA","EAADFF","FFAD88","FFADAD","FFADC1","FFADD6","FFADEA","FFADFF","88C188","88C1AD","88C1C1","88C1D6","88C1EA","88C1FF","ADC188","ADC1AD","ADC1C1","ADC1D6","ADC1EA","ADC1FF","C1C188","C1C1AD","C1C1C1","C1C1D6","C1C1EA","C1C1FF","D6C188","D6C1AD","D6C1C1","D6C1D6","D6C1EA","D6C1FF","EAC188","EAC1AD","EAC1C1","EAC1D6","EAC1EA","EAC1FF","FFC188","FFC1AD","FFC1C1","FFC1D6","FFC1EA","FFC1FF","88D688","88D6AD","88D6C1","88D6D6","88D6EA","88D6FF","ADD688","ADD6AD","ADD6C1","ADD6D6","ADD6EA","ADD6FF","C1D688","C1D6AD","C1D6C1","C1D6D6","C1D6EA","C1D6FF","D6D688","D6D6AD","D6D6C1","D6D6D6","D6D6EA","D6D6FF","EAD688","EAD6AD","EAD6C1","EAD6D6","EAD6EA","EAD6FF","FFD688","FFD6AD","FFD6C1","FFD6D6","FFD6EA","FFD6FF","88EA88","88EAAD","88EAC1","88EAD6","88EAEA","88EAFF","ADEA88","ADEAAD","ADEAC1","ADEAD6","ADEAEA","ADEAFF","C1EA88","C1EAAD","C1EAC1","C1EAD6","C1EAEA","C1EAFF","D6EA88","D6EAAD","D6EAC1","D6EAD6","D6EAEA","D6EAFF","EAEA88","EAEAAD","EAEAC1","EAEAD6","EAEAEA","EAEAFF","FFEA88","FFEAAD","FFEAC1","FFEAD6","FFEAEA","FFEAFF","88FF88","88FFAD","88FFC1","88FFD6","88FFEA","88FFFF","ADFF88","ADFFAD","ADFFC1","ADFFD6","ADFFEA","ADFFFF","C1FF88","C1FFAD","C1FFC1","C1FFD6","C1FFEA","C1FFFF","D6FF88","D6FFAD","D6FFC1","D6FFD6","D6FFEA","D6FFFF","EAFF88","EAFFAD","EAFFC1","EAFFD6","EAFFEA","EAFFFF","FFFF88","FFFFAD","FFFFC1","FFFFD6","FFFFEA","FFFFFF"
    ];

    // Option defaults
    options = $.extend({
      defaultColor:   this.attr('defaultColor') || '#FFF',
      border:       this.attr('border') || '1px solid #000',
      cellWidth:    this.attr('cellWidth') || 10,
      cellHeight:     this.attr('cellHeight') || 10,
      cellMargin:     this.attr('cellMargin') || 1,
      boxWidth:     this.attr('boxWidth') || '115px',
      boxHeight:    this.attr('boxHeight') || '20px',
      columns:      this.attr('columns') || 16,
      insert:       this.attr('insert') || 'after',
      buttonClass:    this.attr('buttonClass') || '',
      colors:       this.attr('colors') || default_colors,
      displayColorCode: this.attr('displayColorCode') || false,
      colorCodeAlign:   this.attr('colorCodeAlign') || 'center',
      colorCodeColor:   this.attr('colorCodeColor') || '#FFF'
    }, options || {});

    // Hide the input
    this.hide();

    // Figure out the cell dimensions
    options.totalWidth = options.columns * (options.cellWidth + (2 * options.cellMargin));
    if ($.browser.msie) {
      options.totalWidth += 2;
    }

    options.totalHeight = Math.ceil(options.colors.length / options.columns) * (options.cellHeight + (2 * options.cellMargin));

    // Store these options so they'll be available to the other functions
    // TODO - must be a better way to do this, not sure what the 'official'
    // jQuery method is. Ideally i want to pass these as a parameter to the 
    // each() function but i'm not sure how
    $.simpleColorOptions = options;

    function buildSelector(index) {
      options = $.simpleColorOptions;

      // Create a container to hold everything
      var container = $("<div class='simpleColorContainer' />");
      
      // Absolutely positioned child elements now 'work'.
			container.css('position', 'relative');

      // Create the color display box
      var default_color = (this.value && this.value != '') ? this.value : options.defaultColor;

      var display_box = $("<div class='simpleColorDisplay' />");
      display_box.css({
        'backgroundColor': default_color,
      	'border':          options.border,
				'width':           options.boxWidth,
				'height':          options.boxHeight,
				// Make sure that the code is vertically centered.
				'line-height':     options.boxHeight,
				'cursor':          'pointer'
			});
      container.append(display_box);
      
      // If 'displayColorCode' is turned on, display the currently selected color code as text inside the button.
      if (options.displayColorCode) {
        display_box.text(this.value);
        display_box.css({
          'color':     options.colorCodeColor,
        	'textAlign': options.colorCodeAlign
        });
      }
      
      var select_callback = function (event) {

        // Use an existing chooser if there is one
        if (event.data.container.chooser) {
          event.data.container.chooser.toggle();
      
        // Build the chooser.
        } else {

          // Make a chooser div to hold the cells
          var chooser = $("<div class='simpleColorChooser'/>");
          chooser.css({
				'border':   options.border,
				'background-color':'#000',
			    'margin':   '0 0 0 5px',
			    'width':    options.totalWidth,
			    'height':   options.totalHeight,
				'top':      -10,
				'left':     options.boxWidth,
				'position': 'absolute'
			});
      
          event.data.container.chooser = chooser;
          event.data.container.append(chooser);
      
          // Create the cells
          for (var i=0; i<options.colors.length; i++) {
            var cell = $("<div class='simpleColorCell' id='" + options.colors[i] + "'/>");
            cell.css({
              'width':           options.cellWidth + 'px',
             	'height':          options.cellHeight + 'px',
			        'margin':          options.cellMargin + 'px',
			        'cursor':          'pointer',
			        'lineHeight':      options.cellHeight + 'px',
			        'fontSize':        '1px',
			        'float':           'left',
			        'backgroundColor': '#'+options.colors[i]
			      });
            chooser.append(cell);

            cell.bind('click', {
              input: event.data.input, 
              chooser: chooser, 
              display_box: display_box
            }, 
            function(event) {
              event.data.input.value = this.id;
              $(event.data.input).change();
              event.data.display_box.css('backgroundColor', '#' + this.id);
              event.data.chooser.hide();
              event.data.display_box.show();
     
              // If 'displayColorCode' is turned on, display the currently selected color code as text inside the button.
              if (options.displayColorCode) {
                event.data.display_box.text(this.id);
              }
            });
          }
        }
      };
      
      var callback_params = {
        container: container, 
        input: this, 
        display_box: display_box
      };

      // Also bind the display box button to display the chooser.
      display_box.bind('click', callback_params, select_callback);

      $(this).after(container);

    };

    this.each(buildSelector);

		$('html').click(function() {
			$('.simpleColorChooser').hide();
		});
		
		$('.simpleColorDisplay').each(function() {
			$(this).click(function(e){
				e.stopPropagation();
			});
		});

    return this;
  };

  /*
   * Close the given color selectors
   */
  $.fn.closeSelector = function() {
    this.each( function(index) {
      var container = $(this).parent().find('div.simpleColorContainer');
      container.find('.simpleColorChooser').hide();
      container.find('.simpleColorDisplay').show();
    });

    return this;
  };

})(jQuery);
