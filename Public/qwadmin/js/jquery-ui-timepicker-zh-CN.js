/* Simplified Chinese translation for the jQuery Timepicker Addon /
/ Written by Will Lu */
(function($) {
	$.timepicker.regional['zh-CN'] = {
		timeOnlyTitle: '选择时间',
		prevText: "上月", 
		nextText: "下月",
		monthNames: ["01","02","03","04","05","06",
			"07","08","09","10","11","12"],
		    monthNamesShort: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
		timeText: '时间',
		hourText: '小时',
		minuteText: '分钟',
		secondText: '秒钟',
		millisecText: '微秒',
		microsecText: '微秒',
		timezoneText: '时区',
		currentText: '当前时间',
		closeText: '关闭',
		timeFormat: 'HH:mm:ss',
		dateFormat: 'yy-mm-dd',
		amNames: ['AM', 'A'],
		pmNames: ['PM', 'P'],
		dayNames: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"], // For formatting
		dayNamesShort: ["日", "一", "二", "三", "四", "五", "六"], // For formatting
		dayNamesMin: ["日", "一", "二", "三", "四", "五", "六"], // Column headings for days starting at Sunday
		isRTL: false,
		changeMonth: true,
		changeYear: true,
		yearRange: '2000:2100'
	};
	$.timepicker.setDefaults($.timepicker.regional['zh-CN']);
})(jQuery);
