=== Google Doc Embedder ===
Contributors: k3davis
Tags: doc, docx, pdf, ppt, pptx, xls, psd, zip, rar, tiff, ttf, office, powerpoint, google
Author URI: http://www.davistribe.org/code/
Donate link: http://www.davistribe.org/gde/donate/
Requires at least: 3.2
Tested up to: 3.5
Stable tag: trunk
License: GPLv2 or later

Lets you embed MS Office, PDF, and many other file types in a web page using the Google Docs Viewer (no Flash or PDF browser plug-ins required).

== Description ==

Google Doc Embedder lets you embed several types of files into your WordPress pages using the Google Docs Viewer - allowing inline viewing (and optional downloading) of the following file types, with no Flash or PDF browser plug-ins required:

* Adobe Acrobat (PDF)
* Microsoft Word (DOC/DOCX*)
* Microsoft PowerPoint (PPT/PPTX*)
* Microsoft Excel (XLS/XLSX*)
* TIFF Images (TIF, TIFF)
* Apple Pages (PAGES)
* Adobe Illustrator (AI)
* Adobe Photoshop (PSD)
* Autodesk AutoCad (DXF)
* Scalable Vector Graphics (SVG)
* PostScript (EPS/PS)
* OpenType/TrueType Fonts (OTF, TTF)
* XML Paper Specification (XPS)
* Archive Files (ZIP/RAR)

Similar to services like Scribd, Google Doc Embedder will allow you to embed these files directly into your page or post, not requiring
the user to have Microsoft Word, Adobe Reader, PowerPoint, or other software installed to view the contents. Unlike Scribd, the files do
not need to be uploaded to any service first - including Google Docs - but can exist anywhere publicly accessible on your site or the
internet.

Office XML (2007+) file formats are sometimes problematic with Google Viewer. Please test your documents, and when possible I recommend
you use the Office 2003 equivalent formats instead.

Note: Use of this plug-in implies your agreement with Google's published [Terms of Service](http://docs.google.com/viewer/TOS?hl=en "Terms of Service").

Translations are welcome; see the [web site](http://www.davistribe.org/gde/notes/#translate "web site") for details.

* English (en\_US), built-in
* Czech (cs\_CZ) by Jirka, thanks! (update needed)
* Dutch (nl\_NL) by Robert van den Breemen, thanks!
* French (fr\_FR) by [Erwan](http://profiles.wordpress.org/erwanlescop "Erwan"), thanks!
* Hungarian (hu\_HU) by [szemcse](http://profiles.wordpress.org/szemcse "szemcse"), thanks!
* Russian (ru\_RU) by J&#243;zek, thanks!
* Spanish (es\_ES) by [elarequi](http://elarequi.com/propuestastic/ "elarequi"), thanks!
* Turkish (tr\_TR) by [LettoBlog](http://profiles.wordpress.org/lettoblog "LettoBlog"), thanks!
* Ukrainian (uk) by J&#243;zek, thanks!

== Installation ==

1. Upload the entire `google-document-embedder` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Done.

Upload the documents to your site using the media upload facility built into WordPress, via FTP, or link to documents on another (public)
site. Use the Media Library or Google Doc Embedder button in the Visual editor to build the appropriate shortcode, or use the documentation.

For basic manual instructions, please see the FAQ. For advanced usage, see the [web site](http://www.davistribe.org/gde/usage/ "web site").

Go to "GDE Settings" (under "Settings" in the admin panel) to change defaults, or override individually using the shortcode syntax in the FAQ.

== Frequently Asked Questions ==

= Where can the files live? =
The file to embed must first be publicly available somewhere on the internet, in order for Google to retrieve the document for conversion.
You can upload it to your WordPress site using the standard techniques, or link to a file on another site.

= How do I embed a file in my page or post? =
There are several ways you can insert a supported document, depending on your preference:

* Manually enter the shortcode (explained below).
* Upload a supported file type from a page or post, and from the Media Library, click the "Insert" button.
* Use the Google Doc Embedder button in the Visual editor to insert the `[gview]` shortcode.
* Paste the URL into the HTML editor, select it, and click the "GDE" quicktag button (HTML/Text editor).

To manually insert the `[gview]` shortcode into your page or post to embed the file, use the syntax below (use of the HTML tab in the editor
recommended):

`[gview file="http://url.to/file.pdf"]`

Note: the `file=` attribute (generally pointing to the full URL of the file) is **required**. If the majority of your files are referenced
from the same directory, you can set a File Base URL in GDE Settings and only put the changing portion in the `file=` attribute (or a full
URL for a file outside of that base URL). File Base URL will be prepended to the value of `file=` unless `file=` starts with `http` or `//`
(dynamic protocol selection).

Common optional attributes:

* `profile=` : Enter the number or name of the desired profile for the viewer to use (default profile is used if not specified)
* `width=` : To override the profile's default width of the viewer, enter a new width value - e.g., "400px" or "80%"
* `height=` : To override the profile's default height of the viewer, enter a new height value - e.g., "400px" or "80%"
* `page=` : Set to the number of the page you want the document to open up to (if not page 1)

For a list of all available attributes, see [Usage](http://www.davistribe.org/gde/usage/ "Usage").

= What are "Profiles"? =
Profiles allow you to create a unique batch of settings and access them from the viewer using only a profile ID (or name), rather than 
writing a horrifically complicated shortcode. This allows each instance of GDE (even on the same page) to be completely customizable while
keeping the shortcode syntax simple.

= Will it embed files that are password-protected or stored in protected folders/sites? =
Most likely, no. If your file requires a login to view - such as being saved in a password-protected directory, or behind a firewall
(on your intranet, etc.), the viewer will probably not be able to access the file. This is what is meant above, that the document should
be "publicly available." Please save the file in a publicly accessible location for best results.

= What about private documents? =
The file must be publically available, but there is no reason why you need to publish the location. With GDE you can hide the URL as well
as block direct downloads of the file. In combination with robots.txt and other mechanisms for blocking search engines or file browsing on
your site, the document can be effectively private to everyone but the viewer itself.

= Does it work with files saved in Google Docs/Drive? =
This plug-in utilizes the viewer from Google Docs in a standalone fashion. There is no direct integration with Google Docs and even those
documents stored there and shared publicly do not embed reliably with their standalone viewer (ironically), so at this time that use is not
supported by the plug-in. Please store your original documents somewhere on your web site in their native supported formats.

= Does it work in Multisite environments? =
Yes, though the plugin does not support network activation at this time. For now, please activate individually on muultisite installs.
I will work to improve this in a coming version. Otherwise, more granular multisite options are planned for future versions based on demand.
If you use GDE in a multisite environment, I welcome your feedback on what functionality you would like to see.

= Other Common Questions =
More common questions are answered on the GDE web site [here](http://www.davistribe.org/gde/notes/ "Notes").

== Screenshots ==

1. Default appearance of embedded viewer (cropped)
2. Enhanced viewer toolbar showing option to view full screen in same window
3. Enhanced viewer colors can be customized using basic settings or your own CSS file. This "dark" template is included as an example.
4. Preview of the settings page (portion of profile edit page)
5. TinyMCE Editor integration

== Changelog ==

(E) Enhanced Viewer

= 2.5.6 =
* Fixed: (E) Hangs on document load due to Google code changes
* Fixed: (E) Profile language not honored on New Window button tip (thanks Ege)
* Fixed: New profiles appear blank on initial load
* Fixed: HTTP status code check is too stringent for some servers
* Fixed: Spurious database error on deactivation if log table not present
* Fixed: PHP error when API key retrieval fails

= 2.5.5 =
* Added: (E) Option to restrict full screen view to logged-in users
* Fixed: "Unable to load profile settings" on some installs
* Fixed: Rate limit exceeded on is.gd shortened URLs (thanks Dan)
* Fixed: Some settings remain in database after uninstall
* Fixed: Dimensions not sanitized in editor dialog
* Changed: Insert from Media Library ability restored for WP 3.5+

= 2.5.4 =
* Fixed: Force download option gives PHP error (thanks Zverina)
* Fixed: Force download breaks on filenames with multiple extensions
* Fixed: Numeric-only profile names accepted (conflict with IDs)
* Fixed: Can't validate redirected URLs (thanks onesoftindiana)
* Fixed: Duplicating table indexes when logging enabled

= 2.5.3 =
* Fixed: Unsupported file type regression interferes with some uploads

= 2.5.2 =
* Fixed: Fatal error with certain plugins and themes
* Changed: Network activation halted for now (multisite single activation OK)
* Changed: Removed reliance on web services for file type recognition
* Changed: Multisite database improvements
* Changed: Text domain now called on the appropriate hook

= 2.5.1 =
* Added: Russian translation (thanks J&#243;zek)
* Added: Dutch translation (thanks Robert)
* Fixed: Default profile not created on clean install (thanks Clifford)
* Fixed: Viewer size from shortcode doesn't default to pixel
* Fixed: Erroneous error message when file validation is blocked

= 2.5 =
* Added: "Profiles" allow each viewer instance to have its own settings
* Added: (E) Private document support (block downloads of source file)
* Added: (E) Customize viewer color scheme
* Added: (E) Full toolbar customization, including removal
* Added: Backup/Import of settings and viewer profiles
* Added: page= shortcode attribute to start viewer on designated page
* Added: Beta delivery API for automatic updates of pre-release versions
* Added: Media Library and editor integration improvements
* Added: Support for dynamic protocol document links (thanks Clifford)
* Added: French translation (thanks Erwan)
* Added: Ukrainian translation (thanks J&#243;zek)
* Fixed: Uses WordPress HTTP API instead of cURL etc. throughout
* Fixed: (E) Hidden toolbar buttons still narrowly clickable (thanks rohan)
* Fixed: Editor dialog and default base URL with non-standard include
* Fixed: File validation fails if content-length missing (thanks paulod)
* Fixed: Invalid HTML in support form
* Changed: Completely rewritten core and administrative interface
* Changed: (E) Improved default viewer toolbar style
* Changed: Now requires WordPress 3.2+ (due to necessary PHP5 functions)
* Changed: Errors now show inline instead of as HTML comments by default
* Removed: force= shortcode attribute (redundant and confusing)

= 2.4.6 =
* Fixed: Error in Mask URL download link for non-PDF file types

= 2.4.5 =
* Fixed: Regression breaks some files containing spaces (thanks mlautens)
* Fixed: Mask URL 400 error on filenames with spaces (thanks mrhaanraadts)
* Fixed: PDF Force Download option doesn't support SSL

= 2.4.4 =
* Added: PPS and OTF support
* Fixed: Broken support of international filenames in IE (thanks beredim)
* Fixed: More robust file size checking with nonstandard filenames
* Fixed: Global disable cache option not always honored
* Fixed: (E) Mobile theme not loaded if not globally requested
* Changed: Now requires WordPress 3.0+ (mainly for support reasons)

= 2.4.3 =
* Added: (E) Dark theme shortcode option (EXPERIMENTAL)
* Added: Turkish translation (thanks LettoBlog)
* Fixed: Visual editor integration for IIS webhosts (thanks Kristof)
* Changed: Debug information is now a support page from plugin list

= 2.4.2 =
* Fixed: PHP Warning related to MIME type expansion (thanks Adebayo)

= 2.4.1 =
* Added: Spanish translation (thanks elarequi)
* Added: Method to obtain debug information
* Fixed: Insertion of non-GDE file types from Media Library

= 2.4 =
* Added: Allow native upload/insert of all supported file types
* Added: Shortcode inserted from Media Library for supported files
* Added: Localization support (translations welcome)
* Added: (E) Ability to use mobile theme
* Fixed: (E) Toolbar customization on mobile
* Fixed: Editor integration no longer loads its own TinyMCE/jquery libs
* Fixed: URL changes for plugin, help links, beta checking
* Fixed: (E) "Moved Temporarily" error (thanks webmonkeywatts)

[Full history...](http://www.davistribe.org/gde/changelog/ "Full history")

== Upgrade Notice ==

= 2.5.6 =
Bug fix release