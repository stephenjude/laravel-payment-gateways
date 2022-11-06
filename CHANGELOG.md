# Changelog

All notable changes to `laravel-payment-gateways` will be documented in this file.

## 1.0.4 - 2022-11-06

- Fixed stripe amount conversion error

## 1.0.3 - 2022-11-06

- Fixed decimal value error

## 1.0.2 - 2022-11-06

- Fixed amount calculation error.

## 1.0.1 - 2022-11-06

- Fixed stripe initialization error due to decimal values.

## 1.0.0 - 2022-08-27

- Ready for production use.
- Fixed failing tests.
- Updated docs.

## 0.1.8 - 2022-08-25

- Fix named parameter error.

## 0.1.7 - 2022-08-11

- Klasha payment gateway integration.
- Improved the fallback error page.

## 0.1.6 - 2022-08-08

- Add callback option for custom code execution after customer payment.
- Parse error messages.
- Add processing status message for delayed confirmation.
- Code improvements.

## 0.1.5 - 2022-07-04

- Bump dependabot/fetch-metadata from 1.3.1 to 1.3.3

## 0.0.4 - 2022-06-14

I would have tagged this significant release with lots of breaking changes, but thanks to God, we still don't have a stable release.

In this release, I reworked the hosted providers to generate the checkout URL from the gateway providers.

Completed the implementation for the following providers:

- Paystack 🔥
- Flutterwave 🔥
- Stripe 🔥

That's all for now, more implementation is coming for Monnify and Paypal 🚀

## 0.1.3 - 2022-06-02

- Increase default timeout to 12 hours

## 0.1.2 - 2022-05-31

- Fix missing debug response

## 0.1.1 - 2022-05-31

- Fix missing debug response

## 0.1.0 - 2022-05-31

- Fix missing debug response

## 0.0.9 - 2022-05-31

- Fix debug mode error

## 00.7 - 2022-05-31

- Updated GitHub Actions
- Updated Readme

## 0.0.6 - 2022-05-31

- Fix config file publishing error

## 0.0.5 - 2022-05-31

Added debug mode for logging HTTP responses.

## 0.0.3 - 2022-05-26

- Allow `null` value for `$channel` property

## 0.0.2 - 2022-05-25

- Clean up
