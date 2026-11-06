(function () {
	var root = document.getElementById('ba-app');
	if (!root || !window.BA) return;
	var S = window.BA.strings, body = document.body;
	var wizard = root.getAttribute('data-wizard') === '1';
	var steps = [].slice.call(root.querySelectorAll('.ba-step'));
	var dots = [].slice.call(root.querySelectorAll('.ba-dots li'));
	var back = root.querySelector('.ba-back'), next = root.querySelector('.ba-next'), fin = root.querySelector('.ba-finish');
	var cur = 0;

	function setLang(l) {
		root.setAttribute('dir', (l === 'fa' || l === 'ar') ? 'rtl' : 'ltr');
		root.setAttribute('lang', l);
		[].forEach.call(root.querySelectorAll('[data-i18n]'), function (el) {
			var k = el.getAttribute('data-i18n');
			if (k === 'finish' || k === 'save') { if (!wizard) k = 'save'; else k = 'finish'; el.setAttribute('data-i18n', k); }
			if (S[l] && S[l][k]) el.textContent = S[l][k];
		});
	}
	function setClass(prefix, v) {
		body.className = body.className.replace(new RegExp('\\s*\\b' + prefix + '-\\S+', 'g'), '') + ' ' + prefix + '-' + v;
	}
	function show(i) {
		cur = i;
		steps.forEach(function (s, n) { s.hidden = wizard && n !== i; });
		dots.forEach(function (d, n) { d.className = n === i ? 'on' : (n < i ? 'ok' : ''); });
		if (wizard) {
			back.hidden = i === 0;
			next.hidden = i === steps.length - 1;
			fin.hidden = i !== steps.length - 1;
		} else { back.hidden = true; next.hidden = true; fin.hidden = false; }
	}

	root.addEventListener('change', function (e) {
		var t = e.target;
		if (t.name === 'lang') setLang(t.value);
		if (t.name === 'style') setClass('ba-style', t.value);
		if (t.name === 'color') setClass('ba-color', t.value);
	});
	next.addEventListener('click', function () { if (cur < steps.length - 1) show(cur + 1); });
	back.addEventListener('click', function () { if (cur > 0) show(cur - 1); });
	show(0);
})();
