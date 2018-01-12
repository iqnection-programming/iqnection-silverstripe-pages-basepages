## Package: iqnection-silverstripe-3-pages-basepages
# Change Log

## [2.0.4]
- finalized _config.php options (i hope)

## [2.0.3]
- bug fix on Google Maps API config variable in ContactPageController.php

## [2.0.2]
- moved base responsive page styling into appropriate Page.css file
- removed namespacing for page types
- added configurable variables for Google maps integration
- various other updates and bug fixes

## [2.0.1]
- file cleanup

## [2.0.0]
- Updated/Rebuilt for SilverStripe 4

## [1.1.17]
- BlogPage bug fix
- Included CSS merge fix

## [1.1.16]
- various SEO imporvements
- various css changes (back to basics)
- add error_reporting declaration in config

## [1.1.15]
- Added validation for BlogPage url, page URL segment cannot be the same as the actual blog path

## [1.1.14]
- added routing rule to app.yml for generic page caching

## [1.1.3]
- Moved ADMIN function ality to Developer tabs
- Removed ADMIN only permissions on some settings
- Added ability to include submission data in auto responder
- removed ADMIN only permissions to developer fields throught CMS. Moved fields into Developer tab

## [1.1.2]
- Removed SiteConfig data caching, now in seo module
- caching improvements

## [1.1.1]
- Added caching system I've been working on. Site Tree, Header HTML, Footer HTML, and SiteConfig Additional Code is now cached at site-tree.json and template-cache/page-[ID].json.json

## [1.1.0]
- Update for upgraded JS files in IQ installer

## [1.0.25]
- added file extensions to form page file field

## [1.0.24]
- added ability to modify form before sending to template, use public function updateForm($form){} in extension class

## [1.0.23]
- ContactPage.js: Fixed JS error when no address provided
- ContactPage.php: Fixed error when no address provided
- FormPage.php: Added FileField validation for file extensions. use 'AllowedExtensions' => array('ext1','ext2') in file field config
- FormUtilities.php: fixed bug

## [1.0.22]
- Added GA form goal tracking, settings are in page/Settings/Google Form tracknig tab

## [1.0.21]
- FormPage.php page after submit provides the page with the submission object, insterad of the submitted data

## [1.0.20]
- Added option for multiple locations on the ContactPage

## [1.0.19]
- Bug Fix: Date field format causing validation errors

## [1.0.18]
- Added functionality for form page auto responder from email

## [1.0.17]
- Changed AdditionalCode field to Text
- Updated CMS tab names for SS 3.2

## [1.0.16]
- CSS update, changed page_columns from Id attribute to element class

## [1.0.15]
- Form no spam bug on Contact Page
- Added functionality to set config values on form fields when extending FromPage class

## [1.0.14]
- added onAfterSubmit, passes extension class function the submission object for after form submit additions

## [1.0.13]
- moved code editor field back into the installer module in composer requirements

## [1.0.12]
- Bug fixes in FormPage.php

## [1.0.11]
- Export friendly, user private static $export_fields in your form submission class

## [1.0.10]
- Changed security login form email field to title Username

## [1.0.9]
- Changed TemplateCode field to CodeEditorField

## [1.0.8]
- Added validation to FormRecipients in CMS

## [1.0.7.2]
- Updated/Corrected results page
- Added helper function to Form Utilities for generating RequiredFields. This method returns the RequiredFields object needed as the validator, but it also adds the class "required" to each of the required fields, for jQuery validate
- Updated default form field name to friendly name

## [1.0.7.1]
- Added CSS so pages not in menu show differently in CMS site tree (lighter blue)

## [1.0.7]
- Corrected Additional Code so actual code is inserted into template, and not HTML safe code

## [1.0.6]
- Made page CSS, JS, CustomJS & ResponsiveCSS functions extendable

## [1.0.5]
- Added CSS class to verticall stack option set fields. Use $field->addExtraClass('vertical');
- Added license

## [1.0.4]
- Further support for File Fields in Form Pages
- Updated ContactFormSubmission class to extend FormPageSubmission

## [1.0.3]
- Contact page is now has extendable methods FormFields [updateFormField(&$fields)] & FormConfig [use: updateFormConfig(&$config)]

## [1.0.2]
- fixed problems with displaying form errors and saving form data on reload
- support for file upload fields in submission email

## [1.0.1]
- Removed create permissions on ConatactPageSubmission

## [1.0.0]
- First stable build