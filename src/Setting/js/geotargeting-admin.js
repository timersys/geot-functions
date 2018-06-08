(function( $ ) {
	'use strict';

	$('document').ready( function() {

		$(".geot-chosen-select").selectize({
		});
		$(".country_ajax").each(function(){
			var $select_city = $(this).next('.cities_container'),
				select_city  = $select_city[0].selectize;

			$(this).selectize({
                onChange: function(value) {
                    if (!value.length) return;
                    select_city.disable();
                    select_city.clearOptions();
                    select_city.load( function(callback) {
                        jQuery.ajax({
                            url: geot.ajax_url,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'geot_cities_by_country',
                                country: value
                            },
                            error: function () {
                                callback();
                            },
                            success: function (res) {
                                select_city.enable();
                                callback(res);
                            }
                        });
                    });
                }
            });
        });

		MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

		var observer = new MutationObserver(function(mutations) {
		    // fired when a mutation occurs

			for( var i = 0; i < mutations.length ; i++) {

				if( $(mutations[i].target).is(".geot-chosen-select") ) {

					var parent = $(mutations[i].target).parent('.geot-select2');
					parent.find('.chosen-container').remove()
					//$(mutations[i].target).chosen('destroy');
					$(mutations[i].target).chosen({width:"90%",no_results_text: "Oops, nothing found!"});
				}
			}
		});
		// define what element should be observed by the observer
		// and what types of mutations trigger the callback
		$('.acf-table').each(function(){

			observer.observe($(this)[0], {
				subtree: true,
				attributes: true
				//...
			});
		});

		$(".add-region").click( function(e){
			e.preventDefault();
			var region 		= $(this).prev('.region-group');
			var new_region 	= region.clone();
			var new_id		= parseInt( region.data('id') ) + 1;

			new_region.find('input[type="text"]').attr('name', 'geot_settings[region]['+new_id+'][name]').val('');
			var $old_select = region.find('select');
            new_region.find('select').attr('name', 'geot_settings[region]['+new_id+'][countries][]').find("option:selected").removeAttr("selected");
            var $selectize = $old_select[0].selectize;
			new_region.find('.selectize-control').remove();
			new_region.insertAfter(region);
			console.log($selectize);
            new_region.find(".geot-chosen-select").selectize({
				options : geot_countries
            });
		});

		$(".geot-settings").on('click','.remove-region', function(e){
			e.preventDefault();
			var region 		= $(this).parent('.region-group');
			region.remove();
		});

		$(".add-city-region").click( function(e){
			e.preventDefault();
			var region 		= $(this).prev('.city-region-group');
			var new_region 	= region.clone();
			var cities = new_region.find(".cities_container");
			var chosen = new_region.find(".country_ajax");

			var new_id		= parseInt( region.data('id') ) + 1;
			new_region.find('input[type="text"]').attr('name', 'geot_settings[city_region]['+new_id+'][name]').val('');
			chosen.attr('name', 'geot_settings[city_region]['+new_id+'][countries][]').find("option:selected").removeAttr("selected");
			cities.attr('name', 'geot_settings[city_region]['+new_id+'][cities][]').find("option:selected").removeAttr("selected");
			new_region.find('.selectize-control').remove();
			new_region.insertAfter(region);
			chosen.attr('data-counter', new_id);
			cities.attr('id', 'cities'+new_id);
			var $select_cities = cities.selectize({
                plugins: ['remove_button'],
                valueField: 'name',
                labelField: 'name',
                searchField: 'name',
				render: function (item,escape) {
					return '<div>' + escape(item.name) + '</div>';
				},
            });
			var select_city = $select_cities[0].selectize;
			chosen.selectize({
                options : geot_countries,
                onChange: function(value) {
                    if (!value.length) return;
                    select_city.disable();
                    select_city.clearOptions();
                    select_city.load( function(callback) {
                        jQuery.ajax({
                            url: geot.ajax_url,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'geot_cities_by_country',
                                country: value
                            },
                            error: function () {
                                callback();
                            },
                            success: function (res) {
                                select_city.enable();
                                callback(res);
                            }
                        });
                    });
                }
            });
		});

		$(".geot-settings").on('click','.remove-city-region', function(e){
			e.preventDefault();
			var region 		= $(this).parent('.city-region-group');
			region.remove();
		});

		$(document).on('change','.region-name', function(){

            $(this).val(slugify($(this).val()));
        });
		function slugify(str) {
            str = str.replace(/^\s+|\s+$/g, ''); // trim
            str = str.toLowerCase();

            // remove accents, swap ñ for n, etc
            var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to   = "aaaaeeeeiiiioooouuuunc------";
            for (var i=0, l=from.length ; i<l ; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                .replace(/\s+/g, '-') // collapse whitespace and replace by -
                .replace(/-+/g, '-'); // collapse dashes

            return str;
        }

		function load_cities( o ) {
			var counter 		= o.data('counter');
			var cities_select 	= $("#cities"+counter);
			var cities_choosen  = cities_select.next('.chosen-container');
			cities_choosen.find('.default').val('loading....');
			$.post(
				geot.ajax_url,
				{ action: 'geot_cities_by_country', country : o.val() },
				function(response) {
					//cities_choosen.remove();
					cities_select.html(response);
					cities_select.trigger("chosen:updated");
                    cities_choosen.find('.default').val('Choose one');
				}
			);
		}

		$(document).on('widget-updated', function(){

			$(".geot-chosen-select").chosen({width:"90%",no_results_text: "Oops, nothing found!"});

		});

		$(document).on('widget-added', function(ev, target){

			$(target).find('.chosen-container').remove();
			$(target).find(".geot-chosen-select").show().chosen({width:"90%",no_results_text: "Oops, nothing found!"});

		});

        $('.check-license').on('click', function (e) {
        	e.preventDefault();
        	var button = $(this),
				license = $('#license').val();
        		button.prop('disabled',true).addClass('btn-spinner');
			$.ajax({
				'url' : ajaxurl,
				'method' : 'POST',
				'dataType': 'json',
				'data'	: { action: 'geot_check_license',license : license},
				'success': function (response) {
					if( response.error ){
                        $('<p style="color:red">'+response.error+'</p>').insertAfter(button).hide().fadeIn();
                        $('#license').removeClass('geot_license_valid')
                    }
					if( response.success ){
                        $('<p style="color:green">'+response.success+'</p>').insertAfter(button).hide().fadeIn();
						$('#license').addClass('geot_license_valid');
                    }
                    button.prop('disabled',false).removeClass('btn-spinner');
                },
                'error': function (response) {
                    $('<p style="color:red">'+response.error+'</p>').insertAfter(button).hide().fadeIn();
                    $('#license').removeClass('geot_license_valid')
                    button.prop('disabled',false).removeClass('btn-spinner');
                }
			});
        });
	});

})( jQuery );
