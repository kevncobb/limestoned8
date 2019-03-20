(function ($, Drupal, drupalSettings) {
    "use strict";
    /**
     * Attaches the JS test behavior to weight div.
     */
    Drupal.behaviors.iniMDSlider = {
        attach: function(context, settings) {
            var effectsIn = drupalSettings.inEffects,
                effectsOut = drupalSettings.outEffects;
            $(document).ready(function(){
                window.listMegaSlide = [];
                var i = 0,
                    cssInline = '';
                $('.md-slide-items').each(function(){
                    var slid = $(this).attr('id').split('-')[2],
                        slider = drupalSettings.md_slider[slid];
                    listMegaSlide[i] = $(this).mdSlider(slider);
                    if(slider.device_enable){
                        cssInline += '@media (max-width: ' + slider.device_width + 'px) {\
                            #md-slider-' + slid + '-block .md-objects {\
                              display: none;\
                            }\
                          } ';
                    }
                    
                    if(slider.device_width){
                        cssInline += '@media (max-width: ' + slider.device_width + 'px) {\
                                .hideonmobile {\
                                  display: none !important;\
                                }\
                              }';
                    }
                    
                    i++;
                });
                /*
                $.each(drupalSettings.md_slider, function(slid, slider) {
                    listMegaSlide[i] = $('#md-slider-' + slid  + '-block').mdSlider(slider);
                    if(slider.device_enable){
                        cssInline += '@media (max-width: ' + slider.device_width + 'px) {\
                            #md-slider-' + slid + '-block .md-objects {\
                              display: none;\
                            }\
                          } ';
                    }
                    
                    if(slider.device_width){
                        cssInline += '@media (max-width: ' + slider.device_width + 'px) {\
                                .hideonmobile {\
                                  display: none !important;\
                                }\
                              }';
                    }
                    
                    i++;
                });
                */
                //$('head').append(`<style>${cssInline}</style>`);
                $('head').append('<style>' +cssInline + '</style>');
            });
        }
    };
})(jQuery, Drupal, drupalSettings);
