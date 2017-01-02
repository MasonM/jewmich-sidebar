function dateTypeChange() {
	jQuery(this).closest('tr')
		.find('.date-range')
			.toggle(jQuery(this).val() !== '')
			.end()
		.find('select[name^=month_start_Gregorian], select[name^=month_end_Gregorian]')
			.toggle(jQuery(this).val() === 'Gregorian')
			.end()
		.find('select[name^=month_start_Hebrew], select[name^=month_end_Hebrew]')
			.toggle(jQuery(this).val() === 'Hebrew');
}
jQuery('select[name^=date_type]').on('change', dateTypeChange).trigger('change');
