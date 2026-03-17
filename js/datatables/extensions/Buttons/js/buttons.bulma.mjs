/*! Bulma integration for DataTables' Buttons
 * © SpryMedia Ltd - datatables.net/license
 */

import jQuery from 'jquery';
import DataTable from 'datatables.net-bm';
import Buttons from 'datatables.net-buttons';

// Allow reassignment of the $ variable
let $ = jQuery;


$.extend(true, DataTable.Buttons.defaults, {
	dom: {
		container: {
			className: 'dt-buttons field is-grouped'
		},
		button: {
			className: 'button is-light',
			active: 'is-active',
			disabled: 'is-disabled'
		},
		collection: {
			action: {
				tag: 'div',
				className: 'dropdown-content',
				dropHtml: ''
			},
			button: {
				tag: 'a',
				className: 'dt-button dropdown-item',
				active: 'dt-button-active',
				disabled: 'is-disabled',
				spacer: {
					className: 'dropdown-divider',
					tag: 'hr'
				}
			},
			closeButton: false,
			container: {
				className: 'dt-button-collection dropdown-menu',
				content: {
					className: 'dropdown-content'
				}
			}
		},
		split: {
			action: {
				tag: 'button',
				className: 'dt-button-split-drop-button button is-light',
				closeButton: false
			},
			dropdown: {
				tag: 'button',
				dropHtml: '<i class="fa fa-angle-down" aria-hidden="true"></i>',
				className: 'button is-light',
				closeButton: false,
				align: 'split-left',
				splitAlignClass: 'dt-button-split-left'
			},
			wrapper: {
				tag: 'div',
				className: 'dt-button-split dropdown-trigger buttons has-addons',
				closeButton: false
			}
		}
	},
	buttonCreated: function (config, button) {
		// For collections
		if (config.buttons) {
			// Wrap the dropdown content in a menu element
			config._collection = $('<div class="dropdown-menu"/>').append(config._collection);

			// And add the collection dropdown icon
			$(button).append(
				'<span class="icon is-small">' +
					'<i class="fa fa-angle-down" aria-hidden="true"></i>' +
					'</span>'
			);
		}

		return button;
	}
});


export default DataTable;
