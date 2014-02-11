// domready https://github.com/ded/domready
!function(a,ctx,b){typeof module!="undefined"?module.exports=b():typeof define=="function"&&typeof define.amd=="object"?define(b):ctx[a]=b()}("domready",this,function(a){function m(a){l=1;while(a=b.shift())a()}var b=[],c,d=!1,e=document,f=e.documentElement,g=f.doScroll,h="DOMContentLoaded",i="addEventListener",j="onreadystatechange",k="readyState",l=/^loade|c/.test(e[k]);return e[i]&&e[i](h,c=function(){e.removeEventListener(h,c,d),m()},d),g&&e.attachEvent(j,c=function(){/^c/.test(e[k])&&(e.detachEvent(j,c),m())}),a=g?function(c){self!=top?l?c():b.push(c):function(){try{f.doScroll("left")}catch(b){return setTimeout(function(){a(c)},50)}c()}()}:function(a){l?a():b.push(a)}});

domready(function() {

// jquery-cookie https://github.com/carhartl/jquery-cookie
(function(e){if(typeof define==="function"&&define.amd){define(["jquery"],e)}else{e(jQuery)}})(function(e){function n(e){return e}function r(e){return decodeURIComponent(e.replace(t," "))}function i(e){if(e.indexOf('"')===0){e=e.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\")}try{return s.json?JSON.parse(e):e}catch(t){}}var t=/\+/g;var s=e.cookie=function(t,o,u){if(o!==undefined){u=e.extend({},s.defaults,u);if(typeof u.expires==="number"){var a=u.expires,f=u.expires=new Date;f.setDate(f.getDate()+a)}o=s.json?JSON.stringify(o):String(o);return document.cookie=[s.raw?t:encodeURIComponent(t),"=",s.raw?o:encodeURIComponent(o),u.expires?"; expires="+u.expires.toUTCString():"",u.path?"; path="+u.path:"",u.domain?"; domain="+u.domain:"",u.secure?"; secure":""].join("")}var l=s.raw?n:r;var c=document.cookie.split("; ");var h=t?undefined:{};for(var p=0,d=c.length;p<d;p++){var v=c[p].split("=");var m=l(v.shift());var g=l(v.join("="));if(t&&t===m){h=i(g);break}if(!t){h[m]=i(g)}}return h};s.defaults={};e.removeCookie=function(t,n){if(e.cookie(t)!==undefined){e.cookie(t,"",e.extend({},n,{expires:-1}));return true}return false}});

var ab = {

	cookyName: 'ab',
	cookyInfo: {
		domain: '', // .example.com add leading dot to use cookies accross subdomains
		path: '/',
		expires: 365
	},
	tests: [],
	idVers: [],
	vers: [],

	init: function(data) {

		if (typeof data === 'undefined')
			return false;

		this.tests = JSON.parse(data);

		if (this.tests.length === 0) {
			this.removeTestCookie(this.cookyName);
			return;
		}

		this.tests = this.applyTestIndex(this.tests);
		this.tests = this.getTotalVersions(this.tests);
		this.idVers = this.setIDVersionTotal(this.tests);

		this.vers = this.selectVersions(this.idVers);
		this.idVers = this.getTestCookie(this.cookyName);
		this.idVers = this.mergeCookyVers(this.idVers, this.vers);
		this.applyTests(this.idVers, this.tests);
		this.setTestCookie(this.cookyName, this.idVers);
	},

	settings: function(obj) {
		obj = $.parseJSON(obj);
		for (o in obj) {
			if (o != '' && obj[o] != '') {
				ab[o] = obj[o];
			}
		}
	},

	applyTestIndex: function(tst) {
		for (var i = 0; i < tst.length; i++) {
			tst[i].tstIndex = i + 1;
		}
		return tst;
	},

	removeTestCookie: function(cooky) {
		$.removeCookie(cooky, ab.cookyInfo);
	},

	getTotalVersions: function(tst) {
		for (var i = 0; i < tst.length; i++) {
			tst[i]['vers'] = tst[i]['versions'].length;
		}
		return tst;
	},

	selectVersions: function(vers) {
		// randomly select which version to use for this user
		var randver = 0,
			min = 1,
			max = 0;

		for (var i = 0; i < vers.length; i++) {
			max = vers[i][1];
			randver = Math.floor(Math.random() * (max - min + 1) + min);
			vers[i][1] = randver;
		}
		return vers;
	},

	setIDVersionTotal: function(tst) {

		var idVers = [];

		for (var i = 0; i < tst.length; i++) {
			idVers[i] = [1 * tst[i].id, tst[i].vers];
		}
		
		return idVers;
	},

	getTestCookie: function(cookyName) {

		var	cooky = [];

		if (typeof $.cookie(this.cookyName) !== 'undefined') {
			cooky = this.parseIdVers('get', $.cookie(cookyName));
		}

		return cooky;
	},

	setTestCookie: function(cookyName, idVers) {

		idVers = this.parseIdVers('set', idVers);

		$.cookie(cookyName, idVers, ab.cookyInfo)
	},

	parseIdVers: function(action, idVers) {
		var i = 0,
			tests = [];
		
		if (action == 'get') {
			idVers = idVers.split(';');

			for (var i = 0; i < idVers.length; i++) {
				idVers[i] = idVers[i].split(':');
				tests[i] = [1 * idVers[i][0], 1 * idVers[i][1]];
			}

		} else if (action == 'set') {

			for (var i = 0; i < idVers.length; i++) {
				idVers[i] = idVers[i].join(':');
			}

			tests = idVers.join(';');
		}
		return tests;
	},

	mergeCookyVers: function(cooky, vers) {

		var newCooky = [],
			index = 0,
			idFound = false;

		for (var i = 0; i < vers.length; i++) {

			for (var j = 0; j < cooky.length; j++) {

				// check if cooky has existing test id and version
				if (vers[i][0] === cooky[j][0])
					idFound = true;

				// if id & version found, keep current stored version
				if (idFound) {
					newCooky[index] = [cooky[j][0], cooky[j][1]];
					index++;
					break;
				}
			}

			// if no id is found, add new random version
			if (idFound === false) {
				newCooky[index] = [vers[i][0], vers[i][1]];
				index++;
			}

			idFound = false;
		}
		return newCooky;
	},

	applyTests: function(idVers, tests) {
		for (var i = 0; i < idVers.length; i++) {				
			for (var j = 0; j < tests.length; j++) {
				if (idVers[i][0] === 1 * tests[j].id) {
					this.fireTest(tests[j], idVers[i][1]);
				}
			}
		}
	},

	fireTest: function(tst, vers) {
		var analytics = false;

		if (tst.active === false)
			return false;

		// analytics = true is set from within the eval statement
		try {
			eval(tst.versions[vers - 1]);
		} catch (e) {
			analytics = false;
		}

		if (tst.winner > 0) {
			vers = tst.winner;
			analytics = false;
		}

		if (analytics) {
			this.fireAnalytics(tst, vers);
		}
	},

	fireAnalytics: function(tst, vers) {
		var ga_dim = {};
		if (typeof _gaq !== 'undefined') {
			_gaq.push(['_setCustomVar', tst.tstIndex, 'AB: ' + tst.test_name, tst.versNames[vers - 1], 2]);
			_gaq.push(['_trackEvent', 'AB Testing', 'Test View', tst.test_name + ' - ' + tst.versNames[vers - 1]]);

		} else if (typeof ga === 'function' && typeof ab.ga_dim !== 'undefined'){
			ga('set', ab.ga_dim, tst.test_name + ' - ' + tst.versNames[vers - 1]);
			ga('send', 'event', 'AB Testing', 'Test View');
		}
	}
};