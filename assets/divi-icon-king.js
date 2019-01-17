(function($) {

	const targetClasses = [
		".et_overlay",
		".et_pb_button",
		".et_pb_custom_button_icon",
		".et_pb_more_button",
		".et_pb_extra_overlay",
		".et-pb-icon",
		".et_pb_shop",
		".et_pb_dmb_breadcrumbs li[data-icon]",
		".et_pb_dmb_breadcrumbs a[data-icon]",
		".dwd-slider-fullscreen button.slick-arrow",
		".single_add_to_cart_button"
	].join(",");

	const icon_list_toggles = [
		".et-core-control-toggle",
		".et-fb-form__toggle[data-name=button]",
		".et-fb-form__toggle[data-name=button_one]",
		".et-fb-form__toggle[data-name=button_two]",
		".et-fb-form__toggle[data-name=image]",
		".et-fb-form__toggle[data-name=overlay]",
	].join(",");

	const iconFontListSelector = ".et-fb-font-icon-list";
	const builderFrameSelector = "iframe#et-fb-app-frame";

	// (function(){

		hide_icons_dikg();

		// setTimeout(function(){
			process_icons_dikg();
			show_icons_dikg();
		// }, 100);

		if (et_fb_check()) {

			// const targetNode = document.getElementById("et-fb-app");
			// const config = {
			// 	childList: true,
			// 	attributes: true,
			// 	subtree: true
			// };

			const callback = function( mutationsList ) {

				mutationsList.forEach(function(thisMutation) {

					if (thisMutation.type === "childList") {

						const target = thisMutation.target;

						if (
							target.id === "et-fb-app" ||
							target.id === "et_fb_root" ||
							target.classList.contains("et_pb_section") ||
							target.classList.contains("et_pb_row") ||
							target.classList.contains("et_pb_column")
						) {
							process_icons_dikg();
							show_icons_dikg();
						}

						if (thisMutation.addedNodes.length > 0) {
							if (
								$(target).attr("data-name") === "button"		||
								$(target).attr("data-name") === "button_one"	||
								$(target).attr("data-name") === "button_two"	||
								$(target).attr("data-name") === "image" 		||
								$(target).attr("data-name") === "overlay"		||
								// Older versions of Divi.
								target.classList.contains("et-fb-form__toggle")
							) {
								processIconListPicker();
							}
						}
					}
				});
			};

			// const observer = new MutationObserver(callback);
			// observer.observe(targetNode, config);
		}
	// })();

	$(document).on("click", iconFontListSelector + " > li", function(evt) {
		// debugger;
		hide_icons_dikg();
		process_icons_dikg();
		processIconListPicker(evt.target, true);
		show_icons_dikg();
	});

	$(document).on("click", icon_list_toggles, //function(e) {
		// processIconListPicker
		function() {
			setTimeout(showIconListInPicker, 180);
		}
	);

	// $(document).ajaxComplete(function() {
	// 	hide_icons_dikg();
	// 	setTimeout(function() {
	// 		processIconListPicker();
	// 		process_icons_dikg();
			// show_icons_dikg();
	// 	}, 100);
	// });

	// Refresh icons on Woocommerce cart update.
	$(document.body).on("updated_cart_totals", function() {
		hide_icons_dikg();
		setTimeout(function() {
			// processIconListPicker();
			process_icons_dikg();
			show_icons_dikg();
		}, 100);
	});

	/**
	 * Detect if the front end builder is active.
	 */
	function et_fb_check() {
    	return $("#et-fb-app").length > 0;
	}

	function hide_icons_dikg() {
		if (et_fb_check() && $(builderFrameSelector).length) {
			const builder_frame = $(builderFrameSelector);
			$(targetClasses, builder_frame.contents()).addClass("hide_icon");
		} else {
			$(targetClasses).addClass("hide_icon");
		}
	}

	function show_icons_dikg() {
		if (et_fb_check() && $(builderFrameSelector).length) {
			const builder_frame = $(builderFrameSelector);
			$(targetClasses, builder_frame.contents()).removeClass("hide_icon");
		} else {
			$(targetClasses).removeClass("hide_icon");
		}
	}

	/**
	 * Parse icon data and display icons on the front end.
	 */
	function process_icons_dikg() {

		let builder_frame;
		let icon_modules;
		let is_et_fb = false;

		// Check if the iframe exists (Divi 3.18.x)
		if (et_fb_check() && $(builderFrameSelector).length) {
			is_et_fb = true;
			builder_frame = $(builderFrameSelector);
			icon_modules = $(targetClasses, builder_frame.contents());
		} else {
			icon_modules = $(targetClasses);
		}

		// Loop through modules and work with icon data.
		for (let i = 0; i < icon_modules.length; i++ ) {

			const module = icon_modules[i];
			let target_element;

			if (is_et_fb) {
				target_element = $(module, builder_frame.contents());
			} else {
				target_element = $(module);
			}

			// If the module isn't found, skip this iteration.
			if (target_element.length ) {

				let icon_data;

				if (target_element.data("icon")) {
					// The module has a "data-icon" attribute set, so use it.
					icon_data = target_element.attr("data-icon").split("~|");

					if (icon_data.length >= 2) {
						target_element.attr("data-icon", icon_data[0]);
					}
				} else {
					// The icon information is in the HTML.
					icon_data = target_element.html().split("~|");

					if (icon_data.length >= 2) {
						target_element.html(icon_data[0]);
					}
				}

				target_element.attr("data-family", icon_data[2]);
				target_element.removeClass("divi_et_icon_gtm divi_elegant-themes_icon_gtm divi_font-awesome_icon_gtm divi_material_icon_gtm divi_undefined_icon_gtm");
				target_element.addClass( "divi_" + icon_data[2] + "_icon_gtm" );

				if (target_element.is("[data-family]")) {
					const this_fam = target_element.attr("data-family");
					if (!target_element.hasClass("divi_" + this_fam + "_icon_gtm" ) ) {
						target_element.addClass("divi_" + this_fam + "_icon_gtm" );
					}
				}

			}

		}
	}

	function processIconListPicker(chosenIcon, iconClick) {
		if (iconClick) {
			// An icon in the picker was clicked.
			const icon_data = chosenIcon.dataset.icon.split("~|");
			if (icon_data.length > 1) {
				chosenIcon = $(chosenIcon);
				chosenIcon.data({
					"icon": icon_data[0],
					"family": icon_data[2],
					"name": icon_data[1]
				});
				chosenIcon.attr("title", icon_data[1]);
				// chosenIcon.addClass("divi_icon_king_gtm divi_" + icon_data[2] + "_icon_gtm");
			} else {
				chosenIcon.data("family", "elegant-themes");
			}
		}
	}

	function showIconListInPicker() {
		const icons = $(iconFontListSelector).children();
		for (let i = 0; i < icons.length; i++) {
			const icon_item = $(icons[i]);
			if (icon_item.not(".divi_icon_king_gtm") || icon_item.hasClass("active")) {
				const icon_data = icon_item.data("icon").split("~|");
				if (icon_data.length > 1) {
					console.log("SETTING DATA:", icon_item);
					// console.log(icon_data);
					icon_item.data({
						"icon": icon_data[0],
						"family": icon_data[2],
						"name": icon_data[1]
					});
					console.log(icon_item.data());
					icon_item.attr("title", icon_data[1]);
					icon_item.addClass("divi_icon_king_gtm divi_" + icon_data[2] + "_icon_gtm");
				} else {
					icon_item.attr("data-family", "elegant-themes");
				}
			}
		}
	}

})(jQuery);
