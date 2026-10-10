(function (wp) {
	'use strict';

	if (!wp || !wp.plugins || !wp.editPost) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginSidebar = wp.editPost.PluginSidebar;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;

	function CatNapPanel() {
		var typingState = useState(0);
		var typingSeconds = typingState[0];
		var setTypingSeconds = typingState[1];

		var breakState = useState(false);
		var onBreak = breakState[0];
		var setOnBreak = breakState[1];

		var breakTimerState = useState(0);
		var breakSeconds = breakTimerState[0];
		var setBreakSeconds = breakTimerState[1];

		useEffect(function () {
			var interval = setInterval(function () {
				if (onBreak) {
					return;
				}
				setTypingSeconds(function (s) {
					if (s >= 299) {
						setOnBreak(true);
						setBreakSeconds(300);
						return 0;
					}
					return s + 1;
				});
			}, 1000);
			return function () {
				clearInterval(interval);
			};
		}, [onBreak, setTypingSeconds, setOnBreak, setBreakSeconds]);

		useEffect(function () {
			if (!onBreak) {
				return undefined;
			}
			var t = setInterval(function () {
				setBreakSeconds(function (s) {
					if (s <= 1) {
						setOnBreak(false);
						return 0;
					}
					return s - 1;
				});
			}, 1000);
			return function () {
				clearInterval(t);
			};
		}, [onBreak, setBreakSeconds, setOnBreak]);

		useEffect(function () {
			document.body.classList.toggle('caas-control-cat-nap-lock', onBreak);
		}, [onBreak]);

		return el(
			PluginSidebar,
			{
				name: 'caas-control-cat-nap',
				title: 'Cat Nap Timer',
				icon: 'pets',
			},
			el(
				PanelBody,
				{ initialOpen: true },
				el('p', null, 'After 5 minutes of typing, the cat demands a 5-minute stretch break.'),
				onBreak
					? el('p', null, 'Nap time: ' + breakSeconds + 's remaining')
					: el('p', null, 'Typing streak: ' + typingSeconds + 's'),
				el(
					Button,
					{
						isPrimary: true,
						disabled: !onBreak,
						onClick: function () {
							setOnBreak(false);
							setBreakSeconds(0);
							setTypingSeconds(0);
						},
					},
					'Wake the cat (skip break)'
				)
			)
		);
	}

	registerPlugin('caas-control-cat-nap', {
		render: CatNapPanel,
	});
})(window.wp);
