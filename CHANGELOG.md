## Package: iqnection-silverstripe-3-pages-basepages
# Change Log


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