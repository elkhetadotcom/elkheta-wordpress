=== Plugin Name ===
Contributors: vibhavsinha, milangupta4
Tags: video, DRM, video plugin, sell video, e-learning, movie
Requires at least: 3.5.1
Tested up to: 5.7
Stable tag: 1.26
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple wordpress plugin which enables you to embed VdoCipher videos inside a WordPress website.

== Description ==

VdoCipher video plugin enables you to host premium video content on your WordPress website. VdoCipher's Video Plugin gives you complete control over your video content, so you can start minting money out of your awesome videos.

With VdoCipher's WordPress video plugin you get the highest protection from video piracy. Our video streaming service involves Video Encryption, Backend Authentication & Watermarking. This video encryption technology makes sure that no downloader or plugin can download your videos.

= Perfect Choice for Premium Video Content =
VdoCipher is the perfect choice for premium video content, such as lecture videos, music, and movies. VdoCipher WordPress video plugin seamlessly integrates with all popular WP membership plugins, including *Members*, *Restrict Content Pro*, *MemberPress* and *WP eMember*. The video plugin also works perfectly with top Learning Management systems such as *Sensei*, *LearnDash*, *WP CourseWare*, *LifterLMS* and *LearnPress*

= Makes Embedding Secure Videos as Easy as Copying URLs =
It would take you at most 10 minutes to signing up on [VdoCipher](https://www.vdocipher.com) and install the video plugin. You can embed videos to your website using a one-line shortcode.

= Getting started =
To get started, you should create an account on [VdoCipher](https://www.vdocipher.com). You will then find an API secret key in the Config section of the dashboard. You will need this API secret key to use the video plugin. You can upload vidoes to the VdoCipher dashboard, or import directly from URL or Dropbox.

= Multi-Bitrate Video Streaming =
With VdoCipher video plugin your users get the choice to watch low bitrate videos to optimize their data usage. Our encoding optimizations ensure that videos use the lowest bandwidth for high quality videos.

[youtube https://www.youtube.com/watch?v=bGJLs6VOvAM]

= Requirements =
php5-curl need to be installed on the server for this plugin to work.

= Choose from different video player themes =
You can add your own custom-made player skin on top of our video player. The themes gallery in the plugin enables you to change player skin and color.

= Additional Resources =
[VdoCipher's video encryption technology](https://www.vdocipher.com/blog/2016/08/encrypted-video-streaming-vdocipher-technology-details/)
[VdoCipher's complete feature set](https://www.vdocipher.com/blog/2016/12/video-hosting-for-business/)
[Watermarking with the WordPress video plugin](https://www.vdocipher.com/blog/2014/12/add-text-to-videos-with-watermark/)
[Choose from Custom Player Themes](https://www.vdocipher.com/blog/2018/10/video-player-themes/)

== Installation ==
1. Activate the "VdoCipher" plugin .
2. Click on the settings link or go to Settings > VdoCipher to configure.
3. Enter the API key that you received from VdoCipher and click Save.

Your video plugin is ready to use. Inside a post or page you can write `[vdo id="id_of_video"]` to embed the video in a post or page.

To set width and height use, `[vdo id="id_of_video" width="300" and height="200"]`

== Frequently Asked Questions ==
Please refer to the [FAQ page on VdoCipher](https://www.vdocipher.com/page/faq)

= Is there a free trial? =
On account creation, you shall be provided with 5GB of free trial bandwidth.

== Screenshots ==

1. Select from different player themes
2. The setting screen to to enter the API key.
3. The options page
4. Using the shortcode to embed a video
5. Video playing inside a post.

== Changelog ==

= 1.25 =
* Added gutenberg block support
* More themes
* auto upgrade of player version
* Fixed undefined notice message
* Fixed some more bugs
* Better handling of video aspect ratio
* Fairplay support in player
* detailed analytics support in wordpress

= 1.24 =
* Bug fixes

= 1.23 =
* Added player themes page

= 1.22 =
* Added vdo_theme attribute to vdo shortcode

= 1.21 =
* HTML5 watermark for custom version 1.6.4
* User can opt for Flash watermark globally
* User can add custom player version
* Height change to auto for player versions more than 1.5.0
* Tested for PHP version 5.6 and above

= 1.20 =
* default player version set to 1.5.0
* corrected bugs
* height auto available
* player tech over-ride enabled to play exclusively html5, flash, zen player

= 1.19 =
* add new player

= 1.17 =
* updated player theme

= 1.16 =
* more documentation
* updated player

= 1.15 =
* fixed bugs for older php versions

= 1.14 =
* add new player version 1.1.0

= 1.13 =
* New player with ability to choose player version
* Add custom themes from theplayer.io

= 1.8 =
Bug fixes

= 1.7 =
* set max height and width as default settings in 16:9 ratio
* use asynchronous code for rendering video player
* watermark date in wp timezone
* use wp transport apis instead of curl

= 1.6 =
* add filter hooks for annotation statement

= 1.3 =
* Compatible with PHP5.2

= 1.0 =
* Annotation can now be set from wordpress dashboard
* Better system for storing client key
* Clear options table of plugin related keys on deactivate
* Include options form to set default options for videos.

= 0.1 =
* A basic plugin which just makes it possible to embed vdocipher videos inside a wordpress plugin

== Upgrade Notice ==

= 1.25 =
* Gutenberg block support
* New themes
* Detailed analytics support
* auto player version upgrade

= 1.24 =
* Bug fixes

= 1.23 =
* Added player themes page

= 1.22 =
* bug fixes and security update
* Added vdo_theme attribute to vdo shortcode

= 1.21 =
* HTML5 watermark for custom version 1.6.4
* User can opt for Flash watermark globally
* User can manually add player version
* Height change to auto for player versions more than 1.5.0
* Tested for PHP version 5.6 and above

= 1.20 =
* default player version set to 1.5.0
* corrected bugs
* height auto available
* player tech over-ride enabled to play exclusively html5, flash, zen player

= 1.17 =
* updated player theme

= 1.8 =
Bug fixes

= 1.7 =
* watermark date in wordpress timezone

= 1.6 =
* annotation pre and post process hooks to add content specific custom variables

= 1.5 =
* Multiple videos bug fix

= 1.3 =
* Compatible with PHP5.3

= 1.0 =
* This allows you to set annotation over video.
* No more editing files directly.
