/*! Foundation integration for DataTables' Buttons
 * © SpryMedia Ltd - datatables.net/license
 */

import jQuery from 'jquery';
import DataTable from 'datatables.net-zf';
import Buttons from 'datatables.net-buttons';

// Allow reassignment of the $ variable
let $ = jQuery;


$.extend(true, DataTable.Buttons.defaults, {
	dom: {
		container: {
			tag: 'div',
			className: 'dt-buttons button-group'
		},
		button: {
			tag: 'a',
			className: 'dt-button button small',
			active: 'secondary active'
		},
		collection: {
			action: {
				dropHtml: ''
			},
			button: {
				tag: 'li',
				className: '',
				active: 'dt-button-active-a',
				liner: {
					tag: 'a'
				}
			},
			container: {
				tag: 'div',
				className: 'dt-button-collection',
				content: {
					tag: 'ul',
					className: 'dropdown menu is-dropdown-submenu'
				}
			}
		},
		split: {
			action: {
				tag: 'button',
				className: 'button small'
			},
			dropdown: {
				tag: 'button',
				className: 'button dropdown arrow-only',
				dropHtml: ''
			},
			wrapper: {
				tag: 'div',
				className: 'button-group dt-button-split'
			}
		}
	}
});

DataTable.ext.buttons.collection.className = 'dropdown';

$(document).on('buttons-popover.dt', function () {
	var notButton = false;
	$('.dtsp-panesContainer').each(function () {
		if (!$(this).is('button')) {
			notButton = true;
		}
	});
	if (notButton) {
		$('.dtsp-panesContainer').removeClass('button-group stacked');
	}
});


export default DataTable;
