import '../css/owlthslider-admin.css';

jQuery(document).ready(($) => {

	function updateRowIndexes() {
		$('#os-slider-table tbody tr').each(function (index) {
			$(this).find('input, textarea').each(function () {
				// Update name attribute
				const name = $(this).attr('name');
				if (name) {
					const newName = name.replace(/\[\d+\]/, `[${index}]`);
					$(this).attr('name', newName);
				}

				// Handle ID attribute, only if it contains 'os_slider'
				const id = $(this).attr('id');
				if (id && id.includes('os_slider')) {
					const newId = id.replace(/(\d+)/, index);
					$(this).attr('id', newId);
				}

				// Handle class attribute, only if it contains 'os_slider'
				const classList = $(this).attr('class');
				if (classList) {
					const updatedClasses = classList.split(' ').map(cls => {
						if (cls.includes('os_slider')) {
							return cls.replace(/(\d+)/, index); // Replace any numeric part with the current index
						}
						return cls; // Return unchanged if 'os_slider' is not present
					}).join(' ');
					$(this).attr('class', updatedClasses);
				}
			});
		});
		autoSaveSlider();
	}

	/**
	 * Initializes TinyMCE editor for the caption textarea within a row.
	 * @param {string} editorId - The ID of the textarea to initialize.
	 */
	function initializeTinyMCE(editorId) {
		tinymce.init({
			selector: `#${editorId}`, // Ensure the selector uses the '#' prefix for ID
			menubar: false,
			branding: false,
			quicktags: true,
			mediaButtons: false,
			quickbars_selection_toolbar: 'bold italic | quicklink h2 h3',
			toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | removeformat | forecolor backcolor',

			setup: function (editor) {
				editor.on('change', function () {
					tinymce.triggerSave(); // Update textarea content on change
					autoSaveSlider();
				});
			}
		});
	}

	$('#add-slider-row').on('click', () => {
		// Get the row template and replace `index_count` with the new index
		let currentIndex = $('#os-slider-table tbody tr').length;
		// currentIndex++;
		let newRowHtml = $('#table-row-template').html();
		newRowHtml = newRowHtml.replace(/index_count/g, currentIndex);

		// Append the new row to the table
		$('#os-slider-table tbody').append(newRowHtml);

		// Initialize TinyMCE for the new row's caption textarea
		const newEditorId = `slides${currentIndex}caption`;
		initializeTinyMCE(newEditorId);

		// Update row indexes in case anything else needs updating
		updateRowIndexes();
	});

	// Initialize TinyMCE for existing editors on page load
	$('#os-slider-table tbody tr').each(function (index) {
		const editorId = `slides${index}caption`;
		initializeTinyMCE(editorId);
	});

	// Remove row functionality
	$('#os-slider-table').on('click', '.remove-row', function () {
		if (confirm('Are you sure you want to remove this row?')) {
			if ($('#os-slider-table tbody tr').length > 1) {
				$(this).closest('tr').remove();
				updateRowIndexes();
			}
		}
	});

	// Duplicate row functionality
	$('#os-slider-table').on('click', '.duplicate-row', function () {
		const row = $(this).closest('tr').clone();
		// row.find('input, textarea').val(''); // Clear values for inputs and textareas in the cloned row
		$(this).closest('tr').after(row);
		updateRowIndexes();

		// Initialize TinyMCE for the cloned row
		const newIndex = $('#os-slider-table tbody tr').length - 1;
		const newEditorId = `slides${newIndex}caption`;
		initializeTinyMCE(newEditorId);
	});

	// Handle image selection
	$('#os-slider-table').on('click', '.slider-select-image', function (e) {
		e.preventDefault();
		const button = $(this);
		var customUploader = wp.media({
			title: 'Slide Background Image',
			button: {
				text: 'Set as Slide Background',
			},
			multiple: false,
		}).on('select', () => {
			const attachment = customUploader.state().get('selection').first().toJSON();

			// Log the selected attachment to debug
			console.log('Selected Attachment:', attachment);

			// Find the hidden input for background image and set its value
			const imageInput = button.closest('td').find('input[name*="[background_image]"]');
			console.log(button.closest('td').find('input[name*="[background_image]"]'));
			// Set the value of the hidden input
			imageInput.val(attachment.url);

			// Log the new value to verify it's being set
			console.log('Hidden Input Value After Setting:', imageInput.val());

			// Trigger change event to ensure autosave logic catches it
			imageInput.trigger('change');

			// Add thumbnail preview for selected image
			button.before(`<div class="slider-image-thumbnail"><img src="${attachment.url}" alt="" /><button type="button" class="button slider-remove-image">&times;</button></div>`);

			button.closest('td').find('input[name*="[background_image]"]');
			// Hide the image selection button after selection
			button.hide();
		}).open();
	});


	// Remove image functionality
	$('#os-slider-table').on('click', '.slider-remove-image', function () {
		$(this).closest('td').find('input[name*="background_image"]').val('');
		$(this).closest('td').find('button.slider-select-image').show();
		$(this).closest('.slider-image-thumbnail').remove();
		autoSaveSlider();
	});

	// Make rows sortable
	$('#os-slider-table tbody').sortable({
		tolerance: 'pointer',
		revert: 'invalid',
		containment: 'parent',
		animation: 200,
		placeholder: "ui-state-highlight",
		update(event, ui) {
			updateRowIndexes();
		},
	});
	$( "#os-slider-table tbody" ).disableSelection();

	// Trigger save for TinyMCE editors before the form is submitted
	$('#post').on('submit', function () {
		tinymce.triggerSave(); // Ensure all TinyMCE editors are saved
	});

	// Debounce function to limit the rate of AJAX calls
	function debounce(func, wait, immediate) {
		let timeout;
		return function () {
			const context = this, args = arguments;
			const later = function () {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			const callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) func.apply(context, args);
		};
	};

	// Function to handle form changes and trigger AJAX auto-save
	const autoSaveSlider = debounce(function () {
		var formData = $('#post').serialize();

		$.ajax({
			url: os_slider_params.ajax_url,
			method: 'POST',
			data: {
				action: 'os_auto_save_sliders',
				os_slider_universal_nonce: os_slider_params.nonce,
				post_id: os_slider_params.post_id,
				slider_data: formData
			},
			success: function (response) {
				if (response.success) {
					console.log('Slider data saved successfully.');
					// Optionally, display a success message to the user
				} else {
					console.error('Error saving slider data:', response.data);
					// Optionally, display an error message to the user
				}
			},
			error: function (xhr, status, error) {
				console.error('AJAX error:', status, error);
				// Optionally, display an error message to the user
			}
		});
	}, 1000); // Adjust the debounce delay as needed

	$('#os_slider_details').on('change input', 'input, select, textarea', autoSaveSlider);

});


jQuery(document).ready(function ($) {
	$('#os_refresh_reviews').on('click', function (e) {
		e.preventDefault();
		var place_id = $('#os_slider_google_place_id').val();

		if (!place_id) {
			alert("Please enter a Google Place ID first.");
			return;
		}

		if (!confirm('Are you sure you want to refresh the reviews?', 'owlthslider')) {
			return;
		}

		// Show a loading indicator
		$('#os_reviews_table_container').html('<p>Refreshing reviews...</p>');

		// Make AJAX request to refresh reviews
		$.ajax({
			url: os_slider_params.ajax_url,
			method: 'POST',
			data: {
				action: 'os_refresh_reviews',
				os_slider_universal_nonce: os_slider_params.nonce,
				post_id: os_slider_params.post_id,
				google_place_id: place_id,
			},
			success: function (response) {
				if (response.success) {
					$('#os_reviews_table_container').html(response.data);
					alert('Reviews refreshed successfully.');
				} else {
					$('#os_reviews_table_container').html('');
					alert('Failed to refresh reviews: ' + response.data);
				}
			},
			error: function (xhr, status, error) {
				$('#os_reviews_table_container').html('');
				console.log(error);
				alert('AJAX error occurred while refreshing reviews.');
			}
		});
	});
});