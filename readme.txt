=== AB Testing with Javascript ===
Contributors: bmorenate
Donate link: https://github.com/etan-nitram/split-testing
Tags: ab testing, ab tests, ab experiments, content experiments
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AB test website content with javascript (no PHP scripting). Run AB experiments without redirecting users to a different URL

== Description ==

A plugin for A/B split-testing HTML/CSS/DOM elements with javascript.  Wordpress, JQuery, and Google Analytics are required.

*	Test changes to your site with Javascript
*	Run up to 5 tests simultaneously (per visitor session)
*	Unlimited versions per test
*	Test info is saved in cookies so returning visitors see the same version of your site
*	Test data is sent to your google analytics account as custom variables
*	No AJAX. Test info and javascript code are served from static `ab.js` file
*	Test info/data is saved in the wordpress table `wp_options`

You can help to contribute to this project on github.com @ [Wordpress AB Testing](https://github.com/etan-nitram/split-testing) 

== Screenshots ==

1.	Enter test code and parameters into wp-admin interface

2.	Test data and results are sent to your google analtyics account. Example: Testing Black or Dark Grey headline colors

== Requirements ==
*	General Wordpress Installation
*	Google Analytics
*	jQuery - available to your wp-admin and client side
*	ability to set file and folder permissions on your server
*	Do not run more than 5 A/B tests per visitor session. Otherwise analytics data will be overwritten or lost
*	Cache control of file `js/ab.js`. This file is rewritten and updated often, so make sure you can update cached versions of this file.

== Installation ==

*	Copy the files from this repo to your server directory `/wp-content/plugins/`
*	In wp-admin, navigate to Plugins and activate 'A/B Split Testing'
*	set server `/js/` directory permissions to 0755 or 0775. Directory must be writable by PHP
*	set server `/js/ab.js` file permissions to 0755 or 0775. File must be writable by PHP
*	link to the file `/js/ab.js` in the header of every page on your website.
`<script src="http://example.com/wp-content/plugins/split-testing/js/ab.js"></script>`
*	Make sure to clear any cache for the `/js/ab.js` file after starting, updating, or stopping tests.

== Javascript ==
*	After your first test is created, you must globally link to the plugin file `js/ab.js` in your wordpress theme.
*	This plugin does not load any external libraries.
*	[jQuery Cookie](https://raw.github.com/carhartl/jquery-cookie/) is included in the `js/ab.js` file.
*	You must load [jQuery](http://jquery.com/) in your wordpress theme to support the jQuery Cookie library.
*	You can load and use any other javascript library you require for your theme and use that library in your A/B tests to manipulate DOM elements during tests.

== Google Analytics ==
*	This plugin uses google analytics to store test data.
*	Data stored is Test Name and Test Version
*	The combination of Test Name and one Test Version Name must be less than 128 characters. Otherwise `_setCustomVar()` will not except all data.
*	`_setCustomVar()` is called just before firing `_trackEvent()` to store test data.
*	Custom Variable applied to track event `_gaq.push(['_setCustomVar', 'Slot', 'AB: ' + 'Test Name', 'Version Name', 2]);` Slot = Test Index
*	Event sent `_gaq.push(['_trackEvent', 'AB Testing', 'Test View', 'Test Name & Version']);`
*	Goals: It is up to you, the webmaster or analytics admin, to set the test goals in your analytics admin settings.
*	Do not run more than 5 A/B tests per visitor session. Otherwise analytics data will be overwritten or lost

Here's further reading about Google Analytics [Custom Variables](https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingCustomVariables), [Analytics Events](https://developers.google.com/analytics/devguides/collection/gajs/eventTrackerGuide), and [Analytics Goals](https://support.google.com/analytics/answer/1032415?hl=en).

When a visitor views a page with a given test running, we use "Session Level" events to track the test view, because this ensures the test data will carry through the session to the conversion goal. From what I understand, "Page Level" custom variables will not work because we are firing an event after page load. Therefore, because the event is fired in the middle of a session, we must use session level custom variables [Read More Here](http://www.kaushik.net/avinash/hits-sessions-metrics-dimensions-web-analytics/)

== A/B Split Test ==
*	All A/B tests are run by manipulating HTML/CSS DOM elements in your wordpress theme with javascript.
*	Depending on how many versions you are testing, all site traffic will be split evenly to show each test version.
*	When a test version is run, the javascript you defined in the plugin admin will execute and show that variation to the user.
*	No Server-Side scripting is used.
*	Any javascript library can be used in your A/B Test, given that you have loaded the library in your wordpress theme.

== Running An A/B Split Test ==

After you have installed the plugin, in the wordpress admin, navigate to `AB Testing` and click `Create New Test`. Enter Test Name, each test Version's Name, and enter your custom javascript in `Version Code` and click `Save Test`. Be sure to include your `analytics=true` flag in all of your test version code snippets.

The combination of Test Name and one Test Version Name must be less than 128 characters. This is because `_setCustomVar` limits character length.

Make sure your server `/js/` directory and file `/js/ab.js` have permissions 0755 or 0775 so PHP can write output to the `ab.js` file.

When you are ready to start your test, click the checkbox `Active`, which will write your new test to the `js/ab.js` file and make your test live to your site visitors.

And don't forget to update any cached versions of `js/ab.js`.

== Example Test Version Code ==

First, decide what elements in your theme HTML you want to test.  Because your `js/ab.js` file is included globally into your theme, your A/B test code will manipulate any DOM elements that meet its conditions.

Therefore, do not run tests on general elements like `<h1>` or `<button>` unless you want to test these changes site wide.  It is better to be highly specific and target your DOM elements with IDs and Classes.

Also, you must remember to include the `analytics=true` flag in your javascript code.

== Example ==

This is how we would test changing the color of `<button class="test_button">Add To Cart</button>` from green to orange.  Let's assume our `button.test_button` is already green in our current theme.  Therefore, green is our control version.

However, since we don't need to change the color for version 1, because it's already green, we must still check to see if `$('button.test_button')` exists in the DOM, and if so, trigger the `analytics=true` flag to send our Custom Variable data to google analytics for the control version.

= Version 1: Javascript Code =

`if ( $('button.test_button').length > 0 ) {
	analytics = true;
}`

= Version 2: Javascript Code =

In version 2 we are changing the color to orange. Once again, we must check if `$('button.test_button')` exists in the DOM.  If it does, we change the button color to orange and trigger the `analytics=true` flag.

`
if ( $('button.test_button').length > 0 ) {
	$('button.test_button').css('background-color', 'orange');
	analytics = true;
}
`

== analytics = true; ==
*	do not override or re-declare the `analytics` variable. It must be available to the `ab.js` file scope and set to true.
*	It is up to you to correctly and logically set the `analytics=true` flag in your javascript code.
*	If you forget to set it to true, no test results (custom variables) will be sent to analytics.
*	If you set it to true in every test, without checking for test page level DOM elements, you will send invalid test data on every website page load.

== Test Results ==
You will find your test data located in Google Analytics Custom Variables Reporting.

Report Navigation: Audience -> Custom -> Custom Variables

== Custom Variables Report ==
You should not have more than 5 tests running at one time.  Or more specifically, no more than 5 tests per visitor session, which is up to you to negotiate.

Depending on how many tests you have running at a given time, your tests will fall into 1 of 5 'slots' in the Custom Variables Report, which are found at;
`Custom Variable (Key 1) Custom Variable (Key 2) Custom Variable (Key 3) Custom Variable (Key 4) Custom Variable (Key 5)`
