=== Cowpay - WooCommerce Payment Gateway ===

Contributors:  nawrasmansour
Tags: cowpay payment getway, Extends WooCommerce by Adding the Cowpay payment Gateway.
Author URI:   https://cowpay.me
Author:       Cowpay
Donate link: https://cowpay.me
Requires at least: 4.0
Tested up to: 4.8
Requires PHP: 5.6
Stable tag: 11.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

cowpay payment getway, Extends WooCommerce by Adding the Cowpay payment Gateway.

== Description ==

COWPAY is a premium payment technology enabler dedicated to helping businesses transform   
their operation collecting , splitting , and disbursing money digitally!

Payment Options : Have multiple of payment options available for your customers from Cash,  to Cards

Cowpay offers the following Environment options :

   1. Staging Environment
   	Staging is your initial account environment on which you must do your testing 	    

    	  operations without your cards being charged.
   2. Staging Environment


Cowpay's API enables merchants to build native payment solutions on top of it.
  You can charge your customers using these methods

   1. Credit Card
   2. Pay At Fawry
	
== Installation ==
   1. first of all, you must install the WooCommerce plugin
   2. Upload "test-plugin.php" to the "/wp-content/plugins/" directory.

== Frequently Asked Questions ==
= How to generate signature? =
Signatures are validation tokens used by cowpay to insure the identity of the merchant beside authentication tokens starting from API v1


= What is merchant_reference_id Key? =
Cowpay requires a unique id for each charge request from the merchant as each charge request represents a separated order on our system. You can use numbers, strings or combination of both.

= How to get customer_merchant_reference_id? =
Cowpay requires customer being charged id on the merchant system. It's value valid format is the same of merchant_reference_id.

= How to go live within my account? =
Your initial account status is staging which means no actual amounts being paid or charged based on your requests. In order to go live please contact one of our business team, and they will take care of that transmission.


== Screenshots ==
1. The screenshot description corresponds to screenshot-1.(png|jpg|jpeg|gif).


== Changelog ==

= 11.0 =
* Initial release.

== Upgrade Notice ==

= 11.0 =
This version is the Initial release.
