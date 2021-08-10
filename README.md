Contao Address Verification
===========================

This extension allows you to manage addresses in the back end which can then be verified against in the front end. 

The extension provides a module where front end visitors can enter their address. This address will be verified against the stored addresses and different content will then be shown (or different redirects will be executed), depending on the verification result. The module also provides a simple autocomplete script by default. All the entered addresses in the back end will be provided for autocompletion on the _street_ input field of the address. The chosen autocompleted result will be filled in in all input fields (street, number, apartment, city and country).

## Address Management

Addresses are divided by _Address Groups_. Each address group has a name and you can also optionally define custom content via [nodes](https://github.com/terminal42/contao-node) that will be display when an address has been verified for the selected groups. Each address currently consists of the following properties:

* Street
* Number (house number)
* Apartment (apartment number or living unit)
* Postal code
* City (_Note:_ only the postal code will be used for verification)
* Country (can optionally be included in the verification)

When editing an address group you also have the possibility to import addresses in bulk through a CSV file. The CSV file needs to have the following format:

```
street,number,apartment,postal code,city,country
```

The country, or city and country together, can be omitted from the CSV file.

## Front End Modules

The extension currently provides two modules in total: one for the actual address verification and one module to automatically redirect to a redirect page, if no address verification session was started yet.

### Address verification

This module handles the actual address verification process and show the form as well as the subsequent content in the front end. It provides the following settings:

* __Address groups:__ addresses are grouped in the back end, similar to news archives for example. Select the address groups against which the entered address should be verified against.
* __Include country:__ includes the country in the output of the front end form and includes it also for the verification process itself.
* __Nodes:__ content to be shown for either a verified address or an unverified address.
* __Redirect page:__ redirects to the defined page directly, instead of showing the nodes based content.

### Require address verification session

This module provides a possibility to redirect to a defined page in the front end, if no address verification session was started yet, i.e. if the front end visitor did not enter an address via the _Address verification module_ yet. You can use this module to prevent access to certain pages in such a case.

## Session Variable

Once a front end visitor has entered their address, a session will be started and the entered address is available via the `address-verification` session variable. This variable will contain an array consisting of the entered address, which can then be used in other places in your process.

## Modify Form Event

The _Address verification_ module provides an `InspiredMinds\ContaoAddressVerification\Event\BuildAddressVerificationFormEvent` event with which you can modify the form based on [`Haste\Form\Form`](https://github.com/codefog/contao-haste/blob/master/docs/Form/Form.md). You can use this event to add additional form fields or validators etc. to the form.

## Autocompletion

This extension integrates [autoComplete.js](https://tarekraafat.github.io/autoComplete.js/) for autocompletion, but does not integrate any of its CSS styles by default, as you need to take care of styling yourself. As a starting point you could download the _autoComplete.js_ package and use one of its stylesheets for your own purposes. The stylesheets are also present under `web/bundles/contaoaddressverification/css/`, so you could also add the following code to a custom `mod_address_verification` template (or elsewhere):

```php
// templates/mod_address_verification.html5
$GLOBALS['TL_CSS'][] = 'bundles/contaoaddressverification/css/autoComplete.02.css';
$this->extend('mod_address_verification');
```

Likewise, if you want to customize the JavaScript itself or the initialisation of the script, create your own copy of the `mod_address_verification` template and modify the JavaScript accordingly.
